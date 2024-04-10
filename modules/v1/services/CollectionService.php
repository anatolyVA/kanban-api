<?php

namespace app\modules\v1\services;

use app\common\interfaces\CollectionServiceInterface;
use app\common\models\Collection;
use app\common\models\View;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class CollectionService implements CollectionServiceInterface
{

    /**
     * @param $view_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function getAll($view_id): array
    {
        $view = View::findById($view_id);
        if(is_null($view)) {
            throw new NotFoundHttpException('View not found');
        }
        return ArrayHelper::toArray($view->getCollections()->all(), [
            Collection::class => [
                'id',
                'view_id',
                'title',
                'status',
                'tasks'
            ]
        ]);
    }

    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function getOne($id): array
    {
        $collection = Collection::findById($id);
        if(is_null($collection)) {
            throw new NotFoundHttpException('Collection not found');
        }
        return [
            'id' => $collection->id,
            'view_id' => $collection->view_id,
            'title' => $collection->title,
            'status' => $collection->status,
            'tasks' => $collection->getTasks()->all()
        ];
    }

    /**
     * @param $data
     * @param $view_id
     * @return Collection
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function create($data, $view_id): Collection
    {
        $view = View::findById($view_id);
        if(is_null($view)) {
            throw new NotFoundHttpException('View not found');
        }
        if($view->project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }
        $collection = new Collection($data);
        $collection->view_id = $view_id;
        if (!$collection->save()) {
            foreach ($collection->getErrors() as $error) {
                throw new BadRequestHttpException($error[0]);
            }
        }
        return $collection;
    }

    /**
     * @param $id
     * @param $data
     * @return Collection
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function update($id, $data): Collection
    {
        try {
            $collection = Collection::findById($id);
            if(is_null($collection)) {
                throw new NotFoundHttpException('Collection not found');
            }
            if($collection->view->getProject()->one()->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
                throw new ForbiddenHttpException('Forbidden for you');
            }

            if(isset($data['view_id']))
            {
                $view = View::findById($data['view_id']);
                if(is_null($view)) {
                    throw new NotFoundHttpException('View not found');
                }
                $data['view_id'] = $view->id;
            }

            $collection->setAttributes($data);
            if (!$collection->save()) {
                foreach ($collection->getErrors() as $error) {
                    throw new BadRequestHttpException($error[0]);
                }
            }
            return $collection;
        } catch (ServerErrorHttpException $e)
        {
            throw new ServerErrorHttpException('Unable to update collection');
        }
    }

    /**
     * @param $id
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function delete($id): void
    {
        $collection = Collection::findById($id);
        if(is_null($collection)) {
            throw new NotFoundHttpException('Collection not found');
        }
        if($collection->view->getProject()->one()->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }
        if (!$collection->delete())
        {
            throw new ServerErrorHttpException('Unable to delete collection');
        };
    }
}