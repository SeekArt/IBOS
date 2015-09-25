<?php

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\main\model\Setting;
use application\modules\user\model\UserBinding;

// 程序根目录路径
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../../../' );
define( 'YII_DEBUG', true );
$defines = PATH_ROOT . '/system/defines.php';
defined( 'TIMESTAMP' ) or define( 'TIMESTAMP', time() );
$yii = PATH_ROOT . '/library/yii.php';
$mainConfig = require_once PATH_ROOT . '/system/config/common.php';
require_once ( $defines );
require_once ( $yii );
require_once '../../login.php';
Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );
Yii::createApplication( 'application\core\components\Application', $mainConfig );

$signature = Env::getRequest( 'signature' );
$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
$userId = Env::getRequest( 'userid' );
if ( strcmp( $signature, md5( $aeskey . $userId ) ) != 0 ) {
	Env::iExit( "签名错误" );
}
if ( !empty( $userId ) ) {
	$uid = UserBinding::model()->fetchUidByValue( $userId, 'wxqy' );
	if ( $uid ) {
		$resArr = dologin( $uid );
		if ( !IBOS::app()->user->isGuest && $resArr['code'] > '0' ) {
			$redirect = Env::getRequest( 'redirect' );
			$url = base64_decode( $redirect );
			$parse = parse_url( $url );
			if ( isset( $parse['scheme'] ) ) {
				header( 'Location:' . $url, true );
				exit();
			} else {
				header( 'Location:../../../' . $url, true );
				exit();
			}
		} else {
			Env::iExit( $resArr['msg'] );
		}
	}
}
Env::iExit( '用户验证失败,尝试以下步骤的操作：<br/>'
		. '1、在“微信企业号->通讯录”，找到并删除该用户<br/>'
		. '2、在“IBOS后台->微信->部门及用户同步”，同步该用户<br/>'
		. '3、邀请该用户关注企业号<br/>'
		. '如果还存在此提示，请将问题反馈给我们的工作人员' );
