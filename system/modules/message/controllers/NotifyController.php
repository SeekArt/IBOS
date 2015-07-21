<?php

/**
 * 消息模块通知中心控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 消息模块通知中心控制器
 * @package application.modules.message.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: NotifyController.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\message\controllers;

use application\core\utils\IBOS;
use application\core\utils\Env;
use application\core\utils\Page;
use application\core\utils\String;
use application\modules\message\model\NotifyMessage;

class NotifyController extends BaseController {

    /**
     * 列表页
     * @return void
     */
    public function actionIndex() {
        $uid = IBOS::app()->user->uid;
        $pageCount = NotifyMessage::model()->fetchPageCountByUid( $uid );
        $pages = Page::create( $pageCount );
        $list = NotifyMessage::model()->fetchAllNotifyListByUid( $uid, 'ctime DESC', $pages->getLimit(), $pages->getOffset() );
        $unreadCount = 0;
        if ( !empty( $list ) ) {
            foreach ( $list as $data ) {
                if ( array_key_exists( 'newlist', $data ) ) {
                    $unreadCount += count( $data['newlist'] );
                }
            }
        }
        $data = array(
            'list' => $list,
            'pages' => $pages,
            'unreadCount' => $unreadCount,
            'modules' => IBOS::app()->getEnabledModule()
        );
        $this->setPageTitle( IBOS::lang( 'Notify' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Message center' ), 'url' => $this->createUrl( 'mention/index' ) ),
            array( 'name' => IBOS::lang( 'Notify' ) )
        ) );
        $this->render( 'index', $data );
    }

    /**
     * 详细页
     * @return void
     */
    public function actionDetail() {
        $uid = IBOS::app()->user->uid;
        $module = Env::getRequest( 'module' );
        $pageCount = IBOS::app()->db->createCommand()
                ->select( 'count(id)' )
                ->from( '{{notify_message}}' )
                ->where( "uid={$uid} AND module = '{$module}'" )
                ->group( 'module' )
                ->queryScalar();
        $pages = Page::create( $pageCount );
        $list = NotifyMessage::model()->fetchAllDetailByTimeLine( $uid, $module, $pages->getLimit(), $pages->getOffset() );
        $data = array(
            'list' => $list,
            'pages' => $pages,
        );
        NotifyMessage::model()->setReadByModule( $uid, $module );
        $this->setPageTitle( IBOS::lang( 'Detail notify' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Message center' ), 'url' => $this->createUrl( 'mention/index' ) ),
            array( 'name' => IBOS::lang( 'Notify' ), 'url' => $this->createUrl( 'notify/index' ) ),
            array( 'name' => IBOS::lang( 'Detail notify' ) )
        ) );
        $this->render( 'detail', $data );
    }

    /**
     * 删除操作
     * @return void
     */
    public function actionDelete() {
        $op = Env::getRequest( 'op' );
        if ( !in_array( $op, array( 'id', 'module' ) ) ) {
            $op = 'id';
        }
        $res = NotifyMessage::model()->deleteNotify( Env::getRequest( 'id' ), $op );
        $this->ajaxReturn( array( 'IsSuccess' => !!$res ) );
    }

    /**
     * 设置当前用户所有未读提醒为已读
     * @return void 
     */
    public function actionSetAllRead() {
        if ( IBOS::app()->request->isAjaxRequest ) {
            $uid = IBOS::app()->user->uid;
            $res = NotifyMessage::model()->setRead( $uid );
            $this->ajaxReturn( array( 'IsSuccess' => !!$res ) );
        }
    }

    /**
     * 设置列表提醒为已读
     * @return void
     */
    public function actionSetIsRead() {
        $module = String::filterCleanHtml( Env::getRequest( 'module' ) );
        $res = NotifyMessage::model()->setReadByModule( IBOS::app()->user->uid, $module );
        $this->ajaxReturn( array( 'IsSuccess' => !!$res ) );
    }

    /**
     * 收到的赞
     * @return void 
     */
    public function actionDigg() {
        $this->setPageTitle( IBOS::lang( 'My digg' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Message center' ), 'url' => $this->createUrl( 'mention/index' ) ),
            array( 'name' => IBOS::lang( 'Notify' ), 'url' => $this->createUrl( 'notify/index' ) ),
            array( 'name' => IBOS::lang( 'My digg' ) )
        ) );
        $this->render( 'digg' );
    }

    /**
     * 提醒跳转统一连接
     */
    public function actionJump() {
        $url = Env::getRequest( 'url' );
        $id = intval( Env::getRequest( 'id' ) );
        NotifyMessage::model()->updateAll( array( 'isread' => 1 ), "id = :id", array( ':id' => $id ) );
        $this->redirect( $url );
    }

}
