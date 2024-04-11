<?php

namespace app\modules\v1\controllers;

use app\common\controllers\AccessController;
use app\modules\v1\services\WorkspaceInviteService;
use Ramsey\Uuid\Uuid;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class WorkspaceInviteController extends AccessController
{
    private WorkspaceInviteService $workspace_invite_service;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['get', 'options'],
                'accept' => ['post', 'options'],
                'decline' => ['post', 'options']
            ]
        ];

        return $behaviors;
    }

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->workspace_invite_service = new WorkspaceInviteService();
    }

    /**
     * @param string $workspace_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionAccept(string $workspace_id): Response
    {
        if(!Uuid::isValid($workspace_id)) {
            throw new BadRequestHttpException('Invalid workspace id');
        }
        $this->workspace_invite_service->accept($workspace_id);
        return $this->formatResponse('Invitation has been accepted');
    }

    /**
     * @param string $workspace_id
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDecline(string $workspace_id): Response
    {
        if(!Uuid::isValid($workspace_id)) {
            throw new BadRequestHttpException('Invalid workspace id');
        }
        $this->workspace_invite_service->decline($workspace_id);
        return $this->formatResponse('Invitation has been declined');
    }

    /**
     * @return Response
     * @throws InvalidConfigException
     */
    public function actionIndex(): Response
    {
        return $this->formatResponse($this->workspace_invite_service->getAll());
    }
}