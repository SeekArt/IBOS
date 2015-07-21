<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use CException;

/**
 * Shmop缓存驱动 
 */
class Shmop extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct( $options = array() ) {
        if ( !extension_loaded( 'shmop' ) ) {
            throw new CException( IBOS::lang( 'Not support', 'error' ) . ':shmop' );
        }
        if ( !empty( $options ) ) {
            $options = array(
                'size' => isset( $this->options['size'] ) ? $this->options['size'] : 100,
                'temp' => IBOS::app()->getRuntimePath(),
                'project' => 's',
                'length' => 0,
            );
        }
        $this->options = $options;
        $this->instance = $this->_ftok( $this->options['project'] );
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name = false ) {
        $id = shmop_open( $this->instance, 'c', 0600, 0 );
        if ( $id !== false ) {
            $ret = unserialize( shmop_read( $id, 0, shmop_size( $id ) ) );
            shmop_close( $id );

            if ( $name === false ) {
                return $ret;
            }
            $name = $this->options['prefix'] . $name;
            if ( isset( $ret[$name] ) ) {
                $content = $ret[$name];
                if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
                    //启用数据压缩
                    $content = gzuncompress( $content );
                }
                return $content;
            } else {
                return null;
            }
        } else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @return boolean
     */
    public function set( $name, $value ) {
        $lh = $this->_lock();
        $val = $this->get();
        if ( !is_array( $val ) ) {
            $val = array();
        }
        if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
            //数据压缩
            $value = gzcompress( $value, 3 );
        }
        $name = $this->options['prefix'] . $name;
        $val[$name] = $value;
        $val = serialize( $val );
        if ( $this->_write( $val, $lh ) ) {
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
        $lh = $this->_lock();
        $val = $this->get();
        if ( !is_array( $val ) ) {
            $val = array();
        }
        $name = $this->options['prefix'] . $name;
        unset( $val[$name] );
        $val = serialize( $val );
        return $this->_write( $val, $lh );
    }

    /**
     * 生成IPC key
     * @param string $project 项目标识名
     * @return integer
     */
    private function _ftok( $project ) {
        if ( function_exists( 'ftok' ) ) {
            return ftok( __FILE__, $project );
        }
        if ( strtoupper( PHP_OS ) == 'WINNT' ) {
            $s = stat( __FILE__ );
            return sprintf( "%u", (($s['ino'] & 0xffff) | (($s['dev'] & 0xff) << 16) |
                    (($project & 0xff) << 24) ) );
        } else {
            $filename = __FILE__ . (string) $project;
            for ( $key = array(); sizeof( $key ) < strlen( $filename ); $key[] = ord( substr( $filename, sizeof( $key ), 1 ) ) )
                ;
            return dechex( array_sum( $key ) );
        }
    }

    /**
     * 写入操作
     * @param string $name 缓存变量名
     * @return integer|boolean
     */
    private function _write( &$val, &$lh ) {
        $id = shmop_open( $this->instance, 'c', 0600, $this->options['size'] );
        if ( $id ) {
            $ret = shmop_write( $id, $val, 0 ) == strlen( $val );
            shmop_close( $id );
            $this->_unlock( $lh );
            return $ret;
        }
        $this->_unlock( $lh );
        return false;
    }

    /**
     * 共享锁定
     * @param string $name 缓存变量名
     * @return boolean
     */
    private function _lock() {
        if ( function_exists( 'sem_get' ) ) {
            $fp = sem_get( $this->instance, 1, 0600, 1 );
            sem_acquire( $fp );
        } else {
            $fp = fopen( $this->options['temp'] . $this->options['prefix'] . md5( $this->instance ), 'w' );
            flock( $fp, LOCK_EX );
        }
        return $fp;
    }

    /**
     * 解除共享锁定
     * @param string $name 缓存变量名
     * @return boolean
     */
    private function _unlock( &$fp ) {
        if ( function_exists( 'sem_release' ) ) {
            sem_release( $fp );
        } else {
            fclose( $fp );
        }
    }

}
