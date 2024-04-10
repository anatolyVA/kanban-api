<?php

namespace app\modules\v1\services;

use app\common\interfaces\WorkspaceServiceInterface;
use app\common\models\Workspace;
use app\common\models\WorkspaceUser;
use app\common\models\User;
use kaabar\jwt\Jwt;
use Lcobucci\JWT\Token;
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
use yii\web\UnauthorizedHttpException;

class WorkspaceService implements WorkspaceServiceInterface
{
    /**
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function create(string $title): Workspace
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new Workspace([
                'title' => $title,
                'creator_id' => Yii::$app->user->getId()
            ]);

            if (!$model->save()) {
                foreach ($model->getErrors() as $error) {
                    throw new BadRequestHttpException($error[0]);
                }
            }

            $relation_model = new WorkspaceUser([
                'workspace_id' => $model->getId(),
                'user_id' => Yii::$app->user->getId()
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
     * @param string $id
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function delete(string $id): void
    {
        $model = Workspace::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        if ($model->creator_id != Yii::$app->user->getId()) {
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
     * @param string $id
     * @param array $user_ids
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function invite(string $id, array $user_ids): void
    {
        $model = Workspace::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        if ($model->creator_id !== Yii::$app->user->getId()) {
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
     * @param string $id
     * @param string $title
     * @return Workspace
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function rename(string $id, string $title): Workspace
    {
        $model = Workspace::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        if ($model->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        $model->title = $title;
        if (!$model->save()) {
            throw new BadRequestHttpException($model->getErrors('title')[0]);
        }

        return $model;
    }

    /**
     * @param string $id
     * @param array $user_ids
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function exclude(string $id, array $user_ids): void
    {
        $model = Workspace::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException('Workspace not found');
        }

        if (Yii::$app->user->getId() !== $model->creator_id) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        if (in_array(Yii::$app->user->getId(), $user_ids)) {
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
     * @param string $id
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function exit(string $id): void
    {

        $model = Workspace::findById($id);
        if (!$model) {
            throw new NotFoundHttpException('Workspace not found');
        }

        $current_user = User::findOne(Yii::$app->user->getId());
        if (!$current_user) {
            throw new NotFoundHttpException('User not found');
        }

        if (Yii::$app->user->getId() === $model->creator_id) {
            throw new BadRequestHttpException("You can't exit of your workspace");
        }

        if ($model->isMember(Yii::$app->user->getId())) {
            $model->unlink('members', $current_user, true);
        }
    }

    /**
     * @param string $id
     * @return Workspace|null
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     */
    public function getOne(string $id): ?Workspace
    {
        $model = Workspace::findById($id);
        if (!$model) {
            throw new NotFoundHttpException('Workspace not found');
        }
        if (!$model->isMember(Yii::$app->user->getId())) {
            throw new UnauthorizedHttpException("You don't have access to this workspace");
        }
        return $model;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return Workspace::findByUserId(Yii::$app->user->getId());
    }
}
