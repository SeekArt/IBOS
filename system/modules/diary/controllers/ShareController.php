<?php

/**
 * 工作日志模块------共享日志控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 工作日志模块------共享日志控制器，继承DiaryBaseController
 * @package application.modules.diary.components
 * @version $Id: ShareController.php 4064 2014-09-03 09:13:16Z zhangrong $
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
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;


class ShareController extends BaseController {

    /**
     * 检查是否开启关注日志功能
     */
    public function init() {
        if ( !$this->issetShare() ) {
            $this->error( IBOS::lang( 'Share not open' ), $this->createUrl( 'default/index' ) );
        }
        parent::init();
    }

    /**
     * 取得侧栏导航
     * @return string
     */
    protected function getSidebar() {
        $sidebarAlias = 'application.modules.diary.views.share.sidebar';

        //取得最近的五篇分享日志
        $records = Diary::model()->fetchAllByShareCondition( IBOS::app()->user->uid, 5 );
        $result = array();
        foreach ( $records as $record ) {
            $record['diarytime'] = date( 'm-d', $record['diarytime'] );
            $record['user'] = User::model()->fetchByUid( $record['uid'] );
            $result[] = $record;
        }
        $sidebarView = $this->renderPartial( $sidebarAlias, array( 'data' => $result, 'statModule' => IBOS::app()->setting->get( 'setting/statmodules' ) ), true );
        return $sidebarView;
    }

    /**
     * 列表页显示
     * @return void
     */
    public function actionIndex() {
        $op = Env::getRequest( 'op' );
        if ( !in_array( $op, array( 'personal' ) ) ) {
            // 日期处理
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
            // 取得shareuid字段中包含作者的数据
            $uid = IBOS::app()->user->uid;
            $condition = "FIND_IN_SET('$uid',shareuid) AND uid NOT IN($uid) AND diarytime=$time";
            $paginationData = Diary::model()->fetchAllByPage( $condition );
            $params = array(
                'dateWeekDay' => DiaryUtil::getDateAndWeekDay( date( 'Y-m-d', strtotime( $date ) ) ),
                'pagination' => $paginationData['pagination'],
                'data' => ICDiary::processShareListData( $uid, $paginationData['data'] ),
                'dashboardConfig' => $this->getDiaryConfig(),
                'attentionSwitch' => $this->issetAttention()
            );
            //上一天和下一天
            $params['prevAndNextDate'] = array(
                'prev' => date( 'Y-m-d', (strtotime( $date ) - 24 * 60 * 60 ) ),
                'next' => date( 'Y-m-d', (strtotime( $date ) + 24 * 60 * 60 ) ),
                'prevTime' => strtotime( $date ) - 24 * 60 * 60,
                'nextTime' => strtotime( $date ) + 24 * 60 * 60
            );
            $this->setPageTitle( IBOS::lang( 'Share diary' ) );
            $this->setPageState( 'breadCrumbs', array(
                array( 'name' => IBOS::lang( 'Personal Office' ) ),
                array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
                array( 'name' => IBOS::lang( 'Share diary' ) )
            ) );
            $this->render( 'index', $params );
        } else {
            $this->$op();
        }
    }

    /**
     * 展示某篇共享日志
     */
    public function actionShow() {
        $diaryid = intval( Env::getRequest( 'diaryid' ) );
        $uid = IBOS::app()->user->uid;
        if ( empty( $diaryid ) ) {
            $this->error( IBOS::lang( 'Parameters error', 'error' ), $this->createUrl( 'share/index' ) );
        }
        $diary = Diary::model()->fetchByPk( $diaryid );
        if ( empty( $diary ) ) {
            $this->error( IBOS::lang( 'No data found' ), $this->createUrl( 'share/index' ) );
        }
        // 权限判断
        if ( !ICDiary::checkScope( $uid, $diary ) ) {
            $this->error( IBOS::lang( 'You do not have permission to view the log' ), $this->createUrl( 'share/index' ) );
        }
        //增加阅读记录
        Diary::model()->addReaderuidByPK( $diary, $uid );
        //取得原计划和计划外内容,下一次计划内容
        $data = Diary::model()->fetchDiaryRecord( $diary );
        $params = array(
            'diary' => ICDiary::processDefaultShowData( $diary ),
            'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK( $diary['diaryid'] ),
            'data' => $data,
        );
        if ( !empty( $diary['attachmentid'] ) ) {
            $params['attach'] = Attach::getAttach( $diary['attachmentid'], true, true, false, false, true );
            $params['count'] = 0;
        }
        // 是否允许评论
        $params['allowComment'] = $this->issetSharecomment() || UserUtil::checkIsSub( $uid, $diary['uid'] ) ? 1 : 0;
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
        $params['sharecomment'] = $this->issetSharecomment();
        $this->setPageTitle( IBOS::lang( 'Show share diary' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
            array( 'name' => IBOS::lang( 'Show share diary' ) )
        ) );
        $this->render( 'show', $params );
    }

    /**
     * 取得某个uid的分享日志
     * @return void
     */
    private function personal() {
        $getUid = intval( Env::getRequest( 'uid' ) );
        $uid = IBOS::app()->user->uid;
        //是否搜索
        if ( Env::getRequest( 'param' ) == 'search' ) {
            $this->search();
        }
        $condition = "uid='{$getUid}' AND FIND_IN_SET('$uid',shareuid) AND uid NOT IN($uid)";
        $this->_condition = DiaryUtil::joinCondition( $this->_condition, $condition );
        $paginationData = Diary::model()->fetchAllByPage( $this->_condition );
        // 是否关注
        $attention = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid, 'auid' => $getUid ) );
        $data = array(
            'pagination' => $paginationData['pagination'],
            'data' => ICDiary::processDefaultListData( $paginationData['data'] ),
            'diaryCount' => Diary::model()->count( $this->_condition ),
            'commentCount' => Diary::model()->countCommentByUid( $getUid, $uid ),
            'user' => User::model()->fetchByUid( $getUid ),
            'dashboardConfig' => $this->getDiaryConfig(),
            'isattention' => empty( $attention ) ? 0 : 1
        );
        $this->setPageTitle( IBOS::lang( 'Share diary' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Work diary' ), 'url' => $this->createUrl( 'default/index' ) ),
            array( 'name' => IBOS::lang( 'Share diary' ) )
        ) );
        $this->render( 'personal', $data );
    }

}
