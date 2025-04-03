<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'app-console',
    'name'=>'Easy Forms',
    'version' => '2.0.5',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii', 'app\components\Bootstrap', 'addons'],
    'controllerNamespace' => 'app\commands',
    'controllerMap' => [
        'migrate' => [
            'class' => 'app\components\console\controllers\MigrateController'
        ],
    ],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'scriptUrl' => 'https://www.example.com/', // Replace with the application base URL
        ],
        'authManager' => [
            'class' => 'Da\User\Component\AuthDbManagerComponent',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'settings' => [
            'class' => 'app\components\Settings'
        ],
        'addons' => [
            'class' => 'app\modules\addons\Module',
        ],
    ],
    'params' => $params,
];
