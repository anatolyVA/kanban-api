<?php

namespace app\modules\v1\services;

use app\common\interfaces\AuthServiceInterface;
use app\common\models\User;
use app\common\models\UserToken;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class AuthService implements AuthServiceInterface
{
    private TokenService $token_service;

    public function __construct()
    {
        $this->token_service = new TokenService();
    }

    /**
     * @param $data
     * @return string
     * @throws BadRequestHttpException
     * @throws ConflictHttpException
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function register($data): string
    {
        if (User::findByEmail($data['email'])) {
            throw new ConflictHttpException('Email is already taken');
        }
        if (User::findByUsername($data['username'])) {
            throw new ConflictHttpException('Username is already taken');
        }


        $model = new User();
        $model->setAttributes($data);
        $model->hashPassword($data['password']);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                foreach ($model->errors as $error) {
                    throw new BadRequestHttpException($error[0]);
                }
            }

            $tokens = $this->token_service->generateTokens($model);

            if (!$this->token_service->createRefreshToken($tokens['refresh_token'], $model)) {
                throw new BadRequestHttpException('Unable to create refresh token');
            }

            $transaction->commit();

            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'refresh_token',
                'value' => $tokens['refresh_token'],
            ]));

            return $tokens['access_token'];

        } catch (Exception|BadRequestHttpException $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return string
     * @throws ConflictHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function login(string $email, string $password): string
    {
        $user = User::findByEmail($email);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        if (!$user->validatePassword($password)) {
            throw new ConflictHttpException('Invalid password');
        }

        $tokens = $this->token_service->generateTokens($user);
        $user_ip = Yii::$app->request->userIP;

        $user->scenario = 'login';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $token_model = UserToken::findOne([
                'user_id' => $user->getId(),
                'user_ip' => $user_ip,
            ]);

            if ($token_model) {
                $this->token_service->updateRefreshToken($tokens['refresh_token'], $token_model);
            } else {
                $this->token_service->createRefreshToken($tokens['refresh_token'], $user);
            }

            $transaction->commit();

            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'refresh_token',
                'value' => $tokens['refresh_token'],
            ]));

            return $tokens['access_token'];
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $refresh_token
     * @return string
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function logout(string $refresh_token): string
    {
        if (!$refresh_token) {
            throw new UnauthorizedHttpException('Unauthorized');
        }

        if (!$this->token_service->validateToken($refresh_token)) {
            throw new BadRequestHttpException('Invalid refresh token');
        }

        if (!$this->token_service->deleteRefreshToken($refresh_token)) {
            throw new BadRequestHttpException('Unable to delete refresh token');
        }

        Yii::$app->response->cookies->remove('refresh_token');
        return 'Successfully logged out';
    }

    /**
     * @param string $refresh_token
     * @return string
     * @throws BadRequestHttpException
     * @throws ConflictHttpException
     * @throws Exception
     * @throws UnauthorizedHttpException
     * @throws \yii\db\Exception
     */
    public function refresh(string $refresh_token): string
    {
        if (!$refresh_token) {
            throw new UnauthorizedHttpException('Unauthorized');
        }

        if (!$this->token_service->validateToken($refresh_token)) {
            throw new BadRequestHttpException('Invalid refresh token');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $token_model = UserToken::findOne(['refresh_token' => $refresh_token]);

            if (!$token_model) {
                throw new UnauthorizedHttpException('Refresh token no longer exists');
            }

            $user = User::find()
                ->where(['id' => $token_model->user_id])
                ->one();

            if (!$user) {
                throw new ConflictHttpException('User associated with token not found');
            }
            $tokens = $this->token_service->generateTokens($user);

            if (!$this->token_service->updateRefreshToken($tokens['refresh_token'], $token_model)) {
                throw new BadRequestHttpException('Unable to update refresh token');
            }

            $transaction->commit();

            Yii::$app->response->cookies->remove('refresh_token');
            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'refresh_token',
                'value' => $tokens['refresh_token'],
            ]));

            return $tokens['access_token'];
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
