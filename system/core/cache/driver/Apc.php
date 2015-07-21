<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use CException;

/**
 * Apc缓存驱动
 */
class Apc extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     */
    public function __construct( $options = array() ) {
        if ( !function_exists( 'apc_cache_info' ) ) {
            throw new CException( IBOS::lang( 'Not support', 'error' ) . ':Apc' );
        }
        $this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name ) {
        return apc_fetch( $this->options['prefix'] . $name );
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
        $result = apc_store( $name, $value, $expire );
        return $result;
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm( $name ) {
        return apc_delete( $this->options['prefix'] . $name );
    }

    /**
     * 清除缓存
     * @return boolean
     */
    public function clear() {
        return apc_clear_cache();
    }

}
