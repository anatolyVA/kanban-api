<?php

namespace app\modules\v1\services;

use app\common\interfaces\ProjectServiceInterface;
use app\common\models\Project;
use app\common\models\Workspace;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ProjectService implements ProjectServiceInterface
{
    /**
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function create(string $title, string $workspace_id): Project
    {
        $workspace = Workspace::findById($workspace_id);
        if(is_null($workspace)) {
            throw new NotFoundHttpException('Workspace not found');
        }

        if($workspace->creator_id != Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        $model = new Project([
            'title' => $title,
            'workspace_id' => $workspace_id,
        ]);

        if (!$model->save()) {
            foreach ($model->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }

        return $model;
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     */
    public function delete(string $id): void
    {
        $project = Project::findById($id);

        if (is_null($project)) {
            throw new NotFoundHttpException('Project not found');
        }
        if($project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }
        if (!$project->delete()) {
            throw new ServerErrorHttpException('Unable to delete project');
        };
    }

    /**
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function rename(string $id, string $title): Project
    {
        $project = Project::findById($id);

        if (is_null($project)) {
            throw new NotFoundHttpException('Project not found');
        }
        if($project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        $project->title = $title;
        if(!$project->save()) {
            foreach ($project->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        };
        return $project;

    }

    /**
     * @throws NotFoundHttpException
     */
    public function getOne(string $id)
    {
        $project = Project::findById($id);
        if (is_null($project)) {
            throw new NotFoundHttpException('Project not found');
        }
        return [
            'id' => $project->id,
            'title' => $project->title,
            'workspace_id' => $project->workspace_id,
            'views' => $project->getViews()->all()
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getAll(string $workspace_id): array
    {
        $workspace = Workspace::findById($workspace_id);
        if (is_null($workspace)) {
            throw new NotFoundHttpException('Workspace not found');
        }

        return ArrayHelper::toArray($workspace->getProjects()->all(), [
            Project::class => ['id', 'title', 'workspace_id', 'views'],
        ]);
    }
}
