<?php

/**
 * Ibos缓存处理类
 *
 * @package application.core.components
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id$ 
 */

namespace application\core\components;

use application\core\utils\IBOS;
use CApplicationComponent;
use CException;

class Cache extends CApplicationComponent {

    /**
     * 操作句柄
     * @var mixed
     */
    protected $instance;

    /**
     * 缓存连接参数
     * @var array
     */
    protected $options = array();

    /**
     * 设置缓存链接参数
     * @param array $options
     */
    public function setOptions( $options = array() ) {
        $this->options = $options;
    }

    /**
     * 获取缓存链接参数
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * 获取支持的缓存扩展
     * @return array
     */
    public function getExtension() {
        return array(
            'apc' => function_exists( 'apc_cache_info' ) && @apc_cache_info(),
            'eaccelerator' => function_exists( 'eaccelerator_get' ),
            'xcache' => function_exists( 'xcache_get' ),
            'wincache' => extension_loaded( 'wincache' ),
            'db' => 1,
            'file' => LOCAL ? 1 : 0,
            'memcache' => extension_loaded( 'memcache' ),
            'redis' => extension_loaded( 'redis' ),
            'shmop' => extension_loaded( 'shmop' ),
            'sqlite' => extension_loaded( 'sqlite' ),
        );
    }

    /**
     * 组件初始化
     * @throws CException
     */
    public function init() {
        $options = $this->options;
        $type = $options['type'];
        unset( $options['type'] );
        if ( empty( $type ) ) {
            $type = $this->options['type'] = 'file';
        }
        $class = strpos( $type, '\\' ) ? $type : 'application\\core\\cache\\driver\\' . ( ucfirst( strtolower( $type ) ) );
        if ( class_exists( $class ) ) {
            $this->instance = new $class( $options );
            parent::init();
        } else {
            throw new CException( IBOS::lang( 'Cache init error', 'error' ) );
        }
    }

    /**
     * 取得一个缓存里的值
     * @staticvar null $getMulti 是否批量获取
     * @param mixed $key 缓存的key
     * @param string $prefix 是否有前缀
     * @return boolean
     */
    public function get( $key ) {
        static $getMulti = null;
        $result = false;
        // 检查其引用实例有无批量获取的方法
        if ( !isset( $getMulti ) ) {
            $getMulti = method_exists( $this->instance, 'getMulti' );
        }
        if ( is_array( $key ) ) {
            if ( $getMulti ) {
                $result = $this->instance->getMulti( $key );
                if ( $result !== false && !empty( $result ) ) {
                    $_result = array();
                    foreach ( (array) $result as $_key => $value ) {
                        $_result[$_key] = $value;
                    }
                    $result = $_result;
                }
            } else {
                $result = array();
                $_result = false;
                foreach ( $key as $id ) {
                    if ( ($_result = $this->instance->get( $id ) ) !== false && isset( $_result ) ) {
                        $result[$id] = $_result;
                    }
                }
            }
            if ( empty( $result ) ) {
                $result = false;
            }
        } else {
            $result = $this->instance->get( $key );
            if ( !isset( $result ) ) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * 设置一个缓存
     * @param string $key 缓存key
     * @param mixed $value 缓存的值
     * @param mixed $ttl 过期时间
     * @return boolean $result
     */
    public function set( $key, $value, $ttl = null ) {
        return $this->instance->set( $key, $value, $ttl );
    }

    /**
     * 移除一个缓存
     * @param string $key 缓存的key
     * @param string $prefix 是否有前缀
     * @return bool $result
     */
    public function rm( $key ) {
        return $this->instance->rm( $key );
    }

    /**
     * 清除所有缓存
     * @return bool $result
     */
    public function clear() {
        if ( method_exists( $this->instance, 'clear' ) ) {
            return $this->instance->clear();
        }
        return true;
    }

}
