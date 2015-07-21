<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use CException;

/**
 * Memcache缓存驱动
 */
class Memcache extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct( $options = array() ) {
        if ( !extension_loaded( 'memcache' ) ) {
            throw new CException( IBOS::lang( 'Not support', 'error' ) . ':memcache' );
        }

        $options = array_merge( array(
            'host' => isset( $this->options['host'] ) ? $this->options['host'] : '127.0.0.1',
            'port' => isset( $this->options['port'] ) ? $this->options['port'] : 11211,
            'timeout' => isset( $this->options['timeout'] ) ? $this->options['timeout'] : false,
            'persistent' => false,
                ), $options );

        $this->options = $options;
        $this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->instance = new memcache;
        $options['timeout'] === false ?
                        $this->instance->$func( $options['host'], $options['port'] ) :
                        $this->instance->$func( $options['host'], $options['port'], $options['timeout'] );
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name ) {
        return $this->instance->get( $this->options['prefix'] . $name );
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set( $name, $value, $expire = null ) {
        if ( is_null( $expire ) ) {
            $expire = $this->options['expire'];
        }
        $name = $this->options['prefix'] . $name;
        if ( $this->instance->set( $name, $value, 0, $expire ) ) {
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm( $name, $ttl = false ) {
        $name = $this->options['prefix'] . $name;
        return $ttl === false ?
                $this->instance->delete( $name ) :
                $this->instance->delete( $name, $ttl );
    }

    /**
     * 清除缓存
     * @return boolean
     */
    public function clear() {
        return $this->instance->flush();
    }

}
