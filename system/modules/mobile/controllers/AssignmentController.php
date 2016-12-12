<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\assignment\core\AssignmentOpApi;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\model\AssignmentApply;
use application\modules\assignment\model\AssignmentLog;
use application\modules\assignment\model\AssignmentRemind;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
use application\modules\assignment\controllers\DefaultController;
use application\modules\dashboard\model\Stamp;
use application\modules\user\model\User;
use application\modules\calendar\model\Calendars;
use CJSON;

class AssignmentController extends DefaultController
{

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     * @param string $routes
     * @return boolean
     */
    public function filterRoutes($routes)
    {
        return true;
    }

    /**
     * 添加任务
     */
    public function actionAdd()
    {
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
        $this->ajaxReturn(array('isSuccess' => true, 'data' => $returnData));
    }

    /**
     * 编辑任务
     */
    public function actionEdit()
    {
        $uid = Ibos::app()->user->uid;
        if (Ibos::app()->request->getIsPostRequest()) {
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
                $opApi->addStepComment($uid, $assignmentId, Ibos::lang('Eidt the assignment', 'assignment.default'));
                // 记录日志
                AssignmentLog::model()->addLog($uid, $assignmentId, 'edit', Ibos::lang('Eidt the assignment', 'assignment.default'));
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Update succeed', 'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Update failed', 'message')));
            }
        } else {
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
            // $editAlias = 'application.modules.assignment.views.default.edit';
            // $editView = $this->renderPartial( $editAlias, $assignment, true );
            // echo $editView;
            $this->ajaxReturn(array('isSuccess' => true, 'data' => $assignment));
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
                'applyData' => $applyData
            );

            $this->ajaxReturn(array(
                'isSuccess' => 1,
                'data' => $params,
                'stamps' => $this->getStamps()
            ));
        } else {
            $this->$op();
        }
    }

}
