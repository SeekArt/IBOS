<?php

/**
 * ICReportTimeCounter class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - 时间统计器基类
 * @package application.modules.report.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\components;

use application\core\utils\DateTime;
use application\modules\statistics\core\Counter;

class ReportTimeCounter extends Counter
{

    /**
     * 统计的用户数组
     * @var array
     */
    private $_uids;

    /**
     * 统计的时间范围
     * @var array
     */
    private $_timeScope;

    /**
     * 统计的类型id（1周、2月、3季、4年）
     * @var integer
     */
    private $_typeid = 1;

    /**
     * 获取统计器ID：此方法应由子类重写
     * @return boolean
     */
    public function getID()
    {
        return false;
    }

    /**
     * 获取统计器统计方法：此方法应由子类重写
     * @return boolean
     */
    public function getCount()
    {
        return false;
    }

    /**
     * 返回统计用户ID
     * @return array
     */
    public function getUid()
    {
        return $this->_uids;
    }

    /**
     * 设置统计用户ID
     * @param array $uid
     */
    public function setUid($uid)
    {
        $this->_uids = $uid;
    }

    /**
     * 返回统计类型id
     * @return integer
     */
    public function getTypeid()
    {
        return $this->_typeid;
    }

    /**
     * 设置统计类型id
     * @param integer $type
     */
    public function setTypeid($typeid)
    {
        $this->_typeid = $typeid;
    }

    /**
     * 设置统计时间范围
     * @param array $timeScope
     */
    public function setTimeScope($timeScope)
    {
        $this->_timeScope = $timeScope;
    }

    /**
     *  返回统计时间范围
     * @return array
     */
    public function getTimeScope()
    {
        return $this->_timeScope;
    }

    /**
     * 获取统计时间范围内的天数
     * @return integer
     */
    public function getDays()
    {
        $scope = $this->getTimeScope();
        return DateTime::getDays($scope['start'], $scope['end']);
    }

    /**
     * 获取统计时间范围内的日期，返回 值为年月日的一维数组格式
     * @staticvar array $return 静态日期缓存数组
     * @return array
     */
    public function getDateScope()
    {
        static $return = array();
        if (empty($return)) {
            $scope = $this->getTimeScope();
            $return = $this->getFormatDate($scope['start'], $scope['end']);
        }
        return $return;
    }

    public function getFormatDate($start, $end)
    {
        $typeid = $this->getTypeid();
        if ($typeid == 1) { // 周报
            return $this->formateDateByWeek($start, $end);
        } elseif ($typeid == 2) { // 月报
            return $this->formateDateByMoon($start, $end);
        } elseif ($typeid == 3) { // 季报
            return $this->formateDateBySeason($start, $end);
        } elseif ($typeid == 4) { // 年报
            return $this->formateDateByYear($start, $end);
        }
    }

    public function formateDateByWeek($start, $end)
    {
        $return = array();
        $st = strtotime('Monday 00:00:00 this week', $start);
        $et = strtotime('Sunday 23:59:59 this week', $end);
        $days = DateTime::getDays($st, $et);
        for ($i = 0; $i <= $days; $i += 7) {
            $k = $i + 6;
            $sd = date('Y-m-d', strtotime("+{$i} day", $st));
            $ed = date('Y-m-d', strtotime("+{$k} day", $st));
            $return[$sd . ':' . $ed] = $sd . '至' . $ed . '周报';
        }
        return $return;
    }

    public function formateDateByMoon($start, $end)
    {
        $return = array();
        $st = date('Y-m-d', $start);
        $et = date('Y-m-d', $end);
        $dates = DateTime::getDiffDate($st, $et);
        $moons = $dates['y'] * 12 + $dates['m']; // 相差月份
        if ($dates['d'] > 0) {
            $moons += 1;
        }
        for ($i = 0; $i < $moons; $i++) {
            $sd = date('Y-m', strtotime("+{$i} month $st")) . '-1';
            $ed = date('Y-m-d', strtotime("+1 month -1 day $sd"));
            $return[$sd . ':' . $ed] = date('Y-m', strtotime("+{$i} month $st")) . '月报';
        }
        return $return;
    }

    public function formateDateBySeason($start, $end)
    {
        $return = array();
        $st = date('Y-m-d', $start);
        $et = date('Y-m-d', $end);
        $dates = DateTime::getDiffDate($st, $et);
        $moons = $dates['y'] * 12 + $dates['m']; // 相差月份
        if (($dates['d'] + $dates['m'] * 12) > 0) {
            $moons += 1;
        }
        for ($i = 0; $i < $moons; $i += 3) {
            $time = strtotime("+{$i} month $st");
            $season = DateTime::getSeasonByMonty(date('m', $time));
            $sd = date('Y-m', strtotime("+{$i} month $st")) . '-1';
            $ed = date('Y-m-d', strtotime("+3 month -1 day $sd"));
            $return[$sd . ':' . $ed] = date('Y', $time) . '年第' . $season . '季报';
        }
        return $return;
    }

    public function formateDateByYear($start, $end)
    {
        $return = array();
        $st = date('Y-m-d', $start);
        $et = date('Y-m-d', $end);
        $dates = DateTime::getDiffDate($st, $et);
        $years = $dates['y']; // 相差年份
        if (($dates['d'] + $dates['m'] * 12) > 0) {
            $years += 1;
        }
        for ($i = 0; $i < $years; $i++) {
            $sd = date('Y-m', strtotime("+{$i} year $st")) . '-1';
            $ed = date('Y-m-d', strtotime("+1 year -1 day $sd"));
            $return[$sd . ':' . $ed] = date('Y', strtotime("+{$i} year $st")) . '年报';
        }
        return $return;
    }

}
