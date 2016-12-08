<?php

/**
 * 核心组件------插件管理器组件类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 核心组件------插件管理器组件类，必须继承CApplicationComponent
 * @package application.core.components
 * @version $Id: PlugManager.php 499 2013-06-03 15:13:23Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\core\components;

use application\core\utils\Ibos;
use CApplicationComponent;

abstract class PlugManager extends CApplicationComponent
{

    /**
     * 是否已安装
     * @var boolean
     * @access private
     */
    private $_init = false;

    /**
     * 初始化各自的插件
     * @param string $moduleName 模块名
     */
    public function setInit($moduleName)
    {
        $installedModule = Ibos::app()->getEnabledModule();
        if (isset($installedModule[$moduleName])) {
            Ibos::app()->getModule($moduleName);
            $this->_init = true;
        }
    }

    /**
     * 返回是否已安装标示符
     * @return boolean
     */
    public function getInit()
    {
        return $this->_init;
    }

}
