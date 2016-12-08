<?php

/**
 * 日程安排模块------ 工具类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模块------  工具类
 * @package application.modules.calendar.utils
 * @version $Id: CalendarUtil.php 1433 2013-10-28 20:39:57Z gzwwb $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\calendar\utils;

use application\core\utils\Ibos;
use application\modules\calendar\model\Calendars as CalendarModel;
use application\modules\calendar\model\CalendarSetup;

Class Calendar
{

    /**
     * 转化时间为输出格式
     * @param <dateline> $phpDate 时间戳
     * @return <string> 返回时间
     */
    public static function php2JsTime($phpDate)
    {
        return "/Date(" . $phpDate . "000" . ")/";
    }

    /**
     * 转化时间格式为时间戳
     * @param <string> $jsdate 时间
     * @return <int> 返回时间戳
     */
    public static function js2PhpTime($jsdate)
    {
        $ret = strtotime($jsdate);
        return $ret;
    }

    /**
     * 取得日期和星期几的数组
     * @param string $dateStr
     * @return array
     */
    public static function getDateAndWeekDay($dateStr)
    {
        list($year, $month, $day) = explode('-', $dateStr);
        $weekArray = array(
            Ibos::lang('Day', 'date'),
            Ibos::lang('One', 'date'),
            Ibos::lang('Two', 'date'),
            Ibos::lang('Three', 'date'),
            Ibos::lang('Four', 'date'),
            Ibos::lang('Five', 'date'),
            Ibos::lang('Six', 'date')
        );
        $weekday = $weekArray[date("w", strtotime($dateStr))];
        return array(
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'weekday' => Ibos::lang('Weekday', 'date') . $weekday
        );
    }

    /**
     * 将星期几的数字转换成中文数字
     * @param string $digitalStr 数字（1-7）逗号隔开的字符串
     * @return string 返回中文的星期几
     */
    public static function digitalToDay($digitalStr)
    {
        $digitalArr = explode(',', $digitalStr);
        $dayArr = array(
            1 => Ibos::lang('One', 'date'),
            2 => Ibos::lang('Two', 'date'),
            3 => Ibos::lang('Three', 'date'),
            4 => Ibos::lang('Four', 'date'),
            5 => Ibos::lang('Five', 'date'),
            6 => Ibos::lang('Six', 'date'),
            7 => Ibos::lang('day')
        );
        $recurringtime = '';
        foreach ($digitalArr as $digital) {
            $recurringtime .= $dayArr[$digital] . ',';
        }
        return rtrim($recurringtime, ',');
    }

    /**
     * 连接条件语句
     * @param string $condition1 条件1
     * @param string $condition2 条件2
     * @return string
     */
    public static function joinCondition($condition1, $condition2)
    {
        if (empty($condition1)) {
            return $condition2;
        } else {
            return $condition1 . ' AND ' . $condition2;
        }
    }

    /**
     * 获取后台设置是否允许给下属添加日程
     * @return boolean
     */
    public static function getIsAllowAdd()
    {
        return Ibos::app()->setting->get('setting/calendaraddschedule');
    }

    /**
     * 获取后台设置是否允许修改下属日程
     * @return boolean
     */
    public static function getIsAllowEdit()
    {
        return Ibos::app()->setting->get('setting/calendareditschedule');
    }

    /**
     * 获取后台设置是否允许修改下属任务
     * @return boolean
     */
    public static function getIsAllowEidtTask()
    {
        return Ibos::app()->setting->get('setting/calendaredittask');
    }

    /**
     * 获取用户设置的日程开始时间
     * @param integer $uid 用户id
     * @return string
     */
    public static function getSetupStartTime($uid)
    {
        $workTime = CalendarSetup::model()->getWorkTimeByUid($uid);
        return $workTime['startTime'];
    }

    /**
     * 获取用户设置的日程结束时间
     * @param integer $uid 用户id
     * @return string
     */
    public static function getSetupEndTime($uid)
    {
        $workTime = CalendarSetup::model()->getWorkTimeByUid($uid);
        return $workTime['endTime'];
    }

    /**
     * 获取用户设置的隐藏日期数组
     * @param integer $uid 用户id
     * @return string
     */
    public static function getSetupHiddenDays($uid)
    {
        $hiddenDays = CalendarSetup::model()->getHiddenDaysByUid($uid);
        return implode(',', $hiddenDays);
    }

    /**
     * 根据用户 uid 获取对应分享日程给我的 uid 数组
     * @param integer $uid 用户 uid
     */
    public static function getShareUidsByUid($uid)
    {
        $shareUids = CalendarSetup::model()->getShareUidsByUid($uid);
        return !empty($shareUids) ? $shareUids : false;
    }

    /**
     * 判断用户是否拥有某个分享给自己的日程的编辑权限
     * @param  integer $myUid 当前登录用户 uid
     * @param  integer $editUid 被修改的用户 uid
     * @return boolean  true | false
     */
    public static function isShareToMeForEdit($myUid, $editUid)
    {
        $setupInfo = CalendarSetup::model()->find(array('condition' => '`uid` = :uid', 'params' => array(':uid' => $editUid)));
        $editsharing = explode(',', $setupInfo['editsharing']);
        if (in_array($myUid, $editsharing))
            return true;
        else
            return false;
    }

}
