<?php

namespace application\core\modules;

use application\core\utils\Env;
use application\core\utils\Ibos;
use CEvent;
use CWebModule;

class Module extends CWebModule
{

    /**
     * 模块初始化方法，重写控制器的命名空间，2执行所有模块注册的初始化行为
     */
    final protected function init()
    {
        $module = $this->getId();
        defined('MODULE_NAME') || define('MODULE_NAME', $module);
        $this->controllerNamespace = 'application\modules\\' . $module . '\controllers';
        if (Ibos::app()->hasEventHandler('onInitModule')) {
            Ibos::app()->onInitModule(new CEvent(Ibos::app()));
        }
        parent::init();
    }

    /**
     * 验证模块合法性
     * @param string $module
     * @return boolean 合法与否
     */
    final protected function checkModule($module)
    {
        return true;
    }

    final protected static function filterOpen()
    {
    }

}
