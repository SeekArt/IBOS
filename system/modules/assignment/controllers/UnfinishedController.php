<?php

/**
 * 任务指派模块------未完成任务控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 任务指派模块------未完成任务控制器，继承AssignmentBaseController
 * @package application.modules.assignment.controllers
 * @version $Id: UnfinishedController.php 3297 2014-04-29 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\assignment\core\AssignmentOpApi;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\model\AssignmentApply;
use application\modules\assignment\model\AssignmentLog;
use application\modules\assignment\model\AssignmentRemind;
use application\modules\dashboard\model\Stamp;
use application\modules\user\utils\User as UserUtil;
use application\modules\calendar\model\Calendars;

class UnfinishedController extends BaseController {

	/**
	 * 未完成的任务列表页
	 */
	public function actionIndex() {
		$uid = IBOS::app()->user->uid;
		$params = $this->getUnfinishedDataByUid( $uid );
		$params['uploadConfig'] = Attach::getUploadConfig();
		$this->setPageTitle( IBOS::lang( 'Assignment' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => IBOS::lang( 'Assignment' ), 'url' => $this->createUrl( 'unfinished/index' ) ),
			array( 'name' => IBOS::lang( 'Unfinished list' ) )
		) );
		$this->render( 'list', $params );
	}

	/**
	 * 下属任务列表
	 */
	public function actionSubList() {
		if ( Env::getRequest( 'op' ) == 'getsubordinates' ) {
			$this->getsubordinates();
			exit();
		}
		$getUid = intval( Env::getRequest( 'uid' ) );
		if ( !$getUid ) {
			$deptArr = UserUtil::getManagerDeptSubUserByUid( IBOS::app()->user->uid ); //取得管理的部门和下属
			if ( !empty( $deptArr ) ) {  // 取得管理的第一个部门的第一个下属
				$firstDept = reset( $deptArr );
				$uid = $firstDept['user'][0]['uid'];
			} else {
				$this->error( IBOS::lang( 'You do not subordinate' ), $this->createUrl( 'schedule/index' ) );
			}
		} else {
			$uid = $getUid;
		}
		// 权限判断
		if ( !UserUtil::checkIsSub( IBOS::app()->user->uid, $uid ) ) {
			$this->error( IBOS::lang( 'No permission to view schedule' ), $this->createUrl( 'schedule/index' ) );
		}
		$params = $this->getUnfinishedDataByUid( $uid );
		$params['uid'] = $uid;
		$this->setPageTitle( IBOS::lang( 'Assignment' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => IBOS::lang( 'Assignment' ), 'url' => $this->createUrl( 'unfinished/index' ) ),
			array( 'name' => IBOS::lang( 'Unfinished list' ) )
		) );
		$this->render( 'sublist', $params );
	}

	/**
	 * 得到某个用户的下属，取5条
	 */
	protected function getsubordinates() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$uid = intval( Env::getRequest( 'uid' ) );
			$getItem = Env::getRequest( 'item' );
			$item = empty( $getItem ) ? 5 : $getItem;
			$users = UserUtil::getAllSubs( $uid );
			$subAlias = 'application.modules.assignment.views.unfinished.subview';
			$subView = $this->renderPartial( $subAlias, array( 'users' => $users, 'item' => $item, 'uid' => $uid ), true );
			echo $subView;
		}
	}

	/**
	 * 异步统一入口
	 */
	public function actionAjaxEntrance() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$op = Env::getRequest( 'op' );
			// 推办，完成，评价，重启，申请延期，延期，同意/拒绝延期申请，申请取消，取消，同意/拒绝取消申请，提醒
			$allowOptions = array( 'push', 'toFinished', 'stamp', 'restart', 'applyDelay', 'delay', 'runApplyDelayResult', 'applyCancel', 'cancel', 'runApplyCancelResult', 'remind' );
			if ( !in_array( $op, $allowOptions ) ) {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Parameters error', 'error' ) ) );
			} else {
				$assignmentId = Env::getRequest( 'id' );
				$paramCheck = $this->checkAvailableById( $assignmentId );
				// 参数检查
				if ( !$paramCheck['isSuccess'] ) {
					$this->ajaxReturn( $paramCheck );
				}
				$this->$op( $assignmentId );
			}
		}
	}

	/**
	 * 推办提醒
	 */
	protected function push( $assignmentId ) {
		// 判断是否是该任务的指派人
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$uid = IBOS::app()->user->uid;
		if ( !$this->checkIsDesigneeuid( $assignment['designeeuid'] ) ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Only the sponsors have permission to remind' ) ) );
		}
		$opApi = AssignmentOpApi::getInstance();
		// 给负责人消息提醒
		$opApi->sendNotify( $uid, $assignmentId, $assignment['subject'], $assignment['chargeuid'], 'assignment_push_message' );
		// 发送一条推办评论
		$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Push the assignment' ) );
		// 记录日志
		AssignmentLog::model()->addLog( $uid, $assignmentId, 'push', IBOS::lang( 'Push the assignment' ) );
		$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
	}

	/**
	 * 完成任务
	 */
	protected function toFinished( $assignmentId ) {
		// 判断是否是该任务的指派人或负责人
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$isDesigneeuid = $this->checkIsDesigneeuid( $assignment['designeeuid'] );
		$isChargeuid = $this->checkIsChargeuid( $assignment['chargeuid'] );
		if ( !$isDesigneeuid && !$isChargeuid ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Only the sponsors or head have permission to complete' ) ) );
		}
		// 更改任务为完成状态
		$updateSuccess = Assignment::model()->modify( $assignmentId, array( 'status' => 2, 'finishtime' => TIMESTAMP ) );
		if ( $updateSuccess ) {
			$opApi = AssignmentOpApi::getInstance();
			$uid = IBOS::app()->user->uid;
			// 给发起人消息提醒
			$opApi->sendNotify( $uid, $assignmentId, $assignment['subject'], $assignment['designeeuid'], 'assignment_finish_message' );
			// 增加积分
			UserUtil::updateCreditByAction( 'finishassignment', $uid );
			// 发送一条完成评论
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Finish the assignment', 'assignment.default' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'finish', IBOS::lang( 'Finish the assignment', 'assignment.default' ) );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'The assignment has been completed' ) ) );
		}
	}

	/**
	 * 评价任务(图章)
	 */
	protected function stamp( $assignmentId ) {
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		if ( $assignment['status'] == 2 ) {
			$stamp = intval( Env::getRequest( 'stamp' ) );
			Assignment::model()->modify( $assignmentId, array( 'stamp' => $stamp, 'status' => 3 ) );
			// 消息提醒(负责人和参与人)
			$chargeuid = explode( ',', $assignment['chargeuid'] );
			$participantuid = explode( ',', $assignment['participantuid'] );
			$uidArr = array_merge( $participantuid, $chargeuid );
			$opApi = AssignmentOpApi::getInstance();
			$uid = IBOS::app()->user->uid;
			$opApi->sendNotify( $uid, $assignmentId, $assignment['subject'], $uidArr, 'assignment_appraisal_message' );
			// 发送一条评价评论
			$stampInfo = Stamp::model()->fetchByPk( $stamp );
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Stamp the assignment', 'assignment.default' ) . '-' . $stampInfo['code'] );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'stamp', IBOS::lang( 'Stamp the assignment' ) . '-' . $stampInfo['code'] );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Assignment has not been finished' ) ) );
		}
	}

	/**
	 * 重启任务
	 */
	protected function restart( $assignmentId ) {
		// 判断是否是该任务的指派人或负责人
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$isDesigneeuid = $this->checkIsDesigneeuid( $assignment['designeeuid'] );
		$isChargeuid = $this->checkIsChargeuid( $assignment['chargeuid'] );
		if ( !$isDesigneeuid && !$isChargeuid ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Only the sponsors or head have permission to restart' ) ) );
		}
		// 更改完成状态
		$updateSuccess = Assignment::model()->modify( $assignmentId, array( 'status' => 1, 'finishtime' => 0, 'stamp' => 0 ) );
		if ( $updateSuccess ) {
			$uid = IBOS::app()->user->uid;
			// 发送一条重启评论
			AssignmentOpApi::getInstance()->addStepComment( $uid, $assignmentId, IBOS::lang( 'Restart the assignment', 'assignment.default' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'restart', IBOS::lang( 'Restart the assignment', 'assignment.default' ) );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Task is the initial state' ) ) );
		}
	}

	/**
	 * 负责人申请延期任务
	 */
	protected function applyDelay( $assignmentId ) {
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		if ( $this->checkIsChargeuid( $assignment['chargeuid'] ) ) { // 负责人判断
			$postStattime = Env::getRequest( 'starttime' );
			$postEndtime = Env::getRequest( 'endtime' );
			if ( empty( $postEndtime ) ) {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'The end time cannot be empty' ) ) );
			}
			$delayReason = Env::getRequest( 'delayReason' ); // 延期理由
			// 记录延期申请
			$uid = IBOS::app()->user->uid;
			$starttime = empty( $postStattime ) ? TIMESTAMP : strtotime( $postStattime );
			$endtime = strtotime( $postEndtime );
			AssignmentApply::model()->addDelay( $uid, $assignmentId, $delayReason, $starttime, $endtime );
			// 给发起人消息提醒
			$opApi = AssignmentOpApi::getInstance();
			$opApi->sendNotify( $uid, $assignmentId, $assignment['subject'], $assignment['designeeuid'], 'assignment_applydelay_message' );
			// 发送一条申请延期评论
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Apply delay the assignment' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'applydelay', IBOS::lang( 'Apply delay the assignment' ) );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
		}
	}

	/**
	 * 指派人延期任务
	 */
	protected function delay( $assignmentId ) {
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$uid = IBOS::app()->user->uid;
		if ( $this->checkIsDesigneeuid( $assignment['designeeuid'] ) ) { // 指派人判断
			$delayStattime = strtotime( Env::getRequest( 'starttime' ) );
			$delayEndtime = strtotime( Env::getRequest( 'endtime' ) );
			if ( empty( $delayEndtime ) ) {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'The end time cannot be empty' ) ) );
			}
			$this->handleDelay( $assignmentId, $delayStattime, $delayEndtime, $uid );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
		}
	}

	/**
	 * 同意或拒绝延期
	 */
	protected function runApplyDelayResult( $assignmentId ) {
		$agree = intval( Env::getRequest( 'agree' ) );
		$opApi = AssignmentOpApi::getInstance();
		$uid = IBOS::app()->user->uid;
		if ( $agree ) { // 同意
			$apply = AssignmentApply::model()->fetchByAttributes( array( 'assignmentid' => $assignmentId ) );
			if ( !empty( $apply ) ) {
				$this->handleDelay( $assignmentId, $apply['delaystarttime'], $apply['delayendtime'], $uid );
			}
			// 发送一条同意延期评论
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Agree delay the assignment' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'agreedelay', IBOS::lang( 'Agree delay the assignment' ) );
			$result = IBOS::lang( 'Agree' );
		} else { // 拒绝
			AssignmentApply::model()->deleteAll( "assignmentid = {$assignmentId}" );
			// 发送一条拒绝延期评论
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Refuse delay the assignment' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'refusedelay', IBOS::lang( 'Refuse delay the assignment' ) );
			$result = IBOS::lang( 'Refuse' );
		}
		// 给申请人消息提醒
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$opApi->sendNotify( $uid, $assignmentId, $assignment['subject'], $assignment['chargeuid'], 'assignment_applydelayresult_message', $result );
		$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
	}

	/**
	 * 处理延期任务的数据
	 */
	private function handleDelay( $assignmentId, $delayStarttime, $delayEndtime, $uid ) {
		Assignment::model()->modify( $assignmentId, array( 'starttime' => $delayStarttime, 'endtime' => $delayEndtime ) );
		AssignmentApply::model()->deleteAll( "assignmentid = {$assignmentId}" );
		// 发送一条延期评论
		AssignmentOpApi::getInstance()->addStepComment( $uid, $assignmentId, IBOS::lang( 'Delay the assignment' ) );
		// 记录日志
		AssignmentLog::model()->addLog( $uid, $assignmentId, 'delay', IBOS::lang( 'Delay the assignment' ) );
		return true;
	}

	/**
	 * 负责人申请取消任务
	 */
	protected function applyCancel( $assignmentId ) {
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$uid = IBOS::app()->user->uid;
		if ( $assignment['status'] == 2 ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'The completed assignment cannot be cancelled, can restart' ) ) );
		}
		if ( $this->checkIsChargeuid( $assignment['chargeuid'] ) ) { // 负责人判断
			$cancelReason = Env::getRequest( 'cancelReason' ); // 延期理由
			// 记录取消申请
			AssignmentApply::model()->addCancel( $uid, $assignmentId, $cancelReason );
			// 给发起人消息提醒
			$opApi = AssignmentOpApi::getInstance();
			$opApi->sendNotify( $uid, $assignmentId, $assignment['subject'], $assignment['designeeuid'], 'assignment_applycancel_message' );
			// 发送一条申请取消评论
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Apply cancel the assignment' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'applycancel', IBOS::lang( 'Apply cancel the assignment' ) );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
		}
	}

	/**
	 * 指派人取消任务
	 */
	protected function cancel( $assignmentId ) {
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$uid = IBOS::app()->user->uid;
		if ( $assignment['status'] == 2 ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'The completed assignment cannot be cancelled, can restart' ) ) );
		}
		if ( $this->checkIsDesigneeuid( $assignment['designeeuid'] ) ) { // 指派人判断
			$this->handleCancel( $assignmentId, $uid );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
		}
	}

	/**
	 * 同意或拒绝取消任务
	 */
	protected function runApplyCancelResult( $assignmentId ) {
		$opApi = AssignmentOpApi::getInstance();
		$agree = intval( Env::getRequest( 'agree' ) );
		$uid = IBOS::app()->user->uid;
		if ( $agree ) { // 同意
			// 发送一条同意取消评论
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Agree cancel the assignment' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'agreecancel', IBOS::lang( 'Agree cancel the assignment' ) );
			$this->handleCancel( $assignmentId, $uid );
			$result = IBOS::lang( 'Agree' );
		} else { // 拒绝
			$result = IBOS::lang( 'Refuse' );
			AssignmentApply::model()->deleteAll( "assignmentid = {$assignmentId}" );
			// 发送一条拒绝取消评论
			$opApi->addStepComment( $uid, $assignmentId, IBOS::lang( 'Refuse cancel the assignment' ) );
			// 记录日志
			AssignmentLog::model()->addLog( $uid, $assignmentId, 'refusecancel', IBOS::lang( 'Refuse cancel the assignment' ) );
		}
		$assignment = Assignment::model()->fetchByPk( $assignmentId );
		$opApi->sendNotify( $uid, $assignmentId, $assignment['subject'], $assignment['chargeuid'], 'assignment_applycancelresult_message', $result );
		$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
	}

	/**
	 * 处理取消任务的数据
	 */
	private function handleCancel( $assignmentId, $uid ) {
		Assignment::model()->modify( $assignmentId, array( 'status' => 4 ) );
		AssignmentApply::model()->deleteAll( "assignmentid = {$assignmentId}" );
		// 发送一条取消评论
		AssignmentOpApi::getInstance()->addStepComment( $uid, $assignmentId, IBOS::lang( 'Cancel the assignment' ) );
		// 记录日志
		AssignmentLog::model()->addLog( $uid, $assignmentId, 'cancel', IBOS::lang( 'Cancel the assignment' ) );
		return true;
	}

	/**
	 * 提醒
	 */
	protected function remind( $assignmentId ) {
		if ( Env::submitCheck( 'remindsubmit' ) ) {
			if ( $this->getIsInstallCalendar() ) {
				$uid = IBOS::app()->user->uid;
				$remindTime = Env::getRequest( 'remindTime' );
				// 删除旧日程
				$oldCalendarids = AssignmentRemind::model()->fetchCalendarids( $assignmentId, $uid );
				Calendars::model()->deleteAll( sprintf( "uid = %d AND FIND_IN_SET(`calendarid`, '%s')", $uid, implode( ',', $oldCalendarids ) ) );
				//删除旧数据
				AssignmentRemind::model()->deleteAll( "assignmentid = {$assignmentId} AND uid = {$uid}" );
				if ( !empty( $remindTime ) ) {
					$remindTime = strtotime( $remindTime );
					$remindContent = \CHtml::encode( Env::getRequest( 'remindContent' ) );
					$calendar = array(
						'subject' => $remindContent,
						'starttime' => $remindTime,
						'endtime' => $remindTime + 1800, // 取半个钟
						'uid' => $uid,
						'upuid' => $uid,
						'lock' => 1,
						'category' => 5
					);

					$cid = Calendars::model()->add( $calendar, true );
					// 关联表，删除旧数据，添加新数据
					AssignmentRemind::model()->add( array( 'assignmentid' => $assignmentId, 'calendarid' => $cid, 'remindtime' => $remindTime, 'uid' => $uid, 'content' => $remindContent ) );
				}
				$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Operation succeed', 'message' ) ) );
			} else {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Not installed calendar to support remind' ) ) );
			}
		} else {
			$remind = AssignmentRemind::model()->fetch( sprintf( "uid = %d AND assignmentid = %d", IBOS::app()->user->uid, $assignmentId ) );
			$remindtime = empty( $remind ) ? TIMESTAMP : $remind['remindtime'];
			$params = array(
				'reminddate' => date( 'Y-m-d', $remindtime ),
				'remindtime' => date( 'H:i', $remindtime ),
				'content' => empty( $remind ) ? '' : $remind['content'],
				'lang' => IBOS::getLangSource( 'assignment.default' )
			);
			$remindAlias = 'application.modules.assignment.views.default.remind';
			$editView = $this->renderPartial( $remindAlias, $params, true );
			echo $editView;
		}
	}

}
