<?php

namespace application\modules\report\widgets;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Stamp;
use application\modules\message\core\Comment as ICComment;
use application\modules\message\model\Comment;
use application\modules\report\model\Report;
use application\modules\report\model\ReportStats;

class ReportComment extends ICComment
{

    const COMMENT_LIST_VIEW = 'application.modules.report.views.comment.loadComment';

    public function init()
    {
        $var['loadmore'] = Env::getRequest('loadmore');
        $var['inAjax'] = intval(Env::getRequest('inajax'));
        $var['module'] = $this->getModule();
        $var['assetUrl'] = Ibos::app()->assetManager->getAssetsUrl('message');
        $var['getUrl'] = Ibos::app()->urlManager->createUrl('report/comment/getcommentlist');
        $var['addUrl'] = Ibos::app()->urlManager->createUrl('report/comment/addcomment');
        $var['delUrl'] = Ibos::app()->urlManager->createUrl('report/comment/delcomment');
        $this->setParseView('comment', self::COMMENT_LIST_VIEW);
        $this->setAttributes($var);
    }

    /**
     * 运行widget
     * @return type
     */
    public function run()
    {
        $attr = $this->getAttributes();
        // 查询条件
        $map = array(
            'module' => $this->getModule(),
            'table' => $this->getTable(),
            'rowid' => $attr['rowid'],
            'isdel' => 0
        );
        // 总条数
        $attr['count'] = Comment::model()->countByAttributes($map);
        $list = $this->getCommentList();
        $isAdministrator = Ibos::app()->user->isadministrator;
        $uid = Ibos::app()->user->uid;
        foreach ($list as &$cm) {
            // 删除权限
            $cm['isCommentDel'] = $isAdministrator || $uid === $cm['uid'];
            // 回复数
            $cm['replys'] = intval(Comment::model()->countByAttributes(
                array(
                    'module' => 'message',
                    'table' => 'comment',
                    'rowid' => $cm['cid'],
                    'isdel' => 0
                )
            ));
        }
        $attr['comments'] = $list;
        $attr['lang'] = Ibos::getLangSources(array('message.default'));
        $attr['url'] = isset($attr['url']) ? $attr['url'] : '';
        $attr['detail'] = isset($attr['detail']) ? $attr['detail'] : '';
        $content = $this->render($this->getParseView('comment'), $attr, true);
        $ajax = $attr['inAjax'];
        $count = $attr['count'];
        unset($attr);
        // 输出数据
        $return = array(
            'isSuccess' => true,
            'data' => $content,
            'count' => $count
        );
        if ($ajax == 1) {
            $this->getOwner()->ajaxReturn($return);
        } else {
            echo $return['data'];
        }
    }

    /**
     * 获取评论/回复列表
     * @return type
     */
    public function fetchCommentList()
    {
        $type = $this->getAttributes('type');
        $this->setAttributes(array('inAjax' => 1, 'loadmore' => Env::getRequest('loadmore')));
        if ($type == 'reply') {
            $this->setParseView('comment', self::REPLY_LIST_VIEW);
        } else {
            $this->setParseView('comment', self::COMMENT_LIST_VIEW);
        }
        return $this->run();
    }

    /**
     * 增加前处理，根据类型设置不同的解析视图
     * @param array $data
     * @param array $sourceInfo
     */
    protected function afterAdd($data, $sourceInfo)
    {
        if (isset($data['type'])) {
            if ($data['type'] == 'reply') {
                $this->setParseView('comment', self::REPLY_PARSE_VIEW, 'parse');
            } else {
                $this->setParseView('comment', self::COMMENT_PARSE_VIEW, 'parse');
            }
        }
        // 图章
        if (isset($data['stamp'])) {
            $repid = $sourceInfo['repid'];
            $allStamp = Stamp::model()->fetchAll(array('select' => 'id'));
            $stampArr = Convert::getSubByKey($allStamp, 'id');
            $stamp = in_array($data['stamp'], $stampArr) ? intval($data['stamp']) : 0;
            if ($stamp == 0) {
                Report::model()->modify($repid, array('isreview' => 1));
            } else {
                Report::model()->modify($repid, array('isreview' => 1, 'stamp' => $stamp));
                $uid = Report::model()->fetchUidByRepId($repid);
                ReportStats::model()->scoreReport($repid, $uid, $stamp);
            }
        }
    }

}
