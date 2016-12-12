<?php
namespace application\modules\article\actions\index;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Image;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\article\core\ArticleCategory;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory as ArticleCategoryModel;
use application\modules\article\model\ArticlePicture;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\dashboard\model\Approval;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\dashboard\model\ApprovalStep;
use application\modules\dashboard\utils\Dashboard;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\vote\components\Vote as VoteComponent;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;

class Base extends \CAction
{

    //全部，包括已读和未读
    const TYPE_ALL = "all";

    //已读
    const TYPE_READ = "read";

    //未读
    const TYPE_UNREAD = "unread";

    //草稿箱
    const TYPE_DRAFT = "draft";

    //已发布
    const TYPE_PUBLISH = "publish";

    //审核中
    const TYPE_APPROVAL = "approval";

    //被退回
    const TYPE_REBACK_TO = "reback_to";

    //待我审核
    const TYPE_WAIT = "wait";

    //我已通过
    const TYPE_PASSED = "passed";

    //被我退回
    const TYPE_REBACK_FROM = "reback_from";

    //默认信息类型：文章
    const ARTICLE_TYPE_DEFAULT = 0;

    //信息类型：图片
    const ARTICLE_TYPE_PICTURE = 1;

    //信息类型：超链接
    const ARTICLE_TYPE_LINK = 2;

    protected $catid = 0;

    protected $type = "all";

    /*
     * 获得下拉框选择选项列，生成分类树所需数据
     * @return array
     */
    public function getCategoryOption()
    {
        $category = new ArticleCategory('application\modules\article\model\ArticleCategory');
        $categoryData = $category->getAjaxCategory($category->getData(array('order' => 'sort ASC')));
        return StringUtil::getTree($categoryData, "<option value='\$catid' \$selected>\$spacer\$name</option>");
    }

    /*
     * 取得后台配置数据
     */
    public function getDashboardConfig()
    {
        $result = array();
        $fields = array(
            'articleapprover',
            'articlecommentenable',
            'articlevoteenable',
            'articlemessageenable'
        );
        foreach ($fields as $field) {
            $result[$field] = Ibos::app()->setting->get('setting/' . $field);
        }
        return $result;
    }

    /*
     * 是否安装邮件模块
     */
    protected function getEmailInstalled()
    {
        $isInstallEmail = Module::getIsEnabled('email');
        return $isInstallEmail;
    }

    /**
     * 是否有安装投票模块
     * @return boolean
     */
    protected function getVoteInstalled()
    {
        return Module::getIsEnabled('vote');
    }

