<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use CException;

/**
 * Xcache缓存驱动
 */
class Xcache extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct( $options = array() ) {
        if ( !function_exists( 'xcache_info' ) ) {
            throw new CException( IBOS::lang( 'Not support', 'error' ) . ':Xcache' );
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
        if ( xcache_isset( $name ) ) {
            return xcache_get( $name );
        }
        return false;
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set( $name, $value, $expire = null ) {
        if ( is_null( $expire ) ) {
            $expire = $this->options['expire'];
        }
        $name = $this->options['prefix'] . $name;
        if ( xcache_set( $name, $value, $expire ) ) {
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
        return xcache_unset( $this->options['prefix'] . $name );
    }

    /**
     * 清空缓存
     * @return boolean
     */
    public function clear() {
        for ( $i = 0, $max = xcache_count( XC_TYPE_VAR ); $i < $max; $i++ ) {
            if ( xcache_clear_cache( XC_TYPE_VAR, $i ) === false ) {
                return false;
            }
        }
        return true;
    }

}
