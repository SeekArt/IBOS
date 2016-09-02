<?php

/**
 * 部门模块函数库类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 部门模块函数库类
 *
 * @package application.modules.department.utils
 * @version $Id: Department.php 7750 2016-08-03 09:26:25Z tanghang $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\department\utils;

use application\core\utils\Convert;
use application\modules\department\model as DepartmentModel;
use application\modules\position\model\NodeRelated;
use application\modules\user\model as UserModel;
use application\modules\user\utils\User;

class Department {

	const TOP_DEPT_ID = 0; //顶级部门的部门id

	public static function loadDepartment( $deptidMixed = NULL, $param = array() ) {
		static $alldepartment = NULL;
		if ( $alldepartment === NULL ) {
			$alldepartment = DepartmentModel\Department::model()->findDeptmentIndexByDeptid( $deptidMixed , $param );
		}
		return $alldepartment;
	}

	/**
	 * 对比看下$pid是否$deptId的父级部门
	 * @param integer $deptId
	 * @param integer $pid
	 * @return boolean
	 */
	public static function isDeptParent( $deptId, $pid ) {
		$depts = self::loadDepartment();
		$_pid = $depts[$deptId]['pid'];
		if ( $_pid == 0 ) {
			return false;
		}
		if ( $_pid == $pid ) {
			return true;
		}
		return self::isDeptParent( $_pid, $pid );
	}

	/**
	 * 获取住角色和辅助角色中最大的权限(0,1,2,4,8)
	 * @param integer $uid 用户id
	 * @param string $url 权限路由 (organization/user/manager或organization/user/view等等的1248权限)
	 * @return integer 最大权限
	 */
	public static function getMaxPurv( $uid, $url ) {
		$user = User::model()->fetchByUid( $uid );
		$roleIds = explode( ',', $user['allroleid'] ); // 所有角色id
		$purvs = array();
		foreach ( $roleIds as $roleId ) {
			$p = NodeRelated::model()->fetchDataValByIdentifier( $url, $roleId );
			$purvs[] = intval( $p );
		}
		$viewPurv = max( $purvs );
		return $viewPurv;
	}

	/**
	 * 按拼音排序部门
	 * @return array
	 */
	public static function getDepartmentByPy() {
		$group = array();
		$list = self::loadDepartment();
		foreach ( $list as $k => $v ) {
			$py = Convert::getPY( $v['deptname'] );
			if ( !empty( $py ) ) {
				$group[strtoupper( $py[0] )][] = $k;
			}
		}
		ksort( $group );
		$data = array( 'datas' => $list, 'group' => $group );
		return $data;
	}

	/**
	 * 更新部门用户列表
	 * @param  integer $departmentid 部门 id
	 * @param  array $uids         用户 uid 数组
	 * @return boolen               TRUE | FALSE
	 */
	public static function updateDepartmentUserList( $departmentid, $uids ) {
		$rmUids = array();
		$deptUids = DepartmentModel\DepartmentRelated::model()->fetchAllUidByDeptId( $departmentid );
		foreach ( $deptUids as $deptUid ) {
			if ( !in_array( $deptUid, $uids ) ) {
				$rmUids[] = $deptUid;
			}
		}
		$rmUids = implode( ',', $rmUids );
		$uids = implode( ',', $uids );
		$removeRes = UserModel\User::model()->updateAll( array( 'deptid' => 0 ), 'FIND_IN_SET(`uid`, :rmUids)', array( ':rmUids' => $rmUids ) );
		$addRes = UserModel\User::model()->updateAll( array( 'deptid' => $departmentid ), 'FIND_IN_SET(`uid`, :uids)', array( ':uids' => $uids ) );
		User::wrapUserInfo( $uids, true, true, true );
		if ( $removeRes >= 0 && $addRes >= 0 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
