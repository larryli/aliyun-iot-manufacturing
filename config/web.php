<?php

$config = [
    'id' => 'larryli/aliyun-iot-manufacturing',
    'name' => '阿里云物联网量产服务',
    'timeZone' => 'Asia/Shanghai',
    'language' => 'zh-CN',
    'basePath' => dirname(__DIR__) . '/src',
    'runtimePath' => dirname(__DIR__) . '/runtime',
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'bootstrap' => ['log', 'config'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@config' => dirname(__DIR__) . '/config',
        '@npm' => '@vendor/npm-asset',
    ],
    'defaultRoute' => 'device',
    'components' => [
        'cache' => [
            'class' => yii\caching\FileCache::class,
            'cachePath' => '@runtime/cache' . (YII_ENV_DEV ? '/dev' : ''),
        ],
        'config' => [
            'class' => app\Config::class,
            'config' => '@config/' . (YII_ENV_DEV ? 'dev.php' : 'config.php'),
        ],
        'db' => [
            'class' => yii\db\Connection::class,
        ],
        'formatter' => [
            'class' => app\Formatter::class,
        ],
        'iot' => [
            'class' => app\aliyun\Iot::class,
            'client' => [
                'class' => app\aliyun\AccessKeyClient::class,
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'except' => [
                        'yii\web\HttpException:4*',
                    ],
                    'logFile' => '@runtime/logs/' . (YII_ENV_DEV ? 'dev.log' : 'app.log'),
                    'logVars' => YII_ENV_DEV ? ['_GET', '_POST', '_FILES', '_SERVER'] : [],
                ],
            ],
        ],
        'mutex' => [
            'class' => yii\mutex\FileMutex::class,
        ],
        'request' => [
            'cookieValidationKey' => 'default',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'params' => [],
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '[::1]'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => yii\gii\Module::class,
        'allowedIPs' => ['127.0.0.1', '[::1]'],
    ];
}

return $config;
