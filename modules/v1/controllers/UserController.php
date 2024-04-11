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
use yii\base\InvalidConfigException;
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
                'index' => ['get', 'options'],
                'view' => ['get', 'options'],
                'get-workspaces' => ['get', 'options'],
                'get-tasks' => ['get', 'options'],
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

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionGetWorkspaces(string $id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $user = User::findIdentity($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $workspaces = $user->getWorkspaces()->all();
        return $this->formatResponse($workspaces);
    }

    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionGetTasks(string $id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $user = User::findIdentity($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $tasks = $user->getTasks()->all();
        return $this->formatResponse($tasks);
    }

    public function actionProfile(): Response
    {
        $user = Yii::$app->user->identity;
        return $this->formatResponse($user);
    }
}
