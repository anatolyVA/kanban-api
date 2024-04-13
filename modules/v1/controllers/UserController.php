<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\common\controllers\BaseController;
use app\common\models\Project;
use app\common\models\User;
use app\modules\v1\services\UserService;
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
    private UserService $service;
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['get', 'options'],
                'view' => ['get', 'options'],
                'get-tasks' => ['get', 'options'],
            ],
        ];
        return $behaviors;
    }

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = new UserService();
    }

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionView(string $id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        return $this->formatResponse($this->service->getOne($id));
    }

    public function actionIndex(): Response
    {
        return $this->formatResponse($this->service->getAll());
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionProfile(): Response
    {
        return $this->formatResponse($this->service->getProfile());
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionGetTasks(): Response
    {
        return $this->formatResponse($this->service->getTasks());
    }
}
