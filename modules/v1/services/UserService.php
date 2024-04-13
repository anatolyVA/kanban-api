<?php

namespace app\modules\v1\services;

use app\common\interfaces\UserServiceInterface;
use app\common\models\Task;
use app\common\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;

class UserService implements UserServiceInterface
{
    public function getAll(): array
    {
        return User::find()->all();
    }

    /**
     * @param $id
     * @return User|IdentityInterface
     * @throws NotFoundHttpException
     */
    public function getOne($id): User|IdentityInterface
    {
        $user = User::findIdentity($id);
        if (is_null($user)) {
            throw new NotFoundHttpException('User not found');
        }
        return $user;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getProfile(): User|IdentityInterface
    {
        $user = Yii::$app->user->identity;
        if(is_null($user)) {
            throw new NotFoundHttpException('User not found');
        }
        return $user;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getTasks(): array
    {
        $user = Yii::$app->user->identity;
        if(is_null($user)) {
            throw new NotFoundHttpException('User not found');
        }
        $tasks = ArrayHelper::toArray($user->getTasks()->all(), [
            Task::class => [
                'id',
                'title',
                'collection_id',
                'creator_id',
                'description',
                'is_completed',
                'deadline',
                'priority',
                'parent_id',
                'subtasks',
            ]
        ]);
        return array_values(array_filter($tasks, function ($task) {
            return $task['parent_id'] === null;
        }));
    }
}
