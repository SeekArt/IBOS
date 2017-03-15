<?php

namespace application\modules\message\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\modules\message\model\Comment;
use application\modules\message\model\UserData;

class CommentController extends BaseController
{

    /**
     * 首页
     */
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $type = Env::getRequest('type');
        $map = array('and');
        if (!in_array($type, array('receive', 'sent'))) {
            $type = 'receive';
        }
        if ($type == 'receive') {
            $con = "touid = '{$uid}' AND uid != '{$uid}' AND `isdel` = 0";
        } else {
            $con = "`uid` = {$uid} AND `isdel` = 0";
        }
        $map[] = $con;
        $count = Comment::model()->count($con . ' AND `isdel` = 0');
        $pages = Page::create($count);
        $list = Comment::model()->getCommentList($map, 'cid DESC', $pages->getLimit(), $pages->getOffset(), true);
        // 将评论和回复内容中多余的字符串清空，如：『回复 @username ： 』
        foreach ($list as &$loopRow) {
            $loopRow['content'] = trim(preg_replace('/^回复\s+?@.+?\s+?：/', '', $loopRow['content']));
            $loopRow['replyInfo'] = trim(preg_replace('/回复\s+?@.+?\s+?：\s/', '', $loopRow['replyInfo']));
        }

        $data = array(
            'list' => $list,
            'type' => $type,
            'pages' => $pages
        );
        // 重置未读评论数
        UserData::model()->resetUserCount($uid, 'unread_comment', 0);
        $this->setPageTitle(Ibos::lang('Comment'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Message center'), 'url' => $this->createUrl('mention/index')),
            array('name' => Ibos::lang('Comment'), 'url' => $this->createUrl('comment/index'))
        ));


        $this->render('index', $data);
    }

    /**
     *
     * @return type
     */
    public function actionDel()
    {
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\message\core\Comment');
        return $widget->delComment();
    }

}
