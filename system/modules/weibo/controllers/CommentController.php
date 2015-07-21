<?php

namespace application\modules\weibo\controllers;

use application\core\utils\Env;
use application\core\utils\String;
use application\core\utils\IBOS;

class CommentController extends BaseController {

    /**
     * 获取评论列表
     */
    public function actionGetCommentList() {
        if ( Env::submitCheck( 'formhash' ) ) {
            $module = String::filterCleanHtml( $_POST['module'] );
            $table = String::filterCleanHtml( $_POST['table'] );
            $rowid = intval( $_POST['rowid'] );
            $moduleuid = intval( $_POST['moduleuid'] );
            $properties = array(
                'module' => $module,
                'table' => $table,
                'attributes' => array(
                    'rowid' => $rowid,
                    'limit' => 10,
                    'moduleuid' => $moduleuid
                )
            );
            $widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\weibo\core\WeiboComment', $properties );
            $list = $widget->fetchCommentList();
            $this->ajaxReturn( array( 'isSuccess' => true, 'data' => $list ) );
        }
    }

    /**
     * 增加一条评论
     * @return type
     */
    public function actionAddComment() {
        if ( Env::submitCheck( 'formhash' ) ) {
            $widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\weibo\core\WeiboComment' );
            return $widget->addComment();
        }
    }

    /**
     * 删除一条评论
     */
    public function actionDelComment() {
        $widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\weibo\core\WeiboComment' );
        return $widget->delComment();
    }

}
