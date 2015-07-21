<?php

namespace application\core\cache\driver;

use application\core\components\Cache;

/**
 * Apachenote缓存驱动
 */
class Apachenote extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     */
    public function __construct( $options = array() ) {
        if ( !empty( $options ) ) {
            $this->options = $options;
        }
        if ( empty( $options ) ) {
            $options = array(
                'host' => '127.0.0.1',
                'port' => 1042,
                'timeout' => 10,
            );
        }
        $this->options = $options;
        $this->instance = null;
        $this->open();
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name ) {
        $this->open();
        $name = $this->options['prefix'] . $name;
        $s = 'F' . pack( 'N', strlen( $name ) ) . $name;
        fwrite( $this->instance, $s );

        for ( $data = ''; !feof( $this->instance ); ) {
            $data .= fread( $this->instance, 4096 );
        }
        $this->close();
        return $data === '' ? '' : unserialize( $data );
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @return boolean
     */
    public function set( $name, $value ) {
        $this->open();
        $value = serialize( $value );
        $name = $this->options['prefix'] . $name;
        $s = 'S' . pack( 'NN', strlen( $name ), strlen( $value ) ) . $name . $value;

        fwrite( $this->instance, $s );
        $ret = fgets( $this->instance );
        $this->close();
        if ( $ret === "OK\n" ) {
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
        $this->open();
        $name = $this->options['prefix'] . $name;
        $s = 'D' . pack( 'N', strlen( $name ) ) . $name;
        fwrite( $this->instance, $s );
        $ret = fgets( $this->instance );
        $this->close();
        return $ret === "OK\n";
    }

    /**
     * 关闭缓存
     */
    private function close() {
        fclose( $this->instance );
        $this->instance = false;
    }

    /**
     * 打开缓存
     */
    private function open() {
        if ( !is_resource( $this->instance ) ) {
            $this->instance = fsockopen( $this->options['host'], $this->options['port'], $_, $_, $this->options['timeout'] );
        }
    }

}
