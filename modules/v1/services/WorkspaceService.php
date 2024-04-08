<?php

namespace app\modules\v1\services;

use app\common\interfaces\WorkspaceServiceInterface;
use app\common\models\Workspace;
use app\common\models\WorkspaceUser;
use app\common\models\User;
use Ramsey\Uuid\Uuid;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class WorkspaceService implements WorkspaceServiceInterface
{

    /**
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function create(string $title, string $current_user_id): Workspace
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new Workspace([
                'title' => $title,
                'creator_id' => $current_user_id
            ]);

            if (!$model->save()) {
                foreach ($model->getErrors() as $error) {
                    throw new BadRequestHttpException($error[0]);
                }
            }

            $relation_model = new WorkspaceUser([
                'workspace_id' => $model->getId(),
                'user_id' => $current_user_id
            ]);

            if (!$relation_model->save()) {
                throw new BadRequestHttpException('Unable to save Workspace');
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
     * @throws Throwable
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function delete(string $id, string $current_user_id): void
    {
        $model = Workspace::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        if ($model->creator_id != $current_user_id) {
            throw new ForbiddenHttpException('You do not have permission to delete this workspace');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()
                ->delete('workspace_user', ['workspace_id' => $id])
                ->execute();

            if (!$model->delete()) {
                throw new ServerErrorHttpException('Unable to delete Workspace');
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
    public function rename(string $workspace_id, string $current_user_id, string $title): Workspace
    {
        $model = Workspace::findById($workspace_id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        if ($model->creator_id !== $current_user_id) {
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
    public function invite(string $workspace_id, string $current_user_id, array $user_ids): void
    {
        $model = Workspace::findById($workspace_id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        if ($model->creator_id !== $current_user_id) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        foreach ($user_ids as $uid) {
            if (!Uuid::isValid($uid)) {
                throw new BadRequestHttpException('Invalid uuid');
            }
            $user = User::findOne($uid);
            if (is_null($user)) {
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
    public function exclude(string $workspace_id, string $current_user_id, array $user_ids): void
    {
        $model = Workspace::findById($workspace_id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }

        if ($model->creator_id !== $current_user_id) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        if (in_array($current_user_id, $user_ids)) {
            throw new BadRequestHttpException("You can't exclude yourself");
        }

        foreach ($user_ids as $uid) {
            if (!Uuid::isValid($uid)) {
                throw new BadRequestHttpException('Invalid uuid');
            }
            $user = User::findOne($uid);
            if (is_null($user)) {
                throw new NotFoundHttpException('User not found');
            }
            if ($model->isMember($uid)) {
                $model->unlink('members', $user);
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
    public function exit(string $workspace_id, string $current_user_id): void
    {
        $model = Workspace::findById($workspace_id);
        if (!$model) {
            throw new NotFoundHttpException('Workspace not found');
        }

        $current_user = User::findOne($current_user_id);
        if (!$current_user) {
            throw new NotFoundHttpException('User not found');
        }

        if ($current_user_id === $model->creator_id) {
            throw new BadRequestHttpException("You can't exit of your workspace");
        }

        if ($model->isMember($current_user_id)) {
            $model->unlink('members', $current_user, true);
        }
    }
}
