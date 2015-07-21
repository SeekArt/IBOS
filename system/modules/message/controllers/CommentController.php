<?php

namespace application\modules\message\controllers;

use application\core\utils\IBOS;
use application\core\utils\Env;
use application\core\utils\Page;
use application\modules\message\model\Comment;
use application\modules\message\model\UserData;

class CommentController extends BaseController {

    /**
     * 首页
     */
    public function actionIndex() {
        $uid = IBOS::app()->user->uid;
        $type = Env::getRequest( 'type' );
        $map = array( 'and' );
        if ( !in_array( $type, array( 'receive', 'sent' ) ) ) {
            $type = 'receive';
        }
        if ( $type == 'receive' ) {
            $con = "touid = '{$uid}' AND uid != '{$uid}' AND `isdel` = 0";
        } else {
            $con = "`uid` = {$uid} AND `isdel` = 0";
        }
        $map[] = $con;
        $count = Comment::model()->count( $con . ' AND `isdel` = 0' );
        $pages = Page::create( $count );
        $list = Comment::model()->getCommentList( $map, 'cid DESC', $pages->getLimit(), $pages->getOffset(), true );
        $data = array(
            'list' => $list,
            'type' => $type,
            'pages' => $pages
        );
        // 重置未读评论数
        UserData::model()->resetUserCount( $uid, 'unread_comment', 0 );
        $this->setPageTitle( IBOS::lang( 'Comment' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Message center' ), 'url' => $this->createUrl( 'mention/index' ) ),
            array( 'name' => IBOS::lang( 'Comment' ), 'url' => $this->createUrl( 'comment/index' ) )
        ) );
        $this->render( 'index', $data );
    }

    /**
     * 
     * @return type
     */
    public function actionDel() {
        $widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\message\core\Comment' );
        return $widget->delComment();
    }

}
