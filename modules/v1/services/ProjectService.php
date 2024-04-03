<?php

namespace app\modules\v1\services;

use app\common\interfaces\ProjectServiceInterface;
use app\common\models\Project;
use app\common\models\ProjectUser;
use kaabar\jwt\Jwt;
use Lcobucci\JWT\Token;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

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

    public function delete(int $project_id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        // TODO: Implement delete() method.
    }

    public function update(int $project_id, string $title)
    {
        // TODO: Implement update() method.
    }

    public function invite(int $project_id, array|string $user_id)
    {
        // TODO: Implement invite() method.
    }

    public function exclude(int $project_id, array|string $user_id)
    {
        // TODO: Implement exclude() method.
    }

    public function exit(int $project_id)
    {
        // TODO: Implement exit() method.
    }
}
