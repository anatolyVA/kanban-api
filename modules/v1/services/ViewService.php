<?php

namespace app\modules\v1\services;

use app\common\interfaces\ViewServiceInterface;
use app\common\models\Project;
use app\common\models\User;
use app\common\models\View;
use app\common\models\ViewUser;
use Ramsey\Uuid\Uuid;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ViewService implements ViewServiceInterface
{
    /**
     * @param string $title
     * @param string $project_id
     * @return View
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function create(string $title, string $project_id): View
    {
        $project = Project::findById($project_id);
        if(is_null($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        if($project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        $model = new View([
            'title' => $title,
            'project_id' => $project_id
        ]);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                foreach ($model->getErrors() as $error) {
                    throw new BadRequestHttpException($error[0]);
                }
            }

            $transaction->commit();
        } catch (ServerErrorHttpException $e) {
            $transaction->rollBack();
            throw new $e();
        }

        return $model;
    }

    /**
     * @param string $id
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function delete(string $id): void
    {
        $model = View::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException('View not found');
        }
        if ($model->project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->delete()) {
                foreach ($model->getErrors() as $error) {
                    throw new BadRequestHttpException($error[0]);
                }
            }
            $transaction->commit();
        } catch (ServerErrorHttpException $e) {
            $transaction->rollBack();
            throw new $e();
        }
    }

    /**
     * @param string $id
     * @param string $title
     * @return View
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function rename(string $id, string $title): View
    {
        $model = View::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException('View not found');
        }
        if($model->project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
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
     * @return array
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function getOne(string $id): array
    {
        $view = View::findById($id);
        if(is_null($view)) {
            throw new NotFoundHttpException('View not found');
        }
        return [
            'id' => $view->id,
            'title' => $view->title,
            'project_id' => $view->project_id,
            'members' => $view->getMembers()->all(),
            'collections' => $view->getCollections()->all()
        ];
    }

    /**
     * @param string $project_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function getAll(string $project_id): array
    {
        $project = Project::findById($project_id);
        if(is_null($project)) {
            throw new NotFoundHttpException('Project not found');
        }
        return ArrayHelper::toArray($project->getViews()->all(), [
            View::class => ['id', 'title', 'project_id', 'members', 'collections'],
        ]);
    }

    /**
     * @param string $id
     * @param array $user_ids
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function addMembers(string $id, array $user_ids): void
    {
        $model = View::findById($id);
        if(is_null($model)) {
            throw new NotFoundHttpException('View not found');
        }
        if($model->project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }
        foreach ($user_ids as $user_id) {
            if (!Uuid::isValid($user_id)) {
                throw new BadRequestHttpException('Invalid uuid');
            }
            $user = User::findOne($user_id);
            if (is_null($user)) {
                throw new NotFoundHttpException('User not found');
            }
            if(!$model->project->getWorkspace()->one()->isMember($user_id)) {
                throw new BadRequestHttpException('User is not a member of the workspace');
            }
            if (!$model->isMember($user_id)) {
                $model->link('members', $user);
            }
        }
    }

    /**
     * @param string $id
     * @param array $user_ids
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function excludeMembers(string $id, array $user_ids): void
    {
        $model = View::findById($id);

        if(is_null($model)) {
            throw new NotFoundHttpException('View not found');
        }

        if($model->project->getWorkspace()->one()->creator_id !== Yii::$app->user->getId()) {
            throw new ForbiddenHttpException('Forbidden for you');
        }

        if (in_array(Yii::$app->user->getId(), $user_ids)) {
            throw new BadRequestHttpException("You can't exclude yourself");
        }

        foreach ($user_ids as $user_id) {

            if (!Uuid::isValid($user_id)) {
                throw new BadRequestHttpException('Invalid uuid');
            }
            $user = User::findOne($user_id);
            if (is_null($user)) {
                throw new NotFoundHttpException('User not found');
            }
            if ($model->isMember($user_id)) {
                $model->unlink('members', $user);
            }
        }
    }

    /**
     * @param string $id
     * @return array
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function getMembers(string $id): array
    {
        $view = View::findById($id);
        if(is_null($view)) {
            throw new NotFoundHttpException('View not found');
        }
        return $view->getMembers()->all();
    }
}
