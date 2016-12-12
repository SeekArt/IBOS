<?php

/**
 * SubmitTimeCounter class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 日志 - 提交时间统计器
 * @package application.modules.diary.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\components;

use application\modules\diary\model\Diary;
use application\modules\user\model\User;

class SubmitTimeCounter extends TimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'submit';
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
                $list = Diary::model()->fetchAddTimeByUid($uid, $time['start'], $time['end']);
                // 补全所选时间范围内缺失的数值
                $return[$uid]['list'] = $this->ReplenishingDate($list);
                $return[$uid]['name'] = User::model()->fetchRealnameByUid($uid);
            }
        }
        return $return;
    }

    /**
     * 补全结果集日期并赋值
     * @param array $list 结果集
     * @return array 处理后的结果集
     */
    protected function ReplenishingDate($list = array())
    {
        if (empty($list)) {
            return $list;
        }
        // 为应该要显示的所有日期显示默认值 '-',并且键值对换
        $dateScope = array_fill_keys($this->getDateScope(), "'-'");
        foreach ($list as $time) {
            $dayTime = date('Y-m-d', $time['diarytime']);
            $dateScope[$dayTime] = $this->getLegalDate($time['addtime'], $time['diarytime']);
        }
        return $dateScope;
    }

    /**
     * 获取合法的日期
     * @param integer $addTime 日志添加时间戳
     * @param integer $diaryTime 日志时间戳
     * @return mixed
     */
    protected function getLegalDate($addTime, $diaryTime)
    {
        // 如果超时一天，说明这篇日志不合法，属于迟交，在前台就会显示为0点
        if ($addTime - $diaryTime > 86400) {
            $date = 0;
        } else {
            $date = date('G.i', $addTime);
        }
        return $date;
    }

}
