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
                'exclude' => ['post', 'options'],
                'exit' => ['post', 'options'],
            ]
        ];

        return $behaviors;
    }

    private WorkspaceService $project_service;

    public function __construct($id, $module, $config = [])
    {
        $this->project_service = new WorkspaceService(); // Sorry me, God... = new WorkspaceService($this->getCurrentUserId())
        parent::__construct($id, $module, $config);
    }

    /**
     * @return Response returns an array of projects the user is a member of
     * @throws UnauthorizedHttpException|NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionIndex(): Response
    {
        $user_id = $this->getCurrentUserId();
        $projects = Workspace::findByUserId($user_id);

        return $this->formatResponse($projects);
    }

    /**
     * @return Response returns information about a specific project
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionView($id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $project = Workspace::findById($id);

        if (!$project) {
            throw new NotFoundHttpException('Workspace not found');
        }

        $result = [
            'id' => $project->id,
            'title' => $project->title,
            'creator_id' => $project->creator_id,
            'members' => $project->members
        ];

        return $this->formatResponse($result);
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException|NotFoundHttpException
     */
    public function actionCreate(): Response
    {
        $user_id = $this->getCurrentUserId();
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['title'])) {
            throw new BadRequestHttpException('Missing title');
        }

        $model = $this->project_service->create($request['title'], $user_id);

        return $this->formatResponse($model, 201);
    }

    /**
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionUpdate($id): Response
    {
        $user_id = $this->getCurrentUserId();
        $request = Yii::$app->request->getBodyParams();

        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        if (!isset($request['title'])) {
            throw new BadRequestHttpException('Missing title');
        }

        $model = $this->project_service->rename($id, $request['title'], $user_id);

        return $this->formatResponse($model);
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id): Response
    {
        $user_id = $this->getCurrentUserId();
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $this->project_service->delete($id, $user_id);

        return $this->formatResponse(null);
    }

    /**
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionInvite($id): Response
    {
        $user_id = $this->getCurrentUserId();
        $request = Yii::$app->request->getBodyParams();

        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        if (!isset($request['users'])) {
            throw new BadRequestHttpException('Missing users');
        }

        $this->project_service->invite($id, $user_id, $request['users']);

        return $this->formatResponse('The above users have been invited');
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException|UnauthorizedHttpException
     */
    public function actionExclude($id): Response
    {
        $user_id = $this->getCurrentUserId();
        $request = Yii::$app->request->getBodyParams();

        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        if (!isset($request['users'])) {
            throw new BadRequestHttpException('Missing users');
        }

        $this->project_service->exclude($id, $user_id, $request['users']);

        return $this->formatResponse('The above users have been excluded');
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws NotFoundHttpException|BadRequestHttpException|UnauthorizedHttpException
     */
    public function actionExit($id): Response
    {
        $user_id = $this->getCurrentUserId();
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $this->project_service->exit($id, $user_id);

        return $this->formatResponse('The exit was successful');
    }


}
