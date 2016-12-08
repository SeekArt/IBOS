<?php

/**
 * IBOS引擎驱动文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
/**
 * 引擎驱动抽象父类,初始化程序配置文件，提供IO与初始化配置接口给子类扩展
 *
 * @package application.core.utils
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id$
 */

namespace application\core\components;

abstract class Engine
{

    /**
     * 当前引擎处理过后的配置文件
     * @var array
     */
    private $_engineConfig;


    /**
     * 构造方法，初始化安装config与程序config,调用子类特定的引擎配置方法
     * @param array $appConfig 程序配置数组
     */
    final function __construct()
    {
        $this->defineConst();
        $this->preinit();
        $mainConfig = $this->getMainConfig();
        $this->_engineConfig = $this->initConfig($mainConfig);
        $this->init();
    }

    /**
     * 定义各个引擎需要的常量
     */
    abstract protected function defineConst();

    /**
     * 主配置文件：即安装程序生成的配置文件
     * @return array
     */
    abstract public function getMainConfig();

    /**
     * 获取当前引擎处理过后的配置文件
     * @return array
     */
    public function getEngineConfig()
    {
        return (array)$this->_engineConfig;
    }

    /**
     * 开始配置前的预处理，子类应重新实现该方法
     * @return void
     */
    abstract protected function init();

    abstract protected function preinit();

    /**
     * 子类应实现初始化各自引擎的配置文件方法
     */
    abstract protected function initConfig($mainConfig);

    /**
     * io 接口
     */
    abstract public function io();

}
