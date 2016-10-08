<?php

use application\core\utils\Api;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Cache as CacheModel;
use application\modules\dashboard\model\Syscache;
use application\modules\dashboard\utils\CoSync;
use application\modules\department\model\DepartmentBinding;
use application\modules\main\model\Setting;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../../');
$defines = PATH_ROOT . '/system/defines.php';
define('YII_DEBUG', true);
define('TIMESTAMP', time());
define('CALLBACK', true);
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$mainConfig = require_once PATH_ROOT . '/system/config/common.php';
require_once($yii);
require_once('../login.php');
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);
// 接收的参数
$signature = rawurldecode(Env::getRequest('signature'));
$timestamp = Env::getRequest('timestamp');
$aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
define('AESKEY', $aeskey);
if (strcmp($signature, sha1($aeskey . $timestamp)) != 0) {
	Env::iExit('error:sign error');
}

// 接收信息处理
$result = trim(file_get_contents("php://input"), " \t\n\r");

// 解析
if (!empty($result)) {
	$msg = CJSON::decode($result, true);
	$msgOp = $msg['op'];
	$msgData = isset($msg['data']) ? $msg['data'] : array();
	$msgPlatform = isset($msg['platform']) ? $msg['platform'] : 'co';
	switch ($msgOp) {
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
			$res = setBinding($msg['data']);
			break;
		case 'unbind':
			$res = setUnbind();
			break;
		case 'creatuser' :
			$res = setCreat($msgData, $msgPlatform);
			break;
		case 'creatdepartment':
			$res = setCreatDapartment($msgData, $msgPlatform);
			break;
		case 'verifywebsite':
			$res = verifyWebSite();
			break;
		//以下是新的同步部门用户接口
		//新的同步机制以1000个数据为单位
		case 'syncDept':
			$res = syncDept($msgData, $msgPlatform);
			break;
		case 'syncUser':
			$res = syncUser($msgData, $msgPlatform);
			break;
		case 'ibosSync':
			$res = ibosSync($msgPlatform);
			break;
		case 'getBindingUserNum':
			$res = getBindingUserNum($msgPlatform);
			break;
		case 'getAutoSyncStatus':
			$res = getAutoSyncStatus();
			break;
		case 'setAutoSyncStatus':
			$status = isset($msgData['status']) ? $msgData['status'] : 0;
			$res = setAutoSyncStatus($status);
			break;
		default:
			$res = array('isSuccess' => false, 'msg' => '未知操作');
			break;
	}
	header('Content-Type:application/json; charset=' . CHARSET);
	exit(CJSON::encode($res));
}

/**
 * 获取用户id以及用户真实姓名
 * @return array
 */
