<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\common\models\Project;
use app\common\models\ProjectUser;
use app\modules\v1\services\ProjectService;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
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
     * @throws UnauthorizedHttpException
     */
    public function actionIndex(): Response
    {
        $user_id = $this->getCurrentUserId();
        $projects = Project::findByCreatorId($user_id);

        return $this->formatResponse($projects);
    }

    /**
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($project_id): Response
    {
        $user_id = $this->getCurrentUserId();
        $project = Project::findById($project_id);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        return $this->formatResponse($project);
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

    public function actionUpdate($project_id)
    {
        // TODO Implement update action logic
    }

    public function actionDelete($project_id)
    {
        // TODO Implement delete action logic
    }

    public function actionInvite($project_id)
    {
        // TODO Implement invite action logic
    }

    public function actionExclude($project_id)
    {
        // TODO Implement exclude action logic
    }

    public function actionExit($project_id)
    {
        // TODO Implement exit controller logic
    }
}
