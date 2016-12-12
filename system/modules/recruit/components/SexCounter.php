<?php

/**
 * ICRecruitSexCounter class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 招聘 - 性别比例统计器
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\components;

use application\modules\recruit\model\Resume;
use application\modules\recruit\model\ResumeDetail;

class SexCounter extends TimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'sex';
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
            $genders = ResumeDetail::model()->fetchFieldByRerumeids($resumeids, 'gender');
            $ac = array_count_values($genders);
            $return['male'] = array(
                'count' => isset($ac['1']) ? $ac['1'] : 0,
                'sex' => '男'
            );
            $return['female'] = array(
                'count' => isset($ac['2']) ? $ac['2'] : 0,
                'sex' => '女'
            );
        }
        return $return;
    }

}
