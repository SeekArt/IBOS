<?php

/**
 * ICReportLineChart class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 统计模块 - 总结 - 折现图组件
 * @package application.modules.report.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\components;

class ReportLineChart extends ReportChart
{

    /**
     * 获取图表数据序列
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
