<?php

/**
 * 用户模块函数库
 *
 * @package application.app.user.utils
 * @version $Id: User.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\user\utils;

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Credit;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\Org;
use application\core\utils\String;
use application\core\utils\Xml;
use application\modules\contact\model\Contact;
use application\modules\dashboard\model\CreditLog;
use application\modules\department\model as DepartmentModel;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\main\model as MainModel;
use application\modules\position\model as PositionModel;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\role\model\RoleRelated;
use application\modules\role\utils\Role as RoleUtil;
use application\modules\user\model as UserModel;
use application\core\utils\PHPExcel;
use CJSON;

class User {

	/**
	 * 获取用户资料
	 * @staticvar array $modelFields
	 * @return string|null
	 */
	public static function getUserProfile( $field ) {
		if ( IBOS::app()->user->hasState( $field ) ) {
			return IBOS::app()->user->$field;
		}
		static $modelFields = array(
			'count' => array(
				'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5',
				'oltime', 'attachsize'
			),
			'status' => array(
				'regip', 'lastip', 'lastvisit', 'lastactivity', 'invisible'
			)
		);
		$profileModel = '';
		foreach ( $modelFields as $model => $fields ) {
			if ( in_array( $field, $fields ) ) {
				$profileModel = $model;
				break;
			}
		}
		if ( $profileModel ) {
			$model = 'application\modules\user\model\User' . ucfirst( $profileModel );
			$mergeArray = $model::model()->fetchByPk( IBOS::app()->user->uid );
			if ( $mergeArray ) {
				foreach ( $mergeArray as $key => $val ) {
					IBOS::app()->user->setState( $key, $val );
				}
			}
			return IBOS::app()->user->$field;
		}
		return null;
	}

	/**
	 * 加载用户缓存，其中键对应uid
	 * 如果有传入$uids，如果是'1,2,3'格式的字符串，则转成数组，如果array(1,2,3)，继续：
	 * 1、如果传入的uid存在，则取出来，重新组合成一个新的数组，格式和不传入$uids的格式一样：
	  array(
	  1 => array(
	  'uid' => 1,
	  'username' => '沐筱琴',
	  'isadministrator' => 1,
	  'deptid' => 1,
	  'positionid' => 1,
	  'roleid' => 1,
	  'upuid' => 0,
	  'groupid' => 2,
	  'jobnumber' => '萌萌哒',
	  'realname' => '沐筱琴',
	  'password' => 'b04bdfc08616188a32754e21a43e6ee6',
	  'gender' => 0,
	  'weixin' => 'forsona',
	  'mobile' => '13250302684',
	  'email' => '2317216477@qq.com',
	  'status' => 0,
	  'createtime' => '1435830015',
	  'credits' => 2,
	  'newcomer' => 1,
	  'salt' => 'LTmRXK',
	  'validationemail' => 0,
	  'validationmobile' => 0,
	  'lastchangepass' => '1435907360',
	  'guid' => '31163F94-7736-6798-3EE7-54011002FF5C',
	  'group_title' => '初入江湖',
	  'upgrade_percent' => 4,
	  'next_group_credit' => 50,
	  'level' => 2,
	  'alldeptid' => '6,1',
	  'allupdeptid' => '',
	  'alldowndeptid' => '7,8,2,3,4,5,9',
	  'relatedDeptId' => 6,
	  'deptname' => '广州',
	  'allposid' => '1,3',
	  'relatedPosId' => '3',
	  'posname' => '总经理',
	  'allroleid' => '1,3',
	  'relatedRoleId' => '3',
	  'rolename' => '管理员',
	  'space_url' => '?r=user/home/index&uid=1',
	  'avatar_middle' => 'static.php?type=avatar&uid=1&size=middle&engine=LOCAL',
	  'avatar_small' => 'static.php?type=avatar&uid=1&size=small&engine=LOCAL',
	  'avatar_big' => 'static.php?type=avatar&uid=1&size=big&engine=LOCAL',
	  'bg_big' => 'static.php?type=bg&uid=1&size=big&engine=LOCAL',
	  'bg_small' => 'static.php?type=bg&uid=1&size=small&engine=LOCAL',
	  'birthday' => '0',
	  'telephone' => '',
	  'address' => '',
	  'qq' => '',
	  'bio' => '',
	  'remindsetting' => '',
	  ),
	  2 => array(
	  //...
	  ),
	  );
	 * 2、如果传入的uid都不存在，则直接返回全数组
	 * 3、如果不传入uids，则返回全数组
	 * @param mixed $uids
	 * @return type
	 */
	public static function loadUser( $uids = '' ) {
		$users = IBOS::app()->setting->get( 'cache/users' );
		if ( !empty( $uids ) ) {
			$uidArr = is_array( $uids ) ? $uids : explode( ',', $uids );
			$return = array();
			foreach ( $uidArr as $row ) {
				if ( isset( $users[$row] ) ) {
					$return[$row] = $users[$row];
				} else {
					continue;
				}
			}
			return empty( $return ) ? $users : $return;
		} else {
		return $users;
		}
	}

	/**
	 * 导出用户xml格式
	 * @param mixed $uids
	 */
	public static function exportUser( $uids ) {
		set_time_limit( 0 );
		$users = UserModel\User::model()->fetchAllByUids( $uids );
		$usersData = self::fetchAttributeByUser( $users );
		$header = array(
			'用户名', '工号', '真实名字',
			'密码', '性别', '微信号', '手机号',
			'邮箱', '生日', '住宅电话', '地址', 'QQ', '自我介绍'
		);
		$filename = date( 'Y-m-d' ) . '导出用户数据.xls';
		PHPExcel::exportToExcel( $filename, $header, $usersData );
	}

	/**
	 * 过滤导出用户数据，只需要必须字段，并且转换某些数据格式
	 * @param array $users 用户数组
	 * @return array 返回数组
	 */
	private static function fetchAttributeByUser( $users ) {
		$filterfields = array(
			'username', 'jobnumber', 'realname',
			'password', 'gender', 'weixin', 'mobile',
			'email', 'birthday', 'telephone', 'address', 'qq', 'bio',
		);
		$return = array();
		foreach ( $users as $key => $user ) {
			foreach ( $user as $keynew => $value ) {
				if ( in_array( $keynew, $filterfields ) ) {
					if ( $keynew == 'password' ) {
						$return[$key][$keynew] = '';
					} elseif ( $keynew == 'gender' ) {
						$return[$key][$keynew] = $value == 1 ? '男' : '女';
					} elseif ( $keynew == 'birthday' && !empty( $value ) ) {
						$return[$key][$keynew] = date( 'Y-m-d', $value );
					} else {
						$return[$key][$keynew] = $value;
					}
				}
			}
		}
		return $return;
	}

	/**
	 * 清空某用户的缓存
	 * @param integer $uid
	 */
	public static function cleanCache( $uid ) {
		$users = self::loadUser();
		if ( isset( $users[$uid] ) ) {
			unset( $users[$uid] );
			UserModel\User::model()->makeCache( $users );
		}
		Cache::rm( 'userData_' . $uid );
	}

	/**
	 * 
	 * @param type $accessId
	 * @return boolean
	 */
	public static function checkDataPurv( $purvId ) {
		//Todo::测试用途，待完善 by banyan
		return true;
	}

	/**
	 * 从岗位维度设置用户的岗位
	 * @param integer $positionId
	 * @param array $users 
	 * @return boolean
	 */
	public static function setPosition( $positionId, $users ) {
		// 该岗位原有的用户
		$oldUids = UserModel\User::model()->fetchUidByPosId( $positionId, false );
		// 这一次提交的用户
		$userId = explode( ',', trim( $users, ',' ) );
		$newUids = String::getUid( $userId );
		// 找出两种差别
		$delDiff = array_diff( $oldUids, $newUids );
		$addDiff = array_diff( $newUids, $oldUids );
		// 没有可执行操作，直接跳过
		if ( !empty( $addDiff ) || !empty( $delDiff ) ) {
			$updateUser = false;
			// 获取所有用户数据
			$userData = self::loadUser();
			// 给该岗位添加人员
			if ( $addDiff ) {
				foreach ( $addDiff as $newUid ) {
					$record = $userData[$newUid];
					// 如果该用户没有设置主岗位，设之
					if ( empty( $record['positionid'] ) ) {
						UserModel\User::model()->modify( $newUid, array( 'positionid' => $positionId ) );
						$updateUser = true;
					} else if ( strcmp( $record['positionid'], $positionId ) !== 0 ) {
						// 如果要设置的岗位不是该用户当前岗位，把该岗位添加到辅助岗位去
						PositionModel\PositionRelated::model()->add( array( 'positionid' => $positionId, 'uid' => $newUid ), false, true );
					}
				}
			}
			// 删除人员
			if ( $delDiff ) {
				foreach ( $delDiff as $diffId ) {
					$record = $userData[$diffId];
					PositionModel\PositionRelated::model()->deleteAll( "`positionid`={$positionId} AND `uid` ={$diffId}" );
					if ( strcmp( $positionId, $record['positionid'] ) == 0 ) {
						UserModel\User::model()->modify( $diffId, array( 'positionid' => 0 ) );
						$updateUser = true;
					}
				}
			}
			// 更新操作
			$mainNumber = UserModel\User::model()->count( '`positionid` = :positionid', array( ':positionid' => $positionId ) );
			$auxiliaryNumber = PositionModel\PositionRelated::model()->countByPositionId( $positionId );
			PositionModel\Position::model()->modify( $positionId, array( 'number' => (int) ($mainNumber + $auxiliaryNumber) ) );
			$updateUser && Cache::update( 'users' );
			Org::update();
		}
	}

	/**
	 * 封装一个用户数组，增加一些常用的参数
	 * @param array $user 引用用户数组
	 * @return array
	 */
	public static function wrapUserInfo( $user ) {
		// 处理用户组信息
		$user['group_title'] = '';
		$user['next_group_credit'] = $user['upgrade_percent'] = 0;
		$currentGroup = !empty( $user['groupid'] ) ? UserModel\UserGroup::model()->fetchByPk( $user['groupid'] ) : array();
		if ( !empty( $currentGroup ) ) {
			$user['group_title'] = $currentGroup['title'];
			if ( $currentGroup['creditslower'] !== '0' ) {
				$user['upgrade_percent'] = round( (float) ($user['credits'] / $currentGroup['creditslower']) * 100, 2 );
			}
			$user['next_group_credit'] = (int) $currentGroup['creditslower'];
		}
		$user['level'] = self::getUserLevel( $user['groupid'] );
		// 处理用户的所有关联部门ID与岗位ID，方便权限查询
		Cache::load( array( 'department', 'position' ) );
		$position = PositionUtil::loadPosition();
		$department = DepartmentUtil::loadDepartment();
		$role = RoleUtil::loadRole();
		if ( $user['deptid'] > 0 ) {
			$relatedDeptId = DepartmentModel\DepartmentRelated::model()->fetchAllDeptIdByUid( $user['uid'] );
			$deptIds = array_merge( (array) $relatedDeptId, array( $user['deptid'] ) );
			$user['alldeptid'] = implode( ',', array_unique( $deptIds ) );
			$user['allupdeptid'] = DepartmentModel\Department::model()->queryDept( $user['alldeptid'] );
			$user['alldowndeptid'] = DepartmentModel\Department::model()->fetchChildIdByDeptids( $user['alldeptid'] );
			$user['relatedDeptId'] = implode( ',', $relatedDeptId );
			$user['deptname'] = isset( $department[$user['deptid']]['deptname'] ) ? $department[$user['deptid']]['deptname'] : "";
		} else {
			$user['alldeptid'] = $user['allupdeptid'] = $user['alldowndeptid'] = $user['relatedDeptId'] = $user['deptname'] = '';
		}
		if ( $user['positionid'] > 0 ) {
			$relatedPosId = PositionModel\PositionRelated::model()->fetchAllPositionIdByUid( $user['uid'] );
			$posIds = array_merge( array( $user['positionid'] ), (array) $relatedPosId );
			$user['allposid'] = implode( ',', array_unique( $posIds ) );
			$user['relatedPosId'] = implode( ',', $relatedPosId );
			$user['posname'] = isset( $position[$user['positionid']] ) ? $position[$user['positionid']]['posname'] : "";
		} else {
			$user['allposid'] = $user['relatedPosId'] = $user['posname'] = '';
		}
		if ( $user['roleid'] > 0 ) {
			$relatedRoleId = RoleRelated::model()->fetchAllRoleIdByUid( $user['uid'] );
			$roleIds = array_merge( array( $user['roleid'] ), (array) $relatedRoleId );
			$user['allroleid'] = implode( ',', array_unique( $roleIds ) );
			$user['relatedRoleId'] = implode( ',', $relatedRoleId );
			$user['rolename'] = isset( $role[$user['roleid']] ) ? $role[$user['roleid']]['rolename'] : "";
		} else {
			$user['allroleid'] = $user['relatedRoleId'] = $user['rolename'] = '';
		}
		// --------------
		// 空间地址
		$user['space_url'] = "?r=user/home/index&uid=" . $user['uid'];
		// 头像中尺寸
		$user['avatar_middle'] = "static.php?type=avatar&uid={$user['uid']}&size=middle&engine=" . ENGINE;
		// 头像小尺寸
		$user['avatar_small'] = "static.php?type=avatar&uid={$user['uid']}&size=small&engine=" . ENGINE;
		// 头像大尺寸
		$user['avatar_big'] = "static.php?type=avatar&uid={$user['uid']}&size=big&engine=" . ENGINE;
		// 用户个人背景图片 大
		$user['bg_big'] = "static.php?type=bg&uid={$user['uid']}&size=big&engine=" . ENGINE;
		// 用户个人背景图片 小
		$user['bg_small'] = "static.php?type=bg&uid={$user['uid']}&size=small&engine=" . ENGINE;
		// 个人资料
		$profile = UserModel\UserProfile::model()->fetchByUid( $user['uid'] );
		$user = array_merge( $user, (array) $profile );
		return $user;
	}

	/**
	 * 获取指定用户的个人首页banner
	 * @param integer $uid 用户ID
	 * @return string
	 */
	public static function getHomeBg( $uid ) {
		$uid = sprintf( "%09d", abs( intval( $uid ) ) );
		$level1 = substr( $uid, 0, 3 );
		$level2 = substr( $uid, 3, 2 );
		$level3 = substr( $uid, 5, 2 );
		return $level1 . '/' . $level2 . '/' . $level3 . '/' . substr( $uid, -2 ) . "_banner.jpg";
	}

	/**
	 * 获取用户等级
	 * @param array $user 用户数组
	 * @return integer
	 */
	public static function getUserLevel( $groupid ) {
		$cache = IBOS::app()->setting->get( 'cache/usergroup' );
		$userGroupId = array_keys( $cache );
		$level = array_search( $groupid, $userGroupId );
		$level++; // 因为数组索引是从0开始
		if ( $level > 20 ) {
			return 'max';
		} else {
			return intval( abs( $level ) );
		}
	}

	/**
	 * 校验用户组
	 * @param integer $uid
	 */
	public static function checkUserGroup( $uid = 0 ) {
		$credit = Credit::getInstance();
		$credit->checkUserGroup( $uid );
	}

	/**
	 * 批量执行某一条策略规则
	 * @param String $action:  规则action名称
	 * @param Integer $uids: 操作用户可以为单个uid或uid数组
	 * @param array $extrasql: user_count的额外操作字段数组格式为 array('extcredits1' => '1')
	 * @param Integer $coef: 积分放大倍数，当为负数时为反转操作
	 */
	public static function batchUpdateCredit( $action, $uids = 0, $extraSql = array(), $coef = 1 ) {
		$credit = Credit::getInstance();
		if ( $extraSql ) {
			$credit->setExtraSql( $extraSql );
		}
		return $credit->updateCreditByRule( $action, $uids, $coef );
	}

	/**
	 * 添加积分
	 * @param Integer $uids: 用户uid或者uid数组
	 * @param String $dataarr: member count相关操作数组，例: array('threads' => 1, 'doings' => -1)
	 * @param Boolean $checkgroup: 是否检查用户组 true or false
	 * @param String $operation: 操作类型
	 * @param Integer $relatedid:
	 * @param String $ruletxt: 积分规则文本
	 */
	public static function updateUserCount( $uids, $dataArr = array(), $checkGroup = true, $operation = '', $relatedid = 0, $ruletxt = '' ) {
		if ( !empty( $uids ) && (is_array( $dataArr ) && $dataArr) ) {
			return self::_updateUserCount( $uids, $dataArr, $checkGroup, $operation, $relatedid, $ruletxt );
		}
		return true;
	}

	/**
	 * 根据某个动作执行积分规则
	 * @param string $action:  规则action名称
	 * @param integer $uid: 操作用户
	 * @param array $extrasql: user_count的额外操作字段数组格式为 array('extcredits1' => '1')
	 * @param string $needle: 防重字符串
	 * @param integer $coef: 积分放大倍数
	 * @param integer $update: 是否执行更新操作
	 * @return 返回积分策略
	 */
	public static function updateCreditByAction( $action, $uid = 0, $extraSql = array(), $needle = '', $coef = 1, $update = 1 ) {
		$credit = Credit::getInstance();
		if ( !empty( $extraSql ) ) {
			$credit->setExtraSql( $extraSql );
		}
		return $credit->execRule( $action, $uid, $needle, $coef, $update );
	}

	/**
	 * 积分记录日志
	 * @param type $uids
	 * @param type $operation
	 * @param type $relatedid
	 * @param type $data
	 * @return type
	 */
	public static function creditLog( $uids, $operation, $relatedid, $data ) {
		if ( !$operation || empty( $relatedid ) || empty( $uids ) || empty( $data ) ) {
			return;
		}
		$log = array(
			'uid' => $uids,
			'operation' => $operation,
			'relatedid' => $relatedid,
			'dateline' => TIMESTAMP,
		);
		foreach ( $data as $k => $v ) {
			$log[$k] = $v;
		}
		if ( is_array( $uids ) ) {
			foreach ( $uids as $k => $uid ) {
				$log['uid'] = $uid;
				$log['relatedid'] = is_array( $relatedid ) ? $relatedid[$k] : $relatedid;
				CreditLog::model()->add( $log );
			}
		} else {
			CreditLog::model()->add( $log );
		}
	}

	/**
	 * 更新用户统计的扩展方法，仅私有调用
	 * @param type $uids
	 * @param type $dataArr
	 * @param type $checkGroup
	 * @param type $operation
	 * @param type $relatedid
	 * @param type $ruletxt
	 * @return type
	 */
	private static function _updateUserCount( $uids, $dataArr = array(), $checkGroup = true, $operation = '', $relatedid = 0, $ruletxt = '' ) {
		if ( empty( $uids ) ) {
			return;
		}
		if ( !is_array( $dataArr ) || empty( $dataArr ) ) {
			return;
		}
		if ( $operation && $relatedid ) {
			$writeLog = true;
		} else {
			$writeLog = false;
		}
		$data = $log = array();
		foreach ( $dataArr as $key => $val ) {
			if ( empty( $val ) ) {
				continue;
			}
			$val = intval( $val );
			$id = intval( $key );
			$id = !$id && substr( $key, 0, -1 ) == 'extcredits' ? intval( substr( $key, -1, 1 ) ) : $id;
			if ( 0 < $id && $id < 9 ) {
				$data['extcredits' . $id] = $val;
				if ( $writeLog ) {
					$log['extcredits' . $id] = $val;
				}
			} else {
				$data[$key] = $val;
			}
		}
		if ( $writeLog ) {
			self::creditLog( $uids, $operation, $relatedid, $log );
		}
		if ( $data ) {
			$credit = Credit::getInstance();
			$credit->updateUserCount( $data, $uids, $checkGroup, $ruletxt );
		}
	}

	/**
	 * 取得该用户的所有直属下属及下属所在部门数据
	 * @param integer $uid
	 * @return array  //返回带直属下属的部门及其这个部门的直属下属数组
	 */
	public static function getManagerDeptSubUserByUid( $uid ) {
		// 取得该用户的直属下属
		$subUserArr = UserModel\User::model()->fetchSubByPk( $uid );
		$uidArr = Convert::getSubByKey( $subUserArr, 'uid' );
		$allDeptidArr = Convert::getSubByKey( $subUserArr, 'deptid' );
		$deptidArr = array_unique( $allDeptidArr );
		$unit = IBOS::app()->setting->get( 'setting/unit' );
		$undefindDeptName = isset( $unit ) ? $unit['fullname'] : '未定义部门';
		// 将直属下属uid数组转换成字符串，用于IN搜索
		$uidStr = implode( ',', $uidArr );
		//将直属下属组合到部门中
		$dept = array();
		foreach ( $deptidArr as $index => $deptid ) {
			if ( $deptid == 0 ) {  // 没有部门的下属,分到公司名字下或者未定义部门
				$dept[$index]['deptname'] = $undefindDeptName;
			} else {  //有部门的下属,组合到部门
				$dept[$index] = DepartmentModel\Department::model()->fetchByPk( $deptid );
			}
			$subUser = UserModel\User::model()->fetchAll( array(
				'select' => '*',
				'condition' => "deptid=:deptid AND uid IN({$uidStr}) AND status != 2",
				'params' => array( ':deptid' => $deptid )
					) );
			if ( empty( $subUser ) ) {
				unset( $dept[$index] );
				continue;
			}
			foreach ( $subUser as $k => $user ) {  // 判断该用户是否还有下属
				$subUser[$k]['hasSub'] = self::hasSubUid( $user['uid'] );
			}
			$dept[$index]['user'] = $subUser;
			$subUids = Convert::getSubByKey( $subUser, 'uid' );
			$dept[$index]['subUids'] = implode( ',', $subUids );
		}
		return $dept;
	}

	/**
	 * 通过uid判断是否存在下属
	 * @param type $uid
	 * @return boolean
	 */
	public static function hasSubUid( $uid ) {
		static $users = array();
		if ( !isset( $users[$uid] ) ) {
			$users[$uid] = UserModel\User::model()->countByAttributes( array( 'upuid' => $uid ), 'status != :status', array( ':status' => 2 ) );
		}
		return $users[$uid];
	}

	/**
	 * 取得该用户的所有下属数据
	 * @param integer $uid
	 * @param string $limitCondition 默认为空，取全部
	 * @param boolean $uidFlag 是否只返回uid数组
	 * @return type
	 */
	public static function getAllSubs( $uid, $limitCondition = '', $uidFlag = false ) {
		$departmentList = DepartmentUtil::loadDepartment();
		//取得该用户的直属下属uid
		$uidArr = UserModel\User::model()->fetchSubUidByUid( $uid );
		//取出他管理的部门id
		$deptArr = array();
		if ( !empty( $departmentList ) ) {
			foreach ( $departmentList as $department ) {
				if ( $department['manager'] == $uid ) {
					$deptArr[] = $department;
				}
			}
		}
		//取得他管理的部门的所有下属
		$deptAllUidArr = array();
		if ( count( $deptArr ) > 0 ) {
			foreach ( $deptArr as $department ) {
				//取得该部门除部门主管外的用户数据
				$records = UserModel\User::model()->fetchAll( array(
					'select' => array( 'uid' ),
					'condition' => 'deptid=:deptid AND uid NOT IN(:uid) AND status != 2 ' . $limitCondition,
					'params' => array( ':deptid' => $department['deptid'], ':uid' => $uid )
						) );
				$deptUidArr = array();
				foreach ( $records as $record ) {
					$deptUidArr[] = $record['uid'];
				}
				$deptAllUidArr = array_merge( $deptAllUidArr, $deptUidArr );
			}
		}
		$allUidArr = array_merge( $uidArr, $deptAllUidArr );
		$arr = array_unique( $allUidArr );
		if ( $uidFlag ) {
			return $arr;
		}
		$users = array();
		if ( !empty( $arr ) ) {
			$users = UserModel\User::model()->fetchAllByUids( $arr );
		}
		return $users;
	}

	/**
	 * 判断某个uid是否是另一个uid的下属
	 * @param integer $uid 参照上司uid
	 * @param integer $subUid 参照下属uid
	 * @return boolean
	 */
	public static function checkIsSub( $uid, $subUid ) {
		// 是直属下属直接返回真
		$subUidArr = UserModel\User::model()->fetchSubUidByUid( $uid );
		if ( in_array( $subUid, $subUidArr ) ) {
			return true;
		}
		// 不是直属，判断是否是下下级
		if ( !empty( $subUidArr ) ) {
			foreach ( $subUidArr as $uid ) {
				$allSubUids = self::getAllSubs( $uid, '', true );
				if ( in_array( $subUid, $allSubUids ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 判断某个uid是否是另一个uid的直属上司
	 * @param integer $uid 参照下属uid
	 * @param integer $upUid 参照直属上司uid
	 * @return boolean
	 */
	public static function checkIsUpUid( $uid, $upUid ) {
		$user = UserModel\User::model()->fetchByPk( $uid );
		if ( !empty( $user ) && $upUid == $user['upuid'] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取上司UID
	 * @param int $uid  
	 * @return int  上司uid，没上司就返回0
	 */
	public static function getSupUid( $uid ) {
		$user = UserModel\User::model()->fetchByUid( $uid );
		$supUid = 0;
		if ( $user['upuid'] != 0 ) {
			$supUid = $user['upuid'];
		} elseif ( $user['deptid'] != 0 ) {  //，如果所属部门ID不为空，找部门管理者
			$dept = DepartmentModel\Department::model()->fetchByPk( $user['deptid'] );
			$supUid = $dept['manager'] == $uid ? 0 : $dept['manager'];
		}
		return $supUid;
	}

	/**
	 * 获取指定用户头像的文件路径存放地址
	 * @param integer $uid 用户ID
	 * @param string $size 头像尺寸
	 * @param string $type
	 * @return string
	 */
	public static function getAvatar( $uid, $size = 'middle' ) {
		$size = in_array( $size, array( 'big', 'middle', 'small' ) ) ? $size : 'middle';
		$uid = sprintf( "%09d", abs( intval( $uid ) ) );
		$level1 = substr( $uid, 0, 3 );
		$level2 = substr( $uid, 3, 2 );
		$level3 = substr( $uid, 5, 2 );
		return $level1 . '/' . $level2 . '/' . $level3 . '/' . substr( $uid, -2 ) . "_avatar_{$size}.jpg";
	}

	/**
	 * 获取指定用户背景图文件路径存放地址
	 * @param integer $uid 用户ID
	 * @param string $size 背景尺寸
	 * @return string
	 */
	public static function getBg( $uid, $size = 'small' ) {
		$size = in_array( $size, array( 'big', 'middle', 'small' ) ) ? $size : 'small';
		$uid = sprintf( "%09d", abs( intval( $uid ) ) );
		$level1 = substr( $uid, 0, 3 );
		$level2 = substr( $uid, 3, 2 );
		$level3 = substr( $uid, 5, 2 );
		return $level1 . '/' . $level2 . '/' . $level3 . '/' . substr( $uid, -2 ) . "_bg_{$size}.jpg";
	}

	/**
	 * 获取系统自带背景
	 * @param string $name 图片名(目前有temp1,temp2,temp3)
	 * @param string $size 要获取的背景图大小(big middle small)
	 * @return string
	 */
	public static function getTempBg( $name, $size ) {
		$path = './data/home/';
		$bgUrl = $path . $name . '_' . $size . '.jpg';
		return $bgUrl;
	}

	/**
	 * 查看指定uid是否在线
	 * @staticvar array $userOnline 缓存数组
	 * @param integer $uid 用户ID
	 * @return boolean
	 */
	public static function isOnline( $uid ) {
		static $userOnline = array();
		if ( empty( $userOnline[$uid] ) ) {
			$user = MainModel\Session::model()->fetchByUid( $uid );
			if ( $user && $user['invisible'] === '0' ) {
				$userOnline[$uid] = 1;
			}
		}
		return isset( $userOnline[$uid] ) ? true : false;
	}

	/**
	 * 获得指定uid 在线状态
	 * -1 为离线，0为在线，1为忙碌，2为离开
	 * @staticvar array $userOnline 缓存数组
	 * @param integer $uid 用户ID
	 * @return integer
	 */
	public static function getOnlineStatus( $uid ) {
		static $userOnline = array();
		if ( empty( $userOnline[$uid] ) ) {
			$user = MainModel\Session::model()->fetchByUid( $uid );
			if ( $user ) {
				$userOnline[$uid] = $user['invisible'];
			}
		}
		return isset( $userOnline[$uid] ) ? intval( $userOnline[$uid] ) : -1;
	}

	/**
	 * 检查导航权限
	 * @param type $nav
	 */
	public static function checkNavPurv( $nav ) {
		if ( $nav['system'] == '1' && !empty( $nav['module'] ) && !IBOS::app()->user->isadministrator ) {
			$access = self::getUserPurv( IBOS::app()->user->uid );
			return isset( $access[$nav['url']] );
		}
		return true;
	}

	/**
	 * 获取用户权限
	 * @staticvar array $users 用户权限缓存数组
	 * @param integer $uid 用户ID
	 * @return array 权限数组
	 */
	public static function getUserPurv( $uid ) {
		static $users = array();
		if ( !isset( $users[$uid] ) ) {
			$access = array();
			$user = UserModel\User::model()->fetchByUid( $uid );
			foreach ( explode( ',', $user['allroleid'] ) as $roleId ) {
				$access = array_merge( $access, RoleUtil::getPurv( $roleId ) );
			}
			$users[$uid] = $access;
		}
		return $users[$uid];
	}

	/**
	 * 按拼音排序用户
	 * @param array $uids 要排序的用户uid
	 * @param integer $first
	 * @return type
	 */
	public static function getUserByPy( $uids = NULL, $first = false ) {
		$group = array();
		if ( is_array( $uids ) ) {
			$list = UserModel\User::model()->fetchAllByUids( $uids );
		} else {
			$list = self::loadUser();
		}
		foreach ( $list as $k => $v ) {
			$py = Convert::getPY( $v['realname'], $first );
			if ( !empty( $py ) ) {
				$group[strtoupper( $py[0] )][] = $k;
			}
		}
		ksort( $group );
		$data = array( 'datas' => $list, 'group' => $group );
		return $data;
	}

	/**
	 * 获取封装后的js选人框常用联系人uid数组
	 * @return json
	 */
	public static function getJsConstantUids( $uid ) {
		$inEnabledContact = Module::getIsEnabled( 'contact' );
		$cUids = $inEnabledContact ? Contact::model()->fetchAllConstantByUid( $uid ) : array();
		$cUidStr = empty( $cUids ) ? '' : String::wrapId( $cUids );
		return empty( $cUidStr ) ? '' : CJSON::encode( explode( ',', $cUidStr ) );
	}

	/**
	 * 获取安全配置和密码验证规则
	 * @return array
	 */
	public static function getAccountSetting() {
		$account = unserialize( MainModel\Setting::model()->fetchSettingValueByKey( 'account' ) );
		if ( $account['mixed'] ) {
			$preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
		} else {
			$preg = "^[A-Za-z0-9\!\@\#\$\%\^\&\*\.\~]{" . $account['minlength'] . ",32}$";
		}
		switch ( $account['autologin'] ) {
			case '1': // 自动登录一周
				$cookieTime = 86400 * 7;
				break;
			case '2': // 一个月
				$cookieTime = 86400 * 30;
				break;
			case '3': // 三个月
				$cookieTime = 86400 * 90;
				break;
			case '0': // 一天
				$cookieTime = 86400;
			default:
				$cookieTime = 0;
				break;
		}
		$account['preg'] = $preg;
		$account['cookietime'] = $cookieTime;
		$account['timeout'] = $account['timeout'] * 60;
		return $account;
	}

	/**
	 * 将一个用户数组按部门分组，返回以相同部门为一组的用户数组,并且带部门名称；
	 * @param array $users 用户数组
	 * @return array
	 */
	public static function handleUserGroupByDept( $users ) {
		if ( empty( $users ) ) {
			return array();
		}
		$ret = array();
		$deptIdsTemp = Convert::getSubByKey( $users, 'deptid' );
		$deptIds = array_unique( $deptIdsTemp );
		$departments = DepartmentUtil::loadDepartment();
		foreach ( $deptIds as $deptId ) {
			$ret[$deptId]['deptname'] = isset( $departments[$deptId] ) ? $departments[$deptId]['deptname'] : '未定义部门';
			foreach ( $users as $k => $user ) {
				if ( $user['deptid'] == $deptId ) {
					$ret[$deptId]['users'][$user['uid']] = $user;
					unset( $user[$k] );
				}
			}
		}
		return $ret;
	}

}
