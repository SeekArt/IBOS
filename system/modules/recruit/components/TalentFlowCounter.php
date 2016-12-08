<?php

/**
 * ICRecruitTalentFlowCounter class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 招聘 - 人才流动统计器
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\components;

use application\modules\recruit\model\ResumeStats;

class TalentFlowCounter extends TimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'talentFlow';
    }

    /**
     * 统计器统计方法
     * @staticvar array $return 静态统计结果缓存
     * @return array
     */
    public function getCount()
    {
        static $return = array();
        if (empty($return)) {
            $time = $this->getTimeScope();
            $list = ResumeStats::model()->fetchAllByTime($time['start'], $time['end']);
            $statsTemp = array();
            if (!empty($list)) {
                foreach ($list as $stat) {
                    $statsTemp[$stat['datetime']] = $stat;
                }
            }
            // 补全缺失值
            $type = $this->getType();
            if ($type == 'week' || $type == 'month') {
                $return = $this->ReplenishingWeekOrMonth($statsTemp, $time);
            } else {
                $return = $this->ReplenishingDay($statsTemp, $time);
            }
        }
        return $return;
    }

    /**
     * 补全缺失日数据与正确赋值
     * @param array $stats
     * @param array $time
     * @return array
     */
    protected function ReplenishingDay($stats, $time)
    {
        if (empty($stats)) {
            return $stats;
        }
        $return = array();
        $startDateTime = strtotime(date('Y-m-d', $time['start']));
        $endDateTime = strtotime(date('Y-m-d', $time['end']));
        for ($i = $startDateTime; $i <= $endDateTime; $i += 86400) {
            if (in_array($i, array_keys($stats))) {
                $return['new']['list'][$i] = intval($stats[$i]['new']);
                $return['pending']['list'][$i] = intval($stats[$i]['pending']);
                $return['interview']['list'][$i] = intval($stats[$i]['interview']);
                $return['employ']['list'][$i] = intval($stats[$i]['employ']);
                $return['eliminate']['list'][$i] = intval($stats[$i]['eliminate']);
            } else {
                $return['new']['list'][$i] = 0;
                $return['pending']['list'][$i] = 0;
                $return['interview']['list'][$i] = 0;
                $return['employ']['list'][$i] = 0;
                $return['eliminate']['list'][$i] = 0;
            }
            $return['new']['name'] = '新增简历';
            $return['pending']['name'] = '待安排';
            $return['interview']['name'] = '面试';
            $return['employ']['name'] = '录用';
            $return['eliminate']['name'] = '淘汰';
        }
        return $return;
    }

    /**
     * 补全缺失周、月数据与正确赋值
     * @param array $list
     * @return type
     */
    protected function ReplenishingWeekOrMonth($stats)
    {
        if (empty($stats)) {
            return $stats;
        }
        // 为应该要显示的所有日期显示默认值 '-',并且键值对换
        $dateScopeTmp = $this->getDateScope();
        $dateScope = array_flip($dateScopeTmp);
        // 获取正确的数据显示
        $ret = $this->getLegal($dateScope, $stats);
        return $ret;
    }

    /**
     * 赋值正确的数据
     * @param array $dateScope 要显示的日期范围，
     * @param array $stats 结果集
     */
    private function getLegal($dateScope, $stats)
    {
        $return = array();
        foreach ($dateScope as $k => $date) {
            $return['new']['list'][$k] = 0;
            $return['pending']['list'][$k] = 0;
            $return['interview']['list'][$k] = 0;
            $return['employ']['list'][$k] = 0;
            $return['eliminate']['list'][$k] = 0;
            list($st, $et) = explode(':', $date);
            foreach ($stats as $datetime => $stat) {
                // 累计所有在内的数量
                if (strtotime($st) <= $stat['datetime'] && strtotime($et) >= $stat['datetime']) {
                    $return['new']['list'][$k] += $stat['new'];
                    $return['pending']['list'][$k] += $stat['pending'];
                    $return['interview']['list'][$k] += $stat['interview'];
                    $return['employ']['list'][$k] += $stat['employ'];
                    $return['eliminate']['list'][$k] += $stat['eliminate'];
                    unset($stats[$datetime]);
                }
            }
        }
        $return['new']['name'] = '新增简历';
        $return['pending']['name'] = '待安排';
        $return['interview']['name'] = '面试';
        $return['employ']['name'] = '录用';
        $return['eliminate']['name'] = '淘汰';
        return $return;
    }

}
