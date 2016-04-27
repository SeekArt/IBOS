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

    /**
     * 验证模块合法性
     * @param string $module
     * @return boolean 合法与否
     */
    final protected function checkModule( $module ) {
        $fileName = $this->getBasePath() . '/licence.key';
        if ( file_exists( $fileName ) && is_readable( $fileName ) ) {
            $licence = file_get_contents( $fileName );
        } else {
            $licence = '';
        }
        if ( !empty( $licence ) ) {
            $rs = IBOS::app()->licence->readLicence( $licence, false );
            return strcasecmp( $rs, $module ) == 0;
        }
        return false;
    }

}
