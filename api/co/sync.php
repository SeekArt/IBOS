<?php

use application\core\utils\Cache as CacheUtil;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\core\utils\String;
use application\modules\dashboard\model\Syscache;
use application\modules\main\model\Setting;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\utils\User as UserUtil;

// 程序根目录路径
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../../' );
$defines = PATH_ROOT . '/system/defines.php';
define( 'YII_DEBUG', true );
define( 'TIMESTAMP', time() );
define( 'CALLBACK', true );
$yii = PATH_ROOT . '/library/yii.php';
$mainConfig = require_once PATH_ROOT . '/system/config/common.php';
require_once ( $defines );
require_once ( $yii );
require_once ( '../login.php' );
Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );
Yii::createApplication( 'application\core\components\Application', $mainConfig );
// 接收的参数
$signature = rawurldecode( Env::getRequest( 'signature' ) );
$timestamp = Env::getRequest( 'timestamp' );
$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
if ( strcmp( $signature, sha1( $aeskey . $timestamp ) ) != 0 ) {
	Env::iExit( '签名验证错误' );
}

// 接收信息处理
$result = trim( file_get_contents( "php://input" ), " \t\n\r" );
// 解析
if ( !empty( $result ) ) {
	$msg = json_decode( $result, true );
	switch ( $msg['op'] ) {
		case 'getuser':
			$res = getUserList();
			break;
		case 'getbinding':
			$res = getBindingList();
			break;
		case 'set':
			$res = setBinding( $msg['data'] );
			break;
		case 'unbind':
			$res = setUnbind();
			break;
		case 'creatuser' :
			$res = setCreat( $msg['data'] );
			break;
		default:
			$res = array( 'isSuccess' => false, 'msg' => '未知操作' );
			break;
	}
	Env::iExit( json_encode( $res ) );
}

/**
 * 
 * @return array
 */
function getUserList() {
	$users = array();
	$cache = Syscache::model()->fetchAllCache( 'users' );
	if ( !empty( $cache['users'] ) ) {
		foreach ( $cache['users'] as $user ) {
			$users[] = array(
				'uid' => $user['uid'],
				'realname' => $user['realname']
			);
		}
	}
	return array(
		'isSuccess' => true,
		'data' => $users
	);
}

/**
 * 获取绑定用户数组
 * @return array
 */
function getBindingList() {
	$bindings = UserBinding::model()->fetchAllByApp( 'co' );
	$users = array();
	if ( !empty( $bindings ) ) {
		foreach ( $bindings as $row ) {
			$user = User::model()->findByPk( $row['uid'] );
			if ( !empty( $user ) ) {
				$users[] = array(
					'uid' => $row['uid'],
					'bindvalue' => $row['bindvalue'],
					'realname' => $user->realname,
				);
			}
		}
	}
	return array(
		'isSuccess' => true,
		'data' => $users
	);
}

/**
 * 设置绑定用户列表
 * @param array $list
 * @return array
 */
function setBinding( $list ) {
	UserBinding::model()->deleteAllByAttributes( array( 'app' => 'co' ) );
	$count = 0;
	foreach ( $list as $row ) {
		$res = UserBinding::model()->add( array( 'uid' => $row['uid'], 'bindvalue' => $row['guid'], 'app' => 'co' ) );
		$res and $count++;
	}
	// 设置绑定标识
	if ( $count > 0 ) {
		Setting::model()->updateSettingValueByKey( 'cobinding', '1' );
	}
	return array( 'isSuccess' => true );
}

/**
 * 解除绑定
 * @return 
 */
function setUnbind() {
	UserBinding::model()->deleteAllByAttributes( array( 'app' => 'co' ) );
	Setting::model()->updateSettingValueByKey( 'cobinding', '0' );
	Setting::model()->updateSettingValueByKey( 'coinfo', '' );
	return array( 'isSuccess' => true );
}

/**
 * 创建并绑定用户
 * @param array $param
 * @return array
 */
function setCreat( $param ) {
	$param['salt'] = !empty( $param['salt'] ) ? $param['salt'] : String::random( 6 );
	$param['password'] = !empty( $param['password'] ) ? $param['password'] : md5( $param['mobile'] . $param['salt'] );
	$param['groupid'] = !empty( $param['groupid'] ) ? $param['groupid'] : '2';
	$param['createtime'] = TIMESTAMP;
	$data = User::model()->create( $param );
	$newId = User::model()->add( $data, true );
	if ( $newId ) {
		UserCount::model()->add( array( 'uid' => $newId ) );
		$ip = IBOS::app()->setting->get( 'clientip' );
		UserStatus::model()->add(
				array(
					'uid' => $newId,
					'regip' => $ip,
					'lastip' => $ip
				)
		);
		UserProfile::model()->add( array( 'uid' => $newId ) );
		// 创建绑定
		$res = UserBinding::model()->add( array( 'uid' => $newId, 'bindvalue' => $param['guid'], 'app' => 'co' ) );

		// 重建缓存，给新加的用户生成缓存
//		$newUser = User::model()->fetchByPk($newId);
//		$users = UserUtil::loadUser();
//		$users[$newId] = UserUtil::wrapUserInfo($newUser);
//		User::model()->makeCache($users);
		// 更新组织架构js调用接口
//		Org::update();
		// 同步用户钩子
//		Org::hookSyncUser($newId, $origPass, 1);
//		CacheUtil::update();
	} else {
		return array( 'isSuccess' => FALSE );
	}
	return array( 'isSuccess' => true );
}
