<?php

/**
 * 工作日志模块------评阅控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 工作日志模块------评阅控制器，继承DiaryBaseController
 * @package application.modules.diary.controllers
 * @version $Id: ReviewController.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\IBOS; 
use application\modules\dashboard\model\Stamp;
use application\modules\diary\components\Diary as ICDiary;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryAttention;
use application\modules\diary\model\DiaryStats;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\main\utils\Main as MainUtil;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CJSON;
use CPagination;

class ReviewController extends BaseController {

	/**
	 * 列表页显示,取得当前uid所有下属的某一天的日志
	 * @return void
	 */
	public function actionIndex() {
		$op = Env::getRequest( 'op' );
		$option = empty( $op ) ? 'default' : $op;
		$routes = array( 'default', 'show', 'showdiary', 'getsubordinates', 'getStampIcon' );
		if ( !in_array( $option, $routes ) ) {
			$this->error( IBOS::lang( 'Can not find the path' ), $this->createUrl( 'default/index' ) );
		}
		if ( $option == 'default' ) {
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
			}

			$uid = IBOS::app()->user->uid;
			$getSubUids = Env::getRequest( 'uid' );  //是否是点击某个部门
			if ( !empty( $getSubUids ) ) {
				$subUidArr = explode( ',', $getSubUids );
				// 权限判断
				foreach ( $subUidArr as $subUid ) {
					if ( !UserUtil::checkIsSub( $uid, $subUid ) ) {
						$this->error( IBOS::lang( 'Have not permission' ), $this->createUrl( 'default/index' ) );
					}
				}
			} else {
				$subUidArr = User::model()->fetchSubUidByUid( $uid );
			}
			$params = array();
			$subUids = implode( ',', $subUidArr );
			if ( count( $subUidArr ) > 0 ) {
				$condition = "uid IN($subUids)" . " AND diarytime=$time";
				$paginationData = Diary::model()->fetchAllByPage( $condition );

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
					'data' => ICDiary::processReviewListData( $uid, $paginationData['data'] ),
					'noRecordUserList' => $noRecordUserList
				);
			} else {
				$params = array(
					'pagination' => new CPagination( 0 ),
					'data' => array(),
					'noRecordUserList' => array()
				);
			}
			$params['date'] = $date;
			$params['dateWeekDay'] = DiaryUtil::getDateAndWeekDay( $date );
			$params['dashboardConfig'] = $this->getDiaryConfig();
			$params['subUids'] = $subUids;
			$params['stamp'] = CJSON::encode( $this->getStamp() );
			//上一天和下一天
			$params['prevAndNextDate'] = array(
				'prev' => date( 'Y-m-d', (strtotime( $date ) - 24 * 60 * 60 ) ),
				'next' => date( 'Y-m-d', (strtotime( $date ) + 24 * 60 * 60 ) ),
				'prevTime' => strtotime( $date ) - 24 * 60 * 60,
				'nextTime' => strtotime( $date ) + 24 * 60 * 60
			);
			$this->setPageTitle( IBOS::lang( 'Review subordinate diary' ) );
			$this->setPageState( 'breadCrumbs', array(
				array( 'name' => IBOS::lang( 'Personal Office' ) ),
				array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
				array( 'name' => IBOS::lang( 'Subordinate diary' ) )
			) );
			$this->render( 'index', $params );
		} else {
			$this->$option();
		}
	}

	/**
	 * 取得某个uid的所有工作日志
	 * @return void
	 */
	public function actionPersonal() {
		$uid = IBOS::app()->user->uid;
		$getUid = intval( Env::getRequest( 'uid' ) );
		// 权限判断
		if ( !UserUtil::checkIsSub( $uid, $getUid ) ) {
			$this->error( IBOS::lang( 'Have not permission' ), $this->createUrl( 'review/index' ) );
		}
		//是否搜索
		if ( Env::getRequest( 'param' ) == 'search' ) {
			$this->search();
		}
		$this->_condition = DiaryUtil::joinCondition( $this->_condition, "uid = $getUid" );
		$paginationData = Diary::model()->fetchAllByPage( $this->_condition );
		$supUid = UserUtil::getSupUid( $getUid ); //获取上司uid
		// 是否关注
		$attention = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid, 'auid' => $getUid ) );
		$data = array(
			'pagination' => $paginationData['pagination'],
			'data' => ICDiary::processDefaultListData( $paginationData['data'] ),
			'diaryCount' => Diary::model()->count( $this->_condition ),
			'commentCount' => Diary::model()->countCommentByReview( $getUid ),
			'user' => User::model()->fetchByUid( $getUid ),
			'supUid' => $supUid,
			'dashboardConfig' => $this->getDiaryConfig(),
			'isattention' => empty( $attention ) ? 0 : 1
		);
		$this->setPageTitle( IBOS::lang( 'Review subordinate diary' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => IBOS::lang( 'Personal Office' ) ),
			array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
			array( 'name' => IBOS::lang( 'Subordinate personal diary' ) )
		) );
		$this->render( 'personal', $data );
	}

	/**
	 * 显示工作日志
	 * @return void
	 */
	public function actionShow() {
		$diaryid = intval( Env::getRequest( 'diaryid' ) );
		$uid = IBOS::app()->user->uid;
		if ( empty( $diaryid ) ) {
			$this->error( IBOS::lang( 'Parameters error', 'error' ), $this->createUrl( 'review/index' ) );
		}
		$diary = Diary::model()->fetchByPk( $diaryid );
		if( $diary['uid'] == $uid ){
			$this->redirect( $this->createUrl( 'default/show', array( 'diaryid' => $diaryid ) ) );
		}
		if ( empty( $diary ) ) {
			$this->error( IBOS::lang( 'No data found' ), $this->createUrl( 'review/index' ) );
		}
		// 权限判断
		if ( !ICDiary::checkReviewScope( $uid, $diary ) ) {
			$this->error( IBOS::lang( 'You do not have permission to view the log' ), $this->createUrl( 'review/index' ) );
		}
		//增加阅读记录
		Diary::model()->addReaderuidByPK( $diary, $uid );
		//取得原计划和计划外内容,下一次计划内容
		$data = Diary::model()->fetchDiaryRecord( $diary );
		$params = array(
			'diary' => ICDiary::processDefaultShowData( $diary ),
			'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK( $diaryid ),
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
		if ( !empty( $diary['stamp'] ) ) {
			$params['stampUrl'] = Stamp::model()->fetchStampById( $diary['stamp'] );
		}
		//判断后台是否开启自动评阅，若是，把该日志改成已评阅
		if ( $this->issetStamp() && $this->issetAutoReview() ) {
			$this->changeIsreview( $diaryid );
		}
		$this->setPageTitle( IBOS::lang( 'Show subordinate diary' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => IBOS::lang( 'Personal Office' ) ),
			array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
			array( 'name' => IBOS::lang( 'Show subordinate diary' ) )
		) );
		$this->render( 'show', $params );
	}

	/**
	 * 编辑
	 * @return void
	 */
	public function actionEdit() {
		$op = Env::getRequest( 'op' );
		$option = empty( $op ) ? 'default' : $op;
		$routes = array( 'default', 'remind', 'changeIsreview', 'editStamp' );
		if ( !in_array( $option, $routes ) ) {
			$this->error( IBOS::lang( 'Can not find the path' ), $this->createUrl( 'default/index' ) );
		}
		if ( $option == 'default' ) {
			
		} elseif ( $option == 'changeIsreview' ) {
			$diaryid = Env::getRequest( 'diaryid' );
			$this->changeIsreview( $diaryid );
		} else {
			$this->$option();
		}
	}

	/**
	 * 把某篇日志改成已评阅
	 * @param type $diaryid
	 */
	private function changeIsreview( $diaryid ) {
		$diary = Diary::model()->fetchByPk( $diaryid );
		// 判断是否是直属上司，只给直属上司自动评阅
		if ( !empty( $diary ) && UserUtil::checkIsUpUid( $diary['uid'], IBOS::app()->user->uid ) ) {
			if ( $diary['stamp'] == 0 ) {
				$stamp = $this->getAutoReviewStamp();
				Diary::model()->modify( $diaryid, array( 'isreview' => 1, 'stamp' => $stamp ) );
				DiaryStats::model()->scoreDiary( $diary['diaryid'], $diary['uid'], $stamp );
			} else {
				Diary::model()->modify( $diaryid, array( 'isreview' => 1 ) );
			}
		}
	}

	/**
	 * 没有工作日志消息提醒
	 * @void
	 */
	private function remind() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$date = Env::getRequest( 'date' );
			$dateTime = strtotime( $date );
			$getUids = trim( Env::getRequest( 'uids' ), ',' );
			$uidArr = explode( ',', $getUids );
			$uid = IBOS::app()->user->uid;
			if ( empty( $uidArr ) ) {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'No user to remind' ) ) );
			}
			// 权限判断
			foreach ( $uidArr as $subUid ) {
				if ( !UserUtil::checkIsSub( $uid, $subUid ) ) {
					$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => IBOS::lang( 'No permission to remind' ) ) );
				}
			}
			// 取得后台提醒配置及默认提醒内容
			$dashboardConfig = $this->getDiaryConfig();
			// 发送系统提醒
			$config = array(
				'{name}' => User::model()->fetchRealnameByUid( $uid ),
				'{title}' => IBOS::lang( 'Remind title', '', array( 'y' => date( 'Y', $dateTime ), 'm' => date( 'm', $dateTime ), 'd' => date( 'd', $dateTime ) ) ),
				'{content}' => $dashboardConfig['remindcontent']
			);
			if ( count( $uidArr ) > 0 ) {
				Notify::model()->sendNotify( $uidArr, 'diary_message', $config, $uid );
			}
			// 写入cookie，这天已提醒过
			$todayTime = strtotime( date( 'Y-m-d' ) );
			MainUtil::setCookie( "reminded_" . $dateTime, md5( $dateTime ), $todayTime + 24 * 60 * 60 - TIMESTAMP );
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => IBOS::lang( 'Remind succeed' ) ) );
		}
	}

	/**
	 * 得到某个用户的下属，取5条
	 * @return void
	 */
	private function getsubordinates() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$uid = $_GET['uid'];
			$getItem = Env::getRequest( 'item' );
			$item = empty( $getItem ) ? 5 : $getItem;
			$users = UserUtil::getAllSubs( $uid );
			if ( Env::getRequest( 'act' ) == 'stats' ) {
				$theUrl = 'diary/stats/review';
			} else {
				$theUrl = 'diary/review/personal';
			}
			$htmlStr = '<ul class="mng-trd-list">';
			$num = 0;
			foreach ( $users as $user ) {
				if ( $num < $item ) {
					$htmlStr.='<li class="mng-item">
                                            <a href="' . IBOS::app()->urlManager->createUrl( $theUrl, array( 'uid' => $user['uid'] ) ) . '">
                                                <img src="' . $user['avatar_middle'] . '" alt="">
                                                ' . $user['realname'] . '
                                            </a>';
				}
				if ( DiaryUtil::getIsAttention( $user['uid'] ) ) {
					$htmlStr.= '<a href="javascript:;" class="o-gudstar pull-right" data-action="toggleAsteriskUnderling" data-id="' . $user['uid'] . '" data-param=\'{"id": "' . $user['uid'] . '"}\'></a>';
				} else {
					$htmlStr.= '<a href="javascript:;" class="o-udstar pull-right" data-action="toggleAsteriskUnderling" data-id="' . $user['uid'] . '" data-param=\'{"id": "' . $user['uid'] . '"}\'></a>';
				}
				$htmlStr.='</li>';
				$num++;
			}
			$subNums = count( $users );
			if ( $subNums > $item ) {
				$htmlStr.='<li class="mng-item view-all" data-uid="' . $uid . '">
                                                <a href="javascript:;">
                                                    <i class="o-da-allsub"></i>
                                                    ' . IBOS::lang( 'View all subordinate' ) . '
                                                </a>
                                            </li>';
			}
			$htmlStr.='</ul>';
			echo $htmlStr;
		}
	}

	/**
	 * 处理评阅图章
	 */
	private function editStamp() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$diaryid = $_GET['diaryid'];
			$stamp = $_GET['stamp'];
			Diary::model()->modify( $diaryid, array( 'stamp' => $stamp ) );
		}
	}

	/**
	 * 获取图章icon
	 */
	private function getStampIcon() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$diaryid = $_GET['diaryid'];
			$diary = Diary::model()->fetchByPk( $diaryid );
			if ( $diary['stamp'] != 0 ) {
				$icon = Stamp::model()->fetchIconById( $diary['stamp'] );
				$this->ajaxReturn( array( 'isSuccess' => true, 'icon' => $icon ) );
			}
		}
	}

}
