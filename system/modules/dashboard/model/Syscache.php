<?php

/**
 * syscache表的数据层操作文件。
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * syscache表的数据层操作
 * 
 * @package application.modules.main.model
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: Syscache.php 4064 2014-09-03 09:13:16Z zhangrong $
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Cache;

class Syscache extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{syscache}}';
    }

    /**
     * 增加与替换一个系统缓存
     * @param string $cacheName 缓存名称
     * @param mixed $data 缓存数据
     */
    public function addCache( $cacheName, $data ) {
        $this->add( array(
            'name' => $cacheName,
            'type' => is_array( $data ) ? 1 : 0,
            'dateline' => TIMESTAMP,
            'value' => is_array( $data ) ? serialize( $data ) : $data,
                ), false, true );

        if ( Cache::get( $cacheName ) !== false ) {
            Cache::set( $cacheName, $data );
        }
    }

    /**
     * 重写父类增加方法实现syscache自身业务需求:实现更新与保存操作
     * @param array $attributes 要插入的数据
     * @param boolean $returnNewId 是否返回插入的ID
     * @return mixed
     */
    public function modify( $pk, $attributes = null ) {
        $data = $this->handleData( $attributes );
        Cache::set( $pk, $data['value'] );
        return $this->updateAll( $data, 'name = :name', array( ':name' => $pk ) );
    }

    /**
     * 获取指定缓存
     * @param mixed $cacheNames
     * @return null
     */
    public function fetchAllCache( $cacheNames ) {
        $cacheNames = is_array( $cacheNames ) ? $cacheNames : array( $cacheNames );
        // 从内存中读取缓存
        $data = Cache::get( $cacheNames );
        if ( is_array( $data ) && in_array( false, $data, true ) || !$data ) {
            $data = false;
        }
        $newArray = $data !== false ? array_diff( $cacheNames, array_keys( $data ) ) : $cacheNames;
        //如果缓存中存在数据
        if ( empty( $newArray ) ) {
            foreach ( $data as &$cache ) {
                $isSerialized = ($cache == serialize( false ) || @unserialize( $cache ) !== false);
                $cache = $isSerialized ? unserialize( $cache ) : $cache;
            }
            //返回数据
            return $data;
        } else {
            $cacheNames = $newArray;
        }
        // 不存在缓存中，则查找syscache中的信息
        $caches = $this->fetchAll( sprintf( "FIND_IN_SET(name,'%s')", implode( ',', $cacheNames ) ) );
        if ( $caches ) {
            foreach ( $caches as $sysCache ) {
                $data[$sysCache['name']] = $sysCache['type'] ? unserialize( $sysCache['value'] ) : $sysCache['value'];
                //把数据写到缓存中
                Cache::set( $sysCache['name'], $data[$sysCache['name']] );
            }
            foreach ( $cacheNames as $name ) {
                if ( $data[$name] === null ) {
                    $data[$name] = null;
                    Cache::rm( $name );
                }
            }
        }
        return $data;
    }

    /**
     * 封装要更新或插入的syscache表数据
     * @param array $attributes 数据
     * @return array 处理后的数据
     */
    private function handleData( $attributes ) {
        $value = is_array( $attributes ) ? serialize( $attributes ) : $attributes;
        $data = array(
            'type' => is_array( $attributes ) ? 1 : 0,
            'dateline' => time(),
            'value' => $value,
        );
        return $data;
    }

}
