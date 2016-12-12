<?php

/**
 * ICReportScoreTimeCounter class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - 总结得分统计器
 * @package application.modules.report.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\components;

use application\core\utils\Convert;
use application\modules\report\model\Report;
use application\modules\report\model\ReportStats;
use application\modules\user\model\User;

class ReportScoreTimeCounter extends ReportTimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'score';
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
            $typeid = $this->getTypeid();
            foreach ($this->getUid() as $uid) {
                $list = ReportStats::model()->fetchAllStatisticsByUid($uid, $time['start'], $time['end'], $typeid);
                $return[$uid]['list'] = $this->ReplenishingScore($list); // 补全所选时间范围内缺失的数值
                $return[$uid]['name'] = User::model()->fetchRealnameByUid($uid);
            }
        }
        return $return;
    }

    /**
     * 补全缺失积分与正确赋值
     * @param array $list
     * @return type
     */
    protected function ReplenishingScore($list)
    {
        if (empty($list)) {
            return $list;
        }
        // 为应该要显示的所有日期显示默认值 '-',并且键值对换
        $dateScopeTmp = $this->getDateScope();
        $dateScope = array_flip($dateScopeTmp);
        // 取得结果集内的总结ID，查找对应的添加时间
        $repIds = Convert::getSubByKey($list, 'repid');
        $timeList = Report::model()->fetchBETimeById($repIds);
        $new = array();
        // 转换为日期为键，值为总结ID的数组
        foreach ($timeList as $time) {
            $new[$time['repid']] = array('begintime' => $time['begindate'], 'endtime' => $time['enddate']);
        }
        // 获取正确的积分显示
        $ret = $this->getLegalScore($dateScope, $new, $list);
        return $ret;
    }

    /**
     * 赋值正确的积分
     * @param array $dateScope 要显示的日期范围，
     * @param array $newDates 用于对比的 日期=>总结ID 数组
     * @param array $list 结果集
     */
    private function getLegalScore($dateScope, $newDates, $list)
    {
        foreach ($dateScope as $k => $date) {
            list($st, $et) = explode(':', $date);
            foreach ($newDates as $repid => $new) {
                // 如果不在日期范围内，赋值为0
                if (strtotime($st) - 86400 == $new['begintime'] && strtotime($et) - 86400 == $new['endtime']) {
                    $dateScope[$k] = $list[$repid]['integration'];
                    break;
                }
                $dateScope[$k] = 0;
            }
        }
        return $dateScope;
    }

}
