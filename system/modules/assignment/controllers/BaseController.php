<?php

/**
 * 任务指派模块------ 基类控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 任务指派模块------ 信息中心基类控制器，继承ICApplication
 * @package application.modules.assignment.controllers
 * @version $Id: BaseController.php 3297 2014-05-13 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\controllers;

use application\core\controllers\Controller;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class BaseController extends Controller {

	/**
	 * 得到侧栏视图渲染结果
	 * @return string
	 */
	public function getSidebar() {
		$sidebarAlias = 'application.modules.assignment.views.sidebar';
		$uid = Ibos::app()->user->uid;
		$params = array(
			'hasSubUid' => UserUtil::hasSubUid( $uid ),
			'unfinishCount' => Assignment::model()->getUnfinishCountByUid( $uid )
		);
		return $this->renderPartial( $sidebarAlias, $params, true );
	}

	/**
	 * 下属侧栏视图
	 * @return type
	 */
	protected function getSubSidebar() {
		$uid = Ibos::app()->user->uid;
		$deptArr = UserUtil::getManagerDeptSubUserByUid( $uid );
		$params = array(
			'deptArr' => $deptArr,
			'unfinishCount' => Assignment::model()->getUnfinishCountByUid( $uid )
		);
		$sidebarAlias = 'application.modules.assignment.views.subsidebar';
		$sidebarView = $this->renderPartial( $sidebarAlias, $params, true );
		return $sidebarView;
	}

	/**
	 * 判断当前用户是否指派人
	 * @param integer $designeeuid 任务的指派人uid
	 * @return boolean
	 */
	protected function checkIsDesigneeuid( $designeeuid ) {
		if ( $designeeuid == Ibos::app()->user->uid ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 判断当前用户是否负责人
	 * @param integer $chargeuid 任务的负责人uid
	 * @return boolean
	 */
	protected function checkIsChargeuid( $chargeuid ) {
		if ( $chargeuid == Ibos::app()->user->uid ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 判断当前用户是否参与人
	 * @param mix $participantuid 任务的参与人
	 * @return boolean
	 */
	protected function checkIsParticipantuid( $participantuid ) {
		$uids = is_array( $participantuid ) ? $participantuid : explode( ',', $participantuid );
		if ( in_array( Ibos::app()->user->uid, $uids ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 判断当前用户是否有权限查看任务
	 * @param array $assignment 某条任务数据
	 * @return boolean
	 */
	protected function checkShowPermissions( $assignment ) {
		$uid = Ibos::app()->user->uid;
		$participantuid = explode( ',', $assignment['participantuid'] );
		if ( $uid != $assignment['designeeuid'] && $uid != $assignment['chargeuid'] && !in_array( $uid, $participantuid ) ) {
			return false;
		}
		return true;
	}

	/**
	 * 判断是否指派人、负责人、参与人的其中一个上司
	 * @param array $assignment 某条任务数据
	 * @return boolean
	 */
	protected function checkIsSup( $assignment ) {
		$uid = Ibos::app()->user->uid;
		$participantuid = explode( ',', $assignment['participantuid'] );
		if ( UserUtil::checkIsSub( $uid, $assignment['designeeuid'] ) ) {
			return true;
		}
		if ( UserUtil::checkIsSub( $uid, $assignment['chargeuid'] ) ) {
			return true;
		}
		foreach ( $participantuid as $puid ) {
			if ( UserUtil::checkIsSub( $uid, $puid ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 判断请求的任务id是否有效
	 * @param integer $assignmentId
	 * @return array
	 */
	protected function checkAvailableById( $assignmentId ) {
		$ret = array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Check pass' ) );
		if ( empty( $assignmentId ) ) {
			$ret = array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Parameters error', 'error' ) );
		}
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		if ( empty( $assignment ) ) {
			$ret = array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Assignment has been delete' ) );
		}
		return $ret;
	}

	/**
	 * 获得某个用户未完成的任务数据(分为uid指派的、负责的、参与的和用户数据)
	 * @param integer $uid
	 * @return array
	 */
	protected function getUnfinishedDataByUid( $uid ) {
		$datas = Assignment::model()->getUnfinishedByUid( $uid );
		$curUid = Ibos::app()->user->uid;
		$designeeData = AssignmentUtil::handleListData( $datas['designeeData'], $curUid );
		$params = array(
			'user' => User::model()->fetchByUid( $uid ),
			'designeeData' => AssignmentUtil::handleDesigneeData( $designeeData ),
			'chargeData' => AssignmentUtil::handleListData( $datas['chargeData'], $curUid ),
			'participantData' => AssignmentUtil::handleListData( $datas['participantData'], $curUid )
		);
		return $params;
	}

	/**
	 * 是否安装日程
	 * @return boolean
	 */
	protected function getIsInstallCalendar() {
		return Module::getIsEnabled( 'calendar' );
	}

}
