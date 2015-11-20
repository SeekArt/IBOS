<?php

/**
 *  author:banyan
 */

namespace application\modules\report\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\String;

class CommentController extends Controller {

    /**
     * 获取评论列表
     */
    public function actionGetCommentList() {
        if ( Env::submitCheck( 'formhash' ) ) {
            $module = String::filterCleanHtml( $_POST['module'] );
            $table = String::filterCleanHtml( $_POST['table'] );
            $limit = Env::getRequest( 'limit' ); //每页条数
            $offset = Env::getRequest( 'offset' ); //偏移
            $rowid = intval( $_POST['rowid'] );
            $type = Env::getRequest( 'type' );
            $properties = array(
                'module' => $module,
                'table' => $table,
                'attributes' => array(
                    'rowid' => $rowid,
                    'limit' => $limit ? intval( $limit ) : 10,
                    'offset' => $offset ? intval( $offset ) : 0,
                    'type' => $type
                )
            );
            $widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\report\widgets\ReportComment', $properties );
            $list = $widget->fetchCommentList();
            $this->ajaxReturn( array( 'isSuccess' => true, 'data' => $list ) );
        }
    }

    /**
     * 增加一条评论或回复
     * @return string
     */
    public function actionAddComment() {
        if ( Env::submitCheck( 'formhash' ) ) {
            $widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\report\widgets\ReportComment' );
            return $widget->addComment();
        }
    }

    /**
     * 增加一条评论或回复
     * @return void
     */
    public function actionDelComment() {
        $widget = IBOS::app()->getWidgetFactory()->createWidget( $this, 'application\modules\report\widgets\ReportComment' );
        return $widget->delComment();
    }

}
