<?php

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Cache;
use application\core\utils\Org;
use application\core\utils\String;
use application\core\utils\Convert;
use application\modules\dashboard\model\Syscache;
use application\modules\main\model\Setting;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\department\model\Department;
use application\modules\user\model\UserBinding;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;

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
	Env::iExit( 'error:sign error' );
}

// 接收信息处理
$result = trim( file_get_contents( "php://input" ), " \t\n\r" );
// 解析
if ( !empty( $result ) ) {
	$msg = CJSON::decode( $result, true );
	switch ( $msg['op'] ) {
		case 'getuser':
			$res = getUserList();
			break;
		case 'getdept':
			$res = getDepartmentList();
			break;
		case 'getuserallinfo':
			$res = getUserListAllInfo();
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
		case 'creatdepartment':
			$res = setCreatDapartment( $msg['data'] );
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
 * 获取用户列表所有信息， 同步用户时调用
 * @return type
 */
function getUserListAllInfo() {
	$users = array();
	$cache = Syscache::model()->fetchAllCache( 'users' );
	if ( !empty( $cache['users'] ) ) {
		$users = $cache['users'];
	}
	return array(
		'isSuccess' => true,
		'data' => $users
	);
}

/**
 * 获取部门列表数据,同步部门时调用
 * @return array
 */
function getDepartmentList() {
	$departments = array();
	$cache = Syscache::model()->fetchAllCache( 'department' );
	if ( !empty( $cache['department'] ) ) {
		$departments = $cache['department'];
	}
	return array(
		'isSuccess' => true,
		'data' => $departments
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
	//UserBinding::model()->deleteAllByAttributes( array( 'app' => 'co' ) );
	$count = 0;
	foreach ( $list as $row ) {
		$checkbinding = UserBinding::model()->find( sprintf( "`uid` = '%s' AND `bindvalue` = '%s' AND `app` = 'co'", $row['uid'], $row['guid'] ) );
		if ( empty( $checkbinding ) ) {
			$res = UserBinding::model()->add( array( 'uid' => $row['uid'], 'bindvalue' => $row['guid'], 'app' => 'co' ) );
			$res and $count++;
		}
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
 * update by Sam 2015-08-24 <gzxgs@ibos.com.cn>
 */
function setCreat( $data ) {
	//根据手机号去判断是否需要添加用户信息
	if ( !empty( $data ) ) {
		//此处加载缓存以及更新用户缓存必须，要不然会出错。
		Cache::load( 'usergroup' ); // 要注意小写
		Cache::update( 'users' ); // 用户缓存依赖usergroup缓存，单独更新
		foreach ( $data as $param ) {
			$checkIsExist = User::model()->checkIsExistByMobile( $param['mobile'] );
			//判断手机号不存在,执行创建用户
			if ( $checkIsExist === false ) {
	$param['salt'] = !empty( $param['salt'] ) ? $param['salt'] : String::random( 6 );
				$param['password'] = !empty( $param['password'] ) ? $param['password'] : md5( md5( $param['mobile'] ) . $param['salt'] );
	$param['groupid'] = !empty( $param['groupid'] ) ? $param['groupid'] : '2';
	$param['createtime'] = TIMESTAMP;
				$param['guid'] = String::createGuid();
	$data = User::model()->create( $param );
				unset( $data['uid'] );
	$newId = User::model()->add( $data, true );
	if ( $newId ) {
					UserCount::model()->add( array( 'uid' => $newId ) );
					$ip = IBOS::app()->setting->get( 'clientip' );
					UserStatus::model()->add( array( 'uid' => $newId, 'regip' => $ip, 'lastip' => $ip ) );
					//往user_profile添加用户相关数据（即使为空记录），要不然会报错
					UserProfile::model()->add( array( 'uid' => $newId ) );
					//创建用户绑定
					UserBinding::model()->add( array( 'uid' => $newId, 'bindvalue' => $param['guid'], 'app' => 'co' ) );
					// 重建缓存，给新增用户生成缓存
					$newUser = User::model()->fetchByPk( $newId );
					$users = UserUtil::loadUser();
					$users[$newId] = UserUtil::wrapUserInfo( $newUser );
					User::model()->makeCache( $users );
					// 更新组织架构js调用接口
					//Org::update();
					// 同步用户钩子
					//Org::hookSyncUser($newId, $origPass, 1);
					//CacheUtil::update();
				}
			}
		}
	}
}

/**
 * 创建部门
 * @param type $coDepartmentList 添加部门列表
 * @author Sam
 * 2015-08-24 <gzxgs@ibos.com.cn>
 */
function setCreatDapartment( $coDepartmentList ) {
	//对比两个部门，得到不同的部门ID数组，然后设置所属于这些部门的用户的deptID设置为0
	$ibosDepartmentList = Department::model()->fetchAll();
	$ibosDepartmentIdArray = Convert::getSubByKey( $ibosDepartmentList, $pKey = 'deptid' );
	$coDepartmentIdArray = Convert::getSubByKey( $coDepartmentList, $pKey = 'deptid' );
	//获取差异数组ID
	$DepartmentIdsResult = array_diff( $ibosDepartmentIdArray, $coDepartmentIdArray );
	$users = Syscache::model()->fetchAllCache( 'users' );
	$uids = Convert::getSubByKey( $users['users'], 'uid' );
	$attributes = array( 'deptid' => 0 );
	$condition = "";
	if ( !empty( $DepartmentIdsResult ) ) {
		$differentDepartmentIds = implode( ',', $DepartmentIdsResult );
		$condition = '`deptid` IN  (' . $differentDepartmentIds . ')';
	}
	User::model()->updateByConditions( $uids, $attributes, $condition );
	//删除IBOS所有部门
	Department::model()->deleteAll();
	foreach ( $coDepartmentList as $department ) {
		Department::model()->add( $department );
	}
	//更新部门缓存，目的是为了IBOS能够及时看到同步数据
	Cache::update( array( 'department' ) );
}
