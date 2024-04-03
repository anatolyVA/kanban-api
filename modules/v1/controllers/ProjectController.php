<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\common\models\Project;
use app\common\models\ProjectUser;
use app\modules\v1\services\ProjectService;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class ProjectController extends AccessController
{
    private ProjectService $project_service;

    /**
     * @throws UnauthorizedHttpException
     */
    public function __construct($id, $module, $config = [])
    {
        $this->project_service = new ProjectService($this->getCurrentUserId());
        parent::__construct($id, $module, $config);
    }

    /**
     * @return Response returns an array of projects the user is a member of
     * @throws UnauthorizedHttpException
     */
    public function actionIndex(): Response
    {
        $user_id = $this->getCurrentUserId();
        $projects = Project::findByUserId($user_id);

        return $this->formatResponse($projects);
    }

    /**
     * @return Response returns information about a specific project
     * @throws NotFoundHttpException
     */
    public function actionView($project_id): Response
    {
        $project = Project::findById($project_id);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
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
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionUpdate($project_id): Response
    {
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['title'])) {
            throw new BadRequestHttpException('Missing title');
        }

        $model = $this->project_service->rename($project_id, $request['title']);

        return $this->formatResponse($model);
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($project_id): Response
    {
        $this->project_service->delete($project_id);

        return $this->formatResponse(null);
    }

    /**
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionInvite($project_id): Response
    {
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['users'])) {
            throw new BadRequestHttpException('Missing users');
        }

        $this->project_service->invite($project_id, $request['users']);

        return $this->formatResponse('The above users have been invited');
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionExclude($project_id): Response
    {
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['users'])) {
            throw new BadRequestHttpException('Missing users');
        }

        $this->project_service->exclude($project_id, $request['users']);

        return $this->formatResponse('The above users have been excluded');
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws NotFoundHttpException|BadRequestHttpException
     */
    public function actionExit($project_id): Response
    {
        $this->project_service->exit($project_id);

        return $this->formatResponse('The exit was successful');
    }
}
