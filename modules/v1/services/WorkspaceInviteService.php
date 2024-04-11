<?php

namespace app\modules\v1\services;

use app\common\interfaces\WorkspaceInviteServiceInterface;
use app\common\models\User;
use app\common\models\Workspace;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class WorkspaceInviteService implements WorkspaceInviteServiceInterface
{
    /**
     * @param string $workspace_id
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function accept(string $workspace_id): void
    {
        $workspace = Workspace::findById($workspace_id);
        if (is_null($workspace)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        $invite = $workspace->getInvitations()->where(['user_id' => Yii::$app->user->getId()])->one();
        if (is_null($invite)) {
            throw new BadRequestHttpException('You are not invited to this workspace');
        }

        if(!$invite->delete()) {
            throw new ServerErrorHttpException('Unable to accept invite');
        };
        $user = User::findOne(Yii::$app->user->getId());
        $workspace->link('members', $user);
    }

    /**
     * @param string $workspace_id
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function decline(string $workspace_id): void
    {
        $workspace = Workspace::findById($workspace_id);
        if (is_null($workspace)) {
            throw new NotFoundHttpException('Workspace not found');
        }
        $invite = $workspace->getInvitations()->where(['user_id' => Yii::$app->user->getId()])->one();
        if (is_null($invite)) {
            throw new BadRequestHttpException('You are not invited to this workspace');
        }

        if(!$invite->delete()) {
            throw new ServerErrorHttpException('Unable to decline invite');
        };

    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function getAll(): array
    {
        $user = User::findOne(Yii::$app->user->getId());
        return $user->getInvitations()->all();
    }
}
