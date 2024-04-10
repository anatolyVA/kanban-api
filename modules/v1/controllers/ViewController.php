<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\modules\v1\services\ViewService;
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
use yii\web\ServerErrorHttpException;

class ViewController extends AccessController
{
    private ViewService $view_service;
    public function __construct($id, $module, $config = [])
    {
        $this->view_service = new ViewService();
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
                'add-members' => ['post', 'options'],
                'get-members' => ['get', 'options'],
                'exclude-members' => ['delete', 'options'],
            ]
        ];

        return $behaviors;
    }

    /**
     * @param string $project_id
     * @return Response
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionIndex(string $project_id): Response
    {
        if (!Uuid::isValid($project_id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $views = $this->view_service->getAll($project_id);

        return $this->formatResponse($views);
    }

    /**
     * @param string $project_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Exception
     * @throws ServerErrorHttpException
     */
    public function actionCreate(string $project_id): Response
    {
        if (!Uuid::isValid($project_id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $request = Yii::$app->request->getBodyParams();

        if(is_null($request['title']))
        {
            throw new BadRequestHttpException('Title is required');
        }

        $view = $this->view_service->create($request['title'], $project_id);
        return $this->formatResponse($view);
    }

    /**
     * @param string $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionView(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $view = $this->view_service->getOne($id);

        return $this->formatResponse($view);
    }

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $this->view_service->delete($id);

        return $this->formatResponse(null, 204);
    }

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionUpdate(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }

        $request = Yii::$app->request->getBodyParams();

        if(is_null($request['title']))
        {
            throw new BadRequestHttpException('Title is required');
        }

        $view = $this->view_service->rename($id, $request['title']);
        return $this->formatResponse($view);
    }

    /**
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionAddMembers(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();
        if (is_null($request['users']))
        {
            throw new BadRequestHttpException('Missing users');
        }
        $this->view_service->addMembers($id, $request['users']);
        return $this->formatResponse('The above users have been added');
    }

    /**
     * @param string $id
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function actionExcludeMembers(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $request = Yii::$app->request->getBodyParams();
        if (is_null($request['users']))
        {
            throw new BadRequestHttpException('Missing users');
        }
        $this->view_service->excludeMembers($id, $request['users']);
        return $this->formatResponse('The above users have been excluded');
    }

    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionGetMembers(string $id): Response
    {
        if (!Uuid::isValid($id))
        {
            throw new BadRequestHttpException('Invalid uuid');
        }
        $view = $this->view_service->getMembers($id);
        return $this->formatResponse($view);
    }
}