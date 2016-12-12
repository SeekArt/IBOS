<?php

/**
 * 日程安排模块------日程默认控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模块------日程默认控制器，继承CalendarBaseController控制器
 * @package application.modules.calendar.components
 * @version $Id: ScheduleController.php 1441 2013-10-28 16:48:01Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\calendar\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\calendar\model\Calendars;
use application\modules\calendar\model\CalendarSetup;
use application\modules\calendar\utils\Calendar as CalendarUtil;
use application\modules\message\model\Notify;
use application\modules\message\model\NotifyMessage;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\core\model\Log;

Class ScheduleController extends BaseController
{

    /**
     * 日程首页视图
     */
    public function actionIndex()
    {
        $op = Env::getRequest('op');
        if ($op == 'list') {
            $this->getList();
        } else {
            // 权限判断
            if (!$this->checkIsMe()) {
                $this->error(Ibos::lang('No permission to view schedule'), $this->createUrl('schedule/index'));
            }
            $data = array(
                'user' => User::model()->fetchByUid($this->uid)
            );
            $this->setPageTitle(Ibos::lang('Personal schedule'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Personal Office')),
                array('name' => Ibos::lang('Calendar arrangement'), 'url' => $this->createUrl('schedule/index')),
                array('name' => Ibos::lang('Personal schedule'))
            ));
            NotifyMessage::model()->setReadByUrl($this->uid, Ibos::app()->getRequest()->getUrl());
            $this->render('index', $data);
        }
    }

    /**
     * 下属日程视图
     */
    public function actionSubSchedule()
    {
        $op = Env::getRequest('op');
        if ($op == 'getsubordinates') {
            $this->getsubordinates();
        } elseif ($op == 'list') {
            $this->getList();
        } else {
            $workTime = Ibos::app()->setting->get('setting/calendarworkingtime');
            $workingtime = explode(',', $workTime);
            $setting = array(
                'worktimestart' => $workingtime[0],
                'worktimeend' => $workingtime[1],
                'allowAdd' => CalendarUtil::getIsAllowAdd(),
                'allowEdit' => CalendarUtil::getIsAllowEdit()
            );
            $getUid = Env::getRequest('uid');
            if (!$getUid) {
                $deptArr = UserUtil::getManagerDeptSubUserByUid($this->uid); //取得管理的部门和下属
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
            $data = array(
                'setting' => $setting,
                'user' => User::model()->fetchByUid($uid),
                'supUid' => UserUtil::getSupUid($this->uid) //获取上司uid
            );
            $this->setPageTitle(Ibos::lang('Subordinate schedule'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Personal Office')),
                array('name' => Ibos::lang('Calendar arrangement'), 'url' => $this->createUrl('schedule/index')),
                array('name' => Ibos::lang('Subordinate schedule'))
            ));
            $this->render('subschedule', $data);
        }
    }

    /**
     * 分享给我视图动作
     * 分两种请求情况：
     * 1. 获取对应人员的日程列表
     * 2. 渲染视图
     */
    public function actionShareschedule()
    {
        $op = Env::getRequest('op');
        if ($op === 'list') {
            $this->getShareList();
        } else {
            $uid = Env::getRequest('uid');
            if (!$uid) {
                $shareUids = CalendarUtil::getShareUidsByUid($this->uid);
                if ($shareUids !== false) {
                    $shareUidInfos = UserUtil::getUserInfoByUids($shareUids);
                    $deptArr = UserUtil::handleUserGroupByDept($shareUidInfos);
                    // 获取分享人员中第一位的 uid，初次渲染视图默认显示第一位用户的日程数据
                    $tempOrz1 = current($deptArr);
                    $tempOrz2 = current($tempOrz1['users']);
                    $uid = $tempOrz2['uid'];
                } else {
                    $this->error(Ibos::lang('You do not share personnel'), $this->createUrl('schedule/index'));
                }
            }
            // 权限判断
            if (!UserUtil::checkIsSharingToMe(Ibos::app()->user->uid, $uid)) {
                $this->error(Ibos::lang('No permission to view shareschedule'), $this->createUrl('schedule/index'));
            }
            $workTime = CalendarSetup::model()->getWorkTimeByUid($uid);
            $setting = array(
                'worktimestart' => $workTime['startTime'],
                'worktimeend' => $workTime['endTime'],
                'allowAdd' => CalendarUtil::getIsAllowAdd(),
                'allowEdit' => CalendarUtil::getIsAllowEdit()
            );
            $data = array(
                'setting' => $setting,
                'user' => User::model()->fetchByUid($uid),
            );
            $this->setPageTitle(Ibos::lang('Share'));
            $this->setPageState('breadCrumbs', array(
                    array('name' => Ibos::lang('Personal Office')),
                    array('name' => Ibos::lang('Calendar arrangement'), 'url' => $this->createUrl('schedule/index')),
                    array('name' => Ibos::lang('Share'))
                )
            );
            $this->render('shareschedule', $data);
        }
    }

    /**
     * 添加日程
     */
    public function actionAdd()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            /**
             * 日志记录
             */
            $log = array(
                'user' => Ibos::app()->user->username,
                'ip' => Ibos::app()->setting->get('clientip'),
                'isSuccess' => 0,
                'msg' => Ibos::lang('Parameters error', 'error')
            );
            Log::write($log, 'action', 'module.calendar.schedule.add');
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('schedule/index'));
        }
        // 权限判断
        if (!$this->checkAddPermission() && !CalendarUtil::isShareToMeForEdit(Ibos::app()->user->uid, $this->uid)) {
            /**
             * 日志记录
             */
            $log = array(
                'user' => Ibos::app()->user->username,
                'ip' => Ibos::app()->setting->get('clientip'),
                'isSuccess' => 0,
                'msg' => Ibos::lang('No permission to add schedule')
            );
            Log::write($log, 'action', 'module.calendar.schedule.add');
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No permission to add schedule')));
        }
        //操作后的某个日程的开始时间
        $getStartTime = Env::getRequest('CalendarStartTime');
        $sTime = empty($getStartTime) ? date("y-m-d h:i", time()) : $getStartTime;
        //操作后的某个日程的结束时间
        $getEndTime = Env::getRequest('CalendarEndTime');
        $eTime = empty($getEndTime) ? date("y-m-d h:i", time()) : $getEndTime;
        //日程的主题
        $getTitle = Env::getRequest('CalendarTitle');
        //添加getTitle的xss安全过滤
        $title = empty($getTitle) ? '' : htmlspecialchars($getTitle);
        if ($this->uid != $this->upuid) {
            $title .= ' (' . User::model()->fetchRealnameByUid($this->upuid) . ')';
        }
        //是否全天日程
        $getIsAllDayEvent = Env::getRequest('IsAllDayEvent');
        $isAllDayEvent = empty($getIsAllDayEvent) ? 0 : intval($getIsAllDayEvent);
        //颜色的分类
        $getCategory = Env::getRequest('Category');
        $category = empty($getCategory) ? (-1) : $getCategory;
        $schedule = array(
            'uid' => $this->uid,
            'subject' => $title,
            'starttime' => CalendarUtil::js2PhpTime($sTime),
            'endtime' => CalendarUtil::js2PhpTime($eTime),
            'isalldayevent' => $isAllDayEvent,
            'category' => $category,
            'uptime' => time(),
            'upuid' => $this->upuid
        );
        $addId = Calendars::model()->add($schedule, true);
        if ($addId) {
            $ret['isSuccess'] = true;
            $ret['msg'] = 'success';
            $ret['data'] = intval($addId);
            // 消息提醒(当添加人是上司时)
            if ($this->upuid != $this->uid) {
                $config = array(
                    '{sender}' => User::model()->fetchRealnameByUid($this->upuid),
                    '{subject}' => $title,
                    '{url}' => Ibos::app()->urlManager->createUrl('calendar/schedule/index')
                );
                Notify::model()->sendNotify($this->uid, 'add_calendar_message', $config, $this->upuid);
            }
        } else {
            $ret['isSuccess'] = false;
            $ret['msg'] = 'fail';
        }
        //日志记录
        $log = array(
            'user' => Ibos::app()->user->username,
            'ip' => Ibos::app()->setting->get('clientip'),
            'isSuccess' => $ret['isSuccess'] ? 1 : 0
        );
        Log::write($log, 'action', 'module.calendar.schedule.add');
        $this->ajaxReturn($ret);
    }

    /**
     * 编辑日程
     */
    public function actionEdit()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('schedule/index'));
        }
        // 权限判断
        if (!$this->checkEditPermission() && !CalendarUtil::isShareToMeForEdit(Ibos::app()->user->uid, $this->uid)) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No permission to edit schedule')));
        }
        $op = Env::getRequest('op');
        if (empty($op)) {
            $params = $this->getEditData();
            if ($params['calendarid'] > 0) { //如果不是周期性事务
                $ret = Calendars::model()->updateSchedule($params['calendarid'], $params['sTime'], $params['eTime'], $params['subject'], $params['category'], $params['isalldayevent']);
                $falseid = $this->checkEqLoop($params['calendarid']);
                if ($falseid) {
                    $ret['cid'] = $falseid;
                }
            } else {
                $masterid = abs($params['calendarid']);
                $createSubCalendarid = $this->createSubCalendar($masterid, $params['sTimeed'], $params['sTime'], $params['eTime'], $params['subject'], $params['category']);
                if ($createSubCalendarid) {
                    $ret['isSuccess'] = true;
                    $ret['msg'] = 'success';
                    $ret['cid'] = $createSubCalendarid;
                    $ret['instanceType'] = '2';
                } else {
                    $ret['isSuccess'] = false;
                    $ret['msg'] = 'fail';
                }
            }
        } else {
            $ret = $this->$op();
        }
        $this->ajaxReturn($ret);
    }

    /**
     * 删除日程
     */
    public function actionDel()
    {
        if (!Ibos::app()->request->getIsAjaxRequest()) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('schedule/index'));
        }
        // 权限判断
        if (!$this->checkEditPermission() && !CalendarUtil::isShareToMeForEdit(Ibos::app()->user->uid, $this->uid)) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No permission to del schedule')));
        }
        $getCalendarId = Env::getRequest('calendarId');
        $calendarId = $this->checkCalendarid($getCalendarId);
        $type = Env::getRequest('type');
        $allDoptions = array('only', 'after', 'all');
        $getDoption = Env::getRequest('doption');
        $doption = in_array($getDoption, $allDoptions) ? $getDoption : 'only';
        $getStartTime = Env::getRequest('CalendarStartTime');
        $sTime = empty($getStartTime) ? date("y-m-d h:i", time()) : $getStartTime;
        if ($type == 0) { //如果不属于周期性事务/0表示单个日程，1表示周期性事务
            $ret = $this->removeCalendar($calendarId);
        } else if ($type == 1 || $type == 2) {
            $calendarId = abs($calendarId);
            $ret = $this->removeLoopCalendar($calendarId, $type, $doption, $sTime);
        } else {
            $ret['isSuccess'] = false;
        }
        $this->ajaxReturn($ret);
    }

    /**
     * 日程设置
     */
    protected function setup()
    {
        if (Env::submitCheck('formhash')) {
            $interval = Env::getRequest('interval');
            $hiddenDays = Env::getRequest('hiddenDays');
            // 添加阅读权限、编辑权限数据的获取与更新
            $viewSharing = Env::getRequest('viewuid');
            $editSharing = Env::getRequest('edituid');

            $startTime = isset($interval[0]) ? $interval[0] : '8';
            $endTime = isset($interval[1]) ? $interval[1] : '18';
            CalendarSetup::model()->updataSetup(Ibos::app()->user->uid, $startTime, $endTime, $hiddenDays, $viewSharing, $editSharing);
            $this->ajaxReturn(array('isSuccess' => true));
        } else {
            $alias = 'application.modules.calendar.views.schedule.setup';
            $uid = Ibos::app()->user->uid;
            $data['workTime'] = CalendarSetup::model()->getWorkTimeByUid($uid);
            $data['hiddenDays'] = CalendarSetup::model()->getHiddenDaysByUid($uid);
            // 获取分享权限人员的字符串，包括日程查看权限与编辑权限 $data['sharingPersonnel']['viewSharing'] && $data['sharingPersonnel']['editSharing']
            $data['sharingPersonnel'] = CalendarSetup::model()->getSharingPersonnelByUid($uid);
            $data['lang'] = Ibos::getLangSource('calendar.default');
            $view = $this->renderPartial($alias, $data, true);
            $this->ajaxReturn(array('isSuccess' => true, 'view' => $view));
        }
    }

    /**
     * 完成日程
     * @return array
     */
    protected function finish()
    {
        $params = $this->getEditData();
        if ($params['calendarid'] > 0) { //如果不是周期性事务
            $ret = Calendars::model()->updateSchedule($params['calendarid'], $params['sTime'], $params['eTime'], $params['subject'], $params['category'], $params['isalldayevent'], 1);
        } else {
            $masterid = abs($params['calendarid']);
            $params['sTimeed'] = $params['sTime'];
            $createSubCalendarid = $this->createSubCalendar($masterid, $params['sTimeed'], $params['sTime'], $params['eTime'], $params['subject'], $params['category'], 1); //实例周期性事务
            if ($createSubCalendarid) {
                $ret['isSuccess'] = true;
                $ret['cid'] = $createSubCalendarid;
            } else {
                $ret['isSuccess'] = false;
            }
        }
        return $ret;
    }

    /**
     * 未完成日程
     * @return array
     */
    protected function nofinish()
    {
        $params = $this->getEditData();
        $isSuccess = Calendars::model()->modify($params['calendarid'], array('status' => 0));
        if ($isSuccess) {
            $ret['isSuccess'] = true;
        } else {
            $ret['isSuccess'] = false;
        }
        if ($falseid = $this->checkEqLoop($params['calendarid'])) {
            $ret['cid'] = $falseid;
        }
        return $ret;
    }

    /**
     * 获取请求的显示日期和类型，返回结果
     * @return array  返回日期和类型的数组
     */
    protected function getList()
    {
        //显示日期
        $st = Env::getRequest('startDate');
        $et = Env::getRequest('endDate');
        $ret = Calendars::model()->listCalendar(strtotime($st), strtotime($et), $this->uid);
        $this->ajaxReturn($ret);
    }

    /**
     * 根据请求的日期、uid 数据，返回 uid 对应的普通日程数据
     * @return array
     */
    protected function getShareList()
    {
        $startTime = Env::getRequest('startDate');
        $endTime = Env::getRequest('endDate');
        $result = Calendars::model()->getCommonCalendarList(strtotime($startTime), strtotime($endTime), $this->uid);
        if ($result === false) {
            $this->error(Ibos::lang('No permission to view shareschedule'), $this->createUrl('schedule/index'));
        }
        $this->ajaxReturn($result);
    }

    /**
     * 取得异步传递要编辑的数据
     * @return array 返回数组数据
     */
    protected function getEditData()
    {
        $getCalendarId = Env::getRequest('calendarId');
        $getStartTime = Env::getRequest('CalendarStartTime');
        $getEndTime = Env::getRequest('CalendarEndTime');
        $getSubject = Env::getRequest('Subject');
        $getCategory = Env::getRequest('Category');
        $getStartTimeed = Env::getRequest('CalendarStartTimeed');
        $params = array(
            'calendarid' => $this->checkCalendarid($getCalendarId),
            'sTime' => empty($getStartTime) ? date("y-m-d h:i", time()) : $getStartTime,
            'eTime' => empty($getEndTime) ? date("y-m-d h:i", time()) : $getEndTime,
            'subject' => empty($getSubject) ? '' : $getSubject,
            'category' => empty($getCategory) ? (-1) : $getCategory,
            'sTimeed' => empty($getStartTimeed) ? date("y-m-d h:i", time()) : $getStartTimeed,
            'isalldayevent' => Env::getRequest('IsAllDayEvent')
        );
        return $params;
    }

    /**
     * 删除日程
     * @param int $calendarid 日程id
     * @return array 返回状态
     */
    protected function removeCalendar($calendarid)
    {
        $ret = array();
        $removeSuccess = Calendars::model()->remove($calendarid);
        if ($removeSuccess) {
            $ret['isSuccess'] = true;
            $ret['msg'] = 'success';
        } else {
            $ret['isSuccess'] = false;
            $ret['msg'] = 'fail';
        }
        return $ret;
    }

    /**
     * 判断日程ID是否大于零，如果小于零的是周期性任务的伪ID，要转化为真ID的负值
     * @param $id 日程ID
     * @return int
     */
    protected function checkCalendarid($id)
    {
        if (!empty($id)) {
            if ($id < 0) {
                $id = '-' . substr($id, 11);
            }
            return intval($id);
        } else {
            return 0;
        }
    }

    /**
     * 检查日程是否是实例，如果是并且数据与所属周期相同，删除此实例，即还原此实例到初始状态
     * @param int 日程ID
     * @return string 如果匹配条件，返回该日程原来的伪ID
     */
    private function checkEqLoop($calendarid)
    {
        $subrow = Calendars::model()->fetchByPk($calendarid);
        if ($subrow['masterid'] != 0) {
            $mstrow = Calendars::model()->fetchByPk($subrow['masterid']);
            if ($mstrow) {
                $subject = $subrow['subject'] == $mstrow['subject'] ? true : false; //内容
                $category = $subrow['category'] == $mstrow['category'] ? true : false; //颜色
                $location = $subrow['location'] == $mstrow['location'] ? true : false; //地点
                $status = $subrow['status'] == $mstrow['status'] ? true : false; //状态
                $starttimeed = $subrow['starttime'] == strtotime($subrow['mastertime'] . ' ' . date('H:i:s', $mstrow['starttime'])) ? true : false; //是否是原来还没有被实例的时候的时间
                $endtimeed = $subrow['endtime'] == strtotime($subrow['mastertime'] . ' ' . date('H:i:s', $mstrow['endtime'])) ? true : false; //是否是原来还没有被实例的时候的时间
                if ($subject && $category && $location && $status && $starttimeed && $endtimeed) {
                    Calendars::model()->remove($calendarid);
                    return '-' . strtotime($subrow['mastertime'] . ' ' . date('H:i:s', $mstrow['starttime'])) . $subrow['masterid'];
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * 得到某个用户的下属，取5条
     * @param int 查看5条或者查看所有
     * @return void
     */
    protected function getsubordinates()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = $_GET['uid'];
            $getItem = Env::getRequest('item');
            $item = empty($getItem) ? 5 : $getItem;
            $users = UserUtil::getAllSubs($uid);
            $htmlStr = '<ul class="mng-trd-list">';
            $num = 0;
            foreach ($users as $user) {
                if ($num < $item) {
                    $htmlStr .= '<li class="mng-item sub">
                                            <a href="' . $this->createUrl('schedule/subSchedule', array('uid' => $user['uid'])) . '">
                                                <img src="' . $user['avatar_middle'] . '" alt="">
                                                ' . $user['realname'] . '
												<a href="' . $this->createUrl('schedule/subschedule', array('uid' => $user['uid'])) . '" class="o-cal-calendar pull-right mlm" title="日程"></a>
												<a href="' . $this->createUrl('task/subtask', array('uid' => $user['uid'])) . '" class="o-cal-todo pull-right" title="任务"></a>
                                            </a>

                                        </li>';
                }
                $num++;
            }
            $subNums = count($users);
            if ($subNums > $item) {
                $htmlStr .= '<li class="mng-item view-all" data-uid="' . $uid . '" sub-nums="' . $subNums . '">
                                                <a href="javascript:;">
                                                   <i class="o-cal-allsub"></i>
                                                    ' . Ibos::lang('View all subordinate') . '
                                                </a>
                                            </li>';
            }
            $htmlStr .= '</ul>';
            echo $htmlStr;
        }
    }

}