    /*
     * 判断某个uid是否某篇未审核新闻的当前审核人
     * @param array $article 新闻数据
     * @param integer $uid 用户id
     * @return boolean
     */
    protected function checkIsApprovaler($article, $uid)
    {
        $res = false;
        $artApproval = ApprovalRecord::model()->fetchLastStep($article['articleid']);
        $category = ArticleCategoryModel::model()->fetchByPk($article['catid']);
        if (!empty($category['aid'])) {
            $approval = Approval::model()->fetchByPk($category['aid']);
            if (!empty($artApproval)){
                $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $artApproval['step']);
                if (in_array($uid, $nextApproval['uids'])) {
                    $res = true;
                }
            }
        }
        return $res;
    }

    /**
     *  添加、修改新闻前的判断动作
     * @param array $postData 提交的数据
     */
    protected function beforeSaveData(&$postData)
    {
        if (isset($postData['type'])) {
            if ($postData['type'] == self::ARTICLE_TYPE_PICTURE) {
                if (empty($postData['picids'])) {
                    Ibos::app()->controller->error(Ibos::lang('Picture empty tip'),
                        Ibos::app()->controller->createUrl('default/add'));
                } else {

                }
            } elseif ($postData['type'] == self::ARTICLE_TYPE_DEFAULT) {
                if (empty($postData['content'])) {
                    Ibos::app()->controller->error(Ibos::lang('Content empty tip'),
                        Ibos::app()->controller->createUrl('default/add'));
                }
            } elseif ($postData['type'] == self::ARTICLE_TYPE_LINK) {
                if (empty($postData['url'])) {
                    Ibos::app()->controller->error(Ibos::lang('Url empty tip'),
                        Ibos::app()->controller->createUrl('default/add'));
                }
            }
        }
        StringUtil::ihtmlSpecialCharsUseReference($postData['subject']);
    }

    /*
     * 添加或者修改新闻信息
      * @param string $type 类型 add 或 update
	 * @param array $data $_POST数据
	 * @param integer $uid
	 * @return type
     */
    protected function addOrUpdateArticle($type, $data, $uid)
    {
        $attributes = Article::model()->create();
        $attributes['approver'] = $uid;
        $attributes['author'] = $uid;
        //取得发布权限
        $publishscope = StringUtil::handleSelectBoxData($data['publishscope']);
        $attributes['deptid'] = $publishscope['deptid'];
        $attributes['deptid'] = $publishscope['deptid'];
        $attributes['positionid'] = $publishscope['positionid'];
        $attributes['roleid'] = $publishscope['roleid'];
        $attributes['uid'] = $publishscope['uid'];
        if (strtotime($data['topendtime']) < TIMESTAMP) {
            $attributes['istop'] = 0;
        }
        if (strtotime($data['highlightendtime']) < TIMESTAMP) {
            $attributes['ishighlight'] = 0;
        }
        $attributes['votestatus'] = isset($data['votestatus']) ? $data['votestatus'] : 0;
        $attributes['commentstatus'] = isset($data['commentstatus']) ? $data['commentstatus'] : 0;

        //这里是新闻的状态，1.公开，2.待审核，3.草稿，草稿不需要考虑，只需考虑另外两种的情况
        //需要考虑免审核人，如果当前发起用户为免审核人，就应该把状态置为1
        if ($attributes['status'] == 2) {
            $catid = intval($attributes['catid']);
            $category = ArticleCategoryModel::model()->fetchByPk($catid);
            if (empty($category['aid'])) {
                $attributes['status'] = 1;
            } else {
                $approval = Approval::model()->fetchByPk($category['aid']);
                $approvalUid = explode(',', $approval['free']);
                if (in_array($uid, $approvalUid)) {
                    $attributes['status'] = 1;
                } else {
                    $approver = ApprovalStep::model()->getApprovalerStr($category['aid'], 1);
                    $attributes['approver'] = $approver;
                    $attributes['status'] = 2;
                }
            }
        }
        if ($type == "add") {
            $attributes['addtime'] = TIMESTAMP;
            $row = Ibos::app()->db->createCommand()->insert('{{article}}', $attributes);
            if ($row) {
                $id = Ibos::app()->db->getLastInsertID();
                return $id;
            }
            return null;
        } else {
            $attributes['uptime'] = TIMESTAMP;
            return Article::model()->updateByPk($attributes['articleid'], $attributes);
        }
    }

    /*
     * 添加图片类型的信息
     * @param arrray $attach 附件详细数组
     * @paran integer $articleId 新闻ID
     */
    protected function addPicture($attach, $articleId)
    {
        $sort = 0;
        $attachUrl = File::getAttachUrl() . '/';
        foreach ($attach as $value) {
            $picture = array(
                'articleid' => $articleId,
                'aid' => $value['aid'],
                'sort' => $sort,
                'addtime' => TIMESTAMP,
                'postip' => StringUtil::getSubIp(),
                'filename' => $value['filename'],
                'title' => '',
                'type' => StringUtil::getFileExt($value['filename']),
                'size' => $value['filesize'],
                'filepath' => $attachUrl . $value['attachment'],
            );
            if (Ibos::app()->setting->get('setting/articlethumbenable')) {
                list($thumbWidth, $thumbHeight) = explode(',', Ibos::app()->setting->get('setting/articlethumbwh'));
                $imageInfo = Image::getImageInfo(File::imageName($picture['filepath']));
                if ($imageInfo['width'] < $thumbWidth && $imageInfo['height'] < $thumbHeight) {
                    $picture['thumb'] = 0;
                } else {
                    $sourceFileName = explode('/', $picture['filepath']);
                    $sourceFileName[count($sourceFileName) - 1] = 'thumb_' . $sourceFileName[count($sourceFileName) - 1];
                    $thumbName = implode('/', $sourceFileName);
                    $thumbName = Ibos::engine()->io()->file()->thumbnail($picture['filepath'], $thumbName, $thumbWidth,
                        $thumbHeight);
                    $picture['thumb'] = 1;
                }
            }
            ArticlePicture::model()->add($picture);
            $sort++;
        }
    }

    /**
     * 发送待审核新闻处理方法(新增与编辑都可处理)
     * @param array $article 新闻数据
     * @param integer $uid 发送人id
     */
    protected function SendPending($article, $uid)
    {
        $category = ArticleCategoryModel::model()->fetchByPk($article['catid']);
        $approval = Approval::model()->fetchNextApprovalUids($category['aid'], 0);
        if (!empty($approval)) {
            if ($approval['step'] == 'publish') {
                self::verifyComplete($article['articleid'], $uid);
            } else {
                ApprovalRecord::model()->recordStep($article['articleid'], $uid, 3, '');
                $sender = User::model()->fetchRealnameByUid($uid);
                //发送第一步审核人
                $config = array(
                    '{sender}' => $sender,
                    '{subject}' => $article['subject'],
                    '{category}' => $category['name'],
                    '{url}' => Ibos::app()->controller->createUrl('default/show', array('articleid' => $article['articleid'])),
                    '{content}' => Ibos::app()->controller->renderPartial('application.modules.article.views.verify.remindcontent', array(
                        'article' => $article,
                        'author' => $sender,
                    ), true),
                );
                Notify::model()->sendNotify($approval['uids'], 'article_verify_message', $config, $uid);
            }
        }
    }

    /*
     * 审核全部完成
     * @param integer $artId 新闻ID
     * @param integer $uid 用户ID
     */
    protected function verifyComplete($artId, $uid)
    {
        Article::model()->updateAllStatusAndApproverByPks($artId, $uid, 1);
        //审核完成后删除所有审核步骤，可以现在为了能够知道具体的审核步骤，因此审核步骤需要留着，这个方法不用了
        // ArticleApproval::model()->deleteAll("articleid={$artId}");
        ApprovalRecord::model()->recordStep($artId, $uid, 2);//记录审核步骤结束
        $article = Article::model()->fetchByPk($artId);
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
                        '{url}' => Ibos::app()->urlManager->createUrl('article/default/show',
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
            $category = ArticleCategoryModel::model()->fetchByPk($article['catid']);
            $author = User::model()->fetchByPk($article['author']);
            $config = array(
                '{sender}' => $author['realname'],
                '{subject}' => $article['subject'],
                '{content}' => Ibos::app()->controller->renderPartial('application.modules.article.views.verify.remindcontent', array(
                    'article' => $article,
                    'author' => $author['realname'],
                ), true),
                '{orgContent}' => StringUtil::filterCleanHtml($article['content']),
                '{category}' => $category['name'],
                '{url}' => Ibos::app()->urlManager->createUrl('article/default/show',
                    array('articleid' => $article['articleid'])),
                'id' => $artId,
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

    //添加或更新投票
    protected function addOrUpdateVote($articleId, $uid, $op = "add")
    {
        // 只允许两种操作：add 和 update
        $supportOp = array(
            "add",
            "update",
        );
        if (!in_array($op, $supportOp)) {
            throw new \Exception("addOrUpdateVote 方法不支持 add 和 update 以外的操作。");
        }
        $fields = array(
            'articleapprover', 'articlecommentenable', 'articlevoteenable', 'articlemessageenable'
        );
        $dashboardConfig = Dashboard::getDashboardConfig($fields);
        if (isset($_POST['votestatus']) && Module::getIsEnabled('vote') && $dashboardConfig['articlevoteenable']) {
            $voteData = Env::getRequest('vote');
            $voteData['publishscope'] = isset($_POST['publishscope']) ? $_POST['publishscope'] : '';
            $moduleName = isset($_POST['relatedmodule']) ? $_POST['relatedmodule'] : MODULE_NAME;
            if ($op == 'add') {
                //添加投票
                VoteComponent::add($voteData, $moduleName, $articleId);
            } elseif ($op == 'update') {
                // 更新投票
                VoteComponent::update($voteData, $moduleName, $articleId);
            }
        }
    }
}