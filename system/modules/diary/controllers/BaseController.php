<?php

/**
 * 工作日志模块------工作日志基础控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 工作日志模块------工作日志基础控制器，继承Controller
 * @package application.modules.diary.components
 * @version $Id: BaseController.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\model\Diary;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CJSON;

class BaseController extends Controller {

	/**
	 * 查询条件
	 * @var string 
	 * @access protected 
	 */
	protected $_condition;

	const COMPLETE_FALG = 10;
	const UNSTART_FALG = 0;
	
	/**
	 * 获取日志模块后台设置
	 * @return array
	 */
	public function getDiaryConfig() {
		return DiaryUtil::getSetting();
	}

	/**
	 * 获取未评阅的数量
	 * @param type $uid 上司UID
	 */
	public function getUnreviews() {
		//获取所有直属下属id
		$uidArr = User::model()->fetchSubUidByUid( IBOS::app()->user->uid );
		$count = 0;
		foreach ( $uidArr as $subUid ) {
			$diarys = Diary::model()->fetchAll( 'uid=:uid AND isreview=:isreview', array( ':uid' => $subUid, ':isreview' => 0 ) );
			$count += count( $diarys );
		}
		if ( $count == 0 ) {
			$count = '';
		}
		return $count;
	}

	/**
	 * 通过ajax取得侧栏日历数据
	 * @return void
	 */
	protected function getAjaxSidebar() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$sidebarView = $this->getSidebarData();
			$this->ajaxReturn( $sidebarView );
		}
	}

	/**
	 * 通过日历数据
	 * @return array
	 */
	protected function getSidebarData() {
		$uid = IBOS::app()->user->uid;
		$ym = date( 'Ym' );
		if ( array_key_exists( 'ym', $_GET ) ) {
			$ym = $_GET['ym'];
		}
		if ( array_key_exists( 'diaryDate', $_GET ) ) {
			list($year, $month, ) = explode( '-', $_GET['diaryDate'] );
			$ym = $year . $month;
		}
		$currentDay = 0;
		if ( date( 'm' ) == substr( $ym, 4 ) ) {
			$currentDay = date( 'j' );
		}
		if ( array_key_exists( 'currentDay', $_GET ) ) {
			$currentDay = $_GET['currentDay'];
		}
		//取出某个月的所有日志记录，得到每篇日志的有日志，已点评状态
		$diaryList = Diary::model()->fetchAllByUidAndDiarytime( $ym, $uid );
		$calendarStr = DiaryUtil::getCalendar( $ym, $diaryList, $currentDay );
		return $calendarStr;
	}

	/**
	 * 取得侧栏视图
	 * @return string
	 */
	protected function getSidebar() {
		$sidebarAlias = 'application.modules.diary.views.sidebar';

		$month = date( 'm' );
		if ( array_key_exists( 'diaryDate', $_GET ) ) {
			list(, $m, ) = explode( '-', $_GET['diaryDate'] );
			$month = $m;
		}
		$monthName = array( "一", "二", "三", "四", "五", "六", "七", "八", "九", "十", "十一", "十二" );
		$monthStr = $monthName[$month - 1];
		$params = array(
			'statModule' => IBOS::app()->setting->get( 'setting/statmodules' ),
			'calendar' => $this->getSidebarData(),
			'currentDateInfo' => array( 'year' => date( 'Y' ), 'month' => $month, 'monthStr' => $monthStr ),
			'dashboardConfig' => IBOS::app()->setting->get( 'setting/diaryconfig' )
		);
		$sidebarView = $this->renderPartial( $sidebarAlias, $params, true );
		return $sidebarView;
	}

	/**
	 * 获取后台勾选的图章
	 * @return array  返回后台选中好的图章
	 */
	protected function getStamp() {
		$config = $this->getDiaryConfig();
		//取得所有图章
		if ( $config['stampenable'] ) {
			$stampDetails = $config['stampdetails'];
			$stamps = array( );
			if ( !empty( $stampDetails ) ) {
				$stampidArr = explode( ',', trim( $stampDetails ) );
				if ( count( $stampidArr ) > 0 ) {
					foreach ( $stampidArr as $stampidStr ) {
						list($stampId, $score) = explode( ':', $stampidStr );
						if ( $stampId != 0 ) {
							$stamps[$score] = intval( $stampId );
						}
					}
				}
			}
			$stampList = Stamp::model()->fetchAll( );
			// 组合所有图章的输出数据
			$temp = array();
			foreach ( $stampList as $stamp ) {
				$stampid = $stamp['id'];
				$temp[$stampid]['title'] = $stamp['code'];
				$temp[$stampid]['stamp'] = $stamp['stamp'];
				$temp[$stampid]['value'] = $stamp['id'];
				$temp[$stampid]['path'] = File::fileName( Stamp::STAMP_PATH . $stamp['icon'] );
			}
			$result = array();
			if ( !empty( $stamps ) ) {
				// 这个循环为了保持图章输出的顺序与后台设置的顺序一样，并且组合图章对应的分值
				foreach($stamps as $score => $stampid){
					$result[$score] = $temp[$stampid];
					$result[$score]['point'] = $score;
				}
			}
			$ret = CJSON::encode( array_values( $result ) );
		} else {
			$ret = CJSON::encode( '' );
		}
		return $ret;
	}

	/**
	 * 检查是否开启图章功能
	 */
	protected function issetStamp() {
		$config = $this->getDiaryConfig();
		return !!$config['stampenable'];
	}

	/**
	 * 检查是否开启自动评阅
	 * @return boolean
	 */
	protected function issetAutoReview() {
		$config = $this->getDiaryConfig();
		return !!$config['autoreview'];
	}

	/**
	 * 检查是否开启关注功能
	 * @return boolean
	 */
	protected function issetAttention() {
		$config = $this->getDiaryConfig();
		return !!$config['attention'];
	}

	/**
	 * 检查是否开启共享功能
	 * @return boolean
	 */
	protected function issetShare() {
		$config = $this->getDiaryConfig();
		return !!$config['sharepersonnel'];
	}

	/**
	 * 检查是否开启允许点评共享日志
	 * @return boolean
	 */
	protected function issetSharecomment() {
		$config = $this->getDiaryConfig();
		return !!$config['sharecomment'];
	}

	/**
	 * 获取自动评阅的图章id
	 * @return integer
	 */
	protected function getAutoReviewStamp() {
		$config = $this->getDiaryConfig();
		return intval( $config['autoreviewstamp'] );
	}
	
	/**
	 * 获得uid在各个控制器是否有评论权限
	 * @param string $controller
	 * @param integer $uid
	 * @param array $diary
	 * @return integer
	 */
	protected function getIsAllowComment( $controller, $uid, $diary ){
		$ret = 0;
		if ( $controller == 'review' ) {
			$ret = 1;
		} elseif( $controller == 'share' || $controller == 'attention' ) {
			$ret = $this->issetSharecomment() || UserUtil::checkIsSub( $uid, $diary['uid'] ) ? 1 : 0;
		}
		return $ret;
	}
	
	/**
	 * 搜索操作
	 * @return void
	 */
	protected function search() {
		$type = Env::getRequest( 'type' );
		$conditionCookie = MainUtil::getCookie( 'condition' );
		if ( empty( $conditionCookie ) ) {
			MainUtil::setCookie( 'condition', $this->_condition, 10 * 60 );
		}
		if ( $type == 'advanced_search' ) {
			$search = $_POST['search'];
			$this->_condition = DiaryUtil::joinSearchCondition( $search );
		} else if ( $type == 'normal_search' ) {
			$keyword = $_POST['keyword'];
			MainUtil::setCookie( 'keyword', $keyword, 10 * 60 );
			$this->_condition = " content LIKE '%$keyword%' ";
		} else {
			$this->_condition = $conditionCookie;
		}
		//把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
		if ( $this->_condition != MainUtil::getCookie( 'condition' ) ) {
			MainUtil::setCookie( 'condition', $this->_condition, 10 * 60 );
		}
	}

	/**
	 * 判断是否有下属
	 * @return boolean
	 */
	protected function checkIsHasSub() {
		return DiaryUtil::checkIsHasSub();
	}

}
