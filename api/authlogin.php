<?php

use application\core\model\Log;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\dashboard\model\Syscache;
use application\modules\main\utils\Main;
use application\modules\user\components\UserIdentity;
use application\modules\user\model\FailedIp;
use application\modules\user\model\FailedLogin;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

// 程序根目录路径
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../' );
$defines = PATH_ROOT . '/system/defines.php';
define( 'YII_DEBUG', true );
define( 'TIMESTAMP', time() );
define( 'CALLBACK', true );
$yii = PATH_ROOT . '/library/yii.php';
$mainConfig = require_once PATH_ROOT . '/system/config/common.php';
require_once ( $defines );
require_once ( $yii );
require_once ( 'login.php' );
Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );
Yii::createApplication( 'application\core\components\Application', $mainConfig );

$uid = Env::getRequest( 'uid' );
if ( !empty( $uid ) ) {
	$result = dologin( $uid );
	echo CJSON::encode( $result );
}