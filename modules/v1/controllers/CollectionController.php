<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\modules\v1\services\CollectionService;
use Ramsey\Uuid\Uuid;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CollectionController extends AccessController
{

    private CollectionService $collection_service;
    public function __construct($id, $module, $config = [])
    {
        $this->collection_service = new CollectionService();
        parent::__construct($id, $module, $config);
    }

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

    /**
     * @param $view_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionIndex($view_id): Response
    {
        if (!Uuid::isValid($view_id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $collections = $this->collection_service->getAll($view_id);
        return $this->formatResponse($collections);
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $collection = $this->collection_service->getOne($id);
        return $this->formatResponse($collection);
    }

    /**
     * @param $view_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate($view_id): Response
    {
        if (!Uuid::isValid($view_id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();

        $collection = $this->collection_service->create($request, $view_id);
        return $this->formatResponse($collection);
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
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();

        $collection = $this->collection_service->update($id, $request);
        return $this->formatResponse($collection);
    }

    /**
     * @param $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $this->collection_service->delete($id);
        return $this->formatResponse('The above collection has been deleted');
    }
}