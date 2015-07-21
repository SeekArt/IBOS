<?php

/**
 * IBOS 本地引擎文件.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 */
/**
 * IBOS本地环境引擎
 *
 * @package application.core.engines
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: local.php 3049 2014-04-09 01:19:23Z zhangrong $
 */

namespace application\core\engines;

use application\core\components\Engine;
use application\core\engines\local\LocalIo;
use application\core\utils\IBOS;
use CMap;

class Local extends Engine {

    /**
     * 本地引擎初始化配置方法
     * @param array $appConfig 程序配置
     * @param array $mainConfig 安装配置
     * @return array 处理后的配置
     */
    public function initConfig( $appConfig, $mainConfig ) {
        // 本地环境使用安装时配置的数据库信息
        $connectionString = "mysql:host={$mainConfig['db']['host']};port={$mainConfig['db']['port']};dbname={$mainConfig['db']['dbname']}";
        $config = array(
            'runtimePath' => PATH_ROOT . DIRECTORY_SEPARATOR . 'data/runtime',
            'language' => $mainConfig['env']['language'],
            'theme' => $mainConfig['env']['theme'],
            'components' => array(
                'db' => array(
                    'connectionString' => $connectionString,
                    'username' => $mainConfig['db']['username'],
                    'password' => $mainConfig['db']['password'],
                    'tablePrefix' => $mainConfig['db']['tableprefix'],
                    'charset' => $mainConfig['db']['charset']
                )
            )
        );
        return CMap::mergeArray( $appConfig, $config );
    }

    /**
     * 获取 IO 接口
     * @staticvar null $io
     * @return \application\core\engines\local\io
     */
    public function io() {
        static $io = null;
        if ( $io == null ) {
            $io = new LocalIo();
        }
        return $io;
    }

    /**
     * 设置别名，加载驱动路径
     * @return void
     */
    protected function init() {
        // 设置data别名
        IBOS::setPathOfAlias( 'data', PATH_ROOT . DIRECTORY_SEPARATOR . 'data' );
        // 设置引擎驱动别名
        IBOS::setPathOfAlias( 'engineDriver', IBOS::getPathOfAlias( 'application.core.engines.local' ) );
    }

}
