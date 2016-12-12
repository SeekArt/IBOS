<?php

/**
 * ICRecruitAgeCounter class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 招聘 - 年龄结构统计器
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\components;

use application\modules\recruit\core\ResumeDetail as ICResumeDetail;
use application\modules\recruit\model\Resume;
use application\modules\recruit\model\ResumeDetail;

class AgeCounter extends TimeCounter
{

    /**
     * 返回当前统计器标识
     * @return string
     */
    public function getID()
    {
        return 'age';
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
            $birthdays = ResumeDetail::model()->fetchFieldByRerumeids($resumeids, 'birthday');
            $age23 = $age24 = $age27 = $age31 = $age41 = 0;
            foreach ($birthdays as $birthday) {
                $age = ICResumeDetail::handleAge($birthday);
                if ($age <= 23) {
                    $age23++;
                } elseif ($age >= 24 && $age <= 26) {
                    $age24++;
                } elseif ($age >= 27 && $age <= 30) {
                    $age27++;
                } elseif ($age >= 31 && $age <= 40) {
                    $age31++;
                } elseif ($age >= 41) {
                    $age41++;
                }
            }
            $return['age23'] = array(
                'count' => $age23,
                'name' => '23岁以下'
            );
            $return['age24'] = array(
                'count' => $age24,
                'name' => '24-26岁'
            );
            $return['age27'] = array(
                'count' => $age27,
                'name' => '27-30岁'
            );
            $return['age31'] = array(
                'count' => $age31,
                'name' => '31-40岁'
            );
            $return['age41'] = array(
                'count' => $age41,
                'name' => '41岁以上'
            );
        }
        return $return;
    }

}
