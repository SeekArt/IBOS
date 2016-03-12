<?php

/**
 * 新浪云平台引擎文件.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2014 IBOS Inc
 */
/**
 * 新浪云平台环境引擎类
 * 
 * @package application.core.engines
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: sae.php 1826 2013-12-03 01:38:55Z zhangrong $
 */

namespace application\core\engines;

use application\core\components\Engine;
use application\core\engines\sae\SaeIo;
use application\core\utils\IBOS;
use CMap;

class Sae extends Engine {

    /**
     * 实现父类初始化云平台接口方法
     * @param array $appConfig 程序配置
     * @param array $mainConfig 安装配置
     * @return array 组合处理后的配置文件
     */
    public function initConfig( $appConfig, $mainConfig ) {
        $config = array(
            'language' => $mainConfig['env']['language'],
            'runtimePath' => SAE_TMP_PATH, // SAE_TMP_PATH是SAE特有的常量
            'theme' => $mainConfig['env']['theme'],
            'components' => array(
                'db' => array(
                    'charset' => $mainConfig['db']['charset'],
                    'tablePrefix' => $mainConfig['db']['tableprefix'],
                ),
                'cache' => array(
                    'class' => 'application\core\components\Cache',
                    'options' => array(
                        'type' => 'memcachesae', // sae只能使用特定的memcache缓存
                        'prefix' => '',
                        'time' => 0,
                        'compress' => false,
                        'check' => false,
                        'level' => 1,
                    )
                )
            )
        );
        return CMap::mergeArray( $appConfig, $config );
    }

    /**
     * 获取 IO 接口
     * @staticvar null $io
     * @return \application\core\engines\sae\io
     */
    public function io() {
        static $io = null;
        if ( $io == null ) {
            $io = new SaeIo();
        }
        return $io;
    }

    /**
     * 覆盖父类初始化方法，加载sae特有的核心组件类
     * @return void
     */
    protected function init() {
        IBOS::setPathOfAlias( 'engineDriver', IBOS::getPathOfAlias( 'application.core.engines.sae' ) );
        $alias = IBOS::getPathOfAlias( 'engineDriver' );
        $classes = array(
            'CDbCommand' => $alias . '/db/CDbCommand.php',
            'CDbConnection' => $alias . '/db/CDbConnection.php',
        );
        IBOS::$classMap = CMap::mergeArray( IBOS::$classMap, $classes );
    }

}
