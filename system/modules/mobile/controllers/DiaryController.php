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
 * @version $Id: DiaryController.php 7519 2016-07-12 08:08:36Z php_lxy $
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Calendars;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\components\Diary as ICDiary;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryAttention;
use application\modules\diary\model\DiaryRecord;
use application\modules\diary\model\DiaryShare;
use application\modules\diary\model\DiaryStats;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\message\model\Comment;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CPagination;
use application\modules\main\model\Setting;

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
				// 阅读人数
				if ( empty( $v['readeruid'] ) ) {
					$datas["data"][$k]['readercount'] = 0;
				} else {
					$datas["data"][$k]['readercount'] = count( explode( ',', trim( $v['readeruid'], ',' ) ) );
				}
				// 图章
				if ( $v['stamp'] != 0 ) {
					$path = Stamp::model()->fetchIconById( $v['stamp'] );
					$datas["data"][$k]['stampPath'] = File::fileName( Stamp::STAMP_PATH . $path );
				}
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

	/*public function actionReview() {
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
	}*/

	// /**
	//  * 列表页显示
	//  * @return void
	//  */
	// public function actionAttention() {
	// 	//取得shareuid字段中包含作者的数据
	// 	$date = 'yesterday';
	// 	if ( array_key_exists( 'date', $_GET ) ) {
	// 		$date = $_GET['date'];
	// 	}
	// 	if ( $date == 'today' ) {
	// 		$time = strtotime( date( 'Y-m-d' ) );
	// 		$date = date( 'Y-m-d' );
	// 	} else if ( $date == 'yesterday' ) {
	// 		$time = strtotime( date( 'Y-m-d' ) ) - 24 * 60 * 60;
	// 		$date = date( 'Y-m-d', $time );
	// 	} else {
	// 		$time = strtotime( $date );
	// 		$date = date( 'Y-m-d', $time );
	// 	}

	// 	$uid = IBOS::app()->user->uid;
	// 	//关注了哪些人
	// 	$attentions = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid ) );
	// 	$auidArr = Convert::getSubByKey( $attentions, 'auid' );
	// 	$hanAuidArr = $this->handleAuid( $uid, $auidArr );
	// 	$subUidStr = implode( ',', $hanAuidArr['subUid'] );
	// 	$auidStr = implode( ',', $hanAuidArr['aUid'] );
	// 	// 下属日志的条件和非下属日志条件
	// 	$condition = "(FIND_IN_SET(uid, '{$subUidStr}') OR (FIND_IN_SET('{$uid}', shareuid) AND FIND_IN_SET(uid, '{$auidStr}') ) ) AND diarytime=$time";
	// 	$paginationData = Diary::model()->fetchAllByPage( $condition, 100 );
	// 	$params = array(
	// 		'dateWeekDay' => DiaryUtil::getDateAndWeekDay( date( 'Y-m-d', strtotime( $date ) ) ),
	// 		'pagination' => $paginationData['pagination'],
	// 		'pages' => array(
	// 			'pageCount' => $paginationData['pagination']->getPageCount(),
	// 			'page' => $paginationData['pagination']->getCurrentPage(),
	// 			'pageSize' => $paginationData['pagination']->getPageSize()
	// 		),
	// 		'data' => ICDiary::processShareListData( $uid, $paginationData['data'] ),
	// 		'shareCommentSwitch' => 0,
	// 		'attentionSwitch' => 1
	// 	);

	// 	// 与个人日志列表统一数据格式
	// 	$params['datas'] = $params['data'];

	// 	//上一天和下一天
	// 	$params['prevAndNextDate'] = array(
	// 		'prev' => date( 'Y-m-d', (strtotime( $date ) - 24 * 60 * 60 ) ),
	// 		'next' => date( 'Y-m-d', (strtotime( $date ) + 24 * 60 * 60 ) ),
	// 		'prevTime' => strtotime( $date ) - 24 * 60 * 60,
	// 		'nextTime' => strtotime( $date ) + 24 * 60 * 60,
	// 	);
	// 	$this->ajaxReturn( $params, Mobile::dataType() );
	// }

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

	/**
     * 显示工作日志
     * @return array
     */
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
			// 图章
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
		//取得默认共享人员
        if ( $dashboardConfig['sharepersonnel'] ) {
            $data = DiaryShare::model()->fetchShareInfoByUid( $uid );
            $params['defaultShareList'] = $data['shareInfo'];
            $params['deftoid'] = StringUtil::wrapId( $data['deftoid'] );
        }
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
		// 上传文件
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
		//更新积分
		UserUtil::updateCreditByAction( 'adddiary', $uid );

		$this->ajaxReturn( $diaryId, Mobile::dataType() );
	}


	/**
	 * 修改工作日志 
	 * @return void
	 */
	public function actionUpdate() {
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
		 //更新附件
        $attachmentid = trim( $_POST['attachmentid'], ',' );
        Attach::updateAttach( $attachmentid );
        Diary::model()->modify( $diaryId, array( 'attachmentid' => $attachmentid ) );

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
		if ( !empty( $plan ) ) {
			$isDiaryRecord = DiaryRecord::model()->addRecord( $plan, $diaryId, strtotime( $_POST['plantime'] ), $uid, 'new' );
		}
		if ( $isDiary && $isDiaryRecord ) {
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
		if ( $diary > 0 && $diaryRecord > 0 ) {
			$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Del success' ) );
		} else {
			$message = array( 'isSuccess' => false, 'data' => IBOS::lang( 'Del fail' ) );
		}
		$this->ajaxReturn( $message, Mobile::dataType() );
	}

	/**
    * 获取某个下属的日志列表
    */
    public function actionPersonal() {
    	$params = [];
       	$uid = IBOS::app()->user->uid;
		$getUid = intval( Env::getRequest( 'uid' ) );
		$diary = Diary::model()->fetchAllByAttributes( array("uid" => $getUid) );
		$supUid = UserUtil::getSupUid( $getUid ); //获取上司uid
        // 是否关注
        $attention = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid, 'auid' => $getUid ) );
        $isattention = empty( $attention ) ? 0 : 1;
        $params = array(
        	'diary' => $diary,
        	'isattention' => $isattention
    	);
		$this->ajaxReturn( $params, Mobile::dataType() );
    }

    /**
    * 获取所有下属的日志列表
    */
    public function actionAllSubs() {
    	$diary = $attentionList = [];
        $uid = IBOS::app()->user->uid;
        $subUidArr = User::model()->fetchSubUidByUid( $uid );
        // $subUids = implode( ',', $subUidArr );

        // 是否设置了只看直属下属
    	$switch = Setting::model()->fetchSettingValueByKey( 'switch' );
		if ( !$switch ) {
    		$data = array('skey'=>'switch','svalue' => 'on' );
    		Setting::model()->add( $data );
    		$switch = Setting::model()->fetchSettingValueByKey( 'switch' );
    	}
    	if ($switch == 'off') {
    		foreach ($subUidArr as $subUid ) {
	            $_subUidArr = User::model()->fetchSubUidByUid( $subUid );
	            if( isset($_subUidArr['0']) ){
	                $subUidArr[] = $_subUidArr['0'];
	            }
	            // 是否关注
	            $attention = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid, 'auid' => $subUid ) );
	            if ( !empty( $attention ) ) {
	                $attentionList[] = $subUid;
	            }
	        }
    	}else {
    		foreach ($subUidArr as $subUid ) {
	            // 是否关注
	            $attention = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid, 'auid' => $subUid ) );
	            if ( !empty( $attention ) ) {
	                $attentionList[] = $subUid;
	            }
	        }
    	}

    	if ( count( $subUidArr ) > 0 ) {
            $subUids = implode( ',', $subUidArr );
            $condition = "uid IN($subUids)";
            $diary = Diary::model()->fetchAll(array("condition" => $condition, "order" => "diarytime DESC"));
        }
        $params = array(
        	'diary' => $diary,
        	'attentionList' => $attentionList
    	);
        $this->ajaxReturn( $params, Mobile::dataType() );
    }

    /**
    * 获取共享和关注的日志列表
    */
    public function actionOther() {
    	$params = [];
       	$uid = IBOS::app()->user->uid;
		//关注了哪些人
		$attentions = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid ) );
		$auidArr = Convert::getSubByKey( $attentions, 'auid' );
		$auidStr = implode( ',', $auidArr );
		$condition = "(FIND_IN_SET(uid, '{$auidStr}') OR (FIND_IN_SET('{$uid}', shareuid)))";
		$datas = Diary::model()->fetchAll(array("condition" => $condition, "order" => "diarytime DESC"));
		foreach ( $datas as &$data ) {
			// 阅读人数
			if ( empty( $data['readeruid'] ) ) {
				$data['readercount'] = 0;
			} else {
				$data['readercount'] = count( explode( ',', trim( $data['readeruid'], ',' ) ) );
			}
			// 图章
			if ( $data['stamp'] != 0 ) {
				$path = Stamp::model()->fetchIconById( $data['stamp'] );
				$data['stampPath'] = File::fileName( Stamp::STAMP_PATH . $path );
			}
		}
		$this->ajaxReturn( $params, Mobile::dataType() );
    }

    /**
    * 设置共享人员
    */
    public function actionSetShare() {
    	$postDeftoid = $_POST['deftoid'];
    	// $postDeftoid = 'u_4,u_3,u_2';
        $uid = IBOS::app()->user->uid;
        if ( empty( $postDeftoid ) ) {
            DiaryShare::model()->delDeftoidByUid( $uid );
        } else {
            $deftoid = StringUtil::getId( $postDeftoid );
            DiaryShare::model()->addOrUpdateDeftoidByUid( $uid, $deftoid );
        }
    	$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Set succeed' ) );
		$this->ajaxReturn( $message, Mobile::dataType() );
    }

    /**
	 * 设置关注工作日志
	 * @return void
	 */
	public function actionAttention() {
		$auid = Env::getRequest( 'auid' );
		$uid = IBOS::app()->user->uid;
		DiaryAttention::model()->addAttention( $uid, $auid );
		$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Attention succeed' ));
		$this->ajaxReturn( $message, Mobile::dataType() );		
			}

	/**
	 * 取消关注工作日志
	 * @return void
	 */
	public function actionUnattention() {
		$auid = Env::getRequest( 'auid' );
		$uid = IBOS::app()->user->uid;
		DiaryAttention::model()->removeAttention( $uid, $auid );
		$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Unattention succeed' ));
		$this->ajaxReturn( $message, Mobile::dataType() );
	}

	/**
    * 设置关注人员
    */
    public function actionSetAttention() {
    	$postAuid = $_POST['auid'];
    	// $postAuid = 'u_4,u_3,u_2';
        $uid = IBOS::app()->user->uid;
        if ( empty( $postAuid ) ) {
            DiaryAttention::model()->delAttentionByUid( $uid );
        } else {
        	DiaryAttention::model()->delAttentionByUid( $uid );
        	$postAuid = StringUtil::getId( $postAuid );
            DiaryAttention::model()->addAttentionByUid( $uid, $postAuid );
        }
    	$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Set succeed' ) );
		$this->ajaxReturn( $message, Mobile::dataType() );
    }

    /**
    * 设置是否只看直属下属 $switch 开关
    */
    public function actionReadDirect() {
    	$getSwitch = Env::getRequest( 'switch' );
    	$switch = Setting::model()->fetchSettingValueByKey( 'switch' );
    	if ( !$switch ) {
    		$data = array('skey'=>'switch','svalue' => $getSwitch );
    		Setting::model()->add( $data );
    	}else {
    		Setting::model()->updateSettingValueByKey( 'switch', $getSwitch );
    	}
    	$message = array( 'isSuccess' => true, 'data' => IBOS::lang( 'Set succeed' ) );
		$this->ajaxReturn( $message, Mobile::dataType() );
    }

     /**
    * 设置页
    */
    public function actionSet() {
    	$params = [];
       	$uid = IBOS::app()->user->uid;
		//关注了哪些人
		$attentions = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid ) );
		$auidArr = Convert::getSubByKey( $attentions, 'auid' );
		$auidStr = implode( ',', $auidArr );
		// 是否设置只看直属下属，没有就默认设置了
		$switch = Setting::model()->fetchSettingValueByKey( 'switch' );
		if ( !$switch ) {
    		$data = array('skey'=>'switch','svalue' => 'on' );
    		Setting::model()->add( $data );
    		$switch = Setting::model()->fetchSettingValueByKey( 'switch' );
    	}
    	$shareData = DiaryShare::model()->fetchByAttributes( array('uid' => $uid) );
        // $params['defaultShareList'] = $shareData['shareInfo'];
    	// 共享给哪些人
        $params = array(
        	'attentionList' => $auidStr,
        	'switch' => $switch,
        	'defaultShareList' => $shareData['deftoid']
        );
		$this->ajaxReturn( $params, Mobile::dataType() );
    }

     /**
    * 编辑日志页
    */
    public function actionEdit() {
    	$diaryid = intval( Env::getRequest( 'diaryid' ) );
    	$diary = Diary::model()->fetchByPk( $diaryid );
	 	//取得原计划和计划外内容,下一次计划内容
        $data = Diary::model()->fetchDiaryRecord( $diary );
        $dashboardConfig = IBOS::app()->setting->get( 'setting/diaryconfig' );

     	$params = array(
            'diary' => ICDiary::processDefaultShowData( $diary, $data ),
            'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK( $diaryid ),
            'data' => $data,
            'dashboardConfig' => $dashboardConfig,
            // 'uploadConfig' => Attach::getUploadConfig(),
            // 'isInstallCalendar' => $isInstallCalendar,
            // 'workTime' => $workTime
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
        $this->ajaxReturn( $params, Mobile::dataType() );
    }

    /**
    * 从日程中读取数据作为这天的原计划
    */
    public function actionPlanFromSchedule() {
        $uid = IBOS::app()->user->uid;
        $todayDate = $_GET['todayDate'];
        // $todayDate = '2016-07-12';
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
        $this->ajaxReturn( array_values( $plans ), Mobile::dataType() );
    }
}
