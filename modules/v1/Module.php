<?php

namespace app\modules\v1;

use app\modules\v1\services\AuthService;
use Yii;

class Module extends \yii\base\Module
{
    public function init(): void
    {
        parent::init();
        Yii::$app->user->enableSession = false;
        $container = Yii::$container;
        \Yii::configure($this, require __DIR__ . '/config.php');
        $container->set('app\common\interfaces\AuthServiceInterface', AuthService::class);
    }
}
