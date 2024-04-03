<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\common\controllers\BaseController;
use app\common\models\Project;
use app\common\models\User;
use app\traits\UuidTypeTrait;
use kaabar\jwt\Jwt;
use kaabar\jwt\JwtHttpBearerAuth;
use Lcobucci\JWT\Token;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class UserController extends AccessController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['get'],
                'view' => ['get']
            ],
        ];
        return $behaviors;
    }

    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionView(string $id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $user = User::findIdentity($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        return $this->formatResponse($user);
    }

    public function actionIndex(): Response
    {
        $users = User::find()->all();

        return $this->formatResponse($users);
    }



}
