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
 * @version $Id: NotifyController.php 7023 2016-05-10 08:01:05Z Aeolus $
 */

namespace application\modules\message\controllers;

use application\core\utils\Ibos;
use application\core\utils\Env;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\message\model\NotifyMessage;

class NotifyController extends BaseController {

    /**
     * 列表页
     * @return void
     */
    public function actionIndex() {
        $uid = Ibos::app()->user->uid;
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
            'modules' => Ibos::app()->getEnabledModule()
        );
        $this->setPageTitle( Ibos::lang( 'Notify' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => Ibos::lang( 'Message center' ), 'url' => $this->createUrl( 'mention/index' ) ),
            array( 'name' => Ibos::lang( 'Notify' ) )
        ) );
        $this->render( 'index', $data );
    }

    /**
     * 详细页
     * @return void
     */
    public function actionDetail() {
        $uid = Ibos::app()->user->uid;
        $module = Env::getRequest( 'module' );
        $pageCount = Ibos::app()->db->createCommand()
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
        $this->setPageTitle( Ibos::lang( 'Detail notify' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => Ibos::lang( 'Message center' ), 'url' => $this->createUrl( 'mention/index' ) ),
            array( 'name' => Ibos::lang( 'Notify' ), 'url' => $this->createUrl( 'notify/index' ) ),
            array( 'name' => Ibos::lang( 'Detail notify' ) )
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
        if ( Ibos::app()->request->isAjaxRequest ) {
            $uid = Ibos::app()->user->uid;
            $res = NotifyMessage::model()->setRead( $uid );
            $this->ajaxReturn( array( 'IsSuccess' => !!$res ) );
        }
    }

    /**
     * 设置列表提醒为已读
     * @return void
     */
    public function actionSetIsRead() {
        $module = StringUtil::filterCleanHtml( Env::getRequest( 'module' ) );
        $res = NotifyMessage::model()->setReadByModule( Ibos::app()->user->uid, $module );
        $this->ajaxReturn( array( 'IsSuccess' => !!$res ) );
    }

    /**
     * 收到的赞
     * @return void 
     */
    public function actionDigg() {
        $this->setPageTitle( Ibos::lang( 'My digg' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => Ibos::lang( 'Message center' ), 'url' => $this->createUrl( 'mention/index' ) ),
            array( 'name' => Ibos::lang( 'Notify' ), 'url' => $this->createUrl( 'notify/index' ) ),
            array( 'name' => Ibos::lang( 'My digg' ) )
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
