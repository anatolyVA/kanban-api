<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use app\common\controllers\AuthController as BaseAuthController;

class AuthController extends BaseAuthController
{
    /**
     * @throws BadRequestHttpException
     */
    public function actionLogin(): Response
    {
        $request = Yii::$app->request->post();

        $this->validateLoginRequest($request);

        $password = $request['password'];
        $email = $request['email'];

        $data = $this->auth_service->login($email, $password);
        return $this->formatResponse($data);

    }

    /**
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionRegister(): Response
    {
        $request = Yii::$app->request->getBodyParams();

        $this->validateRegisterRequest($request);

        $data = $this->auth_service->register($request);
        return $this->formatResponse($data, 201);
    }

    public function actionLogout(): Response
    {
        $refreshToken = Yii::$app->request->cookies->getValue('refresh_token', false);

        $data = $this->auth_service->logout($refreshToken);

        return $this->formatResponse($data);
    }

    public function actionRefreshToken(): Response
    {
        $token = Yii::$app->request->cookies->getValue('refresh_token', false);

        $data = $this->auth_service->refresh($token);

        return $this->formatResponse($data);
    }
}
