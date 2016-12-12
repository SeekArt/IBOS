<?php

/**
 * LineChart class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 日志 - 折现图组件
 * @package application.modules.diary.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\components;

class LineChart extends Chart
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
