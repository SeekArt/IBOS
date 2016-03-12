<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use application\core\utils\String;
use CException;

/**
 * Sqlite缓存驱动
 */
class Sqlite extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     */
    public function __construct( $options = array() ) {
        if ( !extension_loaded( 'sqlite' ) ) {
            throw new CException( IBOS::lang( 'Not support', 'error' ) . ':sqlite' );
        }
        if ( empty( $options ) ) {
            $options = array(
                'db' => ':memory:',
                'table' => 'sharedmemory',
            );
        }
        $this->options = $options;
        $this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;

        $func = isset( $this->options['persistent'] ) ? 'sqlite_popen' : 'sqlite_open';
        $this->instance = $func( $this->options['db'] );
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name ) {
        $name = $this->options['prefix'] . sqlite_escape_string( $name );
        $sql = 'SELECT value FROM ' . $this->options['table'] . ' WHERE var=\'' . $name . '\' AND (expire=0 OR expire >' . time() . ') LIMIT 1';
        $result = sqlite_query( $this->instance, $sql );
        if ( sqlite_num_rows( $result ) ) {
            $content = sqlite_fetch_single( $result );
            if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
                //启用数据压缩
                $content = gzuncompress( $content );
            }
            return String::utf8Unserialize( $content );
        }
        return false;
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set( $name, $value, $expire = null ) {
        $name = $this->options['prefix'] . sqlite_escape_string( $name );
        $value = sqlite_escape_string( serialize( $value ) );
        if ( is_null( $expire ) ) {
            $expire = $this->options['expire'];
        }
        $expire = ($expire == 0) ? 0 : (time() + $expire); //缓存有效期为0表示永久缓存
        if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
            //数据压缩
            $value = gzcompress( $value, 3 );
        }
        $sql = 'REPLACE INTO ' . $this->options['table'] . ' (var, value,expire) VALUES (\'' . $name . '\', \'' . $value . '\', \'' . $expire . '\')';
        if ( sqlite_query( $this->instance, $sql ) ) {
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
        $name = $this->options['prefix'] . sqlite_escape_string( $name );
        $sql = 'DELETE FROM ' . $this->options['table'] . ' WHERE var=\'' . $name . '\'';
        sqlite_query( $this->instance, $sql );
        return true;
    }

    /**
     * 清除缓存
     * @return boolean
     */
    public function clear() {
        $sql = 'DELETE FROM ' . $this->options['table'];
        sqlite_query( $this->instance, $sql );
        return;
    }

}
