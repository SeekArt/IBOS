<?php
namespace application\modules\article\actions\verify;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\article\model\ArticlePicture;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\article\utils\VerifyUtil;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;

class Test extends Base
{

    public function run()
    {
        $articleid = Env::getRequest('articleid');
        $article = Article::model()->fetchByPk($articleid);
        $category = ArticleCategory::model()->fetchByPk($article['catid']);
        $uid = Ibos::app()->user->uid;
        $result = VerifyUtil::passVerify($category['aid'], $articleid, $uid);
        $sender = User::model()->fetchRealnameByUid($uid);
        if ($result != true) {//通过了后面还有步骤
            $config = array(
                '{sender}' => $sender,
                '{subject]' => $article['subject'],
                '{category}' => Ibos::app()->controller->renderPartial('remindcontent', array(
                    'article' => $article,
                    'author' => $sender,
                ), true),
                '{url}' => Ibos::app()->controller->createUrl('index/index'),
            );
            Notify::model()->sendNotify($result, 'article_verify_message', $config, $uid);
            //审核人为下一个审核该新闻的用户（当前审核已通过）
            $approver = $result;
            $approver = implode(',', $approver);
            Article::model()->updateAllStatusAndApproverByPks($articleid, $approver, 2);
        } else {//最后一步通过且已经结束了
            Article::model()->updateAllStatusAndApproverByPks($articleid, $uid, 1);
            //动态推送
            if (!empty($article)) {
                $wbconf = WbCommonUtil::getSetting(true);
                if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                    $publishscope = array(
                        'deptid' => $article['deptid'],
                        'positionid' => $article['positionid'],
                        'uid' => $article['uid'],
                        'roleid' => $article['roleid']
                    );
                    $data = array(
                        'title' => Ibos::lang('Feed title', '', array(
                            '{subject}' => $article['subject'],
                            '{url}' => Ibos::app()->urlManager->createUrl('article/index/show',
                                array('articleid' => $article['articleid']))
                        )),
                        'body' => $article['content'],
                        'actdesc' => Ibos::lang('Post news'),
                        'userid' => $publishscope['uid'],
                        'deptid' => $publishscope['deptid'],
                        'positionid' => $publishscope['positionid'],
                    );
                    if ($article['type'] == self::ARTICLE_TYPE_PICTURE) {
                        $type = 'postimage';
                        $picture = ArticlePicture::model()->fetchPictureByArticleId(array("articleid" => $article['articleid']));
                        $data['attach_id'] = $picture['aid'];
                    } else {
                        $type = 'post';
                    }
                    WbfeedUtil::pushFeed($article['author'], 'article', 'article', $article['articleid'], $data, $type);
                }
                $category = ArticleCategory::model()->fetchByPk($article['catid']);
                $author = User::model()->fetchByPk($article['author']);
                $config = array(
                    '{sender}' => $author['realname'],
                    '{subject}' => $article['subject'],
                    '{content}' => Ibos::app()->controller->renderPartial('remindcontent', array(
                        'article' => $article,
                        'author' => $author['realname'],
                    ), true),
                    '{orgContent}' => StringUtil::filterCleanHtml($article['content']),
                    '{category}' => $category['name'],
                    '{url}' => Ibos::app()->urlManager->createUrl('article/index/show',
                        array('articleid' => $article['articleid'])),
                    'id' => $articleid,
                );
                $publishscope = array(
                    'deptid' => $article['deptid'],
                    'positionid' => $article['positionid'],
                    'roleid' => $article['roleid'],
                    'uid' => $article['uid']
                );
                $uidArr = ArticleUtil::getScopeUidArr($publishscope);
                Notify::model()->sendNotify($uidArr, 'article_message', $config);
                //更新积分
                UserUtil::updateCreditByAction('addarticle', $article['author']);
            }
        }
    }
}