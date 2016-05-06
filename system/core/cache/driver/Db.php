<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;

/**
 * 数据库方式缓存驱动
 *    CREATE TABLE ibos_cache (
 *      `cachekey` varchar(255) NOT NULL,
 *      `cachevalue` mediumblob NOT NULL,
 *      `dateline` int(10) unsigned NOT NULL DEFAULT '0',
 *      UNIQUE KEY `cachekey` (`cachekey`)
 *    );
 */
class Db extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     */
    public function __construct( $options = array() ) {
        if ( empty( $options ) ) {
            $options = array(
                'table' => 'cache',
            );
        }
        $this->options = $options;
        $this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;
        $this->instance = IBOS::app()->db;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get( $name ) {
        $name = $this->options['prefix'] . addslashes( $name );
        $result = $this->instance->select( 'cachevalue' )
                ->from( '{{' . $this->options['table'] . '}}' )
                ->where( '`cachekey`=' . $name )
                ->queryScalar();
        if ( false !== $result ) {
            if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
                //启用数据压缩
                $content = gzuncompress( $result );
            }
            $content = StringUtil::utf8Unserialize( $result );
            return $content;
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
        $data = serialize( $value );
        $name = $this->options['prefix'] . addslashes( $name );
        if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
            //数据压缩
            $data = gzcompress( $data, 3 );
        }
        $result = $this->instance->select( 'cachekey' )
                ->from( $this->options['table'] )
                ->where( '`cachekey`=' . $name )
                ->queryScalar();
        if ( !empty( $result ) ) {
            //更新记录
            $result = $this->instance->setText( 'UPDATE {{' . $this->options['table'] . '}} SET `cachevalue`=\'' . $data . '\' WHERE `cachekey`=\'' . $name . '\'' )->execute();
        } else {
            //新增记录
            $result = $this->instance->setText( 'INSERT INTO {{' . $this->options['table'] . '}} (`cachekey`,`cachevalue`,`dateline`) VALUES (\'' . $name . '\',\'' . $data . '\',' . time() . ')' )->execute();
        }
        if ( $result ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm( $name ) {
        $name = $this->options['prefix'] . addslashes( $name );
        return $this->instance->setText( 'DELETE FROM `{{' . $this->options['table'] . '}}` WHERE `cachekey`=\'' . $name . '\'' )->execute();
    }

    /**
     * 清除缓存
     * @return boolean
     */
    public function clear() {
        return $this->instance->setText( 'TRUNCATE TABLE `{{' . $this->options['table'] . '}}`' )->execute();
    }

}
