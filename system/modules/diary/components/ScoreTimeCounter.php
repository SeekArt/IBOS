<?php

/**
 * ScoreTimeCounter class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 日志 - 日志得分统计器
 * @package application.modules.diary.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\components;

use application\core\utils\Convert;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryStats;
use application\modules\user\model\User;

class ScoreTimeCounter extends TimeCounter
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
            foreach ($this->getUid() as $uid) {
                $list = DiaryStats::model()->fetchAllStatisticsByUid($uid, $time['start'], $time['end']);
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
        $dateScope = array_fill_keys($this->getDateScope(), "'-'");
        // 取得结果集内的日志ID，查找对应的添加时间
        $diaryIds = Convert::getSubByKey($list, 'diaryid');
        $timeList = Diary::model()->fetchAddTimeByDiaryId($diaryIds);
        $new = array();
        // 转换为日期为键，值为日志ID的数组
        foreach ($timeList as $time) {
            $dayTime = date('Y-m-d', $time['addtime']);
            $new[$dayTime] = $time['diaryid'];
        }
        // 获取正确的积分显示
        $this->getLegalScore($dateScope, $new, $list);
        return $dateScope;
    }

    /**
     * 赋值正确的积分
     * @param array $dateScope 要显示的日期范围，
     * @param array $new 用于对比的 日期=>日志ID 数组
     * @param array $list 结果集
     */
    private function getLegalScore(&$dateScope, $new, $list)
    {
        foreach ($dateScope as $k => &$date) {
            // 如果不在日期范围内，赋值为0
            if (!isset($new[$k])) {
                $date = 0;
            } else {
                $date = $list[$new[$k]]['integration'];
            }
        }
    }

}
