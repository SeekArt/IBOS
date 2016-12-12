<?php

namespace application\modules\weibo\core;

use application\core\model\Source;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\message\core\Comment as IWComment;
use application\modules\message\model\Comment;
use application\modules\message\model\Feed;
use application\modules\user\utils\User as UserUtil;
use CJSON;

class WeiboComment extends IWComment
{

    public function init()
    {
        $var = array();
        // 默认配置数据
        $var['cancomment'] = 1; // 是否可以评论
        $var['canrepost'] = 1; // 是否允许转发
        $var['cancomment_old'] = 1; // 是否可以评论给原作者
        $var['showlist'] = 0; // 默认不显示原评论列表
        $var['tpl'] = 'application.modules.weibo.views.comment.loadcomment'; // 显示模板
        $var['module'] = 'weibo';
        $var['table'] = 'feed';
        $var['limit'] = 10;
        $var['order'] = 'cid DESC';
        $var['inAjax'] = 0;
        $attr = $this->getAttributes();
        if (empty($attr) && Env::submitCheck('formhash')) {
            $attr['moduleuid'] = intval($_POST['moduleuid']);
            $attr['rowid'] = intval($_POST['rowid']);
            $attr['module_rowid'] = intval($_POST['module_rowid']);
            $attr['module_table'] = StringUtil::filterCleanHtml($_POST['module_table']);
            $attr['inAjax'] = intval($_POST['inAjax']);
            $attr['showlist'] = intval($_POST['showlist']);
            $attr['cancomment'] = intval($_POST['cancomment']);
            $attr['cancomment_old'] = intval($_POST['cancomment_old']);
            $attr['module'] = StringUtil::filterCleanHtml($_POST['module']);
            $attr['table'] = StringUtil::filterCleanHtml($_POST['table']);
            $attr['canrepost'] = intval($_POST['canrepost']);
        }
        is_array($attr) && $var = array_merge($var, $attr);
        $var['moduleuid'] = intval($var['moduleuid']);
        $var['rowid'] = intval($var['rowid']);
        if ($var['table'] == 'feed' && Ibos::app()->user->uid != $var['moduleuid']) {
            // 获取资源类型
            $sourceInfo = Feed::model()->get($var['rowid']);
            $var['feedtype'] = $sourceInfo['type'];
            // 获取源资源作者用户信息
            $moduleRowData = Feed::model()->get(intval($var['module_rowid']));
            $var['user_info'] = $moduleRowData['user_info'];
        }
        $this->setAttributes($var);
    }

    /**
     *
     * @return type
     */
    public function run()
    {
        $attr = $this->getAttributes();
        // 渲染模版
        if ($attr['showlist'] == 1) {
            $attr['list'] = $this->fetchCommentList();
        }
        $attr['url'] = isset($attr['url']) ? $attr['url'] : '';
        $attr['detail'] = isset($attr['detail']) ? $attr['detail'] : '';
        $content = $this->render($attr['tpl'], $attr);
        $ajax = $attr['inAjax'];
        unset($attr);
        // 输出数据
        $return = array(
            'isSuccess' => true,
            'data' => $content
        );
        return $ajax == 1 ? CJSON::encode($return) : $return ['data'];
    }

    /**
     *
     * @return type
     */
    public function fetchCommentList()
    {
        $count = $this->getCommentCount();
        $limit = $this->getAttributes('limit');
        $pages = Page::create($count, $limit);
        $this->setAttributes(array('offset' => $pages->getOffset(), 'limit' => $pages->getLimit()));
        $var = array(
            'list' => $this->getCommentList(),
            'lang' => Ibos::getLangSources(array('message.default')),
            'count' => $count,
            'limit' => $limit,
            'rowid' => $this->getAttributes('rowid'),
            'moduleuid' => $this->getAttributes('moduleuid'),
            'showlist' => $this->getAttributes('showlist'),
            'pages' => $pages
        );
        $content = $this->render('application.modules.weibo.views.comment.loadreply', $var, true);
        return $content;
    }

    /**
     *
     * @return type
     */
    public function addComment()
    {
        $this->setParseView('comment', self::REPLY_PARSE_VIEW, 'parse');
        return parent::addComment();
    }

    /**
     *
     * @param type $data
     */
    protected function afterAdd($data, $sourceInfo)
    {
        // 去掉回复用户@
        $lessUids = array();
        if (!empty($data ['touid'])) {
            $lessUids [] = $data ['touid'];
        }
        if (isset($data['sharefeed']) && intval($data['sharefeed']) == 1) {  // 转发到我的微博
            $this->updateToWeibo($data, $sourceInfo, $lessUids);
        }
        if (isset($data['comment']) && intval($data['comment']) == 1) {  // 是否评论给原作者
            $this->updateToComment($data, $sourceInfo, $lessUids);
        }
    }

    /**
     * 转发到我的微博
     * @param string $data
     * @param type $sourceInfo
     * @param type $lessUids
     */
    private function updateToWeibo($data, $sourceInfo, $lessUids)
    {
        $commentInfo = Source::getSourceInfo($data['table'], $data['rowid'], false, $data['module']);
        $oldInfo = isset($commentInfo['sourceInfo']) ? $commentInfo['sourceInfo'] : $commentInfo;

        // 根据评论的对象获取原来的内容
        $arr = array(
            'post',
            'postimage',
        );
        $scream = '';
        if (!in_array($sourceInfo['type'], $arr)) {
            $scream = '//@' . $commentInfo['source_user_info']['realname'] . '：' . $commentInfo['source_content'];
        }
        if (!empty($data ['tocid'])) {
            $replyInfo = Comment::model()->getCommentInfo($data ['tocid'], false);
            $replyScream = '//@' . $replyInfo['user_info'] ['realname'] . ' ：';
            $data['content'] .= $replyScream . $replyInfo ['content'];
        }
        $s['body'] = $data['content'] . $scream;
        $s['curid'] = null;
        $s['sid'] = $oldInfo['source_id'];
        $s['module'] = $oldInfo['module'];
        $s['type'] = $oldInfo['source_table'];
        $s['comment'] = 1; //$data['comment_old'];
        $s['comment_touid'] = $data['moduleuid'];

        // 如果为原创微博，不给原创用户发送@信息
        if ($sourceInfo['type'] == 'post' && empty($data['touid'])) {
            $lessUids[] = Ibos::app()->user->uid;
        }
        Feed::model()->shareFeed($s, 'comment', $lessUids);
        UserUtil::updateCreditByAction('forwardedweibo', Ibos::app()->user->uid);
    }

    /**
     * 评论给原作者
     * @param type $data
     * @param type $sourceInfo
     * @param type $lessUids
     */
    private function updateToComment($data, $sourceInfo, $lessUids)
    {
        $commentInfo = Source::getSourceInfo($data['module_table'], $data['module_rowid'], false, $data['module']);
        $oldInfo = isset($commentInfo['sourceInfo']) ? $commentInfo['sourceInfo'] : $commentInfo;
        // 发表评论
        $c['module'] = $data['module'];
        $c['table'] = 'feed';
        $c['moduleuid'] = !empty($oldInfo['source_user_info']['uid']) ? $oldInfo['source_user_info']['uid'] : $oldInfo['uid'];
        $c['content'] = $data ['content'];
        $c['rowid'] = !empty($oldInfo['sourceInfo']) ? $oldInfo['sourceInfo']['source_id'] : $oldInfo['source_id'];
        if ($data ['module']) {
            $c ['rowid'] = $oldInfo['feedid'];
        }
        $c['from'] = Env::getVisitorClient();
        Comment::model()->addComment($c, false, false, $lessUids);
    }

}
