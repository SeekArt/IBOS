<?php

// 程序根目录路径
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../../../../..' );

$yii = PATH_ROOT . '/library/yii.php';
$config = require PATH_ROOT . '/system/config/common.php';
$config['defaultController'] = 'main/api/index';
require ( $defines );
require ( $yii );

Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );

Yii::createApplication( 'application\core\components\Application', $config )->run();
