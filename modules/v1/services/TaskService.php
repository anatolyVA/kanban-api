<?php

namespace app\modules\v1\services;

use app\common\interfaces\TaskServiceInterface;
use app\common\models\Collection;
use app\common\models\Task;
use Yii;
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
        if ($collection->view->getProject()->one()->getWorkspace()->one()->creator_id !== Yii::$app->user->getId() && !$collection->view->isMember(Yii::$app->user->getId())) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        $model = new Task();
        $model->setAttributes($data);
        $model->collection_id = $collection_id;
        $model->creator_id = Yii::$app->user->getId();
        if(!is_null($collection->status))
        {
            $model->is_completed = boolval($collection->status);
        }
        if(!$model->save()) {
            foreach ($model->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }
        return $model;
    }

    public function update(string $id, $data)
    {
        // TODO: Implement update() method.
    }

    public function delete(string $id)
    {
        // TODO: Implement delete() method.
    }
}
