<?php


$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ]
    ],
    'components' => [
        'jwt' => [
            'class' => \kaabar\jwt\Jwt::class,
            'key' => 'hDyWWaYg!lh9J$D3qZW87EwMa.rZsCdE',  //typically a long random string
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Zt3PlZjaOKoU_zq3ni_4s4bhgmBa9_D7',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'baseUrl' => ''
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'format' => yii\web\Response::FORMAT_JSON,
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'body' => $response->data,
                        'status' => $response->statusCode
                    ];
                }
            },
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\common\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            //'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                'GET,OPTIONS v1/users' => 'v1/user/index',
                'GET,OPTIONS v1/users/<id:[\w-]+>' => 'v1/user/view',
                'GET,OPTIONS v1/workspaces' => 'v1/workspace/index',
                'GET,OPTIONS v1/workspaces/<id:[\w-]+>' => 'v1/workspace/view',
                'POST v1/workspaces' => 'v1/workspace/create',
                'PUT,PATCH v1/workspaces/<id:[\w-]+>' => 'v1/workspace/update',
                'DELETE v1/workspaces/<id:[\w-]+>' => 'v1/workspace/delete',
                'POST v1/workspaces/<id:[\w-]+>/invite' => 'v1/workspace/invite',
                'POST v1/workspaces/<id:[\w-]+>/exclude' => 'v1/workspace/exclude',
                'POST v1/workspaces/<id:[\w-]+>/exit' => 'v1/workspace/exit',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
