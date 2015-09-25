<?php

namespace application\core\modules;

use application\core\utils\IBOS;
use CEvent;
use CWebModule;

class Module extends CWebModule {
	
    /**
     * 模块初始化方法，重写控制器的命名空间，2执行所有模块注册的初始化行为
     */
    final protected function init() {
        $module = $this->getId();
        $this->controllerNamespace = 'application\modules\\' . $module . '\controllers';
        if ( IBOS::app()->hasEventHandler( 'onInitModule' ) ) {
            IBOS::app()->onInitModule( new CEvent( IBOS::app() ) );
        }
        parent::init();
    }
	
}
