<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\modules\v1\services\ProjectService;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProjectController extends AccessController
{
    private ProjectService $project_service;

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
            ]
        ];

        return $behaviors;
    }

    public function __construct($id, $module, $config = [])
    {
        $this->project_service = new ProjectService();
        parent::__construct($id, $module, $config);
    }

    /**
     * @param string $workspace_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionIndex(string $workspace_id): Response
    {
        if (!Uuid::isValid($workspace_id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $projects = $this->project_service->getAll($workspace_id);
        return $this->formatResponse($projects);
    }

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionView(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $project = $this->project_service->getOne($id);
        return $this->formatResponse($project);
    }

    /**
     * @param string $workspace_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionCreate(string $workspace_id): Response
    {
        if (!Uuid::isValid($workspace_id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['title']))
        {
            throw new BadRequestHttpException('Title is required');
        }

        $project = $this->project_service->create($request['title'], $workspace_id);
        return $this->formatResponse($project);
    }

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['title']))
        {
            throw new BadRequestHttpException('Title is required');
        }


        $project = $this->project_service->rename($id, $request['title']);
        return $this->formatResponse($project);
    }

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionDelete(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $this->project_service->delete($id);
        return $this->formatResponse(null, 204);
    }


}