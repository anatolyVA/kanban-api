<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\common\models\Workspace;
use app\modules\v1\services\WorkspaceService;
use Ramsey\Uuid\Uuid;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class WorkspaceController extends AccessController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['get', 'options'],
                'view' => ['get', 'options'],
                'create' => ['post', 'options'],
                'update' => ['put', 'patch', 'options'],
                'delete' => ['delete', 'options'],
                'invite' => ['post', 'options'],
                'get-members' => ['get', 'options'],
                'exclude' => ['post', 'options'],
                'exit' => ['post', 'options'],
            ]
        ];

        return $behaviors;
    }

    private WorkspaceService $project_service;

    public function __construct($id, $module, $config = [])
    {
        $this->project_service = new WorkspaceService();
        parent::__construct($id, $module, $config);
    }

    /**
     * @return Response returns an array of projects the user is a member of
     */
    public function actionIndex(): Response
    {
        $projects = $this->project_service->getAll();
        return $this->formatResponse($projects);
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionView($id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $project = $this->project_service->getOne($id);

        return $this->formatResponse($project);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionCreate(): Response
    {
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['title'])) {
            throw new BadRequestHttpException('Missing title');
        }

        $model = $this->project_service->create($request['title']);

        return $this->formatResponse($model, 201);
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id): Response
    {
        $request = Yii::$app->request->getBodyParams();

        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        if (!isset($request['title'])) {
            throw new BadRequestHttpException('Missing title');
        }

        $model = $this->project_service->rename($id, $request['title']);

        return $this->formatResponse($model);
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete($id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $this->project_service->delete($id);

        return $this->formatResponse(null);
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionInvite($id): Response
    {
        $request = Yii::$app->request->getBodyParams();

        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        if (!isset($request['users'])) {
            throw new BadRequestHttpException('Missing users');
        }

        $this->project_service->invite($id, $request['users']);

        return $this->formatResponse('The above users have been invited');
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function actionExclude($id): Response
    {
        $request = Yii::$app->request->getBodyParams();

        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        if (!isset($request['users'])) {
            throw new BadRequestHttpException('Missing users');
        }

        $this->project_service->exclude($id, $request['users']);

        return $this->formatResponse('The above users have been excluded');
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function actionExit($id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $this->project_service->exit($id);

        return $this->formatResponse('The exit was successful');
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionGetMembers($id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $users = $this->project_service->getMembers($id);

        return $this->formatResponse($users);
    }
}
