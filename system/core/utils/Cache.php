<?php

/**
 * 缓存工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 缓存工具类，提供IBOS缓存组件的简短写法及系统缓存方法封装
 * @package application.core.utils
 * @see application.core.components.cache
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: cache.php -1   $
 */

namespace application\core\utils;

use application\modules\dashboard\model\Syscache;
use CEvent;

class Cache
{

    const CACHE_ALIAS = 'application.core.cache.provider'; // 更新缓存目录别名

    /**
     * 检查缓存组件
     * @return string
     */

    public static function check()
    {
        return Ibos::app()->cache->getIsInitialized();
    }

    /**
     * 设置一个缓存值
     * @param string $key 缓存的key
     * @param mixed $value 缓存的值
     * @param mixed $ttl 缓存的有效期
     * @return boolean
     */
    public static function set($key, $value, $ttl = null)
    {
        return Ibos::app()->cache->set($key, $value, $ttl);
    }

    //@see \CCache
    public static function get($id)
    {
        return Ibos::app()->cache->get($id);
    }

    //@see \CCache
    public static function mget($ids)
    {
        return Ibos::app()->cache->mget($ids);
    }

    /**
     * 根据$key移除一个缓存值
     * @param string $key 缓存的key
     * @return boolean
     */
    public static function rm($key)
    {
        return Ibos::app()->cache->delete($key);
    }

    /**
     * 清空缓存接口
     * @return boolean
     */
    public static function clear()
    {
        return Ibos::app()->cache->flush();
    }

    /**
     * 加载缓存，只是把缓存加载进G里，如果另外发起请求，会消失
     * @staticvar array $loadedCache 已加载的静态缓存数组
     * @param mixed $cacheNames 字符串或数组的缓存名
     * @param boolean $force 强制更新缓存
     * @return boolean
     */
    public static function load($cacheNames, $force = false)
    {
        static $loadedCache = array();
        $cacheNames = is_array($cacheNames) ? $cacheNames : array($cacheNames);
        $caches = array();
        foreach ($cacheNames as $key) {
            if (!isset($loadedCache[$key]) || $force) {
                $caches[] = $key;
                $loadedCache[$key] = true;
            }
        }

        if (!empty($caches)) {
            $cacheData = Syscache::model()->fetchAllCache($caches);
            foreach ($cacheData as $cacheName => $data) {
                if ($cacheName == 'setting') {
                    Ibos::app()->setting->set('setting', $data);
                } else {
                    //TODO::这里要修改缓存路径
                    Ibos::app()->setting->set('cache/' . $cacheName, $data);
                }
            }
        }
        return true;
    }

    /**
     * 更新系统缓存
     * @param string $cacheName 缓存名
     * @param mixed $value 缓存值
     */
    public static function save($cacheName, $value)
    {
        Syscache::model()->addCache($cacheName, $value);
    }

    /**
     * 更新缓存
     * 更新数据库系统缓存表数据
     * @param mixed $cacheName 可以是字符串，也可以是数组
     * @return mixed
     */
    public static function update($cacheName = '')
    {
        $nameSpace = str_replace('.', '\\', self::CACHE_ALIAS);
        $updateList = empty($cacheName) ? array() : (is_array($cacheName) ? $cacheName : array($cacheName));
        if (!$updateList) {
            // 更新所有缓存
            $cacheDir = Ibos::getPathOfAlias(self::CACHE_ALIAS);
            $cacheDirHandle = dir($cacheDir);
            while ($entry = $cacheDirHandle->read()) {
                $isProviderFile = preg_match("/^([\_\w]+)\.php$/", $entry, $matches) && substr($entry, -4) == '.php' && is_file($cacheDir . '/' . $entry);
                if (!in_array($entry, array('.', '..')) && $isProviderFile) {
                    $class = $nameSpace . '\\' . basename($matches[0], '.php');
                    if (class_exists($class)) {
                        Ibos::app()->attachBehavior('onUpdateCache', array('class' => $class));
                    }
                }
            }
        } else {
            // 更新指定缓存
            foreach ($updateList as $entry) {
                $owner = $nameSpace . '\\' . ucfirst($entry);
                if (class_exists($owner)) {
                    Ibos::app()->attachBehavior('onUpdateCache', array('class' => $owner));
                }
            }
        }
        // 发起更新缓存行为
        if (Ibos::app()->hasEventHandler('onUpdateCache')) {
            Ibos::app()->raiseEvent('onUpdateCache', new CEvent(Ibos::app()));
        }
    }

}
