<?php

/**
 * 信息中心模块------文章默认控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 信息中心模块------文章默认控制器，继承BaseController
 * @package application.modules.article.components
 * @version $Id: DefaultController.php 6687 2016-03-26 09:03:55Z gzhyj $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\controllers;

use application\core\utils as util;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\article\core\Article as ICArticle;
use application\modules\article\model as model;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\dashboard\model\Approval;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\main\utils\Main as MainUtil;
use application\modules\message\model\Notify;
use application\modules\message\model\NotifyMessage;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\vote\components\VotePlugManager;
use application\modules\vote\model\Vote;
use application\modules\vote\model\VoteItem;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;
use application\core\model\Log;

class DefaultController extends BaseController {

    /**
     * 获取全部新闻
     * @return void
     */
    public function actionIndex() {
        $op = util\Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'show', 'search', 'getReader', 'getReaderByDeptId', 'getVoteCount', 'preview', 'clickVote');
        if (!in_array($option, $routes)) {
            $this->error(util\IBOS::lang('Can not find the path'), $this->createUrl('default/index'));
        }
        $uid = util\IBOS::app()->user->uid;
        if ($option == 'default') {
            $catid = intval(util\Env::getRequest('catid'));
            $childCatIds = '';
            if (!empty($catid)) {
                $this->catid = $catid;
                $childCatIds = model\ArticleCategory::model()->fetchCatidByPid($this->catid, true);
            }
            if (util\Env::getRequest('param') == 'search') {
                $this->search();
            }
            // 取消已过期的置顶样式标记
            model\Article::model()->cancelTop();
            // 取消已过期的高亮文章的样式标记
            model\Article::model()->updateIsOverHighLight();
            // type信息类型{全部、未读、已读……}
            $type = util\Env::getRequest('type');
            $condition = ArticleUtil::joinListCondition($type, $uid, $childCatIds, $this->condition);
            $datas = model\Article::model()->fetchAllAndPage($condition);
            $articleList = ICArticle::getListData($datas['datas'], $uid);
            $params = array(
                'pages' => $datas['pages'],
                'datas' => $articleList,
                'categoryOption' => $this->getCategoryOption(),
            );
            if ($type == 'notallow') { // 未审核
                $view = 'approval';
                $params['datas'] = ICArticle::handleApproval($params['datas']);
            } else {
                $view = 'list';
            }
            $this->setPageTitle(util\IBOS::lang('Article'));
            $this->setPageState('breadCrumbs', array(
                array('name' => util\IBOS::lang('Information center')),
                array('name' => util\IBOS::lang('Article'), 'url' => $this->createUrl('default/index')),
                array('name' => util\IBOS::lang('Article list'))
            ));
            // 未读数
            $params['newCount'] = model\Article::model()->getArticleCount(ArticleUtil::TYPE_NEW, $uid, $childCatIds, $this->condition);
            // 待审核数
            $params['notallowCount'] = model\Article::model()->getArticleCount(ArticleUtil::TYPE_NOTALLOW, $uid, $childCatIds, $this->condition);
            // 草稿数
            $params['draftCount'] = model\Article::model()->getArticleCount(ArticleUtil::TYPE_DRAFT, $uid, $childCatIds, $this->condition);
            NotifyMessage::model()->setReadByUrl( $uid, IBOS::app()->getRequest()->getUrl() );
            $this->render($view, $params);
        } else {
            $this->$option();
        }
    }

    /**
     * 搜索
     * @return void
     */
    private function search() {
        $type = util\Env::getRequest('type');
        $conditionCookie = MainUtil::getCookie('condition');
        if (empty($conditionCookie)) {
            MainUtil::setCookie('condition', $this->condition, 10 * 60);
        }

        if ($type == 'advanced_search') {
            $this->condition = ArticleUtil::joinSearchCondition($_POST['search'], $this->condition);
        } else if ($type == 'normal_search') {
            $keyword = $_POST['keyword'];
            //添加对keyword的转义，防止SQL错误
            $keyword = \CHtml::encode( $keyword );
            $this->condition = " subject LIKE '%$keyword%' ";
            MainUtil::setCookie('keyword', $keyword, 10 * 60);
        } else {
            $this->condition = $conditionCookie;
        }
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
        if ($this->condition != MainUtil::getCookie('condition')) {
            MainUtil::setCookie('condition', $this->condition, 10 * 60);
        }
    }

    /**
     * 查看动作
     * @return void
     */
    private function show() {
        $articleId = intval($_GET['articleid']);
        if (empty($articleId)) {
            $this->error(util\IBOS::lang('Parameters error', 'error'));
        }
        $article = model\Article::model()->fetchByPk($articleId);
        if (empty($article)) {
            $this->error(util\IBOS::lang('No permission or article not exists'), $this->createUrl('default/index'));
        }
        //判断是否有阅读的权限
        $uid = util\IBOS::app()->user->uid;
        if (!ArticleUtil::checkReadScope($uid, $article)) {
            $this->error(util\IBOS::lang('You do not have permission to read the article'), $this->createUrl('default/index'));
        }
        $data = ICArticle::getShowData($article);
        model\ArticleReader::model()->addReader($articleId, $uid);
        model\Article::model()->updateClickCount($articleId, $data['clickcount']);

        $dashboardConfig = $this->getDashboardConfig();
        //如果类型为链接类型，判断是否存在http，处理后跳转到指定页面
        if ($data['type'] == parent::ARTICLE_TYPE_LINK) {
            $urlArr = parse_url($data['url']);
            $url = isset($urlArr['scheme']) ? $data['url'] : 'http://' . $data['url'];
            header('Location: ' . $url);
            exit;
        }
        $params = array(
            'data' => $data,
            'dashboardConfig' => $dashboardConfig,
            'isInstallEmail' => $this->getEmailInstalled()
        );
        //取出附件
        if (!empty($data['attachmentid'])) {
            $params['attach'] = util\Attach::getAttach($data['attachmentid']);
        }
        if ($data['type'] == parent::ARTICLE_TYPE_PICTURE) {
            $params['pictureData'] = model\ArticlePicture::model()->fetchPictureByArticleId($articleId);
        }
        if ($article['status'] == 2) { // 如果是未审核
            $temp[0] = $params['data'];
            $temp = ICArticle::handleApproval($temp);
            $params['data'] = $temp[0];
            $params['isApprovaler'] = $this->checkIsApprovaler($article, $uid);
        }
        $this->setPageTitle(util\IBOS::lang('Show Article'));
        $this->setPageState('breadCrumbs', array(
            array('name' => util\IBOS::lang('Information center')),
            array('name' => util\IBOS::lang('Article'), 'url' => $this->createUrl('default/index')),
            array('name' => util\IBOS::lang('Show Article'))
        ));
        NotifyMessage::model()->setReadByUrl( $uid, IBOS::app()->getRequest()->getUrl() );
        $this->render('show', $params);
    }

    /**
     * 添加文章
     */
    public function actionAdd() {
        $op = util\Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('submit', 'default', 'checkIsAllowPublish');
        if (!in_array($option, $routes)) {
            $this->error(util\IBOS::lang('Can not find the path'), $this->createUrl('default/index'));
        }
        if ($option == 'default') {
            if (!empty($_GET['catid'])) {
                $this->catid = $_GET['catid'];
            }
            // 是否是免审人能直接发布
            $allowPublish = model\ArticleCategory::model()->checkIsAllowPublish($this->catid, util\IBOS::app()->user->uid);
            $aitVerify = model\ArticleCategory::model()->fetchIsProcessByCatid($this->catid);
            $params = array(
                'categoryOption' => $this->getCategoryOption(),
                'uploadConfig' => util\Attach::getUploadConfig(),
                'dashboardConfig' => $this->getDashboardConfig(),
                'allowPublish' => $allowPublish,
                'aitVerify' => $aitVerify
            );
            $this->setPageTitle(util\IBOS::lang('Add Article'));
            $this->setPageState('breadCrumbs', array(
                array('name' => util\IBOS::lang('Information center')),
                array('name' => util\IBOS::lang('Article'), 'url' => $this->createUrl('default/index')),
                array('name' => util\IBOS::lang('Add Article'))
            ));
            $this->render('add', $params);
        } else if ($option == 'submit') {
            if (ICArticle::formCheck($_POST) == false) {
                $this->error(util\IBOS::lang('Title do not empty'), $this->createUrl('default/add'));
            }
            $uid = util\IBOS::app()->user->uid;
            $this->beforeSaveData($_POST);
            $articleId = $this->addOrUpdateArticle('add', $_POST, $uid);
            if ($_POST['type'] == parent::ARTICLE_TYPE_PICTURE) {
                //图片文章添加
                $pidids = $_POST['picids'];
                if (!empty($pidids)) {
                    util\Attach::updateAttach($pidids);
                    $attach = util\Attach::getAttachData($pidids);
                    $this->addPicture($attach, $articleId);
                }
            }
            //更新附件
            $attachmentid = trim($_POST['attachmentid'], ',');
            if (!empty($attachmentid)) {
                util\Attach::updateAttach($attachmentid);
                model\Article::model()->modify($articleId, array('attachmentid' => $attachmentid));
            }
            //添加投票
            $dashboardConfig = $this->getDashboardConfig();
            if (isset($_POST['votestatus']) && $this->getVoteInstalled() && $dashboardConfig['articlevoteenable']) {
                $voteItemType = $_POST['voteItemType'];
                $type = $voteItemType == 1 ? 'vote' : 'imageVote';
                if (!empty($voteItemType) && trim($_POST[$type]['subject']) != '') {
                    $voteId = $this->addOrUpdateVote($_POST[$type], $articleId, $uid, 'add');
                    $this->addVoteItem($_POST[$type], $voteId, $voteItemType);
                } else {
                    model\Article::model()->modify($articleId, array('votestatus' => 0));
                }
            }
            // 消息提醒
            $user = User::model()->fetchByUid($uid);
            $article = model\Article::model()->fetchByPk($articleId);
            $categoryName = model\ArticleCategory::model()->fetchCateNameByCatid($article['catid']);
            if ($article['status'] == '1') {

                $publishScope = array('deptid' => $article['deptid'], 'positionid' => $article['positionid'], 'uid' => $article['uid']);
                $uidArr = ArticleUtil::getScopeUidArr($publishScope);
                $config = array(
                    '{sender}' => $user['realname'],
                    '{category}' => $categoryName,
                    '{subject}' => $article['subject'],
                    '{content}' => $this->renderPartial('remindcontent', array(
                        'article' => $article,
                        'author' => $user['realname'],
                            ), true),
                    '{orgContent}' => String::filterCleanHtml($article['content']),
                    '{url}' => util\IBOS::app()->urlManager->createUrl('article/default/index', array('op' => 'show', 'articleid' => $articleId)),
                    'id' => $articleId,
                );
                if (count($uidArr) > 0) {
                    Notify::model()->sendNotify($uidArr, 'article_message', $config, $uid);
                }

                // 动态推送
                $wbconf = WbCommonUtil::getSetting(true);
                if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                    $publishScope = array('deptid' => $article['deptid'], 'positionid' => $article['positionid'], 'uid' => $article['uid']);
                    $data = array(
                        'title' => util\IBOS::lang('Feed title', '', array(
                            '{subject}' => $article['subject'],
                            '{url}' => util\IBOS::app()->urlManager->createUrl('article/default/index', array('op' => 'show', 'articleid' => $articleId))
                        )),
                        'body' => $article['subject'],
                        'actdesc' => util\IBOS::lang('Post news'),
                        'userid' => $publishScope['uid'],
                        'deptid' => $publishScope['deptid'],
                        'positionid' => $publishScope['positionid'],
                    );
                    if ($_POST['type'] == self::ARTICLE_TYPE_PICTURE) {
                        $type = 'postimage';
                        $picids = explode(',', $pidids);
                        $data['attach_id'] = array_shift($picids);
                    } else {
                        $type = 'post';
                    }
                    WbfeedUtil::pushFeed(util\IBOS::app()->user->uid, 'article', 'article', $articleId, $data, $type);
                }
                //更新积分
                UserUtil::updateCreditByAction('addarticle', $uid);
            } else if ($article['status'] == '2') {
                $this->SendPending($article, $uid);
            }
            //日志记录
            $log = array(
                'user' => IBOS::app()->user->username,
                'ip' => IBOS::app()->setting->get('clientip')
                , 'isSuccess' => 1
            );
            Log::write($log, 'action', 'module.article.default.add');
            $this->success(util\IBOS::lang('Save succeed', 'message'), $this->createUrl('default/index'));
        } else {
            $this->$option();
        }
    }

    /**
     * 发送待审核新闻处理方法(新增与编辑都可处理)
     * @param array $article 新闻数据
     * @param integer $uid 发送人id
     */
    private function sendPending($article, $uid) {
        $category = model\ArticleCategory::model()->fetchByPk($article['catid']);
        $approval = Approval::model()->fetchNextApprovalUids($category['aid'], 0);
        if (!empty($approval)) {
            if ($approval['step'] == 'publish') {
                $this->verifyComplete($article['articleid'], $uid);
            } else {
                // 记录审核步骤(删除旧数据)
                model\ArticleApproval::model()->deleteAll("articleid={$article['articleid']}");
                model\ArticleApproval::model()->recordStep($article['articleid'], $uid);
                $sender = User::model()->fetchRealnameByUid($uid);
                // 发送消息给第一步审核人
                $config = array(
                    '{sender}' => $sender,
                    '{subject}' => $article['subject'],
                    '{category}' => $category['name'],
                    '{url}' => $this->createUrl('default/index', array('type' => 'notallow', 'catid' => 0)),
                    '{content}' => $this->renderPartial('remindcontent', array(
                        'article' => $article,
                        'author' => $sender,
                            ), true),
                );
                // 去掉不在发布范围的审批者
                foreach ($approval['uids'] as $k => $approvalUid) {
                    if (!ArticleUtil::checkReadScope($approvalUid, $article)) {
                        unset($approval['uids'][$k]);
                    }
                }
                Notify::model()->sendNotify($approval['uids'], 'article_verify_message', $config, $uid);
            }
        }
    }

    /**
     * 判断某个uid在某个分类下是否有直接发布权限
     * @param integer $catid 分类id
     * @param integer $uid 用户id
     * @return boolean
     */
    protected function checkIsAllowPublish() {
        if (util\IBOS::app()->request->isAjaxRequest) {
            $catid = intval(util\Env::getRequest('catid'));
            $uid = intval(util\Env::getRequest('uid'));
            $isAllow = model\ArticleCategory::model()->checkIsAllowPublish($catid, $uid);
            $ArticleCategory = model\ArticleCategory::model()->fetchByPk($catid);
            $checkIsPublish = $ArticleCategory['aid'] == 0 ? false : true;
            $this->ajaxReturn(array('isSuccess' => !!$isAllow, 'checkIsPublish' => $checkIsPublish));
        }
    }

    /**
     * 编辑文章
     */
    public function actionEdit() {
        $op = util\Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'update', 'verify', 'move', 'top', 'highLight', 'back', 'clickVote');
        if (!in_array($option, $routes)) {
            $this->error(util\IBOS::lang('Can not find the path'));
        }
        if ($option == 'default') {
            $articleId = util\Env::getRequest('articleid');
            if (empty($articleId)) {
                $this->error(util\IBOS::lang('Parameters error', 'error'));
            }
            $data = model\Article::model()->fetchByPk($articleId);
            if (empty($data)) {
                $this->error(util\IBOS::lang('No permission or article not exists'));
            }
            //选人框
            $data['publishScope'] = ArticleUtil::joinSelectBoxValue($data['deptid'], $data['positionid'], $data['uid']);
            // 是否是免审人能直接发布
            $allowPublish = model\ArticleCategory::model()->checkIsAllowPublish($data['catid'], util\IBOS::app()->user->uid);
            $aitVerify = model\ArticleCategory::model()->fetchIsProcessByCatid($data['catid']);
            $params = array(
                'data' => $data,
                'categoryOption' => $this->getCategoryOption(),
                'uploadConfig' => util\Attach::getUploadConfig(),
                'dashboardConfig' => $this->getDashboardConfig(),
                'allowPublish' => $allowPublish,
                'aitVerify' => $aitVerify
            );
            //显示附件
            if (!empty($data['attachmentid'])) {
                $params['attach'] = util\Attach::getAttach($data['attachmentid']);
            }
            if ($data['type'] == parent::ARTICLE_TYPE_PICTURE) {
                $params['pictureData'] = model\ArticlePicture::model()->fetchPictureByArticleId($articleId);
                $params['picids'] = '';
                foreach ($params['pictureData'] as $k => $value) {
                    $params['pictureData'][$k]['filepath'] = util\File::fileName($value['filepath']);
                    $params['picids'].=$value['aid'] . ',';
                }
                $params['picids'] = substr($params['picids'], 0, -1);
            }
            $this->setPageTitle(util\IBOS::lang('Edit Article'));
            $this->setPageState('breadCrumbs', array(
                array('name' => util\IBOS::lang('Information center')),
                array('name' => util\IBOS::lang('Article'), 'url' => $this->createUrl('default/index')),
                array('name' => util\IBOS::lang('Edit Article'))
            ));
            $this->render('edit', $params);
        } else {
            $this->$option();
        }
    }

    /**
     * 修改文章
     */
    private function update() {
        $uid = util\IBOS::app()->user->uid;
        $articleId = $_POST['articleid'];
        $this->beforeSaveData($_POST);
        $this->addOrUpdateArticle('update', $_POST, $uid);
        //图片文章修改  删除原来的，增加新的
        if ($_POST['type'] == parent::ARTICLE_TYPE_PICTURE) {
            $pidids = $_POST['picids'];
            if (!empty($pidids)) {
                model\ArticlePicture::model()->deleteAll('articleid=:articleid', array(':articleid' => $articleId));
                util\Attach::updateAttach($pidids);
                $attach = util\Attach::getAttachData($pidids);
                $this->addPicture($attach, $articleId);
            }
        }
        //更新附件
        $attachmentid = trim($_POST['attachmentid'], ',');
        if (!empty($attachmentid)) {
            util\Attach::updateAttach($attachmentid);
            model\Article::model()->modify($articleId, array('attachmentid' => $attachmentid));
        }
        //更新投票
        $dashboardConfig = $this->getDashboardConfig();
        if (isset($_POST['votestatus']) && $this->getVoteInstalled() && $dashboardConfig['articlevoteenable']) {
            $voteItemType = $_POST['voteItemType'];
            $type = $voteItemType == 1 ? 'vote' : 'imageVote';
            if (!empty($voteItemType) && trim($_POST[$type]['subject']) != '') {
                $this->updateVote($voteItemType, $type, $articleId, $uid);
                $rcord = model\Article::model()->fetch(array('select' => array('votestatus'), 'condition' => 'articleid=:articleid', 'params' => array(':articleid' => $articleId)));
                if ($rcord['votestatus'] == 0) {
                    model\Article::model()->modify($articleId, array('votestatus' => 1));
                }
            } else {
                model\Article::model()->modify($articleId, array('votestatus' => 0));
            }
        }
        // 消息提醒
        $user = User::model()->fetchByUid($uid);
        $article = model\Article::model()->fetchByPk($articleId);
        $categoryName = model\ArticleCategory::model()->fetchCateNameByCatid($article['catid']);
        if (!empty($_POST['msgRemind']) && $article['status'] == 1) {
            $publishScope = array('deptid' => $article['deptid'], 'positionid' => $article['positionid'], 'uid' => $article['uid']);
            $uidArr = ArticleUtil::getScopeUidArr($publishScope);
            $config = array(
                '{sender}' => $user['realname'],
                '{category}' => $categoryName,
                '{subject}' => $article['subject'],
                '{content}' => $this->renderPartial('remindcontent', array(
                    'article' => $article,
                    'author' => $user['realname'],
                        ), true),
                '{url}' => util\IBOS::app()->urlManager->createUrl('article/default/index', array('op' => 'show', 'articleid' => $article['articleid'])),
                'id' => $article['articleid'],
            );
            if (count($uidArr) > 0) {
                Notify::model()->sendNotify($uidArr, 'article_message', $config, $uid);
            }
        }
        if ($article['status'] == 2) {
            $this->sendPending($article, $uid);
        }
        model\ArticleBack::model()->deleteAll("articleid = {$articleId}");
        $this->success(util\IBOS::lang('Update succeed'), $this->createUrl('default/index'));
    }

    /**
     *  添加、修改新闻前的判断动作
     * @param array $postData 提交的数据
     */
    private function beforeSaveData(&$postData) {
        if (isset($postData['type'])) {
            if ($postData['type'] == parent::ARTICLE_TYPE_PICTURE) {
                if (empty($postData['picids'])) {
                    $this->error(util\IBOS::lang('Picture empty tip'), $this->createUrl('default/add'));
                } else {

                }
            } elseif ($postData['type'] == parent::ARTICLE_TYPE_DEFAULT) {
                if (empty($postData['content'])) {
                    $this->error(util\IBOS::lang('Content empty tip'), $this->createUrl('default/add'));
                }
            } elseif ($postData['type'] == parent::ARTICLE_TYPE_LINK) {
                if (empty($postData['url'])) {
                    $this->error(util\IBOS::lang('Url empty tip'), $this->createUrl('default/add'));
                }
            }
        }
        String::ihtmlSpecialCharsUseReference($postData['subject']);
    }

    /**
     * 添加或者修改文章信息
     * @param string $type 类型 add 或 update
     * @param array $data $_POST数据
     * @param integer $uid
     * @return type
     */
    private function addOrUpdateArticle($type, $data, $uid) {
        $attributes = model\Article::model()->create();
        $attributes['approver'] = $uid;
        $attributes['author'] = $uid;
        //取得发布权限
        $publishScope = util\String::getId($data['publishScope'], true);
        $publishScope = ArticleUtil::handleSelectBoxData($publishScope);

        $attributes['deptid'] = $publishScope['deptid'];
        $attributes['positionid'] = $publishScope['positionid'];
        $attributes['uid'] = $publishScope['uid'];

        $attributes['votestatus'] = isset($data['votestatus']) ? $data['votestatus'] : 0;
        $attributes['commentstatus'] = isset($data['commentstatus']) ? $data['commentstatus'] : 0;
        // 若所在分类无需审核，则改为发布
        if ($attributes['status'] == 2) {
            $catid = intval($attributes['catid']);
            $category = model\ArticleCategory::model()->fetchByPk($catid);
            $attributes['status'] = empty($category['aid']) ? 1 : 2;
            $attributes['approver'] = !empty($category['aid']) ? 0 : $uid;
        }
        if ($type == 'add') {
            $attributes['addtime'] = TIMESTAMP;
            return model\Article::model()->add($attributes, true);
        } else if ($type == 'update') {
            $attributes['uptime'] = TIMESTAMP;
            return model\Article::model()->updateByPk($attributes['articleid'], $attributes);
        }
    }

    /**
     * 添加图片信息
     * @param type $attach 图片信息
     * @param type $articleId 文章id
     */
    private function addPicture($attach, $articleId) {
        $sort = 0;
        $attachUrl = util\File::getAttachUrl() . '/';
        foreach ($attach as $value) {
            $picture = array(
                'articleid' => $articleId,
                'aid' => $value['aid'],
                'sort' => $sort,
                'addtime' => TIMESTAMP,
                'postip' => util\String::getSubIp(),
                'filename' => $value['filename'],
                'title' => '',
                'type' => util\String::getFileExt($value['filename']),
                'size' => $value['filesize'],
                'filepath' => $attachUrl . $value['attachment']
            );
            if (IBOS::app()->setting->get('setting/articlethumbenable')) {
                list($thumbWidth, $thumbHeight) = explode(',', IBOS::app()->setting->get('setting/articlethumbwh'));
                $imageInfo = util\Image::getImageInfo(util\File::fileName($picture['filepath']));
                if ($imageInfo['width'] < $thumbWidth && $imageInfo['height'] < $thumbHeight) {
                    $picture['thumb'] = 0;
                } else {
                    $sourceFileName = explode('/', $picture['filepath']);
                    $sourceFileName[count($sourceFileName) - 1] = 'thumb_' . $sourceFileName[count($sourceFileName) - 1];
                    $thumbName = implode('/', $sourceFileName);
                    if (LOCAL) {
                        util\Image::thumb($picture['filepath'], $thumbName, $thumbWidth, $thumbHeight);
                    } else {
                        $tempFile = util\File::getTempPath() . 'tmp.' . $picture['type'];
                        $orgImgname = util\IBOS::engine()->IO()->file()->fetchTemp(util\File::fileName($picture['filepath']), $picture['type']);
                        util\Image::thumb($orgImgname, $tempFile, $thumbWidth, $thumbHeight);
                        util\File::createFile($thumbName, file_get_contents($tempFile));
                    }
                    $picture['thumb'] = 1;
                }
            }
            model\ArticlePicture::model()->add($picture);
            $sort++;
        }
    }

    /**
     * 添加投票，包括文字投票和图片投票
     * @param array $data
     * @param integer $type 投票类型
     * @param integer $articleId
     * @param integer $uid
     */
    private function addOrUpdateVote($data, $articleId, $uid, $type, $voteId = '') {
        $vote = array(
            'subject' => $data['subject'],
            'starttime' => TIMESTAMP,
            'endtime' => strtotime($data['endtime']),
            'ismulti' => $data['ismulti'],
            'maxselectnum' => $data['maxselectnum'],
            'isvisible' => $data['isvisible'],
            'status' => 1,
            'uid' => $uid,
            'relatedmodule' => 'article',
            'relatedid' => $articleId,
            'deadlinetype' => $data['deadlineType']
        );
        if ($type == 'add') {
            return Vote::model()->add($vote, true);
        } else {
            return Vote::model()->modify($voteId, $vote);
        }
    }

    /**
     * 增加投票项
     * @param array $data 表单提交数据
     * @param integer $voteId
     * @param string $type
     */
    private function addVoteItem($data, $voteId, $type) {
        foreach ($data['voteItem'] as $key => $value) {
            $voteItem = array(
                'voteid' => $voteId,
                'type' => $type,
                'content' => $value
            );
            if ($type == 1 && !empty($value)) { // 文字投票，去掉内容为空的条目
                VoteItem::model()->add($voteItem);
            } else if ($type == 2) { // 图片投票，只添加有图片或者有内容的条目
                if (!empty($data['picpath'][$key]) || !empty($value)) {
                    $voteItem['picpath'] = $data['picpath'][$key];
                    VoteItem::model()->add($voteItem);
                }
            }
        }
    }

    /**
     * 修改投票数据
     * @param integer $articleId
     * @param integer $uid
     */
    private function updateVote($voteItemType, $type, $articleId, $uid) {
        //判断是否全部是新增投票
        if (empty($_POST['voteid'])) {
            $voteId = $this->addOrUpdateVote($_POST[$type], $articleId, $uid, 'add');
            $this->addVoteItem($_POST[$type], $voteId, $voteItemType);
        } else {
            //新增投票数组、旧的投票数组、要删除的投票项id数组
            $newVoteItemArr = $oldVoteItemArr = $delFlagItemId = array();
            foreach ($_POST[$type]['voteItem'] as $key => $value) {
                if (substr($key, 0, 3) == 'new') {
                    $newVoteItemArr[$key] = $value;
                } else {
                    $oldVoteItemArr[$key] = $value;
                }
            }
            //取得待删除的投票项
            $voteData = Vote::model()->fetchVote('article', $articleId);
            $itemData = $voteData['voteItemList'];
            foreach ($itemData as $value) {
                if (!array_key_exists($value['itemid'], $oldVoteItemArr)) {
                    $delFlagItemId[] = $value['itemid'];
                }
            }
            //增加新增投票
            $this->addOrUpdateVote($_POST[$type], $articleId, $uid, 'update', $_POST['voteid']);
            //修改旧的投票项
            $data = array('voteItem' => $newVoteItemArr);
            if ($type == 'imageVote') {
                $data['picpath'] = $_POST[$type]['picpath'];
            }
            $this->addVoteItem($data, $_POST['voteid'], $voteItemType);
            foreach ($oldVoteItemArr as $key => $value) {
                $voteItem = array('content' => $value);
                if ($type == 'imageVote') {
                    $voteItem['picpath'] = $_POST[$type]['picpath'][$key];
                }
                VoteItem::model()->modify($key, $voteItem);
            }
            //删除要删除的投票项
            VoteItem::model()->deleteByPk($delFlagItemId);
        }
        return true;
    }

    /**
     * 删除文章，附带把评论，投票，附件等内容删除
     */
    public function actionDel() {
        if (util\IBOS::app()->request->isAjaxRequest) {
            $articleids = trim(util\Env::getRequest('articleids'), ',');
            // 删除附件
            $attachmentids = '';
            $attachmentIdArr = model\Article::model()->fetchAllFieldValueByArticleids('attachmentid', $articleids);
            foreach ($attachmentIdArr as $attachmentid) {
                if (!empty($attachmentid)) {
                    $attachmentids.=$attachmentid . ',';
                }
            }
            $count = 0;
            if (!empty($attachmentids)) {
                $splitArray = explode(',', trim($attachmentids, ','));
                $attachmentidArray = array_unique($splitArray);
                $attachmentids = implode(',', $attachmentidArray);
                $count = util\Attach::delAttach($attachmentids);
            }
            //删除投票
            if ($this->getVoteInstalled()) {
                Vote::model()->deleteAllByRelationIdsAndModule($articleids, 'article');
            }
            // 删除图片
            model\ArticlePicture::model()->deleteAllByArticleIds($articleids);
            // 删除文章
            model\Article::model()->deleteAllByArticleIds($articleids);
            // 删除待审核记录
            model\ArticleApproval::model()->deleteByArtIds($articleids);
            $this->ajaxReturn(array('isSuccess' => true, 'count' => $count, 'msg' => util\IBOS::lang('Del succeed', 'message')));
        }
    }

    /**
     * 判断某个uid是否某篇未审核新闻的当前审核人
     * @param array $article 新闻数据
     * @param integer $uid 用户id
     * @return boolean
     */
    private function checkIsApprovaler($article, $uid) {
        $res = false;
        $artApproval = model\ArticleApproval::model()->fetchLastStep($article['articleid']);
        $category = model\ArticleCategory::model()->fetchByPk($article['catid']);
        if (!empty($category['aid'])) {
            $approval = Approval::model()->fetchByPk($category['aid']);
            $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $artApproval['step']);
            if (in_array($uid, $nextApproval['uids'])) {
                $res = true;
            }
        }
        return $res;
    }

    /**
     * 审核
     */
    private function verify() {
        if (util\IBOS::app()->request->isAjaxRequest) {
            $uid = util\IBOS::app()->user->uid;
            $artIds = trim(util\Env::getRequest('articleids'), ',');
            $ids = explode(',', $artIds);
            if (empty($ids)) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => util\IBOS::lang('Parameters error', 'error')));
            }
            $sender = User::model()->fetchRealnameByUid($uid);
            foreach ($ids as $artId) {
                $artApproval = model\ArticleApproval::model()->fetchLastStep($artId);
                if (empty($artApproval)) {
                    $this->verifyComplete($artId, $uid);
                } else {
                    $art = model\Article::model()->fetchByPk($artId);
                    $category = model\ArticleCategory::model()->fetchByPk($art['catid']);
                    $approval = Approval::model()->fetch("id={$category['aid']}");
                    $curApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $artApproval['step']); // 当前审核到的步骤
                    $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $artApproval['step'] + 1); // 下一步应该审核的步骤
                    if (!in_array($uid, $curApproval['uids'])) {
                        $this->ajaxReturn(array('isSuccess' => false, 'msg' => util\IBOS::lang('You do not have permission to verify the article')));
                    }
                    if (!empty($nextApproval)) {
                        if ($nextApproval['step'] == 'publish') { // 已完成标识
                            $this->verifyComplete($artId, $uid);
                        } else { // 记录签收步骤，给下一步签收人发提醒消息
                            model\ArticleApproval::model()->recordStep($artId, $uid);
                            $config = array(
                                '{sender}' => $sender,
                                '{subject}' => $art['subject'],
                                '{category}' => $category['name'],
                                '{content}' => $this->renderPartial('remindcontent', array(
                                    'article' => $art,
                                    'author' => $sender,
                                        ), true),
                                '{url}' => $this->createUrl('default/index', array('type' => 'notallow'))
                            );
                            // 去掉不在发布范围的审批者
                            foreach ($nextApproval['uids'] as $k => $approvalUid) {
                                if (!ArticleUtil::checkReadScope($approvalUid, $art)) {
                                    unset($nextApproval['uids'][$k]);
                                }
                            }
                            Notify::model()->sendNotify($nextApproval['uids'], 'article_verify_message', $config, $uid);
                            model\Article::model()->updateAllStatusAndApproverByPks($artId, $uid, 2);
                        }
                    }
                }
            }
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => util\IBOS::lang('Verify succeed', 'message')));
        }
    }

    /**
     * 全部审核完成动作
     * @param mix $docids 审核的新闻id
     * @param integer $uid 最终审核人id
     */
    private function verifyComplete($artId, $uid) {
        model\Article::model()->updateAllStatusAndApproverByPks($artId, $uid, 1);
        model\ArticleApproval::model()->deleteAll("articleid={$artId}");
        // 动态推送
        $article = model\Article::model()->fetchByPk($artId);
        if (!empty($article)) {
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                $publishScope = array('deptid' => $article['deptid'], 'positionid' => $article['positionid'], 'uid' => $article['uid']);
                $data = array(
                    'title' => util\IBOS::lang('Feed title', '', array(
                        '{subject}' => $article['subject'],
                        '{url}' => util\IBOS::app()->urlManager->createUrl('article/default/index', array('op' => 'show', 'articleid' => $article['articleid']))
                    )),
                    'body' => $article['content'],
                    'actdesc' => util\IBOS::lang('Post news'),
                    'userid' => $publishScope['uid'],
                    'deptid' => $publishScope['deptid'],
                    'positionid' => $publishScope['positionid'],
                );
                if ($article['type'] == self::ARTICLE_TYPE_PICTURE) {
                    $type = 'postimage';
                    $picture = model\ArticlePicture::model()->fetchByAttributes(array("articleid" => $article['articleid']));
                    $data['attach_id'] = $picture['aid'];
                } else {
                    $type = 'post';
                }
                WbfeedUtil::pushFeed($article['author'], 'article', 'article', $article['articleid'], $data, $type);
            }
            //更新积分
            UserUtil::updateCreditByAction('addarticle', $article['author']);
        }
    }

    /**
     * 退回
     */
    private function back() {
        $uid = util\IBOS::app()->user->uid;
        $artIds = trim(util\Env::getRequest('articleids'), ',');
        $reason = util\String::filterCleanHtml(util\Env::getRequest('reason'));
        $ids = explode(',', $artIds);
        if (empty($ids)) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => util\IBOS::lang('Parameters error', 'error')));
        }
        $sender = User::model()->fetchRealnameByUid($uid);
        foreach ($ids as $artId) {
            $art = model\Article::model()->fetchByPk($artId);
            $categoryName = model\ArticleCategory::model()->fetchCateNameByCatid($art['catid']);
            if (!$this->checkIsApprovaler($art, $uid)) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => util\IBOS::lang('You do not have permission to verify the article')));
            }
            $config = array(
                '{sender}' => $sender,
                '{subject}' => $art['subject'],
                '{category}' => $categoryName,
                '{content}' => $reason,
                '{url}' => util\IBOS::app()->urlManager->createUrl('article/default/index', array('type' => 'notallow', 'catid' => 0))
            );
            Notify::model()->sendNotify($art['author'], 'article_back_message', $config, $uid);
            model\ArticleBack::model()->addBack($artId, $uid, $reason, TIMESTAMP); // 添加一条退回记录
        }
        $this->ajaxReturn(array('isSuccess' => true, 'msg' => util\IBOS::lang('Operation succeed', 'message')));
    }

    /**
     * 移动文章
     */
    private function move() {
        if (IBOS::app()->request->isAjaxRequest) {
            $articleids = util\Env::getRequest('articleids');
            $catid = util\Env::getRequest('catid');
            if (!empty($articleids) && !empty($catid)) {
                model\Article::model()->updateAllCatidByArticleIds(ltrim($articleids, ','), $catid);
                $this->ajaxReturn(array('isSuccess' => true));
            } else {
                $this->ajaxReturn(array('isSuccess' => false));
            }
        }
    }

    /**
     * 置顶操作
     */
    private function top() {
        if (util\IBOS::app()->request->isAjaxRequest) {
            $articleids = util\Env::getRequest('articleids');
            $topEndTime = util\Env::getRequest('topEndTime');
            if (!empty($topEndTime)) {
                $topEndTime = strtotime($topEndTime) + 24 * 60 * 60 - 1;
                model\Article::model()->updateTopStatus($articleids, 1, TIMESTAMP, $topEndTime);
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => util\IBOS::lang('Top succeed')));
            } else {
                model\Article::model()->updateTopStatus($articleids, 0, '', '');
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => util\IBOS::lang('Unstuck success')));
            }
        }
    }

    /**
     * 高亮操作
     */
    private function highLight() {
        if (util\IBOS::app()->request->isAjaxRequest) {
            $articleids = trim(util\Env::getRequest('articleids'), ',');
            $highLight = array();
            $highLight['endTime'] = util\Env::getRequest('highlightEndTime');
            $highLight['bold'] = util\Env::getRequest('highlight_bold');
            $highLight['color'] = util\Env::getRequest('highlight_color');
            $highLight['italic'] = util\Env::getRequest('highlight_italic');
            $highLight['underline'] = util\Env::getRequest('highlight_underline');
            $data = ArticleUtil::processHighLightRequestData($highLight);
            if (empty($data['highlightendtime'])) {
                model\Article::model()->updateHighlightStatus($articleids, 0, '', '');
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => util\IBOS::lang('Unhighlighting success')));
            } else {
                model\Article::model()->updateHighlightStatus($articleids, 1, $data['highlightstyle'], $data['highlightendtime']);
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => util\IBOS::lang('Highlight succeed')));
            }
        }
    }

    /**
     * 选择选项点击投票，如果是数字，直接echo，其他则返回json数据
     * @return void
     */
    private function clickVote() {
        if ($this->getVoteInstalled()) {
            $relatedId = util\Env::getRequest('relatedid');
            $voteItemids = util\Env::getRequest('voteItemids');
            $result = VotePlugManager::getArticleVote()->clickVote('article', $relatedId, $voteItemids);
            if (is_numeric($result)) {
                echo $result;
            } else {
                $this->ajaxReturn($result);
            }
        }
    }

    /**
     * 取得一次投票参与人数
     * @return void
     */
    private function getVoteCount() {
        if ($this->getVoteInstalled()) {
            $relatedId = util\Env::getRequest('relatedid');
            $count = Vote::model()->fetchUserVoteCount('article', $relatedId);
            echo $count;
            exit;
        }
    }

    /**
     * 加载阅读情况
     * @return void
     */
    private function getReader() {
        if (util\IBOS::app()->request->isAjaxRequest) {
            $articleid = util\Env::getRequest('articleid');
            $readerData = model\ArticleReader::model()->fetchAll('articleid=:articleid', array(':articleid' => $articleid));
            $departments = DepartmentUtil::loadDepartment();
            $res = $tempDeptids = $users = array();
            foreach ($readerData as $reader) {
                $user = User::model()->fetchByUid($reader['uid']);
                $users[] = $user;
                $deptid = $user['deptid'];
                $tempDeptids[] = $user['deptid'];
            }
            $deptids = array_unique($tempDeptids);
            foreach ($deptids as $deptid) {
                $deptName = isset($departments[$deptid]['deptname']) ? $departments[$deptid]['deptname'] : '--';
                foreach ($users as $k => $user) {
                    if ($user['deptid'] == $deptid) {
                        $res[$deptName][] = $user;
                        unset($users[$k]);
                    }
                }
            }
            $this->ajaxReturn($res);
        }
    }

    /**
     * 取得一篇文章中一个部门的阅读人员
     * @return void
     */
    private function getReaderByDeptId() {
        if (util\IBOS::app()->request->isAjaxRequest) {
            $articleId = $_POST['articleid'];
            $deptId = $_POST['deptid'];
            $readerData = model\ArticleReader::model()->fetchArticleReaderByDeptid($articleId, $deptId);
            $this->ajaxReturn($readerData);
        }
    }

    /**
     * 预览
     * @return void
     */
    private function preview() {
        $this->setPageTitle(util\IBOS::lang('Preview Acticle'));
        $this->setPageState('breadCrumbs', array(
            array('name' => util\IBOS::lang('Information center')),
            array('name' => util\IBOS::lang('Article'), 'url' => $this->createUrl('default/index')),
            array('name' => util\IBOS::lang('Preview Acticle'))
        ));
        $type = $_POST['type'];
        $subject = $_POST['subject'];
        $param = array(
            'type' => $type,
            'subject' => $subject,
        );
        if ($type == self::ARTICLE_TYPE_PICTURE) { // 图片类型
            $picids = util\Env::getRequest('picids');
            $pictureData = util\Attach::getAttachData($picids, false);
            $param['pictureData'] = array_values($pictureData);
        } else {
            $param['content'] = util\Env::getRequest('content');
        }
        $this->render('preview', $param);
    }

}
