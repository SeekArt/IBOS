<?php

namespace application\core\modules;

use application\core\utils\IBOS;
use application\core\utils\String;
use CEvent;
use CWebModule;

class Module extends CWebModule {
	protected $__MODULE__;
	private $_tips = '&#x6A21;&#x5757;&#x9519;&#x8BEF;&#xFF01;&#x7248;&#x672C;&#x4E0D;&#x7B26;&#x5408;&#x8981;&#x6C42;&#x3002;';
	
	protected function preinit() {
		parent::preinit();
		$modulecode = String::authCode($this->__CODE__, "DECODE", $this->getId());
		if (!empty($this->$modulecode)) {exit($this->_tips);}
		$this->$modulecode = $this->getId();
	}
    /**
     * 模块初始化方法，重写控制器的命名空间，2执行所有模块注册的初始化行为
     */
    final protected function init() {
		if (empty($this->__MODULE__)) {	exit($this->_tips);	}
        $this->controllerNamespace = 'application\modules\\' . $this->__MODULE__ . '\controllers';
        if ( IBOS::app()->hasEventHandler( 'onInitModule' ) ) {
            IBOS::app()->onInitModule( new CEvent( IBOS::app() ) );
        }
        parent::init();
    }
	
}
