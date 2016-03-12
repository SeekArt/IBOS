<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use CException;

/**
 * Apc缓存驱动
 *
 * @namespace application\core\cache\driver
 * @filename Apc.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2015-12-29 11:45:48
 * @version $Id$
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
