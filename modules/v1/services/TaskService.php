<?php

namespace app\modules\v1\services;

use app\common\interfaces\TaskServiceInterface;
use app\common\models\Collection;
use app\common\models\Task;
use Ramsey\Uuid\Uuid;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class TaskService implements TaskServiceInterface
{
    /**
     * @param string $collection_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function getAll(string $collection_id): array
    {
        $collection = Collection::findById($collection_id);
        if (is_null($collection)) {
            throw new NotFoundHttpException('Collection not found');
        }

        return ArrayHelper::toArray($collection->getTasks()->all(), [
            Task::class => [
                'id',
                'title',
                'collection_id',
                'creator_id',
                'description',
                'is_completed',
                'deadline',
                'priority',
                'parent_id'
            ]
        ]);
    }

    /**
     * @param string $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function getOne(string $id): array
    {
        $task = Task::findById($id);
        if (is_null($task)) {
            throw new NotFoundHttpException('Task not found');
        }
        return ArrayHelper::toArray($task, [
            Task::class => [
                'id',
                'title',
                'collection_id',
                'creator_id',
                'description',
                'is_completed',
                'deadline',
                'priority',
                'parent_id'
            ]
        ]);
    }

    /**
     * @param $data
     * @param string $collection_id
     * @return Task
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function create($data, string $collection_id): Task
    {
        $collection = Collection::findById($collection_id);
        if (is_null($collection)) {
            throw new NotFoundHttpException('Collection not found');
        }
        if ($collection
                ->view
                ->getProject()
                ->one()
                ->getWorkspace()
                ->one()
                ->creator_id !== Yii::$app->user->getId()
            && !$collection->view->isMember(Yii::$app->user->getId())) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        if(isset($data['parent_id'])) {
            if (!Uuid::isValid($data['parent_id'])) {
                throw new BadRequestHttpException('Invalid uuid');
            }
            if($data['parent_id'] === $data['id']) {
                throw new BadRequestHttpException('Parent and child task cannot be the same');
            }
            $parent = Task::findById($data['parent_id']);
            if (is_null($parent)) {
                throw new NotFoundHttpException('Parent task not found');
            }
        }

        $model = new Task();
        $model->setAttributes($data);
        $model->collection_id = $collection_id;
        $model->creator_id = Yii::$app->user->getId();
        if(!is_null($collection->status)) {
            $model->is_completed = boolval($collection->status);
        }
        if(!$model->save()) {
            foreach ($model->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }
        return $model;
    }

    /**
     * @param string $id
     * @param $data
     * @return Task
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function update(string $id, $data): Task
    {
        $task = Task::findById($id);
        if (is_null($task)) {
            throw new NotFoundHttpException('Task not found');
        }

        if($task->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        if(!is_null($data['collection_id'])) {
            $collection = Collection::findById($data['collection_id']);
            if (is_null($collection)) {
                throw new NotFoundHttpException('Collection not found');
            }
            if ($collection
                    ->view
                    ->getProject()
                    ->one()
                    ->getWorkspace()
                    ->one()
                    ->creator_id !== Yii::$app->user->getId()
                && !$collection->view->isMember(Yii::$app->user->getId())) {
                throw new ForbiddenHttpException('Forbidden for you');
            }
        }

        $task->setAttributes($data);
        if(!$task->save()) {
            foreach ($task->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }

        return $task;
    }

    /**
     * @param string $id
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete(string $id): void
    {
        $task = Task::findById($id);
        if (is_null($task)) {
            throw new NotFoundHttpException('Task not found');
        }

        if($task->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        if (!$task->delete()) {
            foreach ($task->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }
    }

    /**
     * @param string $id
     * @return Task
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function complete(string $id): Task
    {
        $task = Task::findById($id);
        if (is_null($task)) {
            throw new NotFoundHttpException('Task not found');
        }
        if($task->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }
        $task->is_completed = true;
        $collection = $task->getCollection()->one()->getView()->one()->getCollections()->findOne(['status' => 1]);
        if(!is_null($collection)) {
            $task->collection_id = $collection->id;
        }
        if(!$task->save()) {
            foreach ($task->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }
        return $task;
    }

    /**
     * @param string $id
     * @return Task
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function uncomplete(string $id): Task
    {
        $task = Task::findById($id);
        if (is_null($task)) {
            throw new NotFoundHttpException('Task not found');
        }
        if($task->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }
        $task->is_completed = false;
        $collection = $task->getCollection()->one()->getView()->one()->getCollections()->findOne(['status' => 0]);
        if(!is_null($collection)) {
            $task->collection_id = $collection->id;
        }
        if(!$task->save()) {
            foreach ($task->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }
        return $task;
    }
}
