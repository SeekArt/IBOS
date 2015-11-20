<?php

/**
 * 公文模块------公文默认控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzzyb <gzwwb@ibos.com.cn>
 */
/**
 * 公文模块------公文默认控制器，继承OfficialdocBaseController
 * @package application.modules.officialDoc.components
 * @version $Id: OfficialdocOfficialdocController.php 660 2013-06-24 00:58:16Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\String;
use application\modules\dashboard\model\Approval;
use application\modules\main\utils\Main as MainUtil;
use application\modules\message\model\Notify;
use application\modules\officialdoc\core\Officialdoc as ICOfficialdoc;
use application\modules\officialdoc\model\Officialdoc;
use application\modules\officialdoc\model\OfficialdocApproval;
use application\modules\officialdoc\model\OfficialdocBack;
use application\modules\officialdoc\model\OfficialdocCategory;
use application\modules\officialdoc\model\OfficialdocReader;
use application\modules\officialdoc\model\OfficialdocVersion;
use application\modules\officialdoc\model\RcType;
use application\modules\officialdoc\utils\Officialdoc as OfficialdocUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;
use CJSON;
use application\core\model\Log;

class OfficialdocController extends BaseController {

    /**
     * 分类id
     * @var integer 
     */
    protected $catId = 0;

    /**
     * 条件
     * @var string 
     */
    private $_condition = '';

    /**
     * 默认动作
     * @goto actionList
     */
    public function actionIndex() {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'getSign', 'search', 'getUnSign', 'getVersion',
            'getRcType', 'prewiew', 'remind');
        if (!in_array($option, $routes)) {
            $this->error(IBOS::lang('Can not find the path'),
                    $this->createUrl('officialdoc/index'));
        }
        if ($option == 'default') {
            $catid = intval(Env::getRequest('catid'));
            $childCatIds = '';
            if (!empty($catid)) {
                $this->catId = $catid;
                $childCatIds = OfficialdocCategory::model()->fetchCatidByPid($this->catId,
                        true);
            }
            //搜索，必须是post类型请求
            if (Env::getRequest('param') == 'search' && IBOS::app()->request->isPostRequest) {
                $this->search();
            }
            // 取消已过期的置顶样式标记
            Officialdoc::model()->cancelTop();
            // 取消已过期的高亮文章的样式标记
            Officialdoc::model()->updateIsOverHighLight();

            $type = Env::getRequest('type');
            $uid = IBOS::app()->user->uid;
            $condition = OfficialdocUtil::joinListCondition($type, $uid,
                            $childCatIds, $this->_condition);
            $datas = Officialdoc::model()->fetchAllAndPage($condition);
            $officialDocList = ICOfficialdoc::getListDatas($datas['datas']);
            // 判断是否审核人
            $aids = OfficialdocCategory::model()->fetchAids();
            $isApprover = in_array($uid,
                    Approval::model()->fetchApprovalUidsByIds($aids));
            $params = array(
                'pages' => $datas['pages'],
                'officialDocList' => $officialDocList,
                'categorySelectOptions' => $this->getCategoryOption(),
                'isApprover' => $isApprover
            );
            $this->setPageTitle(IBOS::lang('Officialdoc'));
            $this->setPageState('breadCrumbs',
                    array(
                array('name' => IBOS::lang('Information center')),
                array('name' => IBOS::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                array('name' => IBOS::lang('Officialdoc list'))
            ));
            if ($type == 'notallow') {
                $view = 'approval';
                $params['officialDocList'] = ICOfficialdoc::handleApproval($params['officialDocList']);
            } else {
                $view = 'list';
            }
            //未签收数
            $params['countNosign'] = Officialdoc::model()->getOfficialdocCount(OfficialdocUtil::TYPE_NOSIGN,
                    $uid, $childCatIds, $this->_condition);
            //未审核数
            $params['countNotAllOw'] = Officialdoc::model()->getOfficialdocCount(OfficialdocUtil::TYPE_NOTALLOW,
                    $uid, $childCatIds, $this->_condition);
            //草稿数
            $params['countDraft'] = Officialdoc::model()->getOfficialdocCount(OfficialdocUtil::TYPE_DRAFT,
                    $uid, $childCatIds, $this->_condition);
            $this->render($view, $params);
        } else {
            $this->$option();
        }
    }

    /**
     * 新建文章
     */
    public function actionAdd() {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'save', 'checkIsAllowPublish');
        if (!in_array($option, $routes)) {
            $this->error(IBOS::lang('Can not find the path'),
                    $this->createUrl('officialdoc/index'));
        }
        if ($option == 'default') {
            if (!empty($_GET['catid'])) {
                $this->catId = $_GET['catid'];
            }
            // 是否是免审人能直接发布
            $allowPublish = OfficialdocCategory::model()->checkIsAllowPublish($this->catId,
                    IBOS::app()->user->uid);
            $aitVerify = OfficialdocCategory::model()->fetchIsProcessByCatid($this->catId);
            $params = array(
                'categoryOption' => $this->getCategoryOption(),
                'dashboardConfig' => IBOS::app()->setting->get('setting/docconfig'),
                'uploadConfig' => Attach::getUploadConfig(),
                'RCData' => RcType::model()->fetchAll(),
                'allowPublish' => $allowPublish,
                'aitVerify' => $aitVerify
            );
            $this->setPageTitle(IBOS::lang('Add officialdoc'));
            $this->setPageState('breadCrumbs',
                    array(
                array('name' => IBOS::lang('Information center')),
                array('name' => IBOS::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                array('name' => IBOS::lang('Add officialdoc'))
            ));
            $this->render('add', $params);
        } else {
            $this->$option();
        }
    }

    /**
     * 判断某个uid在某个分类下是否有直接发布权限
     * @param integer $catid 分类id
     * @param integer $uid 用户id
     * @return boolean
     */
    protected function checkIsAllowPublish() {
        if (IBOS::app()->request->isAjaxRequest) {
            $catid = intval(Env::getRequest('catid'));
            $uid = intval(Env::getRequest('uid'));
            $isAllow = OfficialdocCategory::model()->checkIsAllowPublish($catid,
                    $uid);
            $officialdocCategory = OfficialdocCategory::model()->fetchByPk($catid);
            $checkIsPublish = $officialdocCategory['aid'] == 0 ? false : true;
            $this->ajaxReturn(array('isSuccess' => !!$isAllow, 'checkIsPublish' => $checkIsPublish));
        }
    }

    /**
     * 编辑文章
     */
    public function actionEdit() {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'update', 'top', 'highLight', 'move', 'verify',
            'back');
        if (!in_array($option, $routes)) {
            $this->error(IBOS::lang('Can not find the path'),
                    $this->createUrl('officialdoc/index'));
        }
        if ($option == 'default') {
            $docid = Env::getRequest('docid');
            if (empty($docid)) {
                $this->error(IBOS::lang('Parameters error', 'error'));
            }
            $data = Officialdoc::model()->fetch('docid=:docid',
                    array(':docid' => $docid));
            if (!empty($data)) {
                //取得最新历史版本
                $data['publishScope'] = OfficialdocUtil::joinSelectBoxValue($data['deptid'],
                                $data['positionid'], $data['uid']);
                $data['ccScope'] = OfficialdocUtil::joinSelectBoxValue($data['ccdeptid'],
                                $data['ccpositionid'], $data['ccuid']);
                // 是否是免审人能直接发布
                $allowPublish = OfficialdocCategory::model()->checkIsAllowPublish($data['catid'],
                        IBOS::app()->user->uid);
                $aitVerify = OfficialdocCategory::model()->fetchIsProcessByCatid($data['catid']);
                $params = array(
                    'data' => $data,
                    'categoryOption' => $this->getCategoryOption(),
                    'dashboard' => IBOS::app()->setting->get('setting/docconfig'),
                    'uploadConfig' => Attach::getUploadConfig(),
                    'RCData' => RcType::model()->fetchAll(),
                    'allowPublish' => $allowPublish,
                    'aitVerify' => $aitVerify
                );
                if (!empty($data['attachmentid'])) {
                    $params['attach'] = Attach::getAttach($data['attachmentid']);
                }
                $this->setPageTitle(IBOS::lang('Edit officialdoc'));
                $this->setPageState('breadCrumbs',
                        array(
                    array('name' => IBOS::lang('Information center')),
                    array('name' => IBOS::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                    array('name' => IBOS::lang('Edit officialdoc'))
                ));
                $this->render('edit', $params);
            }
        } else {
            $this->$option();
        }
    }

    /**
     * 删除公文
     * @return void
     */
    public function actionDel() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docids = trim(Env::getRequest('docids'), ',');
            // 删除附件
            $attachmentIdArr = Officialdoc::model()->fetchAidsByDocids($docids);
            Attach::delAttach($attachmentIdArr);
            if (!empty($docids)) {
                // 删除文章(包括历史版本)
                Officialdoc::model()->deleteAllByDocIds($docids);
                OfficialdocVersion::model()->deleteAllByDocids($docids);
                //删除阅读记录
                OfficialdocReader::model()->deleteReaderByDocIds($docids);
                // 删除待审核记录
                OfficialdocApproval::model()->deleteByDocIds($docids);
                //删除退回记录
                OfficialdocBack::model()->deleteByDocIds($docids);
                $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Del succeed',
                            'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'info' => IBOS::lang('Parameters error',
                            'error')));
            }
        }
    }

    /**
     * 保存文章
     */
    private function save() {
        $uid = IBOS::app()->user->uid;
        $data = $_POST;
        //发布范围
        $publicScope = OfficialdocUtil::handleSelectBoxData(String::getId($data['publishScope'],
                                true));
        $data['uid'] = $publicScope['uid'];
        $data['positionid'] = $publicScope['positionid'];
        $data['deptid'] = $publicScope['deptid'];
        //抄送
        $ccScope = OfficialdocUtil::handleSelectBoxData(String::getId($data['ccScope'],
                                true), false);
        $data['ccuid'] = $ccScope['uid'];
        $data['ccpositionid'] = $ccScope['positionid'];
        $data['ccdeptid'] = $ccScope['deptid'];
        $data['author'] = $uid;
        $data['docno'] = $data['docNo'];
        $data['approver'] = $uid;
        $data['addtime'] = TIMESTAMP;
        $data['uptime'] = TIMESTAMP;
        // 若所在分类无需审核，则改为发布
        if ($data['status'] == 2) {
            $catid = intval($data['catid']);
            $category = OfficialdocCategory::model()->fetchByPk($catid);
            $data['status'] = empty($category['aid']) ? 1 : 2;
            $data['approver'] = !empty($category['aid']) ? 0 : $uid;
        }
        //更新附件
        $attachmentid = trim($data['attachmentid'], ',');
        if (!empty($attachmentid)) {
            Attach::updateAttach($attachmentid);
        }
        $docId = Officialdoc::model()->add($data, true);
        // 消息提醒
        $user = User::model()->fetchByUid($uid);
        $officialdoc = Officialdoc::model()->fetchByPk($docId);
        $categoryName = OfficialdocCategory::model()->fetchCateNameByCatid($officialdoc['catid']);
        if ($data['status'] == '1') {
            $publishScope = array('deptid' => $officialdoc['deptid'], 'positionid' => $officialdoc['positionid'],
                'uid' => $officialdoc['uid']);
            $uidArr = OfficialdocUtil::getScopeUidArr($publishScope);
            $config = array(
                '{sender}' => $user['realname'],
                '{category}' => $categoryName,
                '{subject}' => $officialdoc['subject'],
                '{content}' => $this->renderPartial('remindcontent',
                        array(
                    'doc' => $officialdoc,
                    'author' => $user['realname'],
                        ), true),
                '{url}' => IBOS::app()->urlManager->createUrl('officialdoc/officialdoc/show',
                        array('docid' => $docId)),
                'id' => $docId,
            );
            if (count($uidArr) > 0) {
                Notify::model()->sendNotify($uidArr, 'officialdoc_message',
                        $config, $uid);
            }
            // 动态推送
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article']
                    == 1) {
                $publishScope = array('deptid' => $officialdoc['deptid'], 'positionid' => $officialdoc['positionid'],
                    'uid' => $officialdoc['uid']);
                $data = array(
                    'title' => IBOS::lang('Feed title', '',
                            array(
                        '{subject}' => $officialdoc['subject'],
                        '{url}' => IBOS::app()->urlManager->createUrl('officialdoc/officialdoc/show',
                                array('docid' => $docId))
                    )),
                    'body' => $officialdoc['subject'],
                    'actdesc' => IBOS::lang('Post officialdoc'),
                    'userid' => $publishScope['uid'],
                    'deptid' => $publishScope['deptid'],
                    'positionid' => $publishScope['positionid'],
                );
                WbfeedUtil::pushFeed($uid, 'officialdoc', 'officialdoc', $docId,
                        $data);
            }
            //更新积分
            UserUtil::updateCreditByAction('addofficialdoc', $uid);
        } else if ($data['status'] == '2') {
            $this->SendPending($officialdoc, $uid);
        }
        /**
         * 日志记录
         */
        $log = array(
            'user' => IBOS::app()->user->username,
            'ip' => IBOS::app()->setting->get('clientip'),
            'isSuccess' => 1
        );
        Log::write($log, 'action', 'module.officialdoc.officialdoc.add');
        $this->success(IBOS::lang('Save succeed', 'message'),
                $this->createUrl('officialdoc/index'));
    }

    /**
     * 发送待审核公文处理方法(新增与编辑都可处理)
     * @param array $doc 公文数据
     * @param integer $uid 发送人id
     */
    private function sendPending($doc, $uid) {
        $category = OfficialdocCategory::model()->fetchByPk($doc['catid']);
        $approval = Approval::model()->fetchNextApprovalUids($category['aid'], 0);
        if (!empty($approval)) {
            if ($approval['step'] == 'publish') {
                $this->verifyComplete($doc['docid'], $uid);
            } else {
                // 记录审核步骤(删除旧数据)
                OfficialdocApproval::model()->deleteAll("docid={$doc['docid']}");
                OfficialdocApproval::model()->recordStep($doc['docid'], $uid);
                $sender = User::model()->fetchRealnameByUid($uid);
                // 发送消息给第一步审核人
                $config = array(
                    '{sender}' => $sender,
                    '{subject}' => $doc['subject'],
                    '{category}' => $category['name'],
                    '{url}' => $this->createUrl('officialdoc/index',
                            array('type' => 'notallow')),
                    '{content}' => $this->renderPartial('remindcontent',
                            array(
                        'doc' => $doc,
                        'author' => $sender,
                            ), true),
                );
                // 去掉不在发布范围的审批者
                foreach ($approval['uids'] as $k => $approvalUid) {
                    if (!OfficialdocUtil::checkReadScope($approvalUid, $doc)) {
                        unset($approval['uids'][$k]);
                    }
                }
                Notify::model()->sendNotify($approval['uids'],
                        'officialdoc_verify_message', $config, $uid);
            }
        }
    }

    /**
     * 公文编辑功能
     */
    private function update() {
        if (Env::submitCheck('formhash')) {
            $docid = $_POST['docid'];
            $uid = IBOS::app()->user->uid;
            $data = $_POST;
            //发布范围
            $publicScope = OfficialdocUtil::handleSelectBoxData(String::getId($data['publishScope'],
                                    true));
            $data['uid'] = $publicScope['uid'];
            $data['positionid'] = $publicScope['positionid'];
            $data['deptid'] = $publicScope['deptid'];

            //抄送
            $ccScope = OfficialdocUtil::handleSelectBoxData(String::getId($data['ccScope'],
                                    true), false);
            $data['ccuid'] = $ccScope['uid'];
            $data['ccpositionid'] = $ccScope['positionid'];
            $data['ccdeptid'] = $ccScope['deptid'];

            $data['approver'] = $uid;
            $data['docno'] = $_POST['docNo'];
            $data['commentstatus'] = isset($data['commentstatus']) ? $data['commentstatus']
                        : 0;
            $data['uptime'] = TIMESTAMP;
            $data['version'] = $data['version'] + 1;

            //增加一个历史版本
            $version = Officialdoc::model()->fetchByPk($_POST['docid']);
            $version['editor'] = $uid;
            $version['reason'] = $data['reason'];
            $version['uptime'] = TIMESTAMP;
            OfficialdocVersion::model()->add($version);

            // 若所在分类无需审核，则改为发布
            if ($data['status'] == 2) {
                $catid = intval($data['catid']);
                $category = OfficialdocCategory::model()->fetchByPk($catid);
                $data['status'] = empty($category['aid']) ? 1 : 2;
                $data['approver'] = !empty($category['aid']) ? 0 : $uid;
            }
            //更新附件
            $attachmentid = trim($_POST['attachmentid'], ',');
            if (!empty($attachmentid)) {
                Attach::updateAttach($attachmentid);
                Officialdoc::model()->modify($docid,
                        array('attachmentid' => $attachmentid));
            }
            $attributes = Officialdoc::model()->create($data);
            Officialdoc::model()->updateByPk($data['docid'], $attributes);
            $doc = Officialdoc::model()->fetchByPk($data['docid']);
            $this->sendPending($doc, $uid);

            OfficialdocBack::model()->deleteAll("docid = {$docid}");

            $this->success(IBOS::lang('Update succeed', 'message'),
                    $this->createUrl('officialdoc/index'));
        }
    }

    /**
     * 搜索
     * @return void
     */
    private function search() {
        $type = Env::getRequest('type');
        $conditionCookie = MainUtil::getCookie('condition');
        if (empty($conditionCookie)) {
            MainUtil::setCookie('condition', $this->_condition, 10 * 60);
        }

        if ($type == 'advanced_search') {
            $this->_condition = OfficialdocUtil::joinSearchCondition($_POST['search'],
                            $this->_condition);
        } else if ($type == 'normal_search') {
            //添加对keyword转义，防止SQL错误
            $keyword = addslashes($_POST['keyword']);
            $this->_condition = " subject LIKE '%$keyword%' ";
            MainUtil::setCookie('keyword', $keyword, 10 * 60);
        } else {
            $this->_condition = $conditionCookie;
        }
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
        if ($this->_condition != MainUtil::getCookie('condition')) {
            MainUtil::setCookie('condition', $this->_condition, 10 * 60);
        }
    }

    /**
     * 查看动作
     */
    public function actionShow() {
        if (Env::getRequest('op') == 'sign') {
            $this->sign();
            exit();
        }
        $uid = IBOS::app()->user->uid;
        $docid = intval(Env::getRequest('docid'));
        $version = Env::getRequest('version');
        if (empty($docid)) {
            $this->error(IBOS::lang('Parameters error', 'error'));
        }
        $officialDoc = Officialdoc::model()->fetchByPk($docid);
        if ($version) {  //如果是查看历史版本，合并历史版本数据
            $versionData = OfficialdocVersion::model()->fetchByAttributes(array(
                'docid' => $docid, 'version' => $version));
            $officialDoc = array_merge($officialDoc, $versionData);
        }
        if (!empty($officialDoc)) {
            //如果这篇文章状态是待审核时：如果当前读者是作者本人，可以查看，否者，提示该文章未通过审核
            if (!OfficialdocUtil::checkReadScope($uid, $officialDoc)) {
                $this->error(IBOS::lang('You do not have permission to read the officialdoc'),
                        $this->createUrl('officialdoc/index'));
            }
            $data = ICOfficialdoc::getShowData($officialDoc);
            $signInfo = OfficialdocReader::model()->fetchSignInfo($docid, $uid);
            OfficialdocReader::model()->addReader($docid, $uid);
            Officialdoc::model()->updateClickCount($docid, $data['clickcount']);
            // 是否需要签收
            $needSignUids = Officialdoc::model()->fetchAllUidsByDocId($docid);
            $needSign = in_array($uid, $needSignUids);
            $params = array(
                'data' => $data,
                'signInfo' => $signInfo,
                'dashboardConfig' => IBOS::app()->setting->get('setting/docconfig'),
                'needSign' => $needSign
            );
            if ($data['rcid']) {
                $params['rcType'] = RcType::model()->fetchByPk($data['rcid']);
            }

            if ($officialDoc['status'] == 2) { // 如果是未审核
                $temp[0] = $params['data'];
                $temp = ICOfficialdoc::handleApproval($temp);
                $params['data'] = $temp[0];
                $params['isApprovaler'] = $this->checkIsApprovaler($officialDoc,
                        $uid);
            }
            if (!empty($data['attachmentid'])) {
                $params['attach'] = Attach::getAttach($data['attachmentid']);
            }
            $this->setPageTitle(IBOS::lang('Show officialdoc'));
            $this->setPageState('breadCrumbs',
                    array(
                array('name' => IBOS::lang('Information center')),
                array('name' => IBOS::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                array('name' => IBOS::lang('Show officialdoc'))
            ));
            $this->render('show', $params);
        } else {
            $this->error(IBOS::lang('No permission or officialdoc not exists'),
                    $this->createUrl('officialdoc/index'));
        }
    }

    /**
     * 加载签收情况
     * @return void
     */
    private function getSign() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            $signedInfos = OfficialdocReader::model()->fetchSignedByDocId($docid);
            $signedUsersTemp = array();
            foreach ($signedInfos as $sign) {
                $uid = $sign['uid'];
                $signedUsersTemp[$uid] = User::model()->fetchByUid($uid);
                $signedUsersTemp[$uid]['signInfo'] = $sign;
            }
            $signedUsers = UserUtil::handleUserGroupByDept($signedUsersTemp);
            $params = array(
                'signUsers' => $this->handleShowData($signedUsers),
                'signedCount' => count($signedInfos)
            );
            $signAlias = 'application.modules.officialdoc.views.officialdoc.signDetail';
            $signView = $this->renderPartial($signAlias, $params, true);
            $this->ajaxReturn(array('isSuccess' => true, 'signView' => $signView));
        }
    }

    /**
     * 加载签收情况
     * @return void
     */
    private function getUnSign() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            // 此公文所有需签收的uid
            $uids = Officialdoc::model()->fetchAllUidsByDocId($docid);
            // 已签收uid
            $signedUids = OfficialdocReader::model()->fetchSignedUidsByDocId($docid);
            // 未签收uid
            $unSignedTempUids = array_diff($uids, $signedUids);
            $unSignedUids = User::model()->removeDisableUids($unSignedTempUids);
            $unSignedUsersTemp = User::model()->fetchAllByUids($unSignedUids);
            $unSignedUsers = UserUtil::handleUserGroupByDept($unSignedUsersTemp);
            $params = array(
                'unsignUids' => CJSON::encode($unSignedUids),
                'unsignUsers' => $this->handleShowData($unSignedUsers),
                'unsignedCount' => count($unSignedUids)
            );
            $unsignAlias = 'application.modules.officialdoc.views.officialdoc.unsignDetail';
            $unsignView = $this->renderPartial($unsignAlias, $params, true);
            $this->ajaxReturn(array('isSuccess' => true, 'unsignView' => $unsignView));
        }
    }

    /**
     * 签收提醒
     */
    private function remind() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            $docTitle = Env::getRequest('docTitle');
            $getUids = Env::getRequest('uids');
            $uid = IBOS::app()->user->uid;
            if (empty($getUids)) {
                $this->ajaxReturn(array('isSuccess' => false, 'info' => IBOS::lang('No user to remind')));
            }

            // 发送系统提醒
            $config = array(
                '{name}' => User::model()->fetchRealnameByUid($uid),
                '{url}' => $this->createUrl('officialdoc/show',
                        array('docid' => $docid)),
                '{title}' => $docTitle,
                'id' => $docid,
            );
            if (count($getUids) > 0) {
                Notify::model()->sendNotify($getUids, 'officialdoc_sign_remind',
                        $config, $uid);
            }
            $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Remind succeed')));
        }
    }

    /**
     * 处理签收/未签收数据输出，主要是将自己的部门提到第一位
     * @param array $datas
     */
    private function handleShowData($datas) {
        $user = User::model()->fetchByUid(IBOS::app()->user->uid);
        $self = array();
        foreach ($datas as $deptid => $data) {
            if ($deptid == $user['deptid']) {
                $self[$deptid] = $data;
                unset($datas[$deptid]);
                break;
            }
        }
        if (!empty($self)) {
            $datas = array_merge($self, $datas);
        }
        return $datas;
    }

    /**
     * 加载历史版本
     * @return void
     */
    private function getVersion() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            $versionData = OfficialdocVersion::model()->fetchAllByDocid($docid);
            $this->ajaxReturn($versionData);
        }
    }

    /**
     * 签收处理
     */
    private function sign() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            $uid = IBOS::app()->user->uid;
            OfficialdocReader::model()->updateSignByDocid($docid, $uid);
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => IBOS::lang('Sign for success'),
                'signtime' => date('Y年m月d日 H:i', TIMESTAMP)));
        }
    }

    /**
     * 判断某个uid是否某篇未审核公文的当前审核人
     * @param array $doc 公文数据
     * @param integer $uid 用户id
     * @return boolean
     */
    private function checkIsApprovaler($doc, $uid) {
        $res = false;
        $docApproval = OfficialdocApproval::model()->fetchLastStep($doc['docid']);
        $category = OfficialdocCategory::model()->fetchByPk($doc['catid']);
        if (!empty($category['aid'])) {
            $approval = Approval::model()->fetchByPk($category['aid']);
            $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'],
                    $docApproval['step']);
            if (in_array($uid, $nextApproval['uids'])) {
                $res = true;
            }
        }
        return $res;
    }

    /**
     * 审核公文
     */
    private function verify() {
        if (IBOS::app()->request->isAjaxRequest) {
            $uid = IBOS::app()->user->uid;
            $docids = trim(Env::getRequest('docids'), ',');
            $ids = explode(',', $docids);
            if (empty($ids)) {
                $this->ajaxReturn(array('isSuccess' => false, 'info' => IBOS::lang('Parameters error',
                            'error')));
            }
            $sender = User::model()->fetchRealnameByUid($uid);
            foreach ($ids as $docid) {
                $docApproval = OfficialdocApproval::model()->fetchLastStep($docid);
                if (empty($docApproval)) {
                    $this->verifyComplete($docid, $uid);
                } else {
                    $doc = Officialdoc::model()->fetchByPk($docApproval['docid']);
                    $category = OfficialdocCategory::model()->fetchByPk($doc['catid']);
                    $approval = Approval::model()->fetch("id={$category['aid']}");
                    $curApproval = Approval::model()->fetchNextApprovalUids($approval['id'],
                            $docApproval['step']); // 当前审核到的步骤
                    $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'],
                            $docApproval['step'] + 1); // 下一步应该审核的步骤
                    if (!in_array($uid, $curApproval['uids'])) {
                        $this->ajaxReturn(array('isSuccess' => false, 'info' => IBOS::lang('You do not have permission to verify the official')));
                    }
                    if (!empty($nextApproval)) {
                        if ($nextApproval['step'] == 'publish') { // 已完成标识
                            $this->verifyComplete($docid, $uid);
                        } else { // 记录审核步骤，给下一步签收人发提醒消息
                            OfficialdocApproval::model()->recordStep($docid,
                                    $uid);
                            $config = array(
                                '{sender}' => $sender,
                                '{subject}' => $doc['subject'],
                                '{category}' => $category['name'],
                                '{content}' => $this->renderPartial('remindcontent',
                                        array(
                                    'doc' => $doc,
                                    'author' => $sender,
                                        ), true),
                                '{url}' => $this->createUrl('officialdoc/index',
                                        array('type' => 'notallow'))
                            );
                            // 去掉不在发布范围的审批者
                            foreach ($nextApproval['uids'] as $k => $approvalUid) {
                                if (!OfficialdocUtil::checkReadScope($approvalUid,
                                                $doc)) {
                                    unset($nextApproval['uids'][$k]);
                                }
                            }
                            Notify::model()->sendNotify($nextApproval['uids'],
                                    'officialdoc_verify_message', $config, $uid);
                            Officialdoc::model()->updateAllStatusByDocids($docid,
                                    2, $uid);
                        }
                    }
                }
            }
            $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Verify succeed',
                        'message')));
        }
    }

    /**
     * 全部审核完成动作
     * @param mix $docids 审核的公文id
     * @param integer $uid 最终审核人id
     */
    private function verifyComplete($docid, $uid) {
        Officialdoc::model()->updateAllStatusByDocids($docid, 1, $uid);
        OfficialdocApproval::model()->deleteAll("docid={$docid}");
        // 动态推送
        $doc = Officialdoc::model()->fetchByPk($docid);
        if (!empty($doc)) {
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article']
                    == 1) {
                $publishScope = array('deptid' => $doc['deptid'], 'positionid' => $doc['positionid'],
                    'uid' => $doc['uid']);
                $data = array(
                    'title' => IBOS::lang('Feed title', '',
                            array(
                        '{subject}' => $doc['subject'],
                        '{url}' => IBOS::app()->urlManager->createUrl('officialdoc/officialdoc/show',
                                array('docid' => $doc['docid']))
                    )),
                    'body' => $doc['content'],
                    'actdesc' => IBOS::lang('Post officialdoc'),
                    'userid' => $publishScope['uid'],
                    'deptid' => $publishScope['deptid'],
                    'positionid' => $publishScope['positionid'],
                );
                WbfeedUtil::pushFeed($doc['author'], 'officialdoc',
                        'officialdoc', $doc['docid'], $data);
            }
            //更新积分
            UserUtil::updateCreditByAction('addofficialdoc', $doc['author']);
        }
    }

    /**
     * 退回
     */
    private function back() {
        $uid = IBOS::app()->user->uid;
        $docIds = trim(Env::getRequest('docids'), ',');
        $reason = String::filterCleanHtml(Env::getRequest('reason'));
        $ids = explode(',', $docIds);
        if (empty($ids)) {
            $this->ajaxReturn(array('isSuccess' => false, 'info' => IBOS::lang('Parameters error',
                        'error')));
        }
        $sender = User::model()->fetchRealnameByUid($uid);
        foreach ($ids as $docId) {
            $doc = Officialdoc::model()->fetchByPk($docId);
            $categoryName = OfficialdocCategory::model()->fetchCateNameByCatid($doc['catid']);
            if (!$this->checkIsApprovaler($doc, $uid)) {
                $this->ajaxReturn(array('isSuccess' => false, 'info' => IBOS::lang('You do not have permission to verify the official')));
            }
            $config = array(
                '{sender}' => $sender,
                '{subject}' => $doc['subject'],
                '{category}' => $categoryName,
                '{content}' => $reason,
                '{url}' => $this->createUrl('officialdoc/index',
                        array('type' => 'notallow'))
            );
            Notify::model()->sendNotify($doc['author'], 'official_back_message',
                    $config, $uid);
            OfficialdocBack::model()->addBack($docId, $uid, $reason, TIMESTAMP); // 添加一条退回记录
        }
        $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Operation succeed',
                    'message')));
    }

    /**
     * 移动文章
     */
    private function move() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docids = Env::getRequest('docids');
            $catid = Env::getRequest('catid');
            if (!empty($docids) && !empty($catid)) {
                Officialdoc::model()->updateAllCatidByDocids(ltrim($docids, ','),
                        $catid);
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
        if (IBOS::app()->request->isAjaxRequest) {
            $docids = Env::getRequest('docids');
            $topEndTime = Env::getRequest('topEndTime');
            if (!empty($topEndTime)) {
                $topEndTime = strtotime($topEndTime) + 24 * 60 * 60 - 1;
                Officialdoc::model()->updateTopStatus($docids, 1, TIMESTAMP,
                        $topEndTime);
                $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Top succeed')));
            } else {
                Officialdoc::model()->updateTopStatus($docids, 0, '', '');
                $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Unstuck success')));
            }
        }
    }

    /**
     * 高亮操作
     */
    private function highLight() {
        if (IBOS::app()->request->isAjaxRequest) {
            $docids = trim(Env::getRequest('docids'), ',');
            $highLight = array();
            $highLight['endTime'] = Env::getRequest('highlightEndTime');
            $highLight['bold'] = Env::getRequest('bold');
            $highLight['color'] = Env::getRequest('color');
            $highLight['italic'] = Env::getRequest('italic');
            $highLight['underline'] = Env::getRequest('underline');
            $data = OfficialdocUtil::processHighLightRequestData($highLight);

            if (empty($data['highlightendtime'])) {
                Officialdoc::model()->updateHighlightStatus($docids, 0, '', '');
                $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Unhighlighting success')));
            } else {
                Officialdoc::model()->updateHighlightStatus($docids, 1,
                        $data['highlightstyle'], $data['highlightendtime']);
                $this->ajaxReturn(array('isSuccess' => true, 'info' => IBOS::lang('Highlight succeed')));
            }
        }
    }

    /**
     * 取得套红数据
     * @return void
     */
    private function getRcType() {
        if (IBOS::app()->request->isAjaxRequest) {
            $typeid = Env::getRequest('typeid');
            $rcType = RcType::model()->fetchByPk($typeid);
            $this->ajaxReturn($rcType);
        }
    }

    /**
     * 预览
     */
    private function prewiew() {
        $this->setPageTitle(IBOS::lang('Preview officialdoc'));
        $this->setPageState('breadCrumbs',
                array(
            array('name' => IBOS::lang('Information center')),
            array('name' => IBOS::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
            array('name' => IBOS::lang('Preview officialdoc'))
        ));
        $this->render('prewiew', array('content' => $_POST['content']));
    }

}
