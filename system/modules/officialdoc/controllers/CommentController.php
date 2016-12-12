<?php


namespace application\modules\officialdoc\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

class CommentController extends Controller
{

    /**
     * 获取评论列表
     */
    public function actionGetCommentList()
    {
        if (Env::submitCheck('formhash')) {
            $module = StringUtil::filterCleanHtml($_POST['module']);
            $table = StringUtil::filterCleanHtml($_POST['table']);
            $limit = Env::getRequest('limit'); //每页条数
            $offset = Env::getRequest('offset'); //偏移
            $rowid = intval($_POST['rowid']);
            $type = Env::getRequest('type');
            $url = Env::getRequest('url');
            $properties = array(
                'module' => $module,
                'table' => $table,
                'attributes' => array(
                    'rowid' => $rowid,
                    'limit' => $limit ? intval($limit) : 10,
                    'offset' => $offset ? intval($offset) : 0,
                    'type' => $type,
                    'url' => $url
                )
            );
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\officialdoc\core\OfficialdocComment', $properties);
            $list = $widget->fetchCommentList();
            $this->ajaxReturn(array('isSuccess' => true, 'data' => $list));
        }
    }

    /**
     * 增加一条评论或回复
     * @return string
     */
    public function actionAddComment()
    {
        if (Env::submitCheck('formhash')) {
            $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\officialdoc\core\OfficialdocComment');
            return $widget->addComment();
        }
    }

    /**
     * 增加一条评论或回复
     * @return void
     */
    public function actionDelComment()
    {
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\officialdoc\core\OfficialdocComment');
        return $widget->delComment();
    }

}