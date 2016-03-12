<?php

/**
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 通讯录模块------ 通讯录基类控制器
 * @package application.modules.contact.controllers
 * @version $Id: ContactBaseController.php 2669 2014-03-14 10:58:29Z gzhzh $
 */

namespace application\modules\contact\controllers;

use application\core\controllers\Controller;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\PHPExcel;
use application\modules\contact\model\Contact;
use application\modules\contact\utils\Contact as ContactUtil;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class BaseController extends Controller {
	/*
	 * 所有字母
	 */

	protected $allLetters = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );

	/**
	 * 取得侧栏导航
	 * @return string
	 */
	protected function getSidebar() {
		$sidebarAlias = 'application.modules.contact.views.sidebar';
		$dept = DepartmentUtil::loadDepartment();
		$params = array(
			'dept' => $dept,
			'lang' => IBOS::getLangSource( 'contact.default' ),
			'unit' => IBOS::app()->setting->get( 'setting/unit' )
		);
		$sidebarView = $this->renderPartial( $sidebarAlias, $params, true );
		return $sidebarView;
	}

	/**
	 * 按部门排列
	 * @return array
	 */
	protected function getDataByDept() {
		$deptid = intval( Env::getRequest( 'deptid' ) );
		$allDepts = DepartmentUtil::loadDepartment();
		if ( !empty( $deptid ) ) {
			$childDepts = Department::model()->fetchChildDeptByDeptid( $deptid, $allDepts );
			$selfDept = Department::model()->fetchByPk( $deptid );
			$depts = array_merge( array( $selfDept ), $childDepts );
			$deptsTmp = ContactUtil::handleDeptData( $depts, $deptid );
			$depts = array_merge( array( $selfDept ), $deptsTmp );
		} else {
			$depts = ContactUtil::handleDeptData( $allDepts, 0 );
		}
		if ( !empty( $depts ) ) {
			foreach ( $depts as $k => $childDept ) {
				$pDeptids = Department::model()->queryDept( $childDept['deptid'] );
				$depts[$k]['pDeptids'] = !empty( $pDeptids ) ? array_reverse( explode( ',', trim( $pDeptids ) ) ) : array();
				$deptUids = User::model()->fetchAllUidByDeptid( $childDept['deptid'], false );
				$deptRelatedUids = DepartmentRelated::model()->fetchAllUidByDeptId( $childDept['deptid'] );
				$uids = array_unique( array_merge( $deptUids, $deptRelatedUids ) );
				$uids = $this->removeDisabledUid( $uids );
				$depts[$k]['users'] = User::model()->fetchAllByUids( $uids );
			}
		}
		return $depts;
	}

	/**
	 * 去掉禁用的uid
	 * @param array $uids 要处理的uid数组
	 * @return array
	 */
	private function removeDisabledUid( $uids ) {
		if ( !is_array( $uids ) ) {
			return;
		}
		$disabledUids = User::model()->fetchAllUidsByStatus( 2 );
		foreach ( $uids as $k => $uid ) {
			if ( in_array( $uid, $disabledUids ) ) {
				unset( $uids[$k] );
			}
		}
		return $uids;
	}

	/**
	 * 按拼音排列
	 * @return array
	 */
	protected function getDataByLetter() {
		$deptid = intval( Env::getRequest( 'deptid' ) );
		if ( !empty( $deptid ) ) {
			$deptids = Department::model()->fetchChildIdByDeptids( $deptid, true );
			$uids = User::model()->fetchAllUidByDeptids( $deptids, false );
		} else {
			$users = UserUtil::loadUser();
			$uids = Convert::getSubByKey( $users, 'uid' );
		}
		$uids = $this->removeDisabledUid( $uids );
		$res = UserUtil::getUserByPy( $uids );
		return ContactUtil::handleLetterGroup( $res );
	}

	/**
	 * 异步请求入口
	 */
	protected function ajaxApi() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$op = Env::getRequest( 'op' );
			if ( !in_array( $op, array( 'getProfile', 'changeConstant', 'export', 'printContact' ) ) ) {
				$this->ajaxReturn( array( 'isSuccess' => false, IBOS::lang( 'Request tainting', 'error' ) ) );
			}
			$this->$op();
		}
	}

	/**
	 * 获取某个用户资料
	 */
	protected function getProfile() {
		$uid = intval( Env::getRequest( 'uid' ) );
		$user = User::model()->fetchByUid( $uid );
		// 部门传真
		$user['fax'] = '';
		if ( !empty( $user['deptid'] ) ) {
			$dept = Department::model()->fetchByPk( $user['deptid'] );
			$user['fax'] = $dept['fax'];
		}
		$user['birthday'] = !empty( $user['birthday'] ) ? date( 'Y-m-d', $user['birthday'] ) : '';
		$cuids = Contact::model()->fetchAllConstantByUid( IBOS::app()->user->uid ); // 常联系人id数组
		$this->ajaxReturn( array( 'isSuccess' => true, 'user' => $user, 'uid' => IBOS::app()->user->uid, 'cuids' => $cuids ) );
	}

	/**
	 * 改变常联系人状态
	 */
	protected function changeConstant() {
		$uid = IBOS::app()->user->uid;
		$cuid = intval( Env::getRequest( 'cuid' ) );
		$status = Env::getRequest( 'status' );
		if ( $status == 'mark' ) { // 标记为常联系人
			Contact::model()->addConstant( $uid, $cuid );
		} elseif ( $status == 'unmark' ) { // 取消常联系人
			Contact::model()->deleteConstant( $uid, $cuid );
		}
		$this->ajaxReturn( array( 'isSuccess' => true ) );
	}

	/**
	 * 导出通讯录
	 */
	public function export() {
		$userDatas = $this->getUserData();
		$fieldArr = array(
			IBOS::lang( 'Real name' ),
			IBOS::lang( 'Position' ),
			IBOS::lang( 'Telephone' ),
			IBOS::lang( 'Cell phone' ),
			IBOS::lang( 'Email' ),
			IBOS::lang( 'QQ' )
		);
		$data = array();
		foreach ( $userDatas as $key => $user ) {
			$data[$key]['realname'] = $user['realname'];
			$data[$key]['posname'] = $user['posname'];
			$data[$key]['telephone'] = $user['telephone'];
			$data[$key]['mobile'] = $user['mobile'];
			$data[$key]['email'] = $user['email'];
			$data[$key]['qq'] = $user['qq'];
		}
		$filename = date( 'Y-m-d' ) . mt_rand( 100, 999 ) . '.xls';
		PHPExcel::exportToExcel($filename, $fieldArr, $data);
	}

	/**
	 * 打印通讯录
	 */
	public function printContact() {
		$datas = $this->getDataByDept();
		$params = array(
			'datas' => $datas,
			'lang' => IBOS::getLangSource( 'contact.default' ),
			'uint' => IBOS::app()->setting->get( 'setting/unit' ),
			'assetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'contact' )
		);
		$detailAlias = 'application.modules.contact.views.default.print';
		$detailView = $this->renderPartial( $detailAlias, $params, true );
		$this->ajaxReturn( array( 'view' => $detailView, 'isSuccess' => true ) );
	}

	/**
	 * 获取符合要求的用户数据
	 * @return array
	 */
	protected function getUserData() {
		$uids = Env::getRequest( 'uids' );
		$userDatas = array();
		if ( !empty( $uids ) ) {
			$uidArr = explode( ',', $uids );
			foreach( $uidArr as $uid ){
				$userDatas[] = User::model()->fetchByUid( $uid );
			}
		}
		return $userDatas;
	}

}
