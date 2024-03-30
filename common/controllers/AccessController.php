<?php

namespace app\common\controllers;

use kaabar\jwt\Jwt;
use kaabar\jwt\JwtHttpBearerAuth;
use Lcobucci\JWT\Token;
use Yii;
use yii\web\UnauthorizedHttpException;

class AccessController extends BaseController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3000'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Request-Headers' => ['*'],
            ]
        ];

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];
        $behaviors['authenticator']['except'] = ['options'];
        return $behaviors;
    }

    /**
     * @throws UnauthorizedHttpException
     */
    protected function getCurrentUserId(): string
    {
        $authorization_header = \Yii::$app->request->headers->get('Authorization');
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;

        $token_string = explode(' ', $authorization_header)[1];

        $token = $jwt->loadToken($token_string);

        if (!$token instanceof Token) {
            throw new UnauthorizedHttpException("Invalid access token");
        }
        return $token->claims()->get("id");
    }
}
