<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use CException;

/**
 * Wincache缓存驱动
 */
class Wincache extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     */
    public function __construct( $options = array() ) {
        if ( !function_exists( 'wincache_ucache_info' ) ) {
            throw new CException( IBOS::lang( 'Not support', 'error' ) . ':Wincache' );
        }
        $this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name ) {
        $name = $this->options['prefix'] . $name;
        return wincache_ucache_exists( $name ) ? wincache_ucache_get( $name ) : false;
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
        if ( wincache_ucache_set( $name, $value, $expire ) ) {
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm( $name ) {
        return wincache_ucache_delete( $this->options['prefix'] . $name );
    }

    /**
     * 清除缓存
     * @return boolean
     */
    public function clear() {
        return wincache_ucache_clear();
    }

}
