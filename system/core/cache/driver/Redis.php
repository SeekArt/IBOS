<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use CException;

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     */
    public function __construct( $options = array() ) {
        if ( !extension_loaded( 'redis' ) ) {
            throw new CException( IBOS::lang( 'Not support', 'error' ) . ':redis' );
        }
        if ( empty( $options ) ) {
            $options = array(
                'host' => isset( $this->options['host'] ) ? $this->options['host'] : '127.0.0.1',
                'port' => isset( $this->options['port'] ) ? $this->options['port'] : 6379,
                'timeout' => isset( $this->options['timeout'] ) ? $this->options['timeout'] : false,
                'persistent' => false,
            );
        }
        $this->options = $options;
        $this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->instance = new redis;
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
        $value = $this->instance->get( $this->options['prefix'] . $name );
        $jsonData = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData; //检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
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
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object( $value ) || is_array( $value )) ? json_encode( $value ) : $value;
        if ( is_int( $expire ) ) {
            $result = $this->instance->setex( $name, $expire, $value );
        } else {
            $result = $this->instance->set( $name, $value );
        }
        return $result;
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm( $name ) {
        return $this->instance->delete( $this->options['prefix'] . $name );
    }

    /**
     * 清除缓存
     * @return boolean
     */
    public function clear() {
        return $this->instance->flushDB();
    }

}
