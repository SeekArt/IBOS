<?php

/**
 * Counter class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * Counter 是所有图表统计组件的基类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.statistics.core
 * @since 2.0
 */

namespace application\modules\statistics\core;

use CApplicationComponent;

abstract class Counter extends CApplicationComponent
{

    public abstract function getID();

    public abstract function getCount();
}
