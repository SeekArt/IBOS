<?php
/**
 * 通讯录缓存抽象类
 *
 * @namespace application\modules\contact\utils
 * @filename CacheUtilInterface.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/17 8:52
 */

namespace application\modules\contact\utils;

use application\core\utils\Ibos;
use application\core\utils\System;


/**
 * Class AbstractCacheUtil
 *
 * @package application\modules\contact\utils
 */
abstract class AbstractCacheUtil extends System
{
    /**
     * @var string 缓存名，需要自己设置
     */
    protected $cacheName = '';

    /**
     * @var mixed 本地缓存
     */
    protected $localCache = null;

    /**
     * @var bool 是否需要更新缓存
     */
    protected $needUpdate = false;

    /**
     * 抽象方法：建立缓存
     *
     * @return boolean
     */
    abstract public function buildCache();

    /**
     * 初始化方法：禁止用户使用 new 关键字创建该类 && 检查 CACHE_NAME 是否设置
     */
    protected function __construct()
    {
        $this->check();
    }

    /**
     * 禁止用户使用 clone 关键字克隆对象
     */
    private function __clone()
    {
    }

    /**
     * 禁止用户通过 unserialize 方法创建对象
     */
    private function __wakeup()
    {
    }

    /**
     * 在程序终止前，才去更新系统缓存
     */
    public function __destruct()
    {
        if ($this->needUpdate) {
            $this->setCache($this->localCache);
        }
    }


    /**
     * 检查用户是否设置了 CACHE_NAME
     */
    private function check()
    {
        if (empty($this->cacheName)) {
            throw new \Exception('CACHE_NAME MUST BE SET!');
        }

        return true;
    }

    /**
     * @param string $className
     * @return object
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }


    /**
     * 获取缓存
     *
     * @return mixed 如果不存在缓存，则返回 false
     */
    public function getCache()
    {
        if (empty($this->localCache)) {
            // 避免直接从缓存中拿数据，减少反序列的性能损失
            $this->localCache = Ibos::app()->cache->get($this->cacheName);
        }
        
        return $this->localCache;
    }

    /**
     * 更新本地缓存值，在系统结束前，会将新的缓存值写入系统缓存
     *
     * @param mixed $cacheValue
     */
    public function updateLocalCache($cacheValue)
    {
        $this->localCache = $cacheValue;
        $this->needUpdate = true;
    }

    /**
     * 设置缓存
     *
     * @param mixed $cacheValue 缓存值
     * @param int $expire 缓存的生命周期，单位秒，0表示无限
     * @return bool 设置缓存是否成功
     */
    public function setCache($cacheValue, $expire = 0)
    {
        return Ibos::app()->cache->set($this->cacheName, $cacheValue, $expire);
    }

    /**
     * 删除缓存
     *
     * @return boolean 删除缓存是否成功
     */
    public function rmCache()
    {
        // 清空本地缓存
        $this->localCache = null;

        return Ibos::app()->cache->delete($this->cacheName);
    }

    /**
     * 重新建立缓存
     */
    public function rebuildCache()
    {
        $this->rmCache();
        $this->buildCache();
    }
}