<?php

/**
 * ICRecruitTimeCounter class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 日志 - 时间统计器基类
 * @package application.modules.recruit.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\recruit\components;

use application\core\utils\DateTime;
use application\modules\statistics\core\Counter;

class TimeCounter extends Counter
{

    /**
     * 统计的类型(日、月、周)
     * @var string
     */
    private $_type = 'day';

    /**
     * 设置统计类型
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * 返回统计类型
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * 统计的时间范围
     * @var array
     */
    private $_timeScope;

    /**
     * 选择的时间(本周、上周、本月、上月)
     * @var string
     */
    private $_timestr;

    /**
     * 设置选择的时间
     * @param string $type
     */
    public function setTimestr($timestr)
    {
        $this->_timestr = $timestr;
    }

    /**
     * 返回选择的时间
     * @return string
     */
    public function getTimestr()
    {
        return $this->_timestr;
    }

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
        $type = $this->getType();
        if ($type == 'week') { // 周
            return $this->formateDateByWeek($start, $end);
        } elseif ($type == 'month') { // 月
            return $this->formateDateByMoon($start, $end);
        } else { // 日
            return DateTime::getFormatDate($start, $end, 'Y-m-d');
        }
    }

    /**
     * 处理按周显示数据
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return type array
     */
    public function formateDateByWeek($start, $end)
    {
        $return = array();
        $sDate = date('Y-m-d', $start);
        $eDate = date('Y-m-d', $end);
        $st = strtotime('Monday 00:00:00 this week', $start); // 相对$start那个星期一的时间戳
        $days = DateTime::getDays($start, $end); // 相差天数
        for ($i = 0; $i < $days; $i += 7) {
            $k = $i + 6;
            $sd = date('Y-m-d', strtotime("+{$i} day", $st));
            $ed = date('Y-m-d', strtotime("+{$k} day", $st));
            if ($i == 0) {
                $return[$sDate . ':' . $ed] = $sDate . '至' . $ed;
            } elseif ($i + 7 > $days) {
                $return[$sd . ':' . $eDate] = $sd . '至' . $eDate;
            } else {
                $return[$sd . ':' . $ed] = $sd . '至' . $ed;
            }
        }
        return $return;
    }

    /**
     * 处理按月显示数据
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return type array
     */
    public function formateDateByMoon($start, $end)
    {
        $return = array();
        $st = date('Y-m-d', $start);
        $et = date('Y-m-d', $end);
        $firstDateOfStartMonth = date('Y-m', $start) . '-1'; // $start这个月第一天
        $firstDateOfEndMonth = date('Y-m', $end) . '-1'; // $end这个月第一天
        $lastDateOfEndMonth = date('Y-m-d', strtotime("+1 month -1 day $firstDateOfEndMonth")); // $end这个月最后一天
        $dates = DateTime::getDiffDate($firstDateOfStartMonth, $lastDateOfEndMonth);
        $moons = $dates['y'] * 12 + $dates['m'] + 1; // 相差月份
        if ($moons == 1) { // 选择的日期在同一个月内，特殊处理
            $return[$st . ':' . $et] = $st . '至' . $et;
            return $return;
        }
        for ($i = 0; $i < $moons; $i++) {
            $sd = date('Y-m', strtotime("+{$i} month $st")) . '-1'; // 这个月第一天
            $ed = date('Y-m-d', strtotime("+1 month -1 day $sd")); // 这个月最后一天
            // 第一个月和最后一个月需要特殊显示，因为不足一个月
            if ($i == 0) {
                $return[$st . ':' . $ed] = $st . '至' . $ed;
            } elseif ($i + 1 >= $moons) {
                $return[$sd . ':' . $et] = $sd . '至' . $et;
            } else {
                $return[$sd . ':' . $ed] = date('Y-m', strtotime("+{$i} month $st"));
            }
        }
        return $return;
    }

}
