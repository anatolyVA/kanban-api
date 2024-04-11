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
                // users
                'GET,OPTIONS v1/users' => 'v1/user/index',
                'GET,OPTIONS v1/users/me' => 'v1/user/profile',
                'GET,OPTIONS v1/users/<id:[\w-]+>' => 'v1/user/view',
                'GET,OPTIONS v1/users/<id:[\w-]+>/workspaces' => 'v1/user/get-workspaces',
                'GET,OPTIONS v1/users/<id:[\w-]+>/tasks' => 'v1/user/get-tasks',
                // workspaces
                'GET,OPTIONS v1/workspaces' => 'v1/workspace/index',
                'GET,OPTIONS v1/workspaces/<id:[\w-]+>' => 'v1/workspace/view',
                'POST,OPTIONS v1/workspaces' => 'v1/workspace/create',
                'PUT,PATCH,OPTIONS v1/workspaces/<id:[\w-]+>' => 'v1/workspace/update',
                'DELETE,OPTIONS v1/workspaces/<id:[\w-]+>' => 'v1/workspace/delete',
                'POST,OPTIONS v1/workspaces/<id:[\w-]+>/members' => 'v1/workspace/invite',
                'DELETE,OPTIONS v1/workspaces/<id:[\w-]+>/members' => 'v1/workspace/exclude',
                'GET,OPTIONS v1/workspaces/<id:[\w-]+>/members' => 'v1/workspace/get-members',
                'POST,OPTIONS v1/workspaces/<id:[\w-]+>/exit' => 'v1/workspace/exit',
                // projects
                'POST,OPTIONS v1/workspaces/<workspace_id:[\w-]+>/projects' => 'v1/project/create',
                'GET,OPTIONS v1/workspaces/<workspace_id:[\w-]+>/projects' => 'v1/project/index',
                'GET,OPTIONS v1/projects/<id:[\w-]+>' => 'v1/project/view',
                'PUT,PATCH,OPTIONS v1/projects/<id:[\w-]+>' => 'v1/project/update',
                'DELETE,OPTIONS v1/projects/<id:[\w-]+>' => 'v1/project/delete',
                // views
                'POST,OPTIONS v1/projects/<project_id:[\w-]+>/views' => 'v1/view/create',
                'GET,OPTIONS v1/projects/<project_id:[\w-]+>/views' => 'v1/view/index',
                'GET,OPTIONS v1/views/<id:[\w-]+>' => 'v1/view/view',
                'PUT,PATCH,OPTIONS v1/views/<id:[\w-]+>' => 'v1/view/update',
                'DELETE,OPTIONS v1/views/<id:[\w-]+>' => 'v1/view/delete',
                'POST,OPTIONS v1/views/<id:[\w-]+>/members' => 'v1/view/add-members',
                'GET,OPTIONS v1/views/<id:[\w-]+>/members' => 'v1/view/get-members',
                'DELETE,OPTIONS v1/views/<id:[\w-]+>/members' => 'v1/view/exclude-members',
                // collections
                'POST,OPTIONS v1/views/<view_id:[\w-]+>/collections' => 'v1/collection/create',
                'GET,OPTIONS v1/views/<view_id:[\w-]+>/collections' => 'v1/collection/index',
                'GET,OPTIONS v1/collections/<id:[\w-]+>' => 'v1/collection/view',
                'PUT,PATCH,OPTIONS v1/collections/<id:[\w-]+>' => 'v1/collection/update',
                'DELETE,OPTIONS v1/collections/<id:[\w-]+>' => 'v1/collection/delete',
                // tasks
                'POST,OPTIONS v1/collections/<collection_id:[\w-]+>/tasks' => 'v1/task/create',
                'GET,OPTIONS v1/collections/<collection_id:[\w-]+>/tasks' => 'v1/task/index',
                'GET,OPTIONS v1/tasks/<id:[\w-]+>' => 'v1/task/view',
                'PUT,PATCH,OPTIONS v1/tasks/<id:[\w-]+>' => 'v1/task/update',
                'DELETE,OPTIONS v1/tasks/<id:[\w-]+>' => 'v1/task/delete',
                // invitations
                'GET,OPTIONS v1/invites' => 'v1/workspace-invite/index',
                'POST,OPTIONS v1/invites/workspaces/<workspace_id:[\w-]+>/accept' => 'v1/workspace-invite/accept',
                'POST,OPTIONS v1/invites/workspaces/<workspace_id:[\w-]+>/decline' => 'v1/workspace-invite/decline',
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
