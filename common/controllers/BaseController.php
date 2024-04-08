<?php

namespace app\common\controllers;

use app\filters\RateLimitFilter;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    /** Get formatted response
     * @param $data
     * @param int $status Response status code
     * @return Response
     */
    protected function formatResponse($data, int $status = 200): Response
    {
        $response = \Yii::$app->response;
        $response->statusCode = $status;
        $response->data = $data;

        return $response;
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['rateLimit'] = [
            'class' => RateLimitFilter::class,
        ];

        return $behaviors;
    }
}
