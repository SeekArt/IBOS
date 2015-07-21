<?php

namespace application\core\cache\driver;

use application\core\components\Cache;

/**
 * Eaccelerator缓存驱动
 */
class Eaccelerator extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct( $options = array() ) {
        $this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name ) {
        return eaccelerator_get( $this->options['prefix'] . $name );
    }

    /**
     * 写入缓存
     * @access public
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
        eaccelerator_lock( $name );
        if ( eaccelerator_put( $name, $value, $expire ) ) {
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
        return eaccelerator_rm( $this->options['prefix'] . $name );
    }

    /**
     * 清空缓存
     * @return boolean
     */
    public function clear() {
        // first, remove expired content from cache
        eaccelerator_gc();
        // now, remove leftover cache-keys
        $keys = eaccelerator_list_keys();
        foreach ( $keys as $key ) {
            $this->rm( substr( $key['name'], 1 ) );
        }
        return true;
    }

}
