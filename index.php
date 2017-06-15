<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
//正式环境
//defined ( 'YII_DEBUG' ) or define ( 'YII_DEBUG', false );
//defined ( 'YII_ENV' ) or define ( 'YII_ENV', 'PRODUCTION' );

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/config/web.php');

(new yii\web\Application($config))->run();
