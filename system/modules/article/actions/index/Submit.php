<?php
namespace application\modules\article\actions\index;

use application\core\model\Log;
use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\core\Article as ICArticle;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\article\model\ArticlePicture;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;

/*
 * 添加和编辑提交新闻的数据接口，这个接口就不要再实现ApiInterface接口了，直接单独对POST过来的数据进行处理
 */

class Submit extends Base
{

    public function run()
    {
        $articleid = Env::getRequest('articleid');
        $uid = Ibos::app()->user->uid;
        if (!isset($articleid) || empty($articleid)) {//添加操作做
            if (ICArticle::formCheck($_POST) == false) {
                Ibos::app()->controller->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('Title do not empty')
                ));
            }
            $this->beforeSaveData($_POST);
            $articleId = $this->addOrUpdateArticle('add', $_POST, $uid);
            if ($_POST['type'] == parent::ARTICLE_TYPE_PICTURE) {
                //图片文章添加
                $pidids = $_POST['picids'];
                if (!empty($pidids)) {
                    Attach::updateAttach($pidids);
                    $attach = Attach::getAttachData($pidids);
                    $this->addPicture($attach, $articleId);
                }
            }
            $attachmentid = trim($_POST['attachmentid'], ',');
            if (!empty($attachmentid)) {
                Attach::updateAttach($attachmentid);
                Article::model()->modify($articleId, array('attachmentid' => $attachmentid));
            }
            $dashboardConfig = $this->getDashboardConfig();
            //根据后台判断新闻可否投票
            if (isset($_POST['votestatus']) && $this->getVoteInstalled() && $dashboardConfig['articlevoteenable'] && $_POST['votestatus'] == 1) {
                //添加投票内容
                $this->addOrUpdateVote($articleId, $uid, 'add');
                Article::model()->modify($articleId, array('votestatus' => 1));
            } else {
                Article::model()->modify($articleId, array('votestatus' => 0));
            }
            //根据后台判断新闻可否评论
            if (isset($_POST['commentstatus']) && $dashboardConfig['articlecommentenable']) {
                Article::model()->modify($articleId, array('commentstatus' => 1));
            } else {
                Article::model()->modify($articleId, array('commentstatus' => 0));
            }
            //消息提醒
            $user = User::model()->fetchByUid($uid);
            $article = Article::model()->fetchByPk($articleId);
            $categoryName = ArticleCategory::model()->fetchCateNameByCatid($article['catid']);
            if ($article['status'] == 1) {//直接发布
                ApprovalRecord::model()->recordStep($article['articleid'], $uid, 3);
                $publishscope = array(
                    'deptid' => $article['deptid'],
                    'positionid' => $article['positionid'],
                    'roleid' => $article['roleid'],
                    'uid' => $article['uid']
                );
                $uidArr = ArticleUtil::getScopeUidArr($publishscope);
                $config = array(
                    '{sender}' => $user['realname'],
                    '{category}' => $categoryName,
                    '{subject}' => $article['subject'],
                    '{content}' => Ibos::app()->controller->renderPartial('remindcontent', array(
                        'article' => $article,
                        'author' => $user['realname'],
                    ), true),
                    '{orgContent}' => StringUtil::filterCleanHtml($article['content']),
                    '{url}' => Ibos::app()->urlManager->createUrl('article/default/show', array('articleid' => $articleId)),
                    'id' => $articleId,
                );
                if (count($uidArr) > 0) {
                    Notify::model()->sendNotify($uidArr, 'article_message', $config, $uid);
                }
                //动态推送
                $wbconf = WbCommonUtil::getSetting(true);
                if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                    $publishscope = array(
                        'deptid' => $article['deptid'],
                        'positionid' => $article['positionid'],
                        'roleid' => $article['roleid'],
                        'uid' => $article['uid']
                    );
                    $data = array(
                        'title' => Ibos::lang('Feed title', '', array(
                            '{subject}' => $article['subject'],
                            '{url}' => Ibos::app()->urlManager->createUrl('article/default/show',
                                array('articleid' => $articleId))
                        )),
                        'body' => $article['subject'],
                        'actdesc' => Ibos::lang('Post news'),
                        'userid' => $publishscope['uid'],
                        'deptid' => $publishscope['deptid'],
                        'positionid' => $publishscope['positionid'],
                        'roleid' => $publishscope['roleid'],
                    );
                    if ($_POST['type'] == self::ARTICLE_TYPE_PICTURE) {
                        $type = 'postimage';
                        $picids = explode(',', $pidids);
                        $data['attach_id'] = array_shift($picids);
                    } else {
                        $type = 'post';
                    }
                    WbfeedUtil::pushFeed(Ibos::app()->user->uid, 'article', 'article', $articleId, $data, $type);
                }
                UserUtil::updateCreditByAction('addarticle', $uid);
            } elseif ($article['status'] == 2) {//需要审核
                $this->SendPending($article, $uid);
            }
            //日志记录
            $log = array(
                'user' => Ibos::app()->user->username,
                'ip' => Ibos::app()->setting->get('clientip'),
                'isSuccess' => 1,
            );
            Log::write($log, 'action', 'module.article.publish.add');
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Save succeed', 'message'),
                'data' => array(
                    'articleid' => $articleId,
                    'status' => $article['status'],
                ),
            ));
        } else {//编辑操作
            $this->edit($articleid, $uid);
        }

    }

    private function edit($articleid, $uid)
    {
        $this->beforeSaveData($_POST);
        $this->addOrUpdateArticle('update', $_POST, $uid);
        //图片文章修改，删除原来的，增加新的
        if ($_POST['type'] == parent::ARTICLE_TYPE_PICTURE) {
            $pidids = $_POST['picids'];
            if (!empty($pidids)) {
                ArticlePicture::model()->deleteAll("articleid = :articleid", array(':articleid' => $articleid));
                Attach::updateAttach($pidids);
                $attach = Attach::getAttachData($pidids);
                $this->addPicture($attach, $articleid);
            }
        }
        //更新附件
        $attachmentid = trim($_POST['attachmentid'], ',');
        if (!empty($attachmentid)) {
            Attach::updateAttach($attachmentid);
            Article::model()->modify($articleid, array('attachmentid' => $attachmentid));
        }
        $user = User::model()->fetchByUid($uid);
        $article = Article::model()->fetchByPk($articleid);
        $categoryName = ArticleCategory::model()->fetchCateNameByCatid($article['catid']);
        //更新投票
        if ($_POST['votestatus'] == 1) {
            $this->addOrUpdateVote($articleid, $uid, $op = "update");
        }
        if (!empty($_POST['msgRemind']) || $article['status'] == 1) {//新闻为公开状态，直接发布就行
            $publishscope = array(
                'deptid' => $article['deptid'],
                'positionid' => $article['positionid'],
                'uid' => $article['uid'],
            );
            $uidArr = ArticleUtil::getScopeUidArr($publishscope);
            $config = array(
                '{sender}' => $user['realname'],
                '{category}' => $categoryName,
                '{subject}' => $article['subject'],
                '{content}' => Ibos::app()->controller->renderPartial('remindcontent', array(
                    'article' => $article,
                    'author' => $user['realname'],
                ), true),
                '{url}' => Ibos::app()->urlManager->createUrl('article/default/show',
                    array('articleid' => $article['articleid'])),
                'id' => $article['articleid'],
            );
            if (count($uidArr) > 0) {
                Notify::model()->sendNotify($uidArr, 'article_message', $config, $uid);
            }
        }
        if ($article['status'] == 2) {//新闻需要审核
            $this->SendPending($article, $uid);
        }
        //日志记录
        $log = array(
            'user' => Ibos::app()->user->username,
            'ip' => Ibos::app()->setting->get('clientip'),
            'isSuccess' => 1,
        );
        Log::write($log, 'action', 'module.article.default.add');
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Save success'),
            'url' => $this->getController()->createUrl('publish/draft'),
        ));
    }
}