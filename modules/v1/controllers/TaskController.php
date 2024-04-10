<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\common\models\Collection;
use app\modules\v1\services\TaskService;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TaskController extends AccessController
{
    private TaskService $task_service;

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
        $this->task_service = new TaskService();
        parent::__construct($id, $module, $config);
    }

    /**
     * @param $collection_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionIndex($collection_id): Response
    {
        if (!Uuid::isValid($collection_id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        return $this->formatResponse($this->task_service->getAll($collection_id));
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        return $this->formatResponse($this->task_service->getOne($id));
    }

    /**
     * @param $collection_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     */
    public function actionCreate($collection_id): Response
    {
        if (!Uuid::isValid($collection_id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();
        return $this->formatResponse($this->task_service->create($request, $collection_id));
    }

    /**
     * @param $id
     * @param $data
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionUpdate($id, $data): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();
        return $this->formatResponse($this->task_service->update($id, $request));
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionDelete($id): Response
    {
        if (!Uuid::isValid($id)) {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $this->task_service->delete($id);
        return $this->formatResponse(null, 204);
    }
}