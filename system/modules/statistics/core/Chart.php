<?php

/**
 * Chart class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * Chart 是所有图表的抽象基类，它规定了一些所有的图表都必须存在的方法
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.statistics.core
 * @since 2.0
 */

namespace application\modules\statistics\core;

use CComponent;

abstract class Chart extends CComponent
{

    protected $counter;

    /**
     *
     * @param Counter $owner
     */
    public function __construct(Counter $counter)
    {
        $this->setCounter($counter);
    }

    /**
     * 获取图表工具箱
     */
    //public abstract function getToolbox();

    /**
     * 获取提示框信息
     */
    //public abstract function getTooltip();

    /**
     * 获取该图表数据系列
     */
    public abstract function getSeries();

    /**
     * 获取直角坐标系中的横轴，通常并默认为类目轴
     */
    public abstract function getXaxis();

    /**
     * 直角坐标系中的纵轴，通常并默认为数值轴
     */
    public abstract function getYaxis();

    /**
     * 获取模块图表统计器
     * @return object
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * 设置图表统计器
     * @param Counter $counter
     */
    protected function setCounter(Counter $counter)
    {
        $this->counter = $counter;
    }

}
