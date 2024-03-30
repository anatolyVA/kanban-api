<?php

namespace app\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\TooManyRequestsHttpException;

class RateLimitFilter extends ActionFilter
{
    public int $rateLimit = 100;
    public int $rateLimitInterval = 60;

    /**
     * @throws TooManyRequestsHttpException
     */
    public function beforeAction($action): bool
    {
        $cache = Yii::$app->cache;

        $cacheKey = 'rate_limit_' . Yii::$app->request->getUserIP();
        $count = $cache->get($cacheKey);

        if ($count === false) {
            $count = 1;
        } else {
            $count++;
        }

        if ($count > $this->rateLimit) {
            throw new TooManyRequestsHttpException('Too many requests');
        }

        $cache->set($cacheKey, $count, $this->rateLimitInterval);

        return parent::beforeAction($action);
    }
}