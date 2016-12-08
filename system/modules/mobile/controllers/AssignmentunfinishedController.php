<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\assignment\core\AssignmentOpApi;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\model\AssignmentApply;
use application\modules\assignment\model\AssignmentLog;
use application\modules\assignment\model\AssignmentRemind;
use application\modules\assignment\controllers\UnfinishedController;
use application\modules\dashboard\model\Stamp;
use application\modules\user\utils\User as UserUtil;
use application\modules\calendar\model\Calendars;


class AssignmentUnfinishedController extends UnfinishedController
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
     * 未完成的任务列表页
     */
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $params = $this->getUnfinishedDataByUid($uid);
        // $params['uploadConfig'] = Attach::getUploadConfig();

        $this->ajaxReturn($params);
    }

    /**
     * 下属任务列表
     */
    public function actionSubList()
    {
        if (Env::getRequest('op') == 'getsubordinates') {
            $this->getsubordinates();
            exit();
        }
        $getUid = intval(Env::getRequest('uid'));
        if (!$getUid) {
            $deptArr = UserUtil::getManagerDeptSubUserByUid(Ibos::app()->user->uid); //取得管理的部门和下属
            if (!empty($deptArr)) {  // 取得管理的第一个部门的第一个下属
                $firstDept = reset($deptArr);
                $uid = $firstDept['user'][0]['uid'];
            } else {
                $this->error(Ibos::lang('You do not subordinate'), $this->createUrl('schedule/index'));
            }
        } else {
            $uid = $getUid;
        }
        // 权限判断
        if (!UserUtil::checkIsSub(Ibos::app()->user->uid, $uid)) {
            $this->error(Ibos::lang('No permission to view schedule'), $this->createUrl('schedule/index'));
        }
        $params = $this->getUnfinishedDataByUid($uid);
        $params['uid'] = $uid;
        $this->ajaxReturn($params);
    }

    /**
     * 得到某个用户的下属，取5条
     */
    protected function getsubordinates()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = intval(Env::getRequest('uid'));
            $getItem = Env::getRequest('item');
            $item = empty($getItem) ? 5 : $getItem;
            $users = UserUtil::getAllSubs($uid);
            $deptArr = UserUtil::getManagerDeptSubUserByUid($uid); //取得管理的部门和下属
            foreach ($deptArr as $dept) {
                foreach ($dept['user'] as $value) {
                    foreach ($users as $key => $u) {
                        if ($value['uid'] == $u['uid']) {
                            $users[$key]['hasSub'] = $value['hasSub'];
                        }
                    }
                }
            }
            $this->ajaxReturn(array('users' => $users, 'item' => $item, 'uid' => $uid));
        }
    }

    /**
     * 提醒
     */
    protected function remind($assignmentId)
    {
        if (Ibos::app()->request->getIsPostRequest()) {
            if ($this->getIsInstallCalendar()) {
                $uid = Ibos::app()->user->uid;
                $remindTime = Env::getRequest('remindTime');
                // 删除旧日程
                $oldCalendarids = AssignmentRemind::model()->fetchCalendarids($assignmentId, $uid);
                Calendars::model()->deleteAll(sprintf("uid = %d AND FIND_IN_SET(`calendarid`, '%s')", $uid, implode(',', $oldCalendarids)));
                //删除旧数据
                AssignmentRemind::model()->deleteAll("assignmentid = {$assignmentId} AND uid = {$uid}");
                if (!empty($remindTime)) {
                    $remindTime = strtotime($remindTime);
                    $remindContent = Env::getRequest('remindContent');
                    $calendar = array(
                        'subject' => $remindContent,
                        'starttime' => $remindTime,
                        'endtime' => $remindTime + 1800, // 取半个钟
                        'uid' => $uid,
                        'upuid' => $uid,
                        'lock' => 1,
                        'category' => 5
                    );

                    $cid = Calendars::model()->add($calendar, true);
                    // 关联表，删除旧数据，添加新数据
                    AssignmentRemind::model()->add(array('assignmentid' => $assignmentId, 'calendarid' => $cid, 'remindtime' => $remindTime, 'uid' => $uid, 'content' => $remindContent));
                }
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Operation succeed', 'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Not installed calendar to support remind')));
            }
        } else {
            $remind = AssignmentRemind::model()->fetch(sprintf("uid = %d AND assignmentid = %d", Ibos::app()->user->uid, $assignmentId));
            $remindtime = empty($remind) ? TIMESTAMP : $remind['remindtime'];
            $params = array(
                'reminddate' => date('Y-m-d', $remindtime),
                'remindtime' => date('H:i', $remindtime),
                'content' => empty($remind) ? '' : $remind['content']
            );
            $this->ajaxReturn(array('isSuccess' => true, 'data' => $params));
        }
    }
}
