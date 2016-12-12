<?php

/**
 * ChartFactory class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 图表工厂类，负责统一创建图表实例
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.statistics.core
 * @version $Id$
 * @since 2.0
 */

namespace application\modules\statistics\core;

use application\core\utils\Ibos;
use CApplicationComponent;
use CException;
use CMap;

class ChartFactory extends CApplicationComponent
{

    /**
     *
     * @var array
     */
    public $charts = array();

    /**
     *
     * @param Counter $counter 图表所属的统计器
     * @param string $className 图表的类名。这个参数也可以用path alias代替。(e.g. system.web.widgets.COutputCache)
     * @param array $properties 初始化图表所需参数
     * @return Chart
     */
    public function createChart($counter, $className, $properties = array())
    {
        $chart = new $className($counter);
        $this->chkInstance($chart);
        if (isset($this->charts[$className])) {
            $properties = $properties === array() ? $this->charts[$className] : CMap::mergeArray($this->charts[$className], $properties);
        }
        foreach ($properties as $name => $value) {
            $chart->$name = $value;
        }
        return $chart;
    }

    /**
     * 检查图表来源是否正确
     * @param Chart $chart
     * @throws CException
     */
    private function chkInstance($chart)
    {
        if (!$chart instanceof Chart) {
            throw new CException(Ibos::t('error', 'Class "{class}" is illegal.', array('{class}' => get_class($chart))));
        }
    }

}
