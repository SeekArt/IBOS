<?php

/**
 * ICRecruitScoreTimeCounter class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 招聘 - 工作年限统计器
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\components;

use application\modules\recruit\model\Resume;
use application\modules\recruit\model\ResumeDetail;

class WorkYearsCounter extends TimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'workYears';
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
            $resumeids = Resume::model()->fetchAllByTime($time['start'], $time['end']);
            $workyears = ResumeDetail::model()->fetchFieldByRerumeids($resumeids, 'workyears');
            $ac = array_count_values($workyears);
            $return['0'] = array(
                'count' => isset($ac['0']) ? $ac['0'] : 0,
                'name' => '应届生'
            );
            $return['1'] = array(
                'count' => isset($ac['1']) ? $ac['1'] : 0,
                'name' => '一年以上'
            );
            $return['2'] = array(
                'count' => isset($ac['2']) ? $ac['2'] : 0,
                'name' => '两年以上'
            );
            $return['3'] = array(
                'count' => isset($ac['3']) ? $ac['3'] : 0,
                'name' => '三年以上'
            );
            $return['5'] = array(
                'count' => isset($ac['5']) ? $ac['5'] : 0,
                'name' => '五年以上'
            );
            $return['10'] = array(
                'count' => isset($ac['10']) ? $ac['10'] : 0,
                'name' => '十年以上'
            );
        }
        return $return;
    }

}
