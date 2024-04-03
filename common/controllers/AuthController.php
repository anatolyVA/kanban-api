<?php

namespace app\common\controllers;

use app\common\interfaces\AuthServiceInterface;
use app\filters\RateLimitFilter;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

class AuthController extends BaseController
{
    protected AuthServiceInterface $auth_service;

    /**
     * @throws BadRequestHttpException
     */
    protected function validateRegisterRequest($request): void
    {
        $requiredKeys = ['first_name', 'last_name', 'password', 'email', 'role'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $request)) {
                throw new BadRequestHttpException('Missing required parameter - ' . $key);
            }
            if (strlen($request['password']) < 6) {
                throw new BadRequestHttpException('Password must contain min 6 symbols');
            }
        }
    }

    /**
     * @throws BadRequestHttpException
     */
    protected function validateLoginRequest($request): void
    {
        if (!isset($request['email']) || !isset($request['password'])) {
            throw new BadRequestHttpException('Missing required parameters');
        }
    }


    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3000'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Request-Headers' => ['*'],
            ]
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'login' => ['post'],
                'register' => ['post'],
                'logout' => ['get'],
                'refresh-token' => ['get']
            ],
        ];

        return $behaviors;
    }

    public function __construct($id, $module, AuthServiceInterface $auth_service, $config = [])
    {
        $this->auth_service = $auth_service;
        parent::__construct($id, $module, $config);
    }
}
