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
use application\core\utils\Ibos;

class Local extends Engine
{

    protected function defineConst()
    {
        define('LOCAL', true);
    }

    public function getMainConfig()
    {
        $mainConfigFile = PATH_ROOT . '/system/config/config.php';
        $mainConfig = require($mainConfigFile);
        return $mainConfig;
    }

    /**
     * 本地引擎初始化配置方法
     * @param array $mainConfig 安装配置
     * @return array 处理后的配置
     */
    protected function initConfig($mainConfig)
    {
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
        return $config;
    }

    /**
     * 获取 IO 接口
     * @staticvar null $io
     * @return \application\core\engines\local\io
     */
    public function io()
    {
        static $io = null;
        if ($io == null) {
            $io = new LocalIo();
        }
        return $io;
    }

    /**
     * 设置别名，加载驱动路径
     * @return void
     */
    protected function init()
    {
        // 设置data别名
        Ibos::setPathOfAlias('data', PATH_ROOT . DIRECTORY_SEPARATOR . 'data');
        // 设置引擎驱动别名
        Ibos::setPathOfAlias('engineDriver', Ibos::getPathOfAlias('application.core.engines.local'));
    }

    protected function preinit()
    {
        // 检查安装
        if (!is_file(PATH_ROOT . '/data/install.lock') && !defined('INSTALL_PAGE')) {
            header('Location:./install/');
            exit();
        }
    }

}
