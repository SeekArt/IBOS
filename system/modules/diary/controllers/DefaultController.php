<?php

/**
 * 工作日志模块------工作日志默认控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 工作日志模块------工作日志默认控制器，继承DiaryBaseController
 * @package application.modules.diary.controllers
 * @version $Id: DefaultController.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\DateTime;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\String;
use application\modules\calendar\model\Calendars;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\components\Diary as ICDiary;
use application\modules\diary\model\CalendarRecord;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryRecord;
use application\modules\diary\model\DiaryShare;
use application\modules\diary\model\DiaryStats;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\message\model\Comment;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;
use application\core\model\Log;

class DefaultController extends BaseController {

	/**
	 * 显示列表页
	 * @return void
	 */
	public function actionIndex() {
		$op = Env::getRequest( 'op' );
		$option = empty( $op ) ? 'default' : $op;
		$routes = array( 'default', 'show', 'showdiary', 'getreaderlist', 'getcommentlist', 'getAjaxSidebar' );
		if ( !in_array( $option, $routes ) ) {
			$this->error( IBOS::lang( 'Can not find the path' ), $this->createUrl( 'default/index' ) );
		}
		if ( $option == 'default' ) {
			$uid = IBOS::app()->user->uid;
			//是否搜索
			if ( Env::getRequest( 'param' ) == 'search' ) {
				//15-7-27 下午2:28 gzdzl 搜索必须是post类型请求
				if ( Ibos::app()->request->isPostRequest ) {
				$this->search();
				}
			}
			$this->_condition = DiaryUtil::joinCondition( $this->_condition, "uid = $uid" );
			$paginationData = Diary::model()->fetchAllByPage( $this->_condition );
			$params = array(
				'pagination' => $paginationData['pagination'],
				'data' => ICDiary::processDefaultListData( $paginationData['data'] ),
				'diaryCount' => Diary::model()->count( $this->_condition ),
				'commentCount' => Diary::model()->countCommentByReview( $uid ),
				'user' => User::model()->fetchByUid( $uid ),
				'diaryIsAdd' => Diary::model()->checkDiaryisAdd( strtotime( date( 'Y-m-d' ) ), $uid ),
				'dashboardConfig' => IBOS::app()->setting->get( 'setting/diaryconfig' )
			);
			$this->setPageTitle( IBOS::lang( 'My diary' ) );
			$this->setPageState( 'breadCrumbs', array(
				array( 'name' => IBOS::lang( 'Personal Office' ) ),
				array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
				array( 'name' => IBOS::lang( 'Diary list' ) )
			) );
			$this->render( 'index', $params );
		} else {
			$this->$option();
		}
	}

	/**
	 * 去工作日志添加页面
	 * @return void
	 */
	public function actionAdd() {
		$op = Env::getRequest( 'op' );
		$option = empty( $op ) ? 'default' : $op;
		$routes = array( 'default', 'save', 'planFromSchedule' );
		if ( !in_array( $option, $routes ) ) {
			$this->error( IBOS::lang( 'Can not find the path' ), $this->createUrl( 'default/index' ) );
		}
		if ( $option == 'default' ) {
			$todayDate = date( 'Y-m-d' );
			if ( array_key_exists( 'diaryDate', $_GET ) ) {
				$todayDate = $_GET['diaryDate'];
				if ( strtotime( $todayDate ) > strtotime( date( 'Y-m-d' ) ) ) {
					$this->error( IBOS::lang( 'No new permissions' ), $this->createUrl( 'default/index' ) );
				}
			}
			$todayTime = strtotime( $todayDate );
			$uid = IBOS::app()->user->uid;
			if ( Diary::model()->checkDiaryisAdd( $todayTime, $uid ) ) {
				$this->error( IBOS::lang( 'Do not repeat to add' ), $this->createUrl( 'default/index' ) );
			}
			//取得今日的工作计划
			$diaryRecordList = DiaryRecord::model()->fetchAllByPlantime( $todayTime );
			$originalPlanList = $outsidePlanList = array();
			foreach ( $diaryRecordList as $diaryRecord ) {
				if ( $diaryRecord['planflag'] == 1 ) {
					$originalPlanList[] = $diaryRecord;
				} else {
					$outsidePlanList[] = $diaryRecord;
				}
			}
			$dashboardConfig = IBOS::app()->setting->get( 'setting/diaryconfig' );
			// 检测是否已安装日程模块，用于添加日志时“来自日程”的计划功能
			$isInstallCalendar = Module::getIsEnabled( 'calendar' );
			$workTime = $this->getWorkTime( $isInstallCalendar );
			$params = array(
				'originalPlanList' => $originalPlanList,
				'outsidePlanList' => $outsidePlanList,
				'dateWeekDay' => DiaryUtil::getDateAndWeekDay( $todayDate ),
				'nextDateWeekDay' => DiaryUtil::getDateAndWeekDay( date( "Y-m-d", strtotime( "+1 day", $todayTime ) ) ),
				'dashboardConfig' => $dashboardConfig,
				'todayDate' => $todayDate,
				'uploadConfig' => Attach::getUploadConfig(),
				'isInstallCalendar' => $isInstallCalendar,
				'workTime' => $workTime
			);
			//取得默认共享人员
			if ( $dashboardConfig['sharepersonnel'] ) {
				$data = DiaryShare::model()->fetchShareInfoByUid( $uid );
				$params['defaultShareList'] = $data['shareInfo'];
				$params['deftoid'] = String::wrapId( $data['deftoid'] );
			}
			$this->setPageTitle( IBOS::lang( 'Add Diary' ) );
			$this->setPageState( 'breadCrumbs', array(
				array( 'name' => IBOS::lang( 'Personal Office' ) ),
				array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
				array( 'name' => IBOS::lang( 'Add Diary' ) )
			) );
			$this->render( 'add', $params );
		} else {
			$this->$option();
		}
	}

	/**
	 * 添加工作日志
	 * @return void
	 */
	private function save() {
		//15-7-27 下午2:23 gzdzl
		//判断是否是POST请求，不是的话非法访问，则要做相应的错误处理
		if ( Ibos::app()->request->isPostRequest ) {
		$uid = IBOS::app()->user->uid;
		$realname = User::model()->fetchRealnameByUid( $uid );
		$originalPlan = $planOutside = array();
		if ( array_key_exists( 'originalPlan', $_POST ) ) {
			$originalPlan = $_POST['originalPlan'];
		}
		if ( array_key_exists( 'planOutside', $_POST ) ) {
			$planOutside = array_filter( $_POST['planOutside'], create_function( '$v', 'return !empty($v["content"]);' ) );
		}
		//如果原计划存在，修改原计划完成情况
		$originalPlanContent = array();
		if ( !empty( $originalPlan ) ) {
			foreach ( $originalPlan as $key => $value ) {
				$originalPlanContent[] = DiaryRecord::model()->fetchContentByRecordId( $key );
				DiaryRecord::model()->modify( $key, array( 'schedule' => $value ) );
			}
		}
		$date = $_POST['todayDate'] . ' ' . IBOS::lang( 'Weekday', 'date' ) . DateTime::getWeekDay( strtotime( $_POST['todayDate'] ) );
		//保存最新计划
		$shareUidArr = isset( $_POST['shareuid'] ) ? String::getId( $_POST['shareuid'] ) : array();
		$diary = array(
			'uid' => $uid,
			'diarytime' => strtotime( $_POST['todayDate'] ),
			'nextdiarytime' => strtotime( $_POST['plantime'] ),
			'addtime' => TIMESTAMP,
			'content' => $_POST['diaryContent'],
			'shareuid' => implode( ',', $shareUidArr ),
			'readeruid' => '',
			'remark' => '',
			'attention' => ''
		);
		if ( !empty( $_POST['attachmentid'] ) ) {
			Attach::updateAttach( $_POST['attachmentid'] );
		}
		$diary['attachmentid'] = $_POST['attachmentid'];
		$diaryId = Diary::model()->add( $diary, true );
		//如果存在计划外，增加到该天的计划记录中
		if ( !empty( $planOutside ) ) {
			DiaryRecord::model()->addRecord( $planOutside, $diaryId, strtotime( $_POST['todayDate'] ), $uid, 'outside' );
		}
		$plan = array_filter( $_POST['plan'], create_function( '$v', 'return !empty($v["content"]);' ) );
		DiaryRecord::model()->addRecord( $plan, $diaryId, strtotime( $_POST['plantime'] ), $uid, 'new' );
		// 动态推送
		$wbconf = WbCommonUtil::getSetting( true );
		if ( isset( $wbconf['wbmovement']['diary'] ) && $wbconf['wbmovement']['diary'] == 1 ) {
			$supUid = UserUtil::getSupUid( $uid );
			if ( intval( $supUid ) > 0 ) {
				$data = array(
					'title' => IBOS::lang( 'Feed title', '', array(
						'{subject}' => $realname . ' ' . $date . ' ' . IBOS::lang( 'Work diary' ),
						'{url}' => IBOS::app()->urlManager->createUrl( 'diary/review/show', array( 'diaryid' => $diaryId ) )
					) ),
					'body' => String::cutStr( $diary['content'], 140 ),
					'actdesc' => IBOS::lang( 'Post diary' ),
					'userid' => $supUid,
					'deptid' => '',
					'positionid' => '',
				);
				WbfeedUtil::pushFeed( $uid, 'diary', 'diary', $diaryId, $data );
			}
		}
		//更新积分
		UserUtil::updateCreditByAction( 'adddiary', $uid );
		// 给直属上司发提醒
		$upUid = UserUtil::getSupUid( $uid );
		if ( !empty( $upUid ) ) {
			$config = array(
				'{sender}' => User::model()->fetchRealnameByUid( $uid ),
				'{title}' => IBOS::lang( 'New diary title', '', array( '{sub}' => $realname, '{date}' => $date ) ),
				'{content}' => $this->renderPartial( 'remindcontent', array(
					'realname' => $realname,
					'date' => $date,
					'lang' => IBOS::getLangSources(),
					'originalPlan' => array_values( $originalPlanContent ),
					'planOutside' => array_values( $planOutside ),
					'content' => String::cutStr( strip_tags( $_POST['diaryContent'] ), 200 ),
					'plantime' => $_POST['plantime'] . ' ' . IBOS::lang( 'Weekday', 'date' ) . DateTime::getWeekDay( strtotime( $_POST['plantime'] ) ),
					'plan' => array_values( $plan )
						), true ),
				'{url}' => IBOS::app()->urlManager->createUrl( 'diary/review/show', array( 'diaryid' => $diaryId ) ),
				'id' => $diaryId,
			);
			Notify::model()->sendNotify( $upUid, 'diary_message', $config, $uid );
		}
			/**
			 * 日志记录
			 * 
			 * @TODO 日志创建统计
			 */
			$log = array(
				'user' => Ibos::app()->user->username,
				'ip' => Ibos::app()->setting->get( 'clientip' )
			);
			Log::write( $log, 'action', 'module.diary.default.save' );
		$this->success( IBOS::lang( 'Save succeed', 'message' ), $this->createUrl( 'default/index' ) );
		} else {
			//15-7-27 下午2:22
			//不是post类型过来的请求都跳转回到日志添加页面
			IBOS::app()->request->redirect( Ibos::app()->createUrl( 'diary/default/add' ) );
		}
	}

	/**
	 * 设置默认分享人
	 * @return void
	 */
	private function setShare() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$postDeftoid = $_POST['deftoid'];
			$uid = IBOS::app()->user->uid;
			if ( empty( $postDeftoid ) ) {
				DiaryShare::model()->delDeftoidByUid( $uid );
			} else {
				$deftoid = String::getId( $postDeftoid );
				DiaryShare::model()->addOrUpdateDeftoidByUid( $uid, $deftoid );
			}
			$result['isSuccess'] = true;
			$this->ajaxReturn( $result );
		}
	}

	/**
	 * 显示工作日志
	 * @return void
	 */
	public function actionShow() {
		$diaryid = Env::getRequest( 'diaryid' );
		$diaryDate = Env::getRequest( 'diarydate' );
		if ( empty( $diaryid ) && empty( $diaryDate ) ) {
			$this->error( IBOS::lang( 'Parameters error', 'error' ), $this->createUrl( 'default/index' ) );
		}
		$diary = array();
		$uid = IBOS::app()->user->uid;
		if ( !empty( $diaryid ) ) {
			$diary = Diary::model()->fetchByPk( $diaryid );
		} else {
			$diary = Diary::model()->fetch( 'diarytime=:diarytime AND uid=:uid', array( ':diarytime' => strtotime( $diaryDate ), ':uid' => $uid ) );
		}
		if ( empty( $diary ) ) {
			$this->error( IBOS::lang( 'File does not exists', 'error' ), $this->createUrl( 'default/index' ) );
		}
		// 日志权限判断
		if ( $diary['uid'] != $uid ) {
			if ( ICDiary::checkReviewScope( $uid, $diary ) ) { // 上司的话跳到评阅
				$this->redirect( $this->createUrl( 'review/show', array( 'diaryid' => $diaryid ) ) );
			} else if ( in_array( $uid, explode( ',', $diary['shareuid'] ) ) ) {
				$this->redirect( $this->createUrl( 'share/show', array( 'diaryid' => $diaryid ) ) );
			} else {
				$this->error( IBOS::lang( 'You do not have permission to view the log' ), $this->createUrl( 'default/index' ) );
			}
		}
		//增加阅读记录
		Diary::model()->addReaderuidByPk( $diary, $uid );
		//取得原计划和计划外内容,下一次计划内容
		$data = Diary::model()->fetchDiaryRecord( $diary );
		$data['tomorrowPlanList'] = $this->handelRemindTime( $data['tomorrowPlanList'] );
		$params = array(
			'diary' => ICDiary::processDefaultShowData( $diary ),
			'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK( $diary['diaryid'] ),
			'data' => $data,
			'isInstallCalendar' => Module::getIsEnabled( 'calendar' ),
			'dashboardConfig' => IBOS::app()->setting->get( 'setting/diaryconfig' )
		);
		//附件
		if ( !empty( $diary['attachmentid'] ) ) {
			$params['attach'] = Attach::getAttach( $diary['attachmentid'], true, true, false, false, true );
			$params['count'] = 0;
		}
		//阅读人
		if ( !empty( $diary['readeruid'] ) ) {
			$readerArr = explode( ',', $diary['readeruid'] );
			$params['readers'] = User::model()->fetchAllByPk( $readerArr );
		} else {
			$params['readers'] = '';
		}
		if ( !empty( $diary['stamp'] ) ) {
			$params['stampUrl'] = Stamp::model()->fetchStampById( $diary['stamp'] );
		}
		$this->setPageTitle( IBOS::lang( 'Show Diary' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => IBOS::lang( 'Personal Office' ) ),
			array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
			array( 'name' => IBOS::lang( 'Show Diary' ) )
		) );
		$this->render( 'show', $params );
	}

	/**
	 * 编辑页面
	 */
	public function actionEdit() {
		$op = Env::getRequest( 'op' );
		$option = empty( $op ) ? 'default' : $op;
		$routes = array( 'default', 'update', 'setShare' );
		if ( !in_array( $option, $routes ) ) {
			$this->error( IBOS::lang( 'Can not find the path' ), $this->createUrl( 'default/index' ) );
		}
		if ( $option == 'default' ) {
			$diaryid = intval( Env::getRequest( 'diaryid' ) );
			if ( empty( $diaryid ) ) {
				$this->error( IBOS::lang( 'Parameters error', 'error' ), $this->createUrl( 'default/index' ) );
			}
			$diary = Diary::model()->fetchByPk( $diaryid );
			if ( empty( $diary ) ) {
				$this->error( IBOS::lang( 'No data found', 'error' ), $this->createUrl( 'default/index' ) );
			}
			// 权限判断
			if ( !ICDiary::checkReadScope( IBOS::app()->user->uid, $diary ) ) {
				$this->error( IBOS::lang( 'You do not have permission to edit the log' ), $this->createUrl( 'default/index' ) );
			}
			//日志是否被锁定，锁定则不能修改
			$dashboardConfig = IBOS::app()->setting->get( 'setting/diaryconfig' );
			if ( !empty( $dashboardConfig['lockday'] ) ) {
				$isLock = (time() - $diary['addtime']) > $dashboardConfig['lockday'] * 24 * 60 * 60;
				if ( $isLock ) {
					$this->error( IBOS::lang( 'The diary is locked' ), $this->createUrl( 'default/index' ) );
				}
			}
			// 日志是否开启评阅后锁定，评阅后则锁定不能修改
			if ( $dashboardConfig['reviewlock'] == 1 ) {
				if ( $diary['isreview'] == 1 ) {
					$this->error( IBOS::lang( 'The diary is locked' ), $this->createUrl( 'default/index' ) );
				}
			}
			//取得原计划和计划外内容,下一次计划内容
			$data = Diary::model()->fetchDiaryRecord( $diary );
			//是否安装日程模块
			$isInstallCalendar = Module::getIsEnabled( 'calendar' );
			$workTime = $this->getWorkTime( $isInstallCalendar );
			$params = array(
				'diary' => ICDiary::processDefaultShowData( $diary, $data ),
				'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK( $diaryid ),
				'data' => $data,
				'dashboardConfig' => $dashboardConfig,
				'uploadConfig' => Attach::getUploadConfig(),
				'isInstallCalendar' => $isInstallCalendar,
				'workTime' => $workTime
			);
			//取得附件
			if ( !empty( $diary['attachmentid'] ) ) {
				$params['attach'] = Attach::getAttach( $diary['attachmentid'] );
			}
			//取得默认共享人员
			if ( $dashboardConfig['sharepersonnel'] ) {
				$shareData = DiaryShare::model()->fetchShareInfoByUid( IBOS::app()->user->uid );
				$params['defaultShareList'] = $shareData['shareInfo'];
			}
			$this->setPageTitle( IBOS::lang( 'Edit Diary' ) );
			$this->setPageState( 'breadCrumbs', array(
				array( 'name' => IBOS::lang( 'Personal Office' ) ),
				array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
				array( 'name' => IBOS::lang( 'Edit Diary' ) )
			) );
			$this->render( 'edit', $params );
		} else {
			$this->$option();
		}
	}

	/**
	 * 编辑工作日志
	 * @return void
	 */
	private function update() {
		//15-7-27 下午2:24
		//判断是否是post类型请求
		if ( !Ibos::app()->request->isPostRequest ) {
			//不是post请求跳到工作日志页面
			Ibos::app()->request->redirect( Ibos::app()->createUrl( 'diary/default/index' ) );
		}

		$diaryId = $_POST['diaryid'];
		$diary = Diary::model()->fetchByPk( $diaryId );
		$uid = IBOS::app()->user->uid;
		// 权限判断
		if ( !ICDiary::checkReadScope( $uid, $diary ) ) {
			$this->error( IBOS::lang( 'You do not have permission to edit the log' ), $this->createUrl( 'default/index' ) );
		}
		//如果原计划存在，修改原计划的值
		if ( isset( $_POST['originalPlan'] ) ) {
			foreach ( $_POST['originalPlan'] as $key => $value ) {
				if ( isset( $value ) ) {
					DiaryRecord::model()->modify( $key, array( 'schedule' => $value ) );
				}
			}
		}
		//如果存在计划外，删除原来的外部计划，插入新的外部计划
		DiaryRecord::model()->deleteAll( 'diaryid=:diaryid AND planflag=:planflag', array( ':diaryid' => $diaryId, ':planflag' => 0 ) );
		if ( !empty( $_POST['planOutside'] ) ) {
			$planOutside = array_filter( $_POST['planOutside'], create_function( '$v', 'return !empty($v["content"]);' ) );
			DiaryRecord::model()->addRecord( $planOutside, $diaryId, $_POST['diarytime'], $uid, 'outside' );
		}
		//保存最新计划
		$attributes = array( 'content' => $_POST['diaryContent'] );
		if ( array_key_exists( 'shareuid', $_POST ) ) {
			$shareUidArr = String::getId( $_POST['shareuid'] );
			$attributes['shareuid'] = implode( ',', $shareUidArr );
		}
		Diary::model()->modify( $diaryId, $attributes );
		//更新附件
		$attachmentid = trim( $_POST['attachmentid'], ',' );
		Attach::updateAttach( $attachmentid );
		Diary::model()->modify( $diaryId, array( 'attachmentid' => $attachmentid ) );
		//若已安装日程，删除关联表数据和有提醒时间的日程,再重新插入新的
		$isInstallCalendar = Module::getIsEnabled( 'calendar' );
		if ( $isInstallCalendar ) {
			Calendars::model()->deleteALL( "`calendarid` IN(select `cid` from {{calendar_record}} where `did`={$diaryId})" );
			CalendarRecord::model()->deleteAll( "did = {$diaryId}" );
		}
		//删除原来计划，插入新计划
		DiaryRecord::model()->deleteAll( 'plantime=:plantime AND uid=:uid AND planflag=:planflag', array(
			':plantime' => strtotime( $_POST['plantime'] ), ':uid' => $uid, ':planflag' => 1 ) );
		if ( !isset( $_POST['plan'] ) ) {
			$this->error( IBOS::lang( 'Please fill out at least one work plan' ), $this->createUrl( 'default/edit', array( 'diaryid' => $diaryId ) ) );
		}
		$plan = array_filter( $_POST['plan'], create_function( '$v', 'return !empty($v["content"]);' ) );
		DiaryRecord::model()->addRecord( $plan, $diaryId, strtotime( $_POST['plantime'] ), $uid, 'new' );
		// 给直属上司发修改日志提醒
		$date = date( 'Y-m-d', $diary['addtime'] ) . ' ' . IBOS::lang( 'Weekday', 'date' ) . DateTime::getWeekDay( $diary['addtime'] );
		$realname = User::model()->fetchRealnameByUid( $uid );
		$upUid = UserUtil::getSupUid( $uid );
		if ( !empty( $upUid ) ) {
			$config = array(
				'{sender}' => User::model()->fetchRealnameByUid( $uid ),
				'{title}' => IBOS::lang( 'Edit diary title', '', array( '{sub}' => $realname, '{date}' => $date ) ),
				'{content}' => $this->renderPartial( 'remindcontent', array(
					'realname' => $realname,
					'date' => $date,
					'lang' => IBOS::getLangSources(),
					'content' => String::cutStr( strip_tags( $_POST['diaryContent'] ), 200 ),
					'plantime' => $_POST['plantime'] . ' ' . IBOS::lang( 'Weekday', 'date' ) . DateTime::getWeekDay( strtotime( $_POST['plantime'] ) ),
					'plan' => array_values( $plan )
						), true ),
				'{url}' => IBOS::app()->urlManager->createUrl( 'diary/review/show', array( 'diaryid' => $diaryId ) ),
				'id' => $diaryId,
			);
			Notify::model()->sendNotify( $upUid, 'diary_message', $config, $uid );
		}
		$this->success( IBOS::lang( 'Update succeed', 'message' ), $this->createUrl( 'default/index' ) );
	}

	/**
	 * 删除工作日志
	 * @return void
	 */
	public function actionDel() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$diaryids = Env::getRequest( 'diaryids' );
			$uid = IBOS::app()->user->uid;
			if ( empty( $diaryids ) ) {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'Select at least one' ) ) );
			}
			$pk = '';
			if ( strpos( $diaryids, ',' ) ) {
				$diaryids = trim( $diaryids, ',' );
				$pk = explode( ',', $diaryids );
			} else {
				$pk = array( $diaryids );
			}
			$diarys = Diary::model()->fetchAllByPk( $pk );
			foreach ( $diarys as $diary ) {
				// 权限判断
				if ( !ICDiary::checkReadScope( $uid, $diary ) ) {
					$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'You do not have permission to delete the log' ) ) );
				}
			}
			//删除附件
			$aids = Diary::model()->fetchAllAidByPks( $pk );
			if ( $aids ) {
				Attach::delAttach( $aids );
			}
			//若已安装日程，删除关联表数据和有提醒时间的日程
			$isInstallCalendar = Module::getIsEnabled( 'calendar' );
			if ( $isInstallCalendar ) {
				Calendars::model()->deleteALL( "`calendarid` IN(select `cid` from {{calendar_record}} where FIND_IN_SET(`did`, '{$diaryids}')) " );
				CalendarRecord::model()->deleteAll( "did IN ({$diaryids})" );
			}
			Diary::model()->deleteByPk( $pk );
			DiaryRecord::model()->deleteAll( "diaryid IN ({$diaryids})" );
			// 删除评分
			DiaryStats::model()->deleteAll( "diaryid IN ({$diaryids})" );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Del succeed', 'message' ) ) );
		}
	}

	/**
	 * 得到详细的日志信息
	 * @return void
	 */
	private function showdiary() {
		$diaryid = intval( $_GET['diaryid'] );
		$isShowDiarytime = Env::getRequest( 'isShowDiarytime' );
		$fromController = Env::getRequest( 'fromController' );
		$uid = IBOS::app()->user->uid;
		if ( empty( $diaryid ) ) {
			$this->error( IBOS::lang( 'Parameters error', 'error' ), $this->createUrl( 'default/index' ) );
		}
		$diary = Diary::model()->fetchByPk( $diaryid );
		if ( empty( $diary ) ) {
			$this->error( IBOS::lang( 'No data found', 'error' ), $this->createUrl( 'default/index' ) );
		}
		// 权限判断
		if ( !ICDiary::checkScope( $uid, $diary ) ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'You do not have permission to view the log' ) ) );
		}
		//增加阅读记录
		Diary::model()->addReaderuidByPK( $diary, $uid );
		//取得原计划和计划外内容,下一次计划内容
		$data = Diary::model()->fetchDiaryRecord( $diary );
		$data['tomorrowPlanList'] = $this->handelRemindTime( $data['tomorrowPlanList'] );
		$attachs = array();
		if ( !empty( $diary['attachmentid'] ) ) {
			$attachs = Attach::getAttach( $diary['attachmentid'], true, true, false, false, true );
		}
		//获取阅读人员
		$readers = array();
		if ( !empty( $diary['readeruid'] ) ) {
			$readerArr = explode( ',', $diary['readeruid'] );
			$readers = User::model()->fetchAllByPk( $readerArr );
		} else {
			$readers = '';
		}
		// 图章
		$stampUrl = '';
		if ( $diary['stamp'] != 0 ) {
			$stamp = Stamp::model()->fetchStampById( $diary['stamp'] );
			$stampUrl = File::fileName( Stamp::STAMP_PATH ) . $stamp;
		}
		// 日志时间拆分
		$diary['diarytime'] = DiaryUtil::getDateAndWeekDay( date( 'Y-m-d', $diary['diarytime'] ) );
		$diary['nextdiarytime'] = DiaryUtil::getDateAndWeekDay( date( 'Y-m-d', $diary['nextdiarytime'] ) );
		// 日期个性化
		$diary['addtime'] = Convert::formatDate( $diary['addtime'], 'u' );
		$params = array(
			'lang' => IBOS::getLangSource( 'diary.default' ),
			'diaryid' => $diaryid,
			'diary' => $diary,
			'uid' => $uid,
			'data' => $data,
			'attachs' => $attachs,
			'readers' => $readers,
			'stampUrl' => $stampUrl,
			'fromController' => $fromController,
			'isShowDiarytime' => $isShowDiarytime,
			'allowComment' => $this->getIsAllowComment( $fromController, $uid, $diary )
		);
		$params['isSup'] = UserUtil::checkIsSub( $uid, $diary['uid'] );
		$params['isShare'] = Diary::model()->checkUidIsShared( $uid, $diary['diaryid'] );
		$detailAlias = 'application.modules.diary.views.detail';
		$detailView = $this->renderPartial( $detailAlias, $params, true );
		$this->ajaxReturn( array( 'data' => $detailView, 'isSuccess' => true ) );
	}

	/**
	 * 取得所有阅读和阅读人信息
	 * @return void
	 */
	private function getreaderlist() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$diaryId = Env::getRequest( 'diaryid' );
			$record = Diary::model()->fetch( array(
				'select' => 'readeruid',
				'condition' => 'diaryid=:diaryid',
				'params' => array( ':diaryid' => $diaryId )
					) );
			$readerUids = $record['readeruid'];
			$htmlStr = '<table class="pop-table">';
			if ( !empty( $readerUids ) ) {
				$htmlStr .= '<div class="da-reviews-avatar">';
				$readerUidArr = explode( ',', trim( $readerUids, ',' ) );
				$users = User::model()->fetchAllByUids( $readerUidArr );
				foreach ( $users as $user ) {
					$htmlStr.='<a href="' . IBOS::app()->createUrl( 'user/home/index', array( 'uid' => $user['uid'] ) ) . '">
								<img class="img-rounded" src="' . $user['avatar_small'] . '" title="' . $user['realname'] . '" />
							</a>';
				}
			} else {
				$htmlStr .= '<div><li align="middle">' . IBOS::lang( 'Has not reader' ) . '</li>';
			}
			$htmlStr.='</div></table>';
			echo $htmlStr;
		}
	}

	/**
	 * 取得评论数据
	 * @return void
	 */
	private function getcommentlist() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$diaryid = Env::getRequest( 'diaryid' );
			$records = Comment::model()->fetchAll(
					array(
						'select' => array( 'uid', 'content', 'ctime' ),
						'condition' => "module=:module AND `table`=:table AND rowid=:rowid AND isdel=:isdel ORDER BY ctime DESC LIMIT 0,5",
						'params' => array( ':module' => 'diary', ':table' => 'diary', ':rowid' => $diaryid, ':isdel' => 0 )
					)
			);
			$htmlStr = '<div class="pop-comment"><ul class="pop-comment-list">';
			if ( !empty( $records ) ) {
				foreach ( $records as $record ) {
					$record['realname'] = User::model()->fetchRealnameByUid( $record['uid'] );
					$content = String::parseHtml( String::cutStr( $record['content'], 45 ) );
					$htmlStr.= '<li class="media">
									<a href="' . IBOS::app()->createUrl( 'user/home/index', array( 'uid' => $record['uid'] ) ) . '" class="pop-comment-avatar pull-left">
										<img src="static.php?type=avatar&uid=' . $record['uid'] . '&size=small&engine=' . ENGINE . '" title="' . $record['realname'] . '" class="img-rounded"/>
									</a>
									<div class="media-body">
										<p class="pop-comment-body"><em>' . $record['realname'] . ': </em>' . $content . '</p>
									</div>
								</li>';
				}
			} else {
				$htmlStr .= '<li align="middle">' . IBOS::lang( 'Has not comment' ) . '</li>';
			}
			$htmlStr .= '</ul></div>';
			echo $htmlStr;
		}
	}

	/**
	 * 从日程中读取数据作为这天的原计划
	 */
	private function planFromSchedule() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$uid = IBOS::app()->user->uid;
			$todayDate = $_GET['todayDate'];
			$st = intval( strtotime( $todayDate ) );
			$et = $st + 24 * 60 * 60 - 1;
			$calendars = Calendars::model()->listCalendarByRange( $st, $et, $uid );
			$plans = $calendars['events'];
			foreach ( $plans as $k => $v ) {  //处理完成度输出数据
				$plans[$k]['schedule'] = $v['status'] ? self::COMPLETE_FALG : 0;
				if ( $v['isfromdiary'] ) {
					unset( $plans[$k] );
				}
			}
			$this->ajaxReturn( array_values( $plans ) );
		}
	}

	/**
	 * 根据是否安装了日程模块取出一天的开始工作时间
	 * @param type $isInstallCalendar
	 */
	private function getWorkTime( $isInstallCalendar ) {
		if ( $isInstallCalendar ) {  // 若已安装日程，取出配置的开始工作时间
			$workingTime = IBOS::app()->setting->get( 'setting/calendarworkingtime' );
			$workingTimeArr = explode( ',', $workingTime );
			$start = floor( $workingTimeArr[0] - 0.5 ); // 向下0.5取整
			$end = ceil( $workingTimeArr[1] + 0.5 ); //向上0.5取整
			if ( $start < 0 ) {
				$start = 0;
			}
			if ( $end > 24 ) {
				$end = 24;
			}
			$workTime['start'] = intval( $start );
			$workTime['cell'] = intval( ( $end - $start ) * 2 ); // 格数，每格表示半小时
		} else {
			$workTime['start'] = 6;
			$workTime['cell'] = 28;
		}
		return $workTime;
	}

	/**
	 * 处理计划输出时间格式
	 * @param array $recordList 计划数组
	 * @return array
	 */
	private function handelRemindTime( $recordList ) {
		if ( !empty( $recordList ) ) {
			foreach ( $recordList as $k => $record ) {
				if ( !empty( $record['timeremind'] ) ) {
					$timeremind = explode( ',', $record['timeremind'] );
					$timeremindSt = date( 'H:i', strtotime( date( 'Y-m-d' ) ) + $timeremind[0] * 60 * 60 );
					$timeremindEt = date( 'H:i', strtotime( date( 'Y-m-d' ) ) + $timeremind[1] * 60 * 60 );
					$recordList[$k]['timeremind'] = $timeremindSt . '-' . $timeremindEt;
				}
			}
		}
		return $recordList;
	}

}
