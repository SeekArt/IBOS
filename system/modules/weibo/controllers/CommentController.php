<?php

namespace application\modules\weibo\controllers;

use application\core\utils\Env;
use application\core\utils\StringUtil;
use application\core\utils\Ibos;

class CommentController extends BaseController
{

    /**
     * 获取评论列表
     */
    public function actionGetCommentList()
    {
        if (Env::submitCheck('formhash')) {
            $module = StringUtil::filterCleanHtml($_POST['module']);
            $table = StringUtil::filterCleanHtml($_POST['table']);
            $rowid = intval($_POST['rowid']);
            $moduleuid = intval($_POST['moduleuid']);
            $properties = array(
                'module' => $module,
                'table' => $table,
                'attributes' => array(
                    'rowid' => $rowid,
                    'limit' => 10,
                    'moduleuid' => $moduleuid
                )
            );
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\weibo\core\WeiboComment', $properties);
            $list = $widget->fetchCommentList();
            $this->ajaxReturn(array('isSuccess' => true, 'data' => $list));
        }
    }

    /**
     * 增加一条评论
     * @return type
     */
    public function actionAddComment()
    {
        if (Env::submitCheck('formhash')) {
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\weibo\core\WeiboComment');
            return $widget->addComment();
        }
    }

    /**
     * 删除一条评论
     */
    public function actionDelComment()
    {
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\weibo\core\WeiboComment');
        return $widget->delComment();
    }

}
