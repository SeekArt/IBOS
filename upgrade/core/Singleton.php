<?php
/**
 * @namespace ibos\upgrade\core
 * @filename Singleton.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2017/1/4 19:18
 */

namespace ibos\upgrade\core;


/**
 * 通用单例代码
 *
 * @package ibos\upgrade\core
 */
abstract class Singleton
{
    protected static $instance;

    final private function __construct()
    {
        $this->init();
    }

    protected function init()
    {
    }

    final public static function getInstance()
    {
        if (isset(static::$instance)) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    final private function __clone()
    {
    }

    final private function __wakeup()
    {
    }
}