<?php

/**
 * 日程安排模块------日程模块基本控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模块------日程基本控制器，继承ICController控制器
 * @package application.modules.calendar.components
 * @version $Id: BaseController.php 1441 2013-10-28 16:48:01Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\calendar\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\calendar\model\Calendars;
use application\modules\calendar\utils\Calendar as CalendarUtil;
use application\modules\user\utils\User as UserUtil;

Class BaseController extends Controller
{

    private $_attributes = array(
        'uid' => 0,
        'upuid' => 0
    );

    public function __set($name, $value)
    {
        if (isset($this->_attributes[$name])) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            parent::__get($name);
        }
    }

    public function init()
    {
        $uid = intval(Env::getRequest('uid'));
        $this->uid = $uid ? $uid : Ibos::app()->user->uid;
        $this->upuid = Ibos::app()->user->uid;
        parent::init();
    }

    /**
     * 取得侧栏视图
     * @return void
     */
    protected function getSidebar()
    {
        $sidebarAlias = 'application.modules.calendar.views.sidebar';
        $params = array(
            'hasSubUid' => UserUtil::hasSubUid(Ibos::app()->user->uid),
            'hasShareUid' => CalendarUtil::getShareUidsByUid(Ibos::app()->user->uid),
            'lang' => Ibos::getLangSource('calendar.default'),
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    /**
     * 下属侧栏视图
     * @return string
     */
    protected function getSubSidebar()
    {
        $sidebarAlias = 'application.modules.calendar.views.subsidebar';
        $params = array(
            'deptArr' => UserUtil::getManagerDeptSubUserByUid(Ibos::app()->user->uid),
            'hasShareUid' => CalendarUtil::getShareUidsByUid(Ibos::app()->user->uid),
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    /**
     * 共享给我侧栏视图
     * @return string
     */
    protected function getShareSidebar()
    {
        $sidebarAlias = 'application.modules.calendar.views.sharesidebar';
        // 根据 uid 数组返回用户信息数组
        $shareUids = CalendarUtil::getShareUidsByUid(Ibos::app()->user->uid);
        $shareUidInfos = UserUtil::getUserInfoByUids($shareUids);
        // 根据用户信息数组按部门进行重新排列形成可用于输出生成侧栏菜单的数组
        $params = array(
            'deptArr' => UserUtil::handleUserGroupByDept($shareUidInfos),
            'hasSubUid' => UserUtil::hasSubUid(Ibos::app()->user->uid),
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    /**
     * 取得(日/周/月)视图
     * @param string $showDate 日历控件传递过来的日期
     * @param string $viewType 日历控件传递过来需要显示的视图类型(日、周、月)
     * @return array 返回开始和结束时间
     */
    protected function getCalendarViewFormat($showDate, $viewType = 'week')
    {
        $phpTime = strtotime($showDate);
        switch ($viewType) {
            case "month":

                //月视图下的开始时间要加上前一个月的多余的几天，结束时间要加上下个月开始多余的几天
                $sd = mktime(0, 0, 0, date("m", $phpTime), 1, date("Y", $phpTime)); //获取当前日期这个月的第一天
                $ed = mktime(0, 0, 0, date("m", $phpTime) + 1, 1, date("Y", $phpTime)) - 1; //获取当前日期下月份的第一天
                $st_day = date("N", $sd); //这个月的第一天是星期几
                $ed_day = date("N", $ed); //这个月的最后一天是星期几
                $st = $sd - ($st_day - 1) * 24 * 60 * 60;
                $et = $ed + (7 - $ed_day) * 24 * 60 * 60;
                break;
            case "week":
                //suppose first day of a week is monday 
                $monday = date("d", $phpTime) - date('N', $phpTime) + 1;
                $st = mktime(0, 0, 0, date("m", $phpTime), $monday, date("Y", $phpTime));
                $et = mktime(0, 0, -1, date("m", $phpTime), $monday + 7, date("Y", $phpTime));
                break;
            case "day":
                $st = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
                $et = mktime(0, 0, -1, date("m", $phpTime), date("d", $phpTime) + 1, date("Y", $phpTime));
                break;
        }
        $result['st'] = $st;
        $result['et'] = $et;
        return $result;
    }

    /**
     * 创建周期性事务实例
     * @param $masterid int 周期性事务的ID
     * @param $mastertime int 要实例的事务原日期时间戳，如：在12-01号的一个周期性事务，要把它实例，那它的mastertime就是12-01的时间戳
     * @param $starttime int 开始时间戳
     * @param $endtime int 结束时间戳
     * @param $subject string 结束字符串
     * @param $category int 事务类型
     * @param $Status int 完成状态，默认为0
     * @return integer 实例事务的ID
     */
    protected function createSubCalendar($masterid, $mastertime, $starttime, $endtime, $subject, $category, $status = 0)
    {
        $uid = Ibos::app()->user->uid;
        $rows = Calendars::model()->fetchByPk($masterid);
        unset($rows['calendarid']);
        $rows['masterid'] = $masterid;
        $rows['subject'] = $subject;
        $rows['category'] = $category;
        $rows['mastertime'] = date('Y-m-d', strtotime($mastertime));
        $rows['starttime'] = strtotime($starttime);
        $rows['endtime'] = strtotime($endtime);
        $rows['upaccount'] = $uid;
        $rows['instancetype'] = 2;
        $rows['status'] = $status;
        return Calendars::model()->add($rows, true);
    }

    /**
     * 删除周期性事务
     * @param integer $id 事务ID
     * @param integer $type 事务类型（周期或者实例）
     * @param string $doption 周期类型
     * @param string $strattime 事务的开始时间
     * @return array 返回状态
     */
    protected function removeLoopCalendar($id, $type, $doption, $starttime)
    {
        $ret = array();
        $isSuccess = '';
        switch ($type) {
            case '1': //周期性事务
                switch ($doption) {
                    case 'only'://如果只删除它自身
                        $endtime = date('Y-m-d H:i', time());
                        $sid = $this->createSubCalendar($id, $starttime, $starttime, $endtime, '', -1, 3); //返回周期性实例
                        if ($sid) {
                            $ret['isSuccess'] = true;
                        } else {
                            $ret['isSuccess'] = false;
                        }
                        return $ret;
                    case 'after': //如果删除以后的
                        $endday = explode(' ', $starttime); //有效期
                        $endday = strtotime($endday[0]) - 24 * 60 * 60;
                        //设置周期的有效时间为当前删除的事务日期的前一天
                        $isSuccess = Calendars::model()->modify($id, array('recurringend' => $endday));
                        if ($isSuccess) {
                            //把当前删除的事务以后的实例都删除
                            Calendars::model()->deleteAll(array(
                                'condition' => 'masterid = :masterid AND starttime > :starttime',
                                'params' => array(':masterid' => $id, ':starttime' => strtotime($starttime))
                            ));
                        }
                        break;
                    case 'all': //如果删除全部
                        $isSuccess = Calendars::model()->remove($id);
                        if ($isSuccess) {
                            Calendars::model()->deleteAll(array(
                                'condition' => 'masterid = :masterid',
                                'params' => array(':masterid' => $id)
                            ));
                        }
                        break;
                }
                break;
            case '2': //周期性实例
                $isSuccess = Calendars::model()->modify($id, array('status' => 3));
                break;
        }
        if ($isSuccess) {
            $ret['isSuccess'] = true;
        } else {
            $ret['isSuccess'] = false;
        }
        return $ret;
    }

    /**
     * 判断是否是查看自己
     * @return boolean
     */
    protected function checkIsMe()
    {
        if ($this->uid != Ibos::app()->user->uid) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查是否有添加日程权限
     * @return boolean
     */
    protected function checkAddPermission()
    {
        if (!$this->checkIsMe() && (!UserUtil::checkIsSub(Ibos::app()->user->uid, $this->uid) || !CalendarUtil::getIsAllowAdd())) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查是否有编辑日程权限
     * @return boolean
     */
    protected function checkEditPermission()
    {
        if (!$this->checkIsMe() && !UserUtil::checkIsSub(Ibos::app()->user->uid, $this->uid)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查是否有编辑任务权限
     * @return boolean
     */
    protected function checkTaskPermission()
    {
        if (!$this->checkIsMe() && (!UserUtil::checkIsSub(Ibos::app()->user->uid, $this->uid) || !CalendarUtil::getIsAllowEidtTask())) {
            return false;
        } else {
            return true;
        }
    }

}
