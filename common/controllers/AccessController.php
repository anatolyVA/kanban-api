<?php

namespace app\common\controllers;

use kaabar\jwt\Jwt;
use kaabar\jwt\JwtHttpBearerAuth;
use Lcobucci\JWT\Token;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class AccessController extends BaseController
{
    public function actions(): array
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];

        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }

    /**
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    protected function getCurrentUserId(): string
    {
        $authorizationHeader = Yii::$app->request->headers->get('Authorization');
        if (!$authorizationHeader) {
            throw new BadRequestHttpException('Missing authorization header');
        }

        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;

        $tokenParts = explode(' ', $authorizationHeader);
        if (count($tokenParts) !== 2 || !isset($tokenParts[1])) {
            throw new BadRequestHttpException("Invalid authorization header format");
        }

        $tokenString = $tokenParts[1];

        $token = $jwt->loadToken($tokenString);

        if (!$token instanceof Token) {
            throw new UnauthorizedHttpException("Invalid access token");
        }

        return $token->claims()->get("id");
    }
}
