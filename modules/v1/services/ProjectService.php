<?php

namespace app\modules\v1\services;

use app\common\interfaces\ProjectServiceInterface;
use app\common\models\Project;
use app\common\models\ProjectUser;
use app\common\models\User;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ProjectService implements ProjectServiceInterface
{
    private string $current_user_id;

    public function __construct(string $user_id)
    {
        $this->current_user_id = $user_id;
    }

    /**
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function create(string $title): Project
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new Project([
                'title' => $title,
                'creator_id' => $this->current_user_id
            ]);

            if (!$model->save()) {
                throw new BadRequestHttpException('Unable to save project');
            }

            $relation_model = new ProjectUser([
                'project_id' => $model->getId(),
                'user_id' => $this->current_user_id
            ]);

            if (!$relation_model->save()) {
                throw new BadRequestHttpException('Unable to save project');
            }

            $transaction->commit();
            return $model;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * @throws Exception
     * @throws \Throwable
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function delete(int $project_id): void
    {
        $model = Project::findById($project_id);
        if (!$model) {
            throw new NotFoundHttpException('Project not found');
        }
        if ($model->creator_id != $this->current_user_id) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()
                ->delete('project_user', ['project_id' => $project_id])
                ->execute();

            if (!$model->delete()) {
                throw new ServerErrorHttpException('Failed to delete the project.');
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function rename(int $project_id, string $title): Project
    {
        $model = Project::findById($project_id);
        if (!$model) {
            throw new NotFoundHttpException('Project not found');
        }
        if ($model->creator_id != $this->current_user_id) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        $model->title = $title;
        if (!$model->save()) {
            throw new BadRequestHttpException($model->getErrors('title')[0]);
        }

        return $model;
    }


    /**
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function invite(int $project_id, array $user_ids): void
    {
        $model = Project::findById($project_id);
        if (!$model) {
            throw new NotFoundHttpException('Project not found');
        }
        if ($model->creator_id != $this->current_user_id) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        foreach ($user_ids as $uid) {
            if (!Uuid::isValid($uid)) {
                throw new BadRequestHttpException('Invalid uuid');
            }
            $user = User::findOne($uid);
            if (!$user) {
                throw new NotFoundHttpException('User not found');
            }
            if (!$model->isMember($uid)) {
                $model->link('members', $user);
            }
        }
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function exclude(int $project_id, array $user_ids): void
    {
        $model = Project::findById($project_id);
        if (!$model) {
            throw new NotFoundHttpException('Project not found');
        }

        if ($model->creator_id != $this->current_user_id) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        if (array_key_exists($this->current_user_id, $user_ids)) {
            throw new BadRequestHttpException("You can't exclude yourself");
        }

        foreach ($user_ids as $uid) {
            if (!Uuid::isValid($uid)) {
                throw new BadRequestHttpException('Invalid uuid');
            }
            if ($model->isMember($uid)) {
                $model->unlink('members', User::findOne($uid), true);
            }
        }
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function exit(int $project_id): void
    {
        $model = Project::findById($project_id);
        if (!$model) {
            throw new NotFoundHttpException('Project not found');
        }

        $current_user = User::findOne($this->current_user_id);
        if (!$current_user) {
            throw new NotFoundHttpException('User not found');
        }

        if ($this->current_user_id == $model->creator_id) {
            throw new BadRequestHttpException("You can't exit of your project");
        }

        if ($model->isMember($this->current_user_id)) {
            $model->unlink('members', $current_user, true);
        }
    }
}
