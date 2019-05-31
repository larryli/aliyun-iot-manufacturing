<?php

defined('YII_ENV') or define('YII_ENV', getenv('ENV') ?: 'prod');
defined('YII_DEBUG') or define('YII_DEBUG', YII_ENV === 'dev');

//defined('TEST_AJAX') or define('TEST_AJAX', true); // 测试 ajax 分页

require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

$config = require dirname(__DIR__) . '/config/web.php';

/** @noinspection PhpUnhandledExceptionInspection */
(new yii\web\Application($config))->run();
