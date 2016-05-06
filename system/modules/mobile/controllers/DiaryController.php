<?php

/**
 * 移动端日志控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端日志控制器文件
 * 
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: DiaryController.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\components\Diary as ICDiary;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryAttention;
use application\modules\diary\model\DiaryRecord;
use application\modules\diary\model\DiaryStats;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\message\model\Comment;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CPagination;

class DiaryController extends BaseController {

	/**
	 * 默认页,获取主页面各项数据统计
	 * @return void 
	 */
	public function actionIndex() {
		$uid = Env::getRequest( 'uid' );
		//$type= Env::getRequest( 'type' );

		if ( !$uid ) {
			$uid = IBOS::app()->user->uid;
		}

		$datas = Diary::model()->fetchAllByPage( "uid=" . $uid );
		if ( isset( $datas["data"] ) ) {
			foreach ( $datas["data"] as $k => $v ) {
				$datas["data"][$k]["content"] = strip_tags( $v["content"] );
			}
		}
		$return = array();
		$return['datas'] = $datas['data'];
		$return['pages'] = array(
			'pageCount' => $datas['pagination']->getPageCount(),
			'page' => $datas['pagination']->getCurrentPage(),
			'pageSize' => $datas['pagination']->getPageSize()
		);
		$this->ajaxReturn( $return, Mobile::dataType() );
	}

	public function actionReview() {
		$op = Env::getRequest( 'op' );
		$option = empty( $op ) ? 'default' : $op;
		$routes = array( 'default', 'show', 'showdiary', 'getsubordinates', 'personal', 'getStampIcon' );
		if ( !in_array( $option, $routes ) ) {
			$this->error( IBOS::lang( 'Can not find the path' ), $this->createUrl( 'default/index' ) );
		}
		$date = 'today';
		if ( array_key_exists( 'date', $_GET ) ) {
			$date = $_GET['date'];
		}
		if ( $date == 'today' ) {
			$time = strtotime( date( 'Y-m-d' ) );
			$date = date( 'Y-m-d' );
		} else if ( $date == 'yesterday' ) {
			$time = strtotime( date( 'Y-m-d' ) ) - 24 * 60 * 60;
			$date = date( 'Y-m-d', $time );
		} else {
			$time = strtotime( $date );
		}

		$uid = IBOS::app()->user->uid;
		$getSubUidArr = Env::getRequest( 'subUidArr' );  //是否有传递下属人员过来，用于前一天、后一天
		$user = Env::getRequest( 'user' );  //是否是点击某个部门
		if ( !empty( $getSubUidArr ) ) {
			$subUidArr = $getSubUidArr;
		} elseif ( !empty( $user ) ) {
			$subUidArr = array();
			foreach ( $user as $v ) {
				$subUidArr[] = $v['uid'];
			}
		} else {
			$subUidArr = User::model()->fetchSubUidByUid( $uid );
		}
		$params = array();
		if ( count( $subUidArr ) > 0 ) {
			$uids = implode( ',', $subUidArr );
			$condition = "uid IN($uids)" . " AND diarytime=$time";
			$paginationData = Diary::model()->fetchAllByPage( $condition, 100 );

			//得到该天没有工作日志的uid --取得该天有记录的uid，总下属uid-有记录的uid
			$recordUidArr = $noRecordUidArr = $noRecordUserList = array();
			foreach ( $paginationData['data'] as $diary ) {
				$recordUidArr[] = $diary['uid'];
			}
			if ( count( $recordUidArr ) > 0 ) {
				foreach ( $subUidArr as $subUid ) {
					if ( !in_array( $subUid, $recordUidArr ) ) {
						$noRecordUidArr[] = $subUid;
					}
				}
			} else {
				$noRecordUidArr = $subUidArr;
			}
			if ( count( $noRecordUidArr ) > 0 ) {
				$noRecordUserList = User::model()->fetchAllByPk( $noRecordUidArr );
			}
			$params = array(
				'pagination' => $paginationData['pagination'],
				'pages' => array(
					'pageCount' => $paginationData['pagination']->getPageCount(),
					'page' => $paginationData['pagination']->getCurrentPage(),
					'pageSize' => $paginationData['pagination']->getPageSize()
				),
				'data' => ICDiary::processReviewListData( $uid, $paginationData['data'] ),
				'noRecordUserList' => $noRecordUserList
			);
		} else {
			$params = array(
				'pagination' => new CPagination( 0 ),
				'pages' => array(),
				'data' => array(),
				'noRecordUserList' => array()
			);
		}
		// 与个人日志列表统一数据格式
		$params['datas'] = $params['data'];

		$params['dateWeekDay'] = DiaryUtil::getDateAndWeekDay( $date );
		$params['dashboardConfig'] = IBOS::app()->setting->get( 'setting/diaryconfig' );
		$params['subUidArr'] = $subUidArr;
		//上一天和下一天
		$params['prevAndNextDate'] = array(
			'prev' => date( 'Y-m-d', (strtotime( $date ) - 24 * 60 * 60 ) ),
			'next' => date( 'Y-m-d', (strtotime( $date ) + 24 * 60 * 60 ) ),
			'prevTime' => strtotime( $date ) - 24 * 60 * 60,
			'nextTime' => strtotime( $date ) + 24 * 60 * 60
		);
		$this->ajaxReturn( $params, Mobile::dataType() );
	}

	/**
	 * 列表页显示
	 * @return void
	 */
	public function actionAttention() {
		//取得shareuid字段中包含作者的数据
		$date = 'yesterday';
		if ( array_key_exists( 'date', $_GET ) ) {
			$date = $_GET['date'];
		}
		if ( $date == 'today' ) {
			$time = strtotime( date( 'Y-m-d' ) );
			$date = date( 'Y-m-d' );
		} else if ( $date == 'yesterday' ) {
			$time = strtotime( date( 'Y-m-d' ) ) - 24 * 60 * 60;
			$date = date( 'Y-m-d', $time );
		} else {
			$time = strtotime( $date );
			$date = date( 'Y-m-d', $time );
		}

		$uid = IBOS::app()->user->uid;
		//关注了哪些人
		$attentions = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid ) );
		$auidArr = Convert::getSubByKey( $attentions, 'auid' );
		$hanAuidArr = $this->handleAuid( $uid, $auidArr );
		$subUidStr = implode( ',', $hanAuidArr['subUid'] );
		$auidStr = implode( ',', $hanAuidArr['aUid'] );
		// 下属日志的条件和非下属日志条件
		$condition = "(FIND_IN_SET(uid, '{$subUidStr}') OR (FIND_IN_SET('{$uid}', shareuid) AND FIND_IN_SET(uid, '{$auidStr}') ) ) AND diarytime=$time";
		$paginationData = Diary::model()->fetchAllByPage( $condition, 100 );
		$params = array(
			'dateWeekDay' => DiaryUtil::getDateAndWeekDay( date( 'Y-m-d', strtotime( $date ) ) ),
			'pagination' => $paginationData['pagination'],
			'pages' => array(
				'pageCount' => $paginationData['pagination']->getPageCount(),
				'page' => $paginationData['pagination']->getCurrentPage(),
				'pageSize' => $paginationData['pagination']->getPageSize()
			),
			'data' => ICDiary::processShareListData( $uid, $paginationData['data'] ),
			'shareCommentSwitch' => 0,
			'attentionSwitch' => 1
		);

		// 与个人日志列表统一数据格式
		$params['datas'] = $params['data'];

		//上一天和下一天
		$params['prevAndNextDate'] = array(
			'prev' => date( 'Y-m-d', (strtotime( $date ) - 24 * 60 * 60 ) ),
			'next' => date( 'Y-m-d', (strtotime( $date ) + 24 * 60 * 60 ) ),
			'prevTime' => strtotime( $date ) - 24 * 60 * 60,
			'nextTime' => strtotime( $date ) + 24 * 60 * 60,
		);
		$this->ajaxReturn( $params, Mobile::dataType() );
	}

	/**
	 * 处理关注的uid中，下属uid和非下属uid分离开
	 * @param integer $uid 登陆用户uid
	 * @param mix $attentionUids 关注的uid
	 * @return array
	 */
	private function handleAuid( $uid, $attentionUids ) {
		$aUids = is_array( $attentionUids ) ? $attentionUids : implode( ',', $attentionUids );
		$ret['subUid'] = array();
		$ret['aUid'] = array();
		if ( !empty( $aUids ) ) {
			foreach ( $aUids as $aUid ) {
				if ( UserUtil::checkIsSub( $uid, $aUid ) ) {
					$ret['subUid'][] = $aUid;
				} else {
					$ret['aUid'][] = $aUid;
				}
			}
		}
		return $ret;
	}

	public function actionCategory() {

		$this->ajaxReturn( array(), Mobile::dataType() );
	}

	public function actionShow() {
		$diaryid = Env::getRequest( 'id' );
		$diaryDate = Env::getRequest( 'diarydate' );
		if ( empty( $diaryid ) && empty( $diaryDate ) ) {
			$this->ajaxReturn( array(), $dataType );
		}
		$diary = array();
		$uid = IBOS::app()->user->uid;
		if ( !empty( $diaryid ) ) {
			$diary = Diary::model()->fetchByPk( $diaryid );
		} else {
			$diary = Diary::model()->fetch( 'diarytime=:diarytime AND uid=:uid', array( ':diarytime' => strtotime( $diaryDate ), ':uid' => $uid ) );
		}
		if ( empty( $diary ) ) {
			$this->ajaxReturn( array(), $dataType );
		}
//		if ( $diary['uid'] != $uid  ) {  //判断是否是自己的日志
//			$this->ajaxReturn( array(),Mobile::dataType());
//		}
		//增加阅读记录
		Diary::model()->addReaderuidByPK( $diary, $uid );
		//取得原计划和计划外内容,下一次计划内容
		$data = Diary::model()->fetchDiaryRecord( $diary );
		$params = array(
			'diary' => ICDiary::processDefaultShowData( $diary ),
			'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK( $diary['diaryid'] ),
			'data' => $data,
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
		//图章
		$stampBasePath = File::fileName( Stamp::STAMP_PATH );
		if ( !empty( $diary['stamp'] ) ) {
			$stamp = Stamp::model()->fetchStampById( $diary['stamp'] );
			$params['stampUrl'] = $stampBasePath . $stamp;
		}
		$allStampA = Stamp::model()->fetchAll();
		$params['stampBasePath'] = $stampBasePath;
		$params['allStamps'] = $allStampA;
		//评论
		$params['list'] = $this->getCommentList( $diary );
		$params['isSup'] = UserUtil::checkIsSub( $uid, $diary['uid'] );
		$params['isShare'] = Diary::model()->checkUidIsShared( $uid, $diary['diaryid'] );
		$this->ajaxReturn( $params, Mobile::dataType() );
	}

	private function getCommentList( $diary ) {
		$limit = Env::getRequest( 'limit', 'P', 5 );
		$offset = Env::getRequest( 'offset', 'P', 0 );
		$arr = array(
			'module' => 'diary',
			'table' => 'diary',
			'attributes' => array(
				'rowid' => $diary['diaryid'],
				'moduleuid' => IBOS::app()->user->uid,
				'limit' => $limit,
				'offset' => $offset,
			),
		);
		$widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\diary\widgets\DiaryComment', $arr );
		$list = $widget->getCommentList();
		return $list;
	}
	/**
	 * 增加一条评论
	 * @return type
	 */
	public function actionAddComment() {
		// 返回结果集默认值
		$return = array( 'isSuccess' => false );
		if ( IBOS::app()->request->isPostRequest ) {
			$post = $_POST;
			// 安全过滤
			foreach ( $post as $key => $val ) {
				$post[$key] = StringUtil::filterCleanHtml( $post[$key] );
			}
			// 判断资源是否被删除
			$sourceInfo = Diary::model()->fetchByPk( $post['diaryid'] );
			if ( !$sourceInfo ) {
				$return['isSuccess'] = false;
				$this->ajaxReturn( $return, Mobile::dataType() );
			}
			$content = StringUtil::filterDangerTag( $post['content'] );
			$sourceUrl = IBOS::app()->urlManager->createUrl( 'mobile/diary/show', array( 'id' => $post['diaryid'] ) );
			$data = array_merge( $post, array(
				'module' => 'diary',
				'table' => 'diary',
				'rowid' => $post['diaryid'],
				'moduleuid' => IBOS::app()->user->uid,
				'content' => $content,
				'data' => '',
				'ctime' => TIMESTAMP,
				'isdel' => 0,
				'url' => $sourceUrl,
				'detail' => IBOS::lang( 'Comment my diray', '', array( '{url}' => $sourceUrl, '{title}' => StringUtil::cutStr( StringUtil::filterCleanHtml( $content ), 50 ) ), 'diary.default' ),
				'uid' => Env::getRequest( 'uid' ),
				'tocid' => Env::getRequest( 'tocid' ),
				'touid' => Env::getRequest( 'touid' ),
				'from' => Env::getRequest( 'from', 'P', 1 ),
				'commentcount' => Env::getRequest( 'commentcount', 'P', 0 ),
				'stamp' => Env::getRequest( 'stamp', 'P', 0 ),
					) );
			$data['cid'] = Comment::model()->addComment( $data );
			$diaryid = $sourceInfo['diaryid'];
			$allStamp = Stamp::model()->fetchAll( array( 'select' => 'id' ) );
			$stampArr = Convert::getSubByKey( $allStamp, 'id' );
			$stamp = in_array( $data['stamp'], $stampArr ) ? intval( $data['stamp'] ) : 0;
			if ( $stamp == 0 ) {
				Diary::model()->modify( $diaryid, array( 'isreview' => 1 ) );
			} else {
				Diary::model()->modify( $diaryid, array( 'isreview' => 1, 'stamp' => $stamp ) );
				$uid = $sourceInfo['uid'];
				DiaryStats::model()->scoreDiary( $diaryid, $uid, $stamp );
			}
			if ( $data['cid'] ) {
				$return['isSuccess'] = true;
				$return['msg'] = '';
				$return['data'] = $data;
			} else {
				$return['msg'] = '添加失败';
			}
		} else {
			$return['msg'] = 'not post request';
		}

		$this->ajaxReturn( $return, Mobile::dataType() );
	}

	public function actionAdd() {
		$dataType = 'JSON';
		$callback = Env::getRequest( 'callback' );
		if ( isset( $callback ) ) {
			$dataType = Mobile::dataType();
		}

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
			$this->ajaxReturn( array( "msg" => "今天已经提交过日志！" ), $dataType );
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
//		$isInstallCalendar = ModuleUtil::getIsInstall( 'calendar' );
//		$workStart = $this->getWorkStart( $isInstallCalendar );
		$params = array(
			'diary' => array(
				'diaryid' => 0,
				'uid' => $uid,
				'diarytime' => DiaryUtil::getDateAndWeekDay( $todayDate ),
				'nextDiarytime' => DiaryUtil::getDateAndWeekDay( date( "Y-m-d", strtotime( "+1 day", $todayTime ) ) ),
				'content' => ''
			),
			'data' => array(
				'originalPlanList' => $originalPlanList,
				'outsidePlanList' => $outsidePlanList,
				'tomorrowPlanList' => ""
			),
			'dashboardConfig' => $dashboardConfig,
//			'uploadConfig' => Attach::getUploadConfig()//,
//			'isInstallCalendar' => $isInstallCalendar,
//			'workStart' => $workStart
		);
		$this->ajaxReturn( $params, Mobile::dataType() );
	}

	/**
	 * 添加工作日志
	 * @return void
	 */
	function actionSave() {
		$uid = IBOS::app()->user->uid;
		$originalPlan = $planOutside = '';
		if ( array_key_exists( 'originalPlan', $_POST ) ) {
			$originalPlan = $_POST['originalPlan'];
		}
		if ( array_key_exists( 'planOutside', $_POST ) ) {
			$planOutside = array_filter( $_POST['planOutside'], create_function( '$v', 'return !empty($v["content"]);' ) );
		}
		//如果原计划存在，修改原计划完成情况
		if ( !empty( $originalPlan ) ) {
			foreach ( $originalPlan as $key => $value ) {
				DiaryRecord::model()->modify( $key, array( 'schedule' => $value ) );
			}
		}
		//保存最新计划
		$shareUidArr = isset( $_POST['shareuid'] ) ? StringUtil::getId( $_POST['shareuid'] ) : array();
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
		$diaryId = Diary::model()->add( $diary, true );
		//如果存在计划外，增加到该天的计划记录中
		if ( !empty( $planOutside ) ) {
			DiaryRecord::model()->addRecord( $planOutside, $diaryId, strtotime( $_POST['todayDate'] ), $uid, 'outside' );
		}
		$plan = array_filter( $_POST['plan'], create_function( '$v', 'return !empty($v["content"]);' ) );
		DiaryRecord::model()->addRecord( $plan, $diaryId, strtotime( $_POST['plantime'] ), $uid, 'new' );
		//更新积分
		UserUtil::updateCreditByAction( 'adddiary', $uid );

		$this->ajaxReturn( $diaryId, Mobile::dataType() );
	}

	/**
	 * 修改工作日志
	 * @return void
	 */
	function actionEdit() {
		$uid = IBOS::app()->user->uid;
		$shareUidArr = isset( $_POST['shareuid'] ) ? StringUtil::getId( $_POST['shareuid'] ) : array();
		$diaryId = Env::getRequest( 'id' );
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
		$isDiary = Diary::model()->modify( $diaryId, $diary );
		
		$originalPlan = $planOutside = '';
		if ( array_key_exists( 'originalPlan', $_POST ) ) {
			$originalPlan = $_POST['originalPlan'];
		}
		if ( array_key_exists( 'planOutside', $_POST ) ) {
			$planOutside = array_filter( $_POST['planOutside'], create_function( '$v', 'return !empty($v["content"]);' ) );
		}
		if ( !empty( $originalPlan ) ) {
			foreach ( $originalPlan as $key => $value ) {
				DiaryRecord::model()->modify( $key, array( 'schedule' => $value ) );
			}
		}
		DiaryRecord::model()->deleteAll( "diaryid = {$diaryId}" );
		if ( !empty( $planOutside ) ) {
			DiaryRecord::model()->addRecord( $planOutside, $diaryId, strtotime( $_POST['todayDate'] ), $uid, 'outside' );
		}
		$plan = array_filter( $_POST['plan'], create_function( '$v', 'return !empty($v["content"]);' ) );
		if( !empty( $plan )){
			$isDiaryRecord = DiaryRecord::model()->addRecord( $plan, $diaryId, strtotime( $_POST['plantime'] ), $uid, 'new' );
		}
		if ( $isDiary && $isDiaryRecord) {
			$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Edit success' ) );
		} else {
			$message = array( 'isSuccess' => false, 'data' => IBOS::lang( 'Edit fail' ) );
		}
		$this->ajaxReturn( $message, Mobile::dataType() );
	}

	/**
	 * 删除工作日志
	 * @return void
	 */
	function actionDel() {
		$diaryId = Env::getRequest( 'id' );
		$diary = Diary::model()->deleteAll( "diaryid = {$diaryId}" );
		$diaryRecord = DiaryRecord::model()->deleteAll( "diaryid = {$diaryId}" );
		if ( $diary > 0 && $diaryRecord > 0) {
			$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Del success' ) );
		} else {
			$message = array( 'isSuccess' => false, 'data' => IBOS::lang( 'Del fail' ) );
		}
		$this->ajaxReturn( $message, Mobile::dataType() );
	}

}