function getUserList() {
	User::model()->setSelect('uid,realname');
	$users = User::model()->findUserByUid();
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
	$users = User::model()->fetchAllByUids(NULL, false);
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
	$cache = Syscache::model()->fetchAllCache('department');
	if (!empty($cache['department'])) {
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
	$bindings = UserBinding::model()->fetchAllByApp('co');
	$users = array();
	if (!empty($bindings)) {
		foreach ($bindings as $row) {
			$user = User::model()->findByPk($row['uid']);
			if (!empty($user)) {
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
function setBinding($list) {
	//UserBinding::model()->deleteAllByAttributes( array( 'app' => 'co' ) );
	$count = 0;
	foreach ($list as $row) {
		//判断是否已经绑定,此处做了容错处理
		$data = array('uid' => $row['uid'], 'bindvalue' => $row['guid'], 'app' => 'co');
		$checkbinding = UserBinding::model()->find(sprintf("`uid` = '%s' AND `app` = 'co'", $row['uid']));
		if (empty($checkbinding)) {
			$res = UserBinding::model()->add($data);
		} else {
			$res = UserBinding::model()->modify($checkbinding['id'], $data);
		}
		$res and $count++;
	}
	// 设置绑定标识
	if ($count > 0) {
		Setting::model()->updateSettingValueByKey('cobinding', '1');
	}
	return array('isSuccess' => true, 'data' => true);
}

/**
 * 解除绑定
 * @return
 */
function setUnbind() {
	UserBinding::model()->deleteAllByAttributes(array('app' => 'co'));
	DepartmentBinding::model()->deleteAllByAttributes(array('app' => 'co'));
	Setting::model()->updateSettingValueByKey('cobinding', '0');
	Setting::model()->updateSettingValueByKey('coinfo', '');
	Setting::model()->updateSettingValueByKey('autosync', serialize(array('status' => 0, 'lastsynctime' => 0)));
	CacheModel::model()->deleteAll("FIND_IN_SET( cachekey, 'cocreatelist,coremovelist,iboscreatelist,ibosremovelist,successinfo' )");
	return array('isSuccess' => true);
}

/**
 * 创建并绑定用户
 * @param array $param
 * @return array
 * update by Sam 2015-08-24 <gzxgs@ibos.com.cn>
 */
function setCreat($msgData, $msgPlatform) {
	// CoSync::CreateUser( $data ); //直接调用工具类执行创建用户，暂时不用理会返回信息
	CoSync::createCoUserByList($data);
}

/**
 * 创建部门
 * @author Sam
 * @time 2015-08-24 <gzxgs@ibos.com.cn>
 */
function setCreatDapartment($msgData, $msgPlatform) {
	return syncDept($msgData, $msgPlatform);
}

/**
 * 验证当前项目环境是否可被外部访问
 * 告诉外部该接口的调用方式后，让对方调用这个接口
 * 如果能返回正确的数据则表示当前环境可被对方访问
 * @return string json 数据
 */
function verifyWebSite() {
	$result = array(
		'isSuccess' => TRUE,
		'msg' => '当前 IBOS 可被正常访问！',
	);
	Env::iExit(json_encode($result));
}

/**
 * 同步部门
 * 第三方（酷办公）传过来的一个单位的部门数据已经保证了从上到下的顺序
 */
function syncDept($msgData, $msgPlatform) {
	$bindArray = array(); //Cache::get( 'sync_department_binding' );
	if (empty($bindArray)) {
		$list = Ibos::app()->db->createCommand()
			->select('deptid,bindvalue')
			->from('{{department_binding}}')
			->where(" `app` = '{$msgPlatform}' ")
			->queryAll();
		$bindArray = array();
		foreach ($list as $row) {
			$bindArray[$row['bindvalue']] = $row['deptid'];
		}
	}
	$return = array('bind' => array(),);
	$connection = Ibos::app()->db;
	$transaction = $connection->beginTransaction();
	foreach ($msgData as $row) {
		if (!isset($bindArray[$row['deptid']])) {
			if ($row['pid'] == '0') {
				//这个关系是不会写在数据库的
				$pDeptid = 0;
			} else {
				//这个等式一定成立的，不然就是传过来的顺序错了！
				$pDeptid = $bindArray[$row['pid']];
			}
			$criteria = new CDbCriteria();
			$criteria->condition = " `pid` = :pid AND `deptname` = :deptname ";
			$criteria->params = array(
				':pid' => $pDeptid,
				':deptname' => $row['deptname'],
			);
			$findByDeptName = $connection->schema->commandBuilder
				->createFindCommand('{{department}}', $criteria)
				->queryRow();
			if (!empty($findByDeptName)) {
				$deptid = $findByDeptName['deptid'];
			} else {
				$connection->schema->commandBuilder
					->createInsertCommand('{{department}}', array(
						'deptname' => $row['deptname'],
						'pid' => $pDeptid,
					))
					->execute();
				$deptid = $connection->lastInsertID;
			}
			$connection->schema->commandBuilder
				->createInsertCommand('{{department_binding}}', array(
					'deptid' => $deptid,
					'bindvalue' => $row['deptid'],
					'app' => $msgPlatform,
				))
				->execute();
			$bindArray[$row['deptid']] = $deptid;
			//Cache::set( 'sync_department_binding', $bindArray );
		}
		$return['bind'][$row['deptid']] = $bindArray[$row['deptid']];
	}
	$transaction->commit();
	return array(
		'isSuccess' => true,
		'msg' => '成功',
		'data' => $return,
	);
}

/**
 * 同步用户
 * 第三方（酷办公）传过来一个单位的用户数据
 */
function syncUser($msgData, $msgPlatform) {
	$bindArray = array(); //Cache::get( 'sync_user_binding' );
	if (empty($bindArray)) {
		$list = Ibos::app()->db->createCommand()
			->select('uid,bindvalue')
			->from('{{user_binding}}')
			->where(" `app` = '{$msgPlatform}' ")
			->queryAll();
		$bindArray = array();
		foreach ($list as $row) {
			$bindArray[$row['bindvalue']] = $row['uid'];
		}
	}
	if (!empty($msgData) && isset($msgData['0']['status'])) { // 老版本没有传status过来
		$delete = classify($msgData, false);
		$msgData = classify($msgData);
	}
	$return = array('bind' => array(), 'delete' => array());
	$ip = Ibos::app()->setting->get('clientip');
	$connection = Ibos::app()->db;
	$transaction = $connection->beginTransaction();
	foreach ($msgData as $row) {
		if (!isset($bindArray[$row['guid']])) {
			$criteria = new CDbCriteria();
			//被禁用的话，不会写入绑定的
			$criteria->condition = " `mobile` = :mobile";
			$criteria->params = array(
				':mobile' => $row['mobile'],
			);
			$findByMobile = $connection->schema->commandBuilder
				->createFindCommand('{{user}}', $criteria)
				->queryRow();
			unset($criteria);
			$writeBind = false;
			if (!empty($findByMobile) && $findByMobile['status'] != 2) {
				$uid = $findByMobile['uid'];
				$writeBind = true;
			} else if (!empty($findByMobile) && $findByMobile['status'] == 2) {
				$uid = $findByMobile['uid'];
				//绑定表里没有，user表有且禁用，什么也不做
			} else {
				$writeBind = true;
				$deptId = 0;
				if (!empty($row['deptid'])) { // 有部门的情况下
					$criteria = new CDbCriteria();
					$criteria->condition = " `bindvalue` = :bindvalue AND `app` = :app ";
					$criteria->params = array(
						':bindvalue' => $row['deptid'],
						':app' => $msgPlatform,
					);
					$dept = $connection->schema->commandBuilder
						->createFindCommand('{{department_binding}}', $criteria)
						->queryRow();
					unset($criteria);
					if (isset($dept['deptid']) && !empty($dept['deptid'])) {
						$deptId = $dept['deptid'];
					}
				}
				
				$connection->schema->commandBuilder
					->createInsertCommand('{{user}}', array(
						'username' => $row['username'],
						'deptid' => $deptId,
						'roleid' => 3, //普通成员
						'realname' => $row['realname'],
						'password' => $row['password'],
						'gender' => $row['gender'] ? 0 : 1, //酷办公0男1女，IBOS的0女1男
						'weixin' => $row['weixin'],
						'mobile' => $row['mobile'],
						'email' => $row['email'],
						'createtime' => TIMESTAMP,
						'salt' => $row['salt'],
						'guid' => StringUtil::createGuid(),
					))
					->execute();
				$uid = $connection->lastInsertID;
				$connection->schema->commandBuilder
					->createInsertCommand('{{user_count}}', array(
						'uid' => $uid,
					))
					->execute();
				$connection->schema->commandBuilder
					->createInsertCommand('{{user_status}}', array(
						'uid' => $uid,
						'regip' => $ip,
						'lastip' => $ip,
					))
					->execute();
				$connection->schema->commandBuilder
					->createInsertCommand('{{user_profile}}', array(
						'uid' => $uid,
					))
					->execute();
			}
			if (true === $writeBind) {
				$connection->schema->commandBuilder
					->createInsertCommand('{{user_binding}}', array(
						'uid' => $uid,
						'bindvalue' => $row['guid'],
						'app' => $msgPlatform,
					))
					->execute();
				$bindArray[$row['guid']] = $uid;
				$return['bind'][$row['uid']] = $uid;
			} else {
				$return['delete'][$row['uid']] = $uid;
			}
		} else {
			$uid = $bindArray[$row['guid']];
			//如果绑定表里有值，但是实际上用户是被禁用的，那么应该删除了这个关系
			$criteria = new CDbCriteria();
			//被禁用的话，不会写入绑定的
			$criteria->condition = " `uid` = :uid AND `status` = 2 ";
			$criteria->params = array(
				':uid' => $uid,
			);
			$findByUid = $connection->schema->commandBuilder
				->createFindCommand('{{user}}', $criteria)
				->queryRow();
			unset($criteria);
			if ($findByUid) {
				$criteria = new CDbCriteria();
				$criteria->condition = " `uid` = :uid AND"
					. " `bindvalue` = :bindvalue AND"
					. " `app` = :app ";
				$criteria->params = array(
					':uid' => $uid,
					':bindvalue' => $row['guid'],
					':app' => $msgPlatform,
				);
				$connection->schema->commandBuilder
					->createDeleteCommand('{{user_binding}}', $criteria)
					->execute();
				unset($criteria);
				unset($bindArray[$row['guid']]);
				$return['delete'][$row['uid']] = $uid;
			} else {
				$return['bind'][$row['uid']] = $uid;
			}
		}
		//Cache::set( 'sync_user_binding', $bindArray );
	}
	if(isset($delete)) {
		foreach ($delete as $_delete) {
			$uid = $bindArray[$_delete['guid']];
			UserBinding::model()->deleteAll('uid = :uid AND bindvalue = :bindvalue AND app = :app', array(
				'uid' => $uid,
				'bindvalue' => $_delete['guid'],
				'app' => $msgPlatform,
			));
			User::model()->updateAll(array('status' => User::USER_STATUS_ABANDONED),'uid = :uid', array('uid' => $uid));
			unset($bindArray[$_delete['guid']]);
			$return['delete'][$_delete['uid']] = $uid;
		}
	}
	$transaction->commit();
	return array(
		'isSuccess' => true,
		'msg' => '成功',
		'data' => $return,
	);
}

function ibosSync($msgPlatform) {
	$coinfo = StringUtil::utf8Unserialize(Setting::model()->fetchSettingValueByKey('coinfo'));
	if (!empty($coinfo['corpid'])) {
		define('CORPID', $coinfo['corpid']);
		$pidArray = createPidOrder();
		$per = 50;
		$times = 10;
		$everyPid = floor(count($pidArray) / $times) + 1;
		$array = array_chunk($pidArray, $everyPid);
		$pid = array_shift($array);
		set_time_limit(0);
		while (1) {
			$deptArray = findDeptByPid($msgPlatform, implode(',', $pid), $per);
			if (count($deptArray) < $per) {
				$pid = array_shift($array);
				if (empty($array)) {
					break;
				}
			}
			if (!empty($deptArray)) {
				sendSyncDept($msgPlatform, $deptArray);
			} else {
				continue;
			}
			if (empty($pidArray)) {
				break;
			}
		}
		$limit = 1000;
		$offset = 0;
		while (1) {
			$userArray = sendSyncUser($msgPlatform, $limit, $offset);
			if (empty($userArray)) {
				break;
			} else {
				$offset += $limit;
			}
		}

		return array(
			'isSuccess' => true,
			'msg' => '成功',
			'data' => array(
				'corpid' => CORPID
			),
		);
	} else {
		return array(
			'isSuccess' => false,
			'msg' => '没有绑定酷办公了啦~'
		);
	}
}

function findDeptByPid($msgPlatform, $pid, $per) {
	$list = Ibos::app()->db->createCommand()
		->select('deptname,deptid,pid')
		->from('{{department}}')
		->where(" `pid` IN ({$pid})")
		->andWhere(" `deptid` NOT IN"
			. " ( SELECT `deptid` FROM `{{department_binding}}` WHERE"
			. " `app` = '{$msgPlatform}' )")
		->limit($per)
		->queryAll();
	return $list;
}

function createPidOrder($pid = array(0)) {
	static $pidArray = array(0);
	$pidString = implode(',', $pid);
	$pidList = Ibos::app()->db->createCommand()
		->select('deptid')
		->from('{{department}}')
		->where(" `pid` IN ( {$pidString} )")
		->queryColumn();
	if (!empty($pidList)) {
		$pidArray = array_merge($pidArray, $pidList);
		return createPidOrder($pidList);
	} else {
		return $pidArray;
	}
}

function sendSyncDept($msgPlatform, $deptArray) {
	$url = getUrl('syncdept');
	$post = array_values($deptArray);
	$res = Api::getInstance()->fetchResult($url, CJSON::encode($post), 'post');
	if (is_string($res)) {
		$array = CJSON::decode($res);
		!empty($array['data']['bind']) && deptBind($msgPlatform, $array['data']['bind']);
		//todo:: 部门是否需要删除
	}
}

function deptBind($msgPlatform, $array) {
	foreach ($array as $deptid => $bindvalue) {
		Ibos::app()->db->createCommand()
			->insert('{{department_binding}}', array(
				'deptid' => $deptid,
				'bindvalue' => $bindvalue,
				'app' => $msgPlatform
			));
	}
}

function getUrl($type) {
	$api = 'http://www.kubangong.com/api/sync/' . $type;
	$time = time();
	return Api::getInstance()->buildUrl($api, array(
		'signature' => sha1(AESKEY . $time),
		'timestamp' => $time,
		'corpid' => CORPID,
		'type' => 'ibos',
	));
}

function sendSyncUser($msgPlatform, $limit, $offset) {
	$userArray = Ibos::app()->db->createCommand()
		->select(implode(',', getSelectUser()))
		->from('{{user}} u')
		->leftJoin('{{user_profile}} up', ' `u`.`uid` = `up`.`uid` ')
		->where(" u.uid NOT IN ( SELECT `uid` FROM {{user_binding}} WHERE"
			. " `app` = '{$msgPlatform}' )")
		->andWhere(" u.status != '2' ")
		->limit($limit)
		->offset($offset)
		->queryAll();
	if (empty($userArray)) {
		return array();
	}
	$url = getUrl('syncuser');
	$post = array_values($userArray);
	$res = Api::getInstance()->fetchResult($url, CJSON::encode($post), 'post');
	if (is_string($res)) {
		$array = CJSON::decode($res);
		!empty($array['data']['bind']) && userBind($msgPlatform, $array['data']['bind']);
		!empty($array['data']['delete']) && userDelete($msgPlatform, $array['data']['delete']);
	}
	return $userArray;
}

function userBind($msgPlatform, $array) {
	foreach ($array as $uid => $bindvalue) {
		Ibos::app()->db->createCommand()
			->insert('{{user_binding}}', array(
				'uid' => $uid,
				'bindvalue' => $bindvalue,
				'app' => $msgPlatform
			));
	}
}

function userDelete($msgPlatform, $array) {
	foreach ($array as $uid => $bindvalue) {
		Ibos::app()->db->createCommand()
			->delete('{{user_binding}}'
				, " `uid` = '{$uid}' AND"
				. " `bindvalue` = '{$bindvalue}' AND"
				. " `app` = '{$msgPlatform}' ");
		Ibos::app()->db->createCommand()
			->update('{{user}}'
				, array('status' => 2)
				, " `uid` = '{$uid}' ");
	}
}

function getSelectUser() {
	return array(
		'u.deptid',
		'u.uid',
		'u.mobile',
		'u.password',
		'u.salt',
		'u.email',
		'u.realname',
		'u.username nickname',
		'u.weixin wechat',
		'up.qq',
		'u.gender',
		'up.birthday',
		'up.address'
	);
}

/**
 * 获取绑定酷办公的用户人数
 *
 * @param $platform
 * @return array
 */
function getBindingUserNum($platform) {
	$userNum = UserBinding::model()->fetchUserNumByApp($platform);
	return array(
		'isSuccess' => true,
		'msg' => '成功',
		'data' => array(
			'usernum' => $userNum,
		),
	);
}

/**
 * 获取自动同步功能的启动状态
 *
 * @return bool
 */
function getAutoSyncStatus() {
	$retData = array(
		'isSuccess' => true,
		'msg' => '成功',
		'data' => array(
			'status' => false,
		),
	);
	$value = Setting::model()->fetchSettingValueByKey('autosync');

	// 未设置自动同步功能
	if (false === $value) {
		return $retData;
	}

	// 设置并启用了自动同步功能
	$autoSync = StringUtil::utf8Unserialize($value);
	if (isset($autoSync['status']) && 1 == $autoSync['status']) {
		$retData['data']['status'] = true;
	}

	return $retData;
}

/**
 * 设置自动同步功能状态
 *
 * @param integer $status 是否开启自动同步功能。1是、0否
 * @return array
 */
function setAutoSyncStatus($status) {
	$status = (int)$status;
	CoSync::setAutoSync($status);

	return array(
		'isSuccess' => true,
		'msg' => '成功',
	);
}

/**
 * 分类接收到的数组，按状态区分,bind为绑定，delete为移除
 * @param $data
 * @param bool $flag
 * @return array
 */
function classify($data, $flag = true) {
	if (!$flag) {
		$delete = array_filter(array_map(function($v) { if('0' == $v['status']) {return $v;}
		} , $data));
		return $delete;
	}
	$bind = array_filter(array_map(function($v) { if('1' == $v['status']) {return $v;}
	} , $data));
	return $bind;
}