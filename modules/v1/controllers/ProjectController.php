<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\common\models\Project;
use app\common\models\ProjectUser;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class ProjectController extends AccessController
{
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
        $project = Project::findByCreatorIdAndProjectId($project_id, $user_id);

        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        return $this->formatResponse($project);
    }

    /**
     * @throws UnauthorizedHttpException
     * @throws InvalidConfigException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionCreate(): Response
    {
        $user_id = $this->getCurrentUserId();
        $request = Yii::$app->request->getBodyParams();

        if (!isset($request['title'])) {
            throw new BadRequestHttpException('Missing title');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new Project([
                'title' => $request['title'],
                'user_id' => $user_id
            ]);

            if (!$model->save()) {
                throw new BadRequestHttpException('Unable to save project');
            }

            $relation_model = new ProjectUser([
                'project_id' => $model->getId(),
                'user_id' => $user_id
            ]);

            if (!$relation_model->save()) {
                throw new BadRequestHttpException('Unable to save project');
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        return $this->formatResponse($model, 201);
    }

    public function actionUpdate($project_id)
    {
        // TODO Implement update controller logic
    }

    public function actionDelete($project_id)
    {
        // TODO Implement delete controller logic
    }

    public function actionAddMember($project_id, $user_id)
    {
        // TODO Implement add-member controller logic
    }

    public function actionDeleteMember($project_id, $user_id)
    {
        // TODO Implement delete-member controller logic
    }
}
