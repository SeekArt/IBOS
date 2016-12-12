<?php

/**
 * 通知模块------通知默认控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzzyb <gzwwb@ibos.com.cn>
 */
/**
 * 通知模块------通知默认控制器，继承OfficialdocBaseController
 * @package application.modules.officialDoc.components
 * @version $Id: OfficialdocOfficialdocController.php 660 2013-06-24 00:58:16Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\controllers;

use application\core\model\Log;
use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Approval;
use application\modules\message\model\Notify;
use application\modules\message\model\NotifyMessage;
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
use CHtml;
use CJSON;

class OfficialdocController extends BaseController
{

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
    public function actionIndex()
    {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'getSign', 'search', 'getUnSign', 'getVersion',
            'getRcType', 'prewiew', 'remind');
        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang('Can not find the path'), $this->createUrl('officialdoc/index'));
        }
        if ($option == 'default') {
            $uid = Ibos::app()->user->uid;
            $catid = intval(Env::getRequest('catid'));
            $childCatIds = '';
            if (!empty($catid)) {
                $this->catId = $catid;
                $childCatIds = OfficialdocCategory::model()->fetchCatidByPid($this->catId, true);
            }
            // 取消已过期的置顶样式标记
            Officialdoc::model()->cancelTop();
            // 取消已过期的高亮文章的样式标记
            Officialdoc::model()->updateIsOverHighLight();
            // 判断是否审核人
            // $aids = OfficialdocCategory::model()->fetchAids();
            // $isApprover = in_array( $uid, Approval::model()->fetchApprovalUidsByIds( $aids ) );
            // $params = array(
            //     'pages' => $datas['pages'],
            //     'officialDocList' => $officialDocList,
            //     'categorySelectOptions' => $this->getCategoryOption(),
            //     'isApprover' => $isApprover
            // );
            $this->setPageTitle(Ibos::lang('Officialdoc'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Information center')),
                array('name' => Ibos::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                array('name' => Ibos::lang('Officialdoc list'))
            ));

            //未签收数
            $params['countNosign'] = OfficialdocUtil::getNoSignNumByUid($uid);
            //未审核数
            $params['countNotAllOw'] = Officialdoc::model()->getOfficialdocCount(OfficialdocUtil::TYPE_NOTALLOW, $uid, $childCatIds, $this->_condition);
            //草稿数
            $params['countDraft'] = Officialdoc::model()->getOfficialdocCount(OfficialdocUtil::TYPE_DRAFT, $uid, $childCatIds, $this->_condition);
            $this->render('list', $params);
        } else {
            $this->$option();
        }
    }

    /**
     * 获取通知列表数据方法
     * @return
     */
    public function actionGetDocList()
    {
        $uid = Ibos::app()->user->uid;
        $type = Env::getRequest('type');
        $catid = intval(Env::getRequest('catid'));
        $childCatIds = '';
        if (!empty($catid)) {
            $this->catId = $catid;
            $childCatIds = OfficialdocCategory::model()->fetchCatidByPid($this->catId, true);
        }

        $this->search();

        if ($type === 'nosign') {
            $condition = OfficialdocUtil::getNoSignDocSqlConditionByUid($uid, $this->_condition);
        } else {
            $condition = OfficialdocUtil::joinListCondition($type, $uid, $childCatIds, $this->_condition);
        }
        NotifyMessage::model()->setReadByUrl($uid, Ibos::app()->getRequest()->getUrl());
        $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '调用成功',
            'data' => $this->handleDocListDataByCondition($condition),
            'draw' => Env::getRequest('draw'),
            'recordsFiltered' => Officialdoc::model()->count($condition),
        ));
    }

    /**
     * 处理通知列表返回数据
     * @param  string $condition 查询条件
     * @return array             列表数据
     */
    private function handleDocListDataByCondition($condition)
    {
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');
        $type = Env::getRequest('type');
        $docList = Officialdoc::model()->fetchAll(array(
            // 'select'    => 'docid, subject, catid, author, uptime, addtime, ishighlight, highlightstyle, clickcount, istop',
            'condition' => $condition,
            'order' => 'istop DESC, addtime DESC',
            'offset' => $start,
            'limit' => $length,
        ));
        // foreach ( $docList as &$doc ) {
        //     $doc = array(
        //         'docid'             => $doc['docid'],
        //         'subject'           => $doc['subject'],
        //         'author'            => $doc['author'],
        //         'catid'             => $doc['catid'],
        //         'uptime'            => $doc['uptime'],
        //         'addtime'           => $doc['addtime'],
        //         'ishighlight'       => $doc['ishighlight'],
        //         'highlightstyle'    => $doc['highlightstyle'],
        //         'clickcount'        => $doc['clickcount'],
        //         'istop'             => $doc['istop'],
        //     );
        // }
        $docList = ICOfficialdoc::getListDatas($docList);
        if ($type == 'notallow') {
            $docList = ICOfficialdoc::handleApproval($docList);
        }
        return $docList;
    }

    /**
     * 新建文章
     */
    public function actionAdd()
    {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'save', 'checkIsAllowPublish');
        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang('Can not find the path'), $this->createUrl('officialdoc/index'));
        }
        if ($option == 'default') {
            if (!empty($_GET['catid'])) {
                $this->catId = $_GET['catid'];
            }
            // 是否是免审人能直接发布
            $allowPublish = OfficialdocCategory::model()->checkIsAllowPublish($this->catId, Ibos::app()->user->uid);
            $aitVerify = OfficialdocCategory::model()->fetchIsProcessByCatid($this->catId);
            $params = array(
                'categoryOption' => $this->getCategoryOption(),
                'dashboardConfig' => Ibos::app()->setting->get('setting/docconfig'),
                'uploadConfig' => Attach::getUploadConfig(),
                'RCData' => RcType::model()->fetchAll(),
                'allowPublish' => $allowPublish,
                'aitVerify' => $aitVerify
            );
            $this->setPageTitle(Ibos::lang('Add officialdoc'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Information center')),
                array('name' => Ibos::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                array('name' => Ibos::lang('Add officialdoc'))
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
    protected function checkIsAllowPublish()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $catid = intval(Env::getRequest('catid'));
            $uid = intval(Env::getRequest('uid'));
            $isAllow = OfficialdocCategory::model()->checkIsAllowPublish($catid, $uid);
            $officialdocCategory = OfficialdocCategory::model()->fetchByPk($catid);
            $checkIsPublish = $officialdocCategory['aid'] == 0 ? false : true;
            $this->ajaxReturn(array('isSuccess' => !!$isAllow, 'checkIsPublish' => $checkIsPublish));
        }
    }

    /**
     * 编辑文章
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        $routes = array('default', 'update', 'top', 'highLight', 'move', 'verify',
            'back');
        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang('Can not find the path'), $this->createUrl('officialdoc/index'));
        }
        if ($option == 'default') {
            $docid = Env::getRequest('docid');
            if (empty($docid)) {
                $this->error(Ibos::lang('Parameters error', 'error'));
            }
            $data = Officialdoc::model()->fetch('docid=:docid', array(':docid' => $docid));
            if (!empty($data)) {
                //取得最新历史版本
                $data['publishScope'] = StringUtil::joinSelectBoxValue($data['deptid'], $data['positionid'], $data['uid'], $data['roleid']);
                $data['ccScope'] = StringUtil::joinSelectBoxValue($data['ccdeptid'], $data['ccpositionid'], $data['ccuid'], $data['ccroleid']);
                // 是否是免审人能直接发布
                $allowPublish = OfficialdocCategory::model()->checkIsAllowPublish($data['catid'], Ibos::app()->user->uid);
                $aitVerify = OfficialdocCategory::model()->fetchIsProcessByCatid($data['catid']);
                $params = array(
                    'data' => $data,
                    'categoryOption' => $this->getCategoryOption(),
                    'dashboard' => Ibos::app()->setting->get('setting/docconfig'),
                    'uploadConfig' => Attach::getUploadConfig(),
                    'RCData' => RcType::model()->fetchAll(),
                    'allowPublish' => $allowPublish,
                    'aitVerify' => $aitVerify
                );
                if (!empty($data['attachmentid'])) {
                    $params['attach'] = Attach::getAttach($data['attachmentid']);
                }
                $this->setPageTitle(Ibos::lang('Edit officialdoc'));
                $this->setPageState('breadCrumbs', array(
                    array('name' => Ibos::lang('Information center')),
                    array('name' => Ibos::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                    array('name' => Ibos::lang('Edit officialdoc'))
                ));
                $this->render('edit', $params);
            }
        } else {
            $this->$option();
        }
    }

    /**
     * 删除通知
     * @return void
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
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
                $this->ajaxReturn(
                    array(
                        'isSuccess' => true,
                        'info' => Ibos::lang('Del succeed', 'message'),
                        'msg' => Ibos::lang('Del succeed', 'message')));
            } else {
                $this->ajaxReturn(
                    array(
                        'isSuccess' => false,
                        'info' => Ibos::lang('Parameters error', 'error'),
                        'msg' => Ibos::lang('Parameters error', 'error')));
            }
        }
    }

    /**
     * 保存通知
     */
    private function save()
    {
        $uid = Ibos::app()->user->uid;
        $data = $_POST;
        $data['subject'] = CHtml::encode($data['subject']);
        $data['docNo'] = CHtml::encode($data['docNo']);
        //发布范围
        $publicScope = StringUtil::handleSelectBoxData($data['publishScope']);
        $data['uid'] = $publicScope['uid'];
        $data['positionid'] = $publicScope['positionid'];
        $data['deptid'] = $publicScope['deptid'];
        $data['roleid'] = $publicScope['roleid'];
        //抄送
        $ccScope = StringUtil::handleSelectBoxData($data['ccScope']);
        $data['ccuid'] = $ccScope['uid'];
        $data['ccpositionid'] = $ccScope['positionid'];
        $data['ccdeptid'] = $ccScope['deptid'];
        $data['ccroleid'] = $ccScope['roleid'];
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
        // $this->addOfficialdocReaderList( $docId, $data );
        // 消息提醒
        $user = User::model()->fetchByUid($uid);
        $officialdoc = Officialdoc::model()->fetchByPk($docId);
        $categoryName = OfficialdocCategory::model()->fetchCateNameByCatid($officialdoc['catid']);
        if ($data['status'] == '1') {
            $publishScope = array(
                'deptid' => $officialdoc['deptid'] . ',' . $data['ccdeptid'],
                'positionid' => $officialdoc['positionid'] . ',' . $data['ccpositionid'],
                'uid' => $officialdoc['uid'] . ',' . $data['ccuid'],
                'roleid' => $officialdoc['roleid'] . ',' . $data['ccroleid']
            );
            $uidArr = OfficialdocUtil::getScopeUidArr($publishScope);
            $config = array(
                '{sender}' => $user['realname'],
                '{category}' => $categoryName,
                '{subject}' => $officialdoc['subject'],
                '{content}' => $this->renderPartial('remindcontent', array(
                    'doc' => $officialdoc,
                    'author' => $user['realname'],
                ), true),
                '{orgContent}' => StringUtil::filterCleanHtml($officialdoc['content']),
                '{url}' => Ibos::app()->urlManager->createUrl('officialdoc/officialdoc/show', array('docid' => $docId)),
                'id' => $docId,
            );
            if (count($uidArr) > 0) {
                Notify::model()->sendNotify($uidArr, 'officialdoc_message', $config, $uid);
            }
            // 动态推送
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                $publishScope = array(
                    'deptid' => $officialdoc['deptid'],
                    'positionid' => $officialdoc['positionid'],
                    'uid' => $officialdoc['uid'],
                    'roleid' => $officialdoc['roleid'],
                );
                $data = array(
                    'title' => Ibos::lang('Feed title', '', array(
                        '{subject}' => $officialdoc['subject'],
                        '{url}' => Ibos::app()->urlManager->createUrl('officialdoc/officialdoc/show', array('docid' => $docId))
                    )),
                    'body' => $officialdoc['subject'],
                    'actdesc' => Ibos::lang('Post officialdoc'),
                    'userid' => $publishScope['uid'],
                    'deptid' => $publishScope['deptid'],
                    'positionid' => $publishScope['positionid'],
                    'roleid' => $publishScope['roleid'],
                );
                WbfeedUtil::pushFeed($uid, 'officialdoc', 'officialdoc', $docId, $data);
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
            'user' => Ibos::app()->user->username,
            'ip' => Ibos::app()->setting->get('clientip'),
            'isSuccess' => 1
        );
        Log::write($log, 'action', 'module.officialdoc.officialdoc.add');
        $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('officialdoc/index'));
    }

    /**
     * 发送待审核通知处理方法(新增与编辑都可处理)
     * @param array $doc 通知数据
     * @param integer $uid 发送人id
     */
    private function sendPending($doc, $uid)
    {
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
                    '{url}' => $this->createUrl('officialdoc/index', array('type' => 'notallow', 'catid' => 0)),
                    '{content}' => $this->renderPartial('remindcontent', array(
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
                Notify::model()->sendNotify($approval['uids'], 'officialdoc_verify_message', $config, $uid);
            }
        }
    }

    /**
     * 通知编辑功能
     */
    private function update()
    {
        if (Env::submitCheck('formhash')) {
            $docid = $_POST['docid'];
            $uid = Ibos::app()->user->uid;
            $data = $_POST;
            $data['subject'] = CHtml::encode($data['subject']);
            $data['docNo'] = CHtml::encode($data['docNo']);
            //发布范围
            $publicScope = StringUtil::handleSelectBoxData($data['publishScope']);
            $data['uid'] = $publicScope['uid'];
            $data['positionid'] = $publicScope['positionid'];
            $data['deptid'] = $publicScope['deptid'];
            $data['roleid'] = $publicScope['roleid'];

            //抄送
            $ccScope = StringUtil::handleSelectBoxData($data['ccScope']);
            $data['ccuid'] = $ccScope['uid'];
            $data['ccpositionid'] = $ccScope['positionid'];
            $data['ccdeptid'] = $ccScope['deptid'];
            $data['ccroleid'] = $ccScope['roleid'];

            $data['approver'] = $uid;
            $data['docno'] = CHtml::encode($_POST['docNo']);
            $data['commentstatus'] = isset($data['commentstatus']) ? $data['commentstatus'] : 0;
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
                Officialdoc::model()->modify($docid, array('attachmentid' => $attachmentid));
            }
            $attributes = Officialdoc::model()->create($data);
            Officialdoc::model()->updateByPk($data['docid'], $attributes);
            $doc = Officialdoc::model()->fetchByPk($data['docid']);
            $this->sendPending($doc, $uid);

            OfficialdocBack::model()->deleteAll("docid = {$docid}");

            $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('officialdoc/index'));
        }
    }

    /**
     * 搜索
     * @return void
     */
    private function search()
    {
        $search = Env::getRequest('search');

        if (isset($search['keyword'])) {
            $this->_condition = OfficialdocUtil::joinSearchCondition($search, $this->_condition);
        } else if (isset($search['value'])) {
            //添加对keyword转义，防止SQL错误
            $keyword = CHtml::encode($search['value']);
            $this->_condition = " subject LIKE '%$keyword%' ";
        }
    }

    /**
     * 查看动作
     */
    public function actionShow()
    {
        if (Env::getRequest('op') == 'sign') {
            $this->sign();
            exit();
        }
        $uid = Ibos::app()->user->uid;
        $docid = intval(Env::getRequest('docid'));
        $version = Env::getRequest('version');
        if (empty($docid)) {
            $this->error(Ibos::lang('Parameters error', 'error'));
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
                $this->error(Ibos::lang('You do not have permission to read the officialdoc'), $this->createUrl('officialdoc/index'));
            }
            //改这个addReader的顺序的时候，同时更新了主表的readers的字段，所以为了获取最新数据，必须把这行提前
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
                'dashboardConfig' => Ibos::app()->setting->get('setting/docconfig'),
                'needSign' => $needSign
            );
            if ($data['rcid']) {
                $params['rcType'] = RcType::model()->fetchByPk($data['rcid']);
            }

            if ($officialDoc['status'] == 2) { // 如果是未审核
                $temp[0] = $params['data'];
                $temp = ICOfficialdoc::handleApproval($temp);
                $params['data'] = $temp[0];
                $params['isApprovaler'] = $this->checkIsApprovaler($officialDoc, $uid);
            }
            if (!empty($data['attachmentid'])) {
                $params['attach'] = Attach::getAttach($data['attachmentid']);
            }
            $readers = $officialDoc['readers'];
            $readersArray = explode(',', $readers);
            array_push($readersArray, $uid);
            $readersString = implode(',', array_unique(array_filter($readersArray)));
            Officialdoc::model()->updateAll(array('readers' => $readersString), " `docid` = '{$docid}' ");
            $params['data']['readers'] = $readersString;
            $this->setPageTitle(Ibos::lang('Show officialdoc'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Information center')),
                array('name' => Ibos::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
                array('name' => Ibos::lang('Show officialdoc'))
            ));
            NotifyMessage::model()->setReadByUrl(Ibos::app()->user->uid, Ibos::app()->getRequest()->getUrl());
            $this->render('show', $params);
        } else {
            $this->error(Ibos::lang('No permission or officialdoc not exists'), $this->createUrl('officialdoc/index'));
        }
    }

    /**
     * 加载签收情况
     * @return void
     */
    private function getSign()
    {
        if (Ibos::app()->request->isAjaxRequest) {
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
     * 加载未签收情况
     * @return void
     */
    private function getUnSign()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            // 此通知所有需签收的uid
            $uids = Officialdoc::model()->fetchAllUidsByDocId($docid);
            // 已签收uid
            $signedUids = OfficialdocReader::model()->fetchSignedUidsByDocId($docid);
            // 未签收uid
            $unSignedTempUids = array_diff($uids, $signedUids);
            $unSignedUids = User::model()->findNotDisabledUid($unSignedTempUids);
            $unSignedUsersTemp = array();
            for ($i = 0; $i < count($unSignedUids); $i++) {
                $uid = $unSignedUids[$i];
                $unSignedUsersTemp[$uid] = User::model()->fetchByUid($uid);
            }
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
    private function remind()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            $docTitle = Env::getRequest('docTitle');
            $getUids = Env::getRequest('uids');
            $uid = Ibos::app()->user->uid;
            $doc = Officialdoc::model()->fetchByPk($docid);
            if (empty($getUids)) {
                $this->ajaxReturn(
                    array(
                        'isSuccess' => false,
                        'info' => Ibos::lang('No user to remind'),
                        'msg' => Ibos::lang('No user to remind')));
            }

            // 发送系统提醒
            $config = array(
                '{name}' => User::model()->fetchRealnameByUid($uid),
                '{url}' => $this->createUrl('officialdoc/show', array('docid' => $docid)),
                '{title}' => $docTitle,
                '{content}' => $doc['content'],
                'id' => $docid,
            );
            if (count($getUids) > 0) {
                Notify::model()->sendNotify($getUids, 'officialdoc_sign_remind', $config, $uid);
            }
            $this->ajaxReturn(
                array(
                    'isSuccess' => true,
                    'info' => Ibos::lang('Remind succeed'),
                    'msg' => Ibos::lang('Remind succeed')));
        }
    }

    /**
     * 处理签收/未签收数据输出，主要是将自己的部门提到第一位
     * @param array $datas
     */
    private function handleShowData($datas)
    {
        $user = User::model()->fetchByUid(Ibos::app()->user->uid);
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
    private function getVersion()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            $versionData = OfficialdocVersion::model()->fetchAllByDocid($docid);
            $this->ajaxReturn($versionData);
        }
    }

    /**
     * 签收处理
     */
    private function sign()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = Env::getRequest('docid');
            $uid = Ibos::app()->user->uid;
            OfficialdocReader::model()->updateSignByDocid($docid, $uid);
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Sign for success'),
                'signtime' => date('Y年m月d日 H:i', TIMESTAMP)));
        }
    }

    /**
     * 判断某个uid是否某篇未审核通知的当前审核人
     * @param array $doc 通知数据
     * @param integer $uid 用户id
     * @return boolean
     */
    private function checkIsApprovaler($doc, $uid)
    {
        $res = false;
        $docApproval = OfficialdocApproval::model()->fetchLastStep($doc['docid']);
        $category = OfficialdocCategory::model()->fetchByPk($doc['catid']);
        if (!empty($category['aid'])) {
            $approval = Approval::model()->fetchByPk($category['aid']);
            $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $docApproval['step']);
            if (in_array($uid, $nextApproval['uids'])) {
                $res = true;
            }
        }
        return $res;
    }

    /**
     * 审核通知
     */
    private function verify()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = Ibos::app()->user->uid;
            $docids = trim(Env::getRequest('docids'), ',');
            $ids = explode(',', $docids);
            if (empty($ids)) {
                $this->ajaxReturn(
                    array(
                        'isSuccess' => false,
                        'info' => Ibos::lang('Parameters error', 'error'),
                        'msg' => Ibos::lang('Parameters error', 'error')));
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
                    $curApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $docApproval['step']); // 当前审核到的步骤
                    $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $docApproval['step'] + 1); // 下一步应该审核的步骤
                    if (!in_array($uid, $curApproval['uids'])) {
                        $this->ajaxReturn(
                            array(
                                'isSuccess' => false,
                                'info' => Ibos::lang('You do not have permission to verify the official'),
                                'msg' => Ibos::lang('You do not have permission to verify the official'),));
                    }
                    if (!empty($nextApproval)) {
                        if ($nextApproval['step'] == 'publish') { // 已完成标识
                            $this->verifyComplete($docid, $uid);
                        } else { // 记录审核步骤，给下一步签收人发提醒消息
                            OfficialdocApproval::model()->recordStep($docid, $uid);
                            $config = array(
                                '{sender}' => $sender,
                                '{subject}' => $doc['subject'],
                                '{category}' => $category['name'],
                                '{content}' => $this->renderPartial('remindcontent', array(
                                    'doc' => $doc,
                                    'author' => $sender,
                                ), true),
                                '{url}' => $this->createUrl('officialdoc/index', array('type' => 'notallow'))
                            );
                            // 去掉不在发布范围的审批者
                            foreach ($nextApproval['uids'] as $k => $approvalUid) {
                                if (!OfficialdocUtil::checkReadScope($approvalUid, $doc)) {
                                    unset($nextApproval['uids'][$k]);
                                }
                            }
                            Notify::model()->sendNotify($nextApproval['uids'], 'officialdoc_verify_message', $config, $uid);
                            //审核人为下一个审核该通知的用户（当前审核已通过）
                            if (isset($nextApproval['uids']) && isset($nextApproval['uids'][0])) {
                                $approver = $nextApproval['uids'][0];
                            } else {
                                $approval = $uid;
                            }
                            Officialdoc::model()->updateAllStatusByDocids($docid, 2, $approver);
                        }
                    }
                }
            }
            $this->ajaxReturn(array('isSuccess' => true,
                'info' => Ibos::lang('Verify succeed', 'message'),
                'msg' => Ibos::lang('Verify succeed', 'message'),));
        }
    }

    /**
     * 全部审核完成动作
     * @param mix $docids 审核的通知id
     * @param integer $uid 最终审核人id
     */
    private function verifyComplete($docid, $uid)
    {
        Officialdoc::model()->updateAllStatusByDocids($docid, 1, $uid);
        OfficialdocApproval::model()->deleteAll("docid={$docid}");
        // 动态推送
        $doc = Officialdoc::model()->fetchByPk($docid);
        if (!empty($doc)) {
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                $publishScope = array(
                    'deptid' => $doc['deptid'],
                    'positionid' => $doc['positionid'],
                    'uid' => $doc['uid'],
                    'roleid' => $doc['roleid'],
                );
                $data = array(
                    'title' => Ibos::lang('Feed title', '', array(
                        '{subject}' => $doc['subject'],
                        '{url}' => Ibos::app()->urlManager->createUrl('officialdoc/officialdoc/show', array('docid' => $doc['docid']))
                    )),
                    'body' => $doc['content'],
                    'actdesc' => Ibos::lang('Post officialdoc'),
                    'userid' => $publishScope['uid'],
                    'deptid' => $publishScope['deptid'],
                    'positionid' => $publishScope['positionid'],
                    'roleid' => $publishScope['roleid'],
                );
                //更新积分
                WbfeedUtil::pushFeed($doc['author'], 'officialdoc', 'officialdoc', $doc['docid'], $data);
            }
            //下面的代码主要是审核全部完成后，应该给接收通知的用户发送推送消息
            $category = OfficialdocCategory::model()->fetchByPk($doc['catid']);
            $user = User::model()->fetchByPk($doc['author']);
            $publishScope = array(
                'deptid' => $doc['deptid'] . ',' . $doc['ccdeptid'],
                'positionid' => $doc['positionid'] . ',' . $doc['ccpositionid'],
                'uid' => $doc['uid'] . ',' . $doc['ccuid'],
                'roleid' => $doc['roleid'] . ',' . $doc['ccroleid']
            );
            $uidArr = OfficialdocUtil::getScopeUidArr($publishScope);
            $config = array(
                '{sender}' => $user['realname'],
                '{category}' => $category['name'],
                '{subject}' => $doc['subject'],
                '{content}' => $this->renderPartial('remindcontent', array(
                    'doc' => $doc,
                    'author' => $user['realname'],
                ), true),
                '{orgContent}' => StringUtil::filterCleanHtml($doc['content']),
                '{url}' => Ibos::app()->urlManager->createUrl('officialdoc/officialdoc/show', array('docid' => $docid)),
                'id' => $docid,
            );
            if (count($uidArr) > 0) {
                Notify::model()->sendNotify($uidArr, 'officialdoc_message', $config);
            }
            //更新积分
            UserUtil::updateCreditByAction('addofficialdoc', $doc['author']);
        }
    }

    /**
     * 退回
     */
    private function back()
    {
        $uid = Ibos::app()->user->uid;
        $docIds = trim(Env::getRequest('docids'), ',');
        $reason = StringUtil::filterCleanHtml(Env::getRequest('reason'));
        $ids = explode(',', $docIds);
        if (empty($ids)) {
            $this->ajaxReturn(array('isSuccess' => false,
                'info' => Ibos::lang('Parameters error', 'error'),
                'msg' => Ibos::lang('Parameters error', 'error'),));
        }
        $sender = User::model()->fetchRealnameByUid($uid);
        foreach ($ids as $docId) {
            $doc = Officialdoc::model()->fetchByPk($docId);
            $categoryName = OfficialdocCategory::model()->fetchCateNameByCatid($doc['catid']);
            if (!$this->checkIsApprovaler($doc, $uid)) {
                $this->ajaxReturn(array('isSuccess' => false,
                    'info' => Ibos::lang('You do not have permission to verify the official'),
                    'msg' => Ibos::lang('You do not have permission to verify the official')));
            }
            $config = array(
                '{sender}' => $sender,
                '{subject}' => $doc['subject'],
                '{category}' => $categoryName,
                '{content}' => $reason,
                //'{url}' => $this->createUrl( 'officialdoc/index', array( 'type' => 'notallow', 'catid' => 0 ) )
                '{url}' => $this->createUrl('officialdoc/show', array('docid' => $docId))
            );
            Notify::model()->sendNotify($doc['author'], 'official_back_message', $config, $uid);
            OfficialdocBack::model()->addBack($docId, $uid, $reason, TIMESTAMP); // 添加一条退回记录
        }
        $this->ajaxReturn(array('isSuccess' => true,
            'info' => Ibos::lang('Operation succeed', 'message'),
            'msg' => Ibos::lang('Operation succeed', 'message'),
        ));
    }

    /**
     * 移动文章
     */
    private function move()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docids = Env::getRequest('docids');
            $catid = Env::getRequest('catid');
            if (!empty($docids) && !empty($catid)) {
                Officialdoc::model()->updateAllCatidByDocids(ltrim($docids, ','), $catid);
                $this->ajaxReturn(array('isSuccess' => true));
            } else {
                $this->ajaxReturn(array('isSuccess' => false));
            }
        }
    }

    /**
     * 置顶操作
     */
    private function top()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docids = Env::getRequest('docids');
            $topEndTime = Env::getRequest('topEndTime');
            if (!empty($topEndTime)) {
                $topEndTime = strtotime($topEndTime) + 24 * 60 * 60 - 1;
                Officialdoc::model()->updateTopStatus($docids, 1, TIMESTAMP, $topEndTime);
                $this->ajaxReturn(
                    array(
                        'isSuccess' => true,
                        'info' => Ibos::lang('Top succeed'),
                        'msg' => Ibos::lang('Top succeed')));
            } else {
                Officialdoc::model()->updateTopStatus($docids, 0, '', '');
                $this->ajaxReturn(
                    array(
                        'isSuccess' => true,
                        'info' => Ibos::lang('Unstuck success'),
                        'msg' => Ibos::lang('Unstuck success')));
            }
        }
    }

    /**
     * 高亮操作
     */
    private function highLight()
    {
        if (Ibos::app()->request->isAjaxRequest) {
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
                $this->ajaxReturn(
                    array(
                        'isSuccess' => true,
                        'info' => Ibos::lang('Unhighlighting success'),
                        'msg' => Ibos::lang('Unhighlighting success')));
            } else {
                Officialdoc::model()->updateHighlightStatus($docids, 1, $data['highlightstyle'], $data['highlightendtime']);
                $this->ajaxReturn(
                    array(
                        'isSuccess' => true,
                        'info' => Ibos::lang('Highlight succeed'),
                        'msg' => Ibos::lang('Highlight succeed')));
            }
        }
    }

    /**
     * 取得套红数据
     * @return void
     */
    private function getRcType()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeid = Env::getRequest('typeid');
            $rcType = RcType::model()->fetchByPk($typeid);
            $this->ajaxReturn($rcType);
        }
    }

    /**
     * 预览
     */
    private function prewiew()
    {
        $this->setPageTitle(Ibos::lang('Preview officialdoc'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Information center')),
            array('name' => Ibos::lang('Officialdoc'), 'url' => $this->createUrl('officialdoc/index')),
            array('name' => Ibos::lang('Preview officialdoc'))
        ));
        $this->render('prewiew', array('content' => $_POST['content']));
    }

    public function actionMove()
    {
        $move = $this->getCategoryOption();
        $param = array(
            'move' => $move,
        );
        return $this->renderPartial('move', $param);
    }

}
