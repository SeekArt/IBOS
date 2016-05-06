<?php

namespace application\modules\report\controllers;

use application\core\controllers\Controller;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Stamp;
use application\modules\main\utils\Main;
use application\modules\message\model\Comment;
use application\modules\report\model\Report;
use application\modules\report\model\ReportRecord;
use application\modules\report\utils\Report as ReportUtil;
use application\modules\user\model\User;
use CHtml;
use CJSON;

/**
 * 工作总结与计划模块------工作总结与计划基础控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 工作总结与计划模块------工作总结与计划基础控制器，继承ICController
 * @package application.modules.report.components
 * @version $Id: DefaultBaseController.php 1897 2013-12-12 12:33:07Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
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
     * 获取总结模块后台设置
     * @return array
     */
    public function getReportConfig() {
        return ReportUtil::getSetting();
    }

    /**
     * ajax总结计划展开页
     */
    protected function showDetail() {
        $repid = intval( Env::getRequest( 'repid' ) );
        $isShowTitle = Env::getRequest( 'isShowTitle' );
        $fromController = Env::getRequest( 'fromController' );
        $report = Report::model()->fetchByPk( $repid );
        // 增加阅读记录
        $uid = IBOS::app()->user->uid;
        Report::model()->addReaderuid( $report, $uid );
        // 取得原计划和计划外内容,下一次计划内容
        $record = ReportRecord::model()->fetchAllRecordByRep( $report );
        // 附件
        $attachs = array();
        if ( !empty( $report['attachmentid'] ) ) {
            $attachs = Attach::getAttach( $report['attachmentid'], true, true, false, false, true );
        }
        //获取阅读人员
        $readers = array();
        if ( !empty( $report['readeruid'] ) ) {
            $readerArr = explode( ',', $report['readeruid'] );
            $readers = User::model()->fetchAllByPk( $readerArr );
        }
        // 图章
        $stampUrl = '';
        if ( $report['stamp'] != 0 ) {
            $stamp = Stamp::model()->fetchStampById( $report['stamp'] );
            $stampUrl = File::fileName( Stamp::STAMP_PATH ) . $stamp;
        }
        // 日期个性化
        $report['addtime'] = Convert::formatDate( $report['addtime'], 'u' );
        $params = array(
            'lang' => IBOS::getLangSource( 'report.default' ),
            'repid' => $repid,
            'report' => $report,
            'uid' => $uid,
            'orgPlanList' => $record['orgPlanList'],
            'outSidePlanList' => $record['outSidePlanList'],
            'nextPlanList' => $record['nextPlanList'],
            'attachs' => $attachs,
            'readers' => $readers,
            'stampUrl' => $stampUrl,
            'fromController' => $fromController,
            'isShowTitle' => $isShowTitle,
            'allowComment' => $this->getIsAllowComment( $fromController )
        );
        $detailAlias = 'application.modules.report.views.detail';
        $detailView = $this->renderPartial( $detailAlias, $params, true );
        $this->ajaxReturn( array( 'data' => $detailView, 'isSuccess' => true ) );
    }

    /**
     * 获得uid在各个控制器是否有评论权限
     * @param string $controller
     * @return integer
     */
    protected function getIsAllowComment( $controller ) {
        $ret = 0;
        if ( $controller == 'review' ) {
            $ret = 1;
        }
        return $ret;
    }

    /**
     * 搜索
     */
    protected function search() {
        $type = Env::getRequest( 'type' );
        $conditionCookie = Main::getCookie( 'condition' );
        if ( empty( $conditionCookie ) ) {
            Main::setCookie( 'condition', $this->_condition, 10 * 60 );
        }
        if ( $type == 'advanced_search' ) {
            $search = $_POST['search'];
            $this->_condition = ReportUtil::joinSearchCondition( $search );
        } else if ( $type == 'normal_search' ) {
            //添加对keyword的转义，防止SQL错误
            $keyword = CHtml::encode( $_POST['keyword'] );
            Main::setCookie( 'keyword', $keyword, 10 * 60 );
            $this->_condition = " ( content LIKE '%$keyword%' OR subject LIKE '%$keyword%' ) ";
        } else {
            $this->_condition = $conditionCookie;
        }
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
        if ( $this->_condition != Main::getCookie( 'condition' ) ) {
            Main::setCookie( 'condition', $this->_condition, 10 * 60 );
        }
    }

    /**
     * 获取后台勾选的图章
     * @return array  返回后台选中好的图章
     */
    protected function getStamp() {
        $config = $this->getReportConfig();
        //取得所有图章
        if ( $config['stampenable'] ) {
            $stampDetails = $config['stampdetails'];
            $stamps = array();
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
            $stampList = Stamp::model()->fetchAll();
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
                foreach ( $stamps as $score => $stampid ) {
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
        $config = $this->getReportConfig();
        return !!$config['stampenable'];
    }

    /**
     * 检查是否开启自动评阅
     * @return boolean
     */
    protected function issetAutoReview() {
        $config = $this->getReportConfig();
        return !!$config['autoreview'];
    }

    /**
     * 获取自动评阅的图章id
     * @return integer
     */
    protected function getAutoReviewStamp() {
        $config = $this->getReportConfig();
        return intval( $config['autoreviewstamp'] );
    }

    /**
     * 获取未评阅的数量
     * @param type $uid 上司UID
     */
    protected function getUnreviews() {
        $uid = IBOS::app()->user->uid;
        //获取所有直属下属id
        $subUidArr = User::model()->fetchSubUidByUid( $uid );
        $count = '';
        if ( !empty( $subUidArr ) ) {
            $subUidStr = implode( ',', $subUidArr );
            $unreviewReps = Report::model()->fetchUnreviewReps( "FIND_IN_SET(`uid`, '{$subUidStr}')" );
            if ( !empty( $unreviewReps ) ) {
                $count = count( $unreviewReps );
            }
        }
        return $count;
    }

    /**
     * 取得所有阅读和阅读人信息
     */
    protected function getReaderList() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            $repid = Env::getRequest( 'repid' );
            $record = Report::model()->fetchByPk( $repid );
            $readerUids = $record['readeruid'];
            $htmlStr = '<table class="pop-table">';
            if ( !empty( $readerUids ) ) {
                $htmlStr .= '<div class="rp-reviews-avatar">';
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
    protected function getCommentList() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            $repid = Env::getRequest( 'repid' );
            $records = Comment::model()->fetchAll(
                    array(
                        'select' => array( 'uid', 'content', 'ctime' ),
                        'condition' => "module=:module AND `table`=:table AND rowid=:rowid AND isdel=:isdel ORDER BY ctime DESC LIMIT 0,5",
                        'params' => array( ':module' => 'report', ':table' => 'report', ':rowid' => $repid, ':isdel' => 0 )
                    )
            );
            $htmlStr = '<div class="pop-comment"><ul class="pop-comment-list">';
            if ( !empty( $records ) ) {
                foreach ( $records as $record ) {
                    $record['realname'] = User::model()->fetchRealnameByUid( $record['uid'] );
                    $content = StringUtil::cutStr( $record['content'], 45 );
                    $htmlStr.= '<li class="media">
									<a href="' . IBOS::app()->createUrl( 'user/home/index', array( 'uid' => $record['uid'] ) ) . '" class="pop-comment-avatar pull-left">
										<img src="' . Org::getDataStatic( $record['uid'], 'avatar', 'small' ) . '" title="' . $record['realname'] . '" class="img-rounded"/>
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
     * 判断是否有下属
     * @return boolean
     */
    protected function checkIsHasSub() {
        $subUidArr = User::model()->fetchSubUidByUid( IBOS::app()->user->uid );
        if ( !empty( $subUidArr ) ) {
            return true;
        } else {
            return false;
        }
    }

}
