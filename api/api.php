<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\main\model\Setting;

// 程序根目录路径
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../' );
$defines = PATH_ROOT . '/system/defines.php';
require_once ( $defines );
define( 'TIMESTAMP', time() );
define( 'YII_DEBUG', true );
$yii = PATH_ROOT . '/library/yii.php';
require_once ( $yii );
$mainConfig = require_once PATH_ROOT . '/system/config/common.php';
Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );
Yii::createApplication( 'application\core\components\Application', $mainConfig );
// 接收信息处理
$result = trim( file_get_contents( "php://input" ), " \t\n\r" );
$signature = Ibos::app()->getRequest()->getQuery( 'signature' );
$timestamp = Ibos::app()->getRequest()->getQuery( 'timestamp' );
$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
if ( strcmp( $signature, md5( $aeskey . $timestamp ) ) != 0 ) {
	Env::iExit( "签名错误" );
}
if ( !empty( $result ) ) {
	$msg = CJSON::decode( $result, true );
	switch ( $msg['op'] ) {
		case 'access':
			$return = 'success';
			break;
		case 'version':
			$return = strtolower( implode( ',', array( ENGINE, VERSION, VERSION_DATE ) ) );
			break;
		default:
			$return = '不予受理的请求类型';
	}
} else {
	$return = '请求数据不允许为空';
}
Env::iExit( $return );

