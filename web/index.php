<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

//header("Access-Control-Allow-Origin: *"); // Разрешаем доступ с любого домена
//header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Разрешаем методы POST, GET и OPTIONS
//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"); // Разрешаем указанные заголовки
//
//// Заголовок Access-Control-Allow-Credentials используется для указания, что CORS-запросы могут быть с использованием куки-данных
//header('Access-Control-Allow-Credentials: true');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
