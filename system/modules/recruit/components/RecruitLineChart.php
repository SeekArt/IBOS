<?php

/**
 * ICRecruitLineChart class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 招聘 - 折线图组件
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\components;

class RecruitLineChart extends RecruitChart
{

    /**
     * 获取图表数据序列
     * @return type
     */
    public function getSeries()
    {
        return $this->getCounter()->getCount();
    }

    /**
     * 获取图表X轴数据
     */
    public function getXaxis()
    {
        return $this->getCounter()->getDateScope();
    }

}
