<?php

/**
 * IWStatRecruitBase class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 总结 - widget base
 * @package application.modules.recruit.widgets
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\widgets;

use application\core\utils\Ibos;
use CWidget;

class StatRecruitBase extends CWidget
{

    /**
     * 统计的类型(日、月、周)
     * @var string
     */
    private $_type = 'day';

    /**
     * 设置统计类型
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * 返回统计类型
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * 选择的时间(本周、上周、本月、上月)
     * @var string
     */
    private $_timestr;

    /**
     * 设置选择的时间
     * @param string $type
     */
    public function setTimestr($timestr)
    {
        $this->_timestr = $timestr;
    }

    /**
     * 返回选择的时间
     * @return string
     */
    public function getTimestr()
    {
        return $this->_timestr;
    }

    /**
     *
     * @param type $class
     * @param type $properties
     * @return type
     */
    protected function createComponent($class, $properties = array())
    {
        return Ibos::createComponent(array_merge(array('class' => $class), $properties));
    }

}
