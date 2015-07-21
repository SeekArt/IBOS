<?php

/**
 * 权限节点数据层
 * @package application.modules.role.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\model;

use application\core\model\Model;

class Node extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{node}}';
    }

    /**
     * 获取所有根节点
     * @return array
     */
    public function fetchAllEmptyNode() {
        return $this->fetchAll( "`node` = ''" );
    }

    /**
     * 查找所有数据节点，使之旗下路由作为key,值为关联表对应的Identifier
     * @return array
     */
    public function fetchAllDataNode() {
        static $dataNodes = array();
        if ( empty( $dataNodes ) ) {
            $record = $this->fetchAll( "`type` = 'data' AND `node` != ''" );
            foreach ( $record as $node ) {
                $routes = explode( ',', $node['routes'] );
                foreach ( $routes as $route ) {
                    $dataNodes[strtolower( $route )] = strtolower( sprintf( '%s/%s/%s', $node['module'], $node['key'], $node['node'] ) );
                }
            }
        }
        return $dataNodes;
    }

    /**
     * 获取所有非根节点，按module和key确定唯一值
     * @param string $module
     * @param string $key
     * @return array
     */
    public function fetchAllNotEmptyNodeByModuleKey( $module, $key ) {
        $params = array( ':module' => $module, ':key' => $key );
        return $this->fetchAll( "`node` != '' AND `module` = :module AND `key` = :key", $params );
    }

}
