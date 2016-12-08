<?php

/**
 * 任务指派模块------任务指派默认控制器
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 任务指派模块------任务指派默认控制器，继承AssignmentBaseController
 * @package application.modules.assignment.controllers
 * @version $Id: DefaultController.php 3297 2014-04-29 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\controllers;

use application\core\model\Log;
use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\assignment\core\AssignmentOpApi;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\model\AssignmentApply;
use application\modules\assignment\model\AssignmentLog;
use application\modules\assignment\model\AssignmentRemind;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
use application\modules\calendar\model\Calendars;
use application\modules\dashboard\model\Stamp;
use application\modules\message\model\NotifyMessage;
use application\modules\user\model\User;
use CJSON;

class DefaultController extends BaseController
{

    /**
     * 图章id(暂定3个，4干得不错，2有进步，3继续努力)
     * @var array
     */
    private $_stamps = array(4, 2, 3);

    /**
     * 添加任务
     */
    public function actionAdd()
    {
        //添加是否是post请求类型判断
        //如果不是post类型请求，就不用执行&&后面的代码
        //防止直接访问该页面时在Env::submitCheck里面判断$_SERVER['HTTP_REFERER']错误
        //判断$_SERVER['HTTP_REFERER']错误在其他的页面直接访问时也有可能出现
        if (Ibos::app()->request->isPostRequest && Env::submitCheck('addsubmit')) {
            $this->beforeSave($_POST); // 空值判断
            $_POST['uid'] = Ibos::app()->user->uid;
            $assignmentId = AssignmentOpApi::getInstance()->addAssignment($_POST);
            $assignment = Assignment::model()->fetchByPk($assignmentId);
            $returnData = array(
                'charge' => User::model()->fetchByUid($assignment['chargeuid']),
                'id' => $assignmentId,
                'subject' => $assignment['subject'],
                'time' => date('m月d日 H:i', $assignment['starttime']) . '--' . date('m月d日 H:i', $assignment['endtime'])
            );
            // 日志记录
            $log = array(
                'user' => Ibos::app()->user->username,
                'ip' => Ibos::app()->setting->get('clientip')
            , 'isSuccess' => 1
            );
            Log::write($log, 'action', 'module.assignment.default.add');
            $this->ajaxReturn(array('isSuccess' => true, 'data' => $returnData));
        }
    }

    /**
     * 编辑任务
     */
    public function actionEdit()
    {
        $uid = Ibos::app()->user->uid;
        if (!Env::submitCheck('updatesubmit')) {
            $assignmentId = intval(Env::getRequest('id'));
            $checkRes = $this->checkAvailableById($assignmentId);
            if (!$checkRes['isSuccess']) {
                $this->ajaxReturn($checkRes);
            }
            $assignment = Assignment::model()->fetchByPk($assignmentId);
            // 只有发起人有权编辑任务
            if ($uid != $assignment['designeeuid']) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('You donot have permission to edit')));
            }
            // 附件
            if (!empty($assignment['attachmentid'])) {
                $assignment['attachs'] = Attach::getAttach($assignment['attachmentid']);
            }
            $assignment['starttime'] = empty($assignment['starttime']) ? '' : date('Y-m-d H:i', $assignment['starttime']);
            $assignment['endtime'] = empty($assignment['endtime']) ? '' : date('Y-m-d H:i', $assignment['endtime']);
            $assignment['chargeuid'] = StringUtil::wrapId($assignment['chargeuid']);
            $assignment['participantuid'] = StringUtil::wrapId($assignment['participantuid']);
            $assignment['lang'] = Ibos::getLangSource('assignment.default');
            $assignment['assetUrl'] = Ibos::app()->assetManager->getAssetsUrl('assignment');
            $editAlias = 'application.modules.assignment.views.default.edit';
            $editView = $this->renderPartial($editAlias, $assignment, true);
            echo $editView;
        } else {
            $assignmentId = intval(Env::getRequest('id'));
            $assignment = Assignment::model()->fetchByPk($assignmentId);
            $this->beforeSave($_POST); // 空值判断
            $data = AssignmentUtil::handlePostData($_POST);
            $data['updatetime'] = TIMESTAMP;
            $updateSuccess = Assignment::model()->updateByPk($assignmentId, $data);
            if ($updateSuccess) {
                $opApi = AssignmentOpApi::getInstance();
                // 更新附件
                Attach::updateAttach($data['attachmentid']);
                // 如果修改了负责人，发送消息提醒
                if ($data['chargeuid'] != $assignment['chargeuid']) {
                    $chargeuid = StringUtil::getId($_POST['chargeuid']);
                    $participantuid = StringUtil::getId($_POST['participantuid']);
                    $uidArr = array_merge($participantuid, $chargeuid);
                    $opApi->sendNotify($uid, $assignmentId, $data['subject'], $uidArr, 'assignment_new_message');
                }
                // 发表一条编辑评论
                $opApi->addStepComment($uid, $assignmentId, Ibos::lang('Eidt the assignment'));
                // 记录日志
                AssignmentLog::model()->addLog($uid, $assignmentId, 'edit', Ibos::lang('Eidt the assignment'));
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Update succeed', 'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Update failed', 'message')));
            }
        }
    }

    /**
     * 删除任务
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $assignmentId = intval(Env::getRequest('id'));
            $checkRes = $this->checkAvailableById($assignmentId);
            if (!$checkRes['isSuccess']) {
                $this->ajaxReturn($checkRes);
            }
            $assignment = Assignment::model()->fetchByPk($assignmentId);
            // 只有发起人有权删除任务
            $uid = Ibos::app()->user->uid;
            if ($uid != $assignment['designeeuid']) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('You donot have permission to delete')));
            }
            // 删除附件
            if (!empty($assignment['attachmentid'])) {
                Attach::delAttach($assignment['attachmentid']);
            }
            //若已安装日程，删除关联表数据和有提醒时间的日程
            if ($this->getIsInstallCalendar() && !empty($assignment['remindtime'])) {
                Calendars::model()->deleteALL("`calendarid` IN(select `calendarid` from {{assignment_remind}} where assignmentid = {$assignmentId}) ");
                AssignmentRemind::model()->deleteAll("assignmentid = {$assignmentId}");
            }
            // 记录日志
            AssignmentLog::model()->addLog($uid, $assignmentId, 'del', Ibos::lang('Delete the assignment'));
            // 删除任务
            Assignment::model()->deleteByPk($assignmentId);
            AssignmentApply::model()->deleteAll("assignmentid = {$assignmentId}");
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Del succeed', 'message')));
        }
    }

    /**
     * 任务详细页
     */
    public function actionShow()
    {
        $op = Env::getRequest('op');
        $uid = Ibos::app()->user->uid;
        if (empty($op)) {
            $assignmentId = intval(Env::getRequest('assignmentId'));
            // 参数检查
            $checkRes = $this->checkAvailableById($assignmentId);
            if (!$checkRes['isSuccess']) {
                $this->error($checkRes['msg'], $this->createUrl('unfinished/index'));
            }
            $assignment = Assignment::model()->fetchByPk($assignmentId);
            // 权限检查
            if (!$this->checkShowPermissions($assignment) && !$this->checkIsSup($assignment)) {
                $this->error(Ibos::lang('You donot have permission to view'), $this->createUrl('unfinished/index'));
            }
            // 取出附件
            if (!empty($assignment['attachmentid'])) {
                $assignment['attach'] = Attach::getAttach($assignment['attachmentid']);
            }
            // 图章
            if (!empty($assignment['stamp'])) {
                $assignment['stampUrl'] = Stamp::model()->fetchStampById($assignment['stamp']);
            }
            // 拿出延期、取消申请记录
            $apply = AssignmentApply::model()->fetchByAttributes(array('assignmentid' => $assignmentId));
            $applyData = $this->handleApplyData($assignmentId, $apply);
            // 是否指派人(用于此任务延期、取消处理)
            $isDesigneeuid = $this->checkIsDesigneeuid($assignment['designeeuid']);
            // 是否负责人
            $isChargeuid = $this->checkIsChargeuid($assignment['chargeuid']);
            // 如果是未读状态，改变成进行中
            if ($isChargeuid && $assignment['status'] == 0) {
                Assignment::model()->modify($assignmentId, array('status' => 1));
                $assignment['status'] = 1;
            }
            // 记录日志
            AssignmentLog::model()->addLog($uid, $assignmentId, 'view', Ibos::lang('View the assignment'));
            // 参与人
            $participantuidArr = explode(',', $assignment['participantuid']);
            $participantuid = array_filter($participantuidArr, create_function('$v', 'return !empty($v);'));
            $reminds = AssignmentRemind::model()->fetchAllByUid($uid);
            $assignment['remindtime'] = in_array($assignmentId, array_keys($reminds)) ? $reminds[$assignmentId] : 0;
            $params = array(
                'isDesigneeuid' => $isDesigneeuid,
                'isChargeuid' => $isChargeuid,
                'designee' => User::model()->fetchByUid($assignment['designeeuid']), // 发起人
                'charge' => User::model()->fetchByUid($assignment['chargeuid']), // 负责人
                'participantCount' => count($participantuid),
                'participant' => User::model()->fetchRealnamesByUids($participantuid, '、'),
                'assignment' => AssignmentUtil::handleShowData($assignment),
                'applyData' => CJSON::encode($applyData)
            );
            $this->setPageTitle(Ibos::lang('See the assignment details'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Assignment'), 'url' => $this->createUrl('unfinished/index')),
                array('name' => Ibos::lang('Assignment details'))
            ));
            NotifyMessage::model()->setReadByUrl($uid, Ibos::app()->getRequest()->getUrl());
            $this->render('show', $params);
        } else {
            $this->$op();
        }
    }

    /**
     * 添加、修改提交前负责人、任务内容是否为空检查
     */
    protected function beforeSave($postData)
    {
        if (empty($postData['chargeuid'])) {
            $this->error(Ibos::lang('Head cannot be empty'), $this->createUrl('unfinished/index'));
        }
        if (empty($postData['subject'])) {
            $this->error(Ibos::lang('Content cannot be empty'), $this->createUrl('unfinished/index'));
        }
        if (empty($postData['endtime'])) {
            $this->error(Ibos::lang('The end time cannot be empty'), $this->createUrl('unfinished/index'));
        }
    }

    /**
     * 处理取消、延期申请的前台数据
     * @param array $apply 申请记录
     * @return array
     */
    protected function handleApplyData($assignmentId, $apply)
    {
        $applyData = array();
        if (!empty($apply)) {
            if ($apply['isdelay']) { // 延期申请
                $applyData = array('id' => $assignmentId, 'uid' => $apply['uid'],
                    'reason' => $apply['delayreason'], 'startTime' => date('m月d日 H:i', $apply['delaystarttime']), 'endTime' => date('m月d日 H:i', $apply['delayendtime']));
            } else { // 取消申请
                $applyData = array('id' => $assignmentId, 'uid' => $apply['uid'],
                    'reason' => $apply['cancelreason']);
            }
        }
        return $applyData;
    }

    /**
     * 获取图章信息
     * @return array
     */
    public function getStamps()
    {
        $stamps = array();
        foreach ($this->_stamps as $id) {
            $stamp = Stamp::model()->fetchByPk($id);
            $stamps[] = array(
                'path' => $stamp['icon'],
                'stampPath' => $stamp['stamp'],
                'stamp' => $stamp['stamp'],
                'title' => $stamp['code'],
                'value' => $id
            );
        }
        return $stamps;
    }

}
