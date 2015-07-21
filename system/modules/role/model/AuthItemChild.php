<?php

/**
 * 认证选项父子关系表数据层
 * @package application.modules.role.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\model;

use application\core\model\Model;

class AuthItemChild extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{auth_item_child}}';
    }

    public function deleteByParent( $parent ) {
        return $this->deleteAll( '`parent` = :parent', array( ':parent' => $parent ) );
    }

    public function deleteByParentWithKeys( $parent, $keys = '', $module = '' ) {
        $routes = array();
        foreach ( explode( ',', $keys ) as $key ) {
            $routes[] = "`child` NOT LIKE '" . $module . '/' . $key . "%' AND ";
        }
        $str = "`parent` = {$parent} AND `child` LIKE '{$module}/%' AND (" . rtrim( implode( '', $routes ), ' AND ' ) . ")";
        return $this->deleteAll( $str );
    }

}
