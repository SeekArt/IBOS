<?php

/**
 * ICRecruitDegreeCounter class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 招聘 - 学历分布统计器
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\components;

use application\modules\recruit\model\Resume;
use application\modules\recruit\model\ResumeDetail;

class DegreeCounter extends TimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'degree';
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
            $educations = ResumeDetail::model()->fetchFieldByRerumeids($resumeids, 'education');
            $ac = array_count_values($educations);
            $return['JUNIOR_HIGH'] = array(
                'count' => isset($ac['JUNIOR_HIGH']) ? $ac['JUNIOR_HIGH'] : 0,
                'name' => '初中'
            );
            $return['SENIOR_HIGH'] = array(
                'count' => isset($ac['SENIOR_HIGH']) ? $ac['SENIOR_HIGH'] : 0,
                'name' => '高中'
            );
            $return['TECHNICAL_SECONDARY'] = array(
                'count' => isset($ac['TECHNICAL_SECONDARY']) ? $ac['TECHNICAL_SECONDARY'] : 0,
                'name' => '中专'
            );
            $return['COLLEGE'] = array(
                'count' => isset($ac['COLLEGE']) ? $ac['COLLEGE'] : 0,
                'name' => '大专'
            );
            $return['BACHELOR_DEGREE'] = array(
                'count' => isset($ac['BACHELOR_DEGREE']) ? $ac['BACHELOR_DEGREE'] : 0,
                'name' => '本科'
            );
            $return['MASTER'] = array(
                'count' => isset($ac['MASTER']) ? $ac['MASTER'] : 0,
                'name' => '硕士'
            );
            $return['DOCTOR'] = array(
                'count' => isset($ac['DOCTOR']) ? $ac['DOCTOR'] : 0,
                'name' => '博士'
            );
        }
        return $return;
    }

}
