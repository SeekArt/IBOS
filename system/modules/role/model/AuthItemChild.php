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

	public function deleteByParentExceptRouteA( $parent, $routeA ) {
		$str = sprintf( " parent = %d AND child NOT LIKE %s ", $parent, implode( ' AND child NOT LIKE ', $routeA ) );
		return $this->deleteAll( $str );
	}
	/**
	 * todo：这里是写死的，因为目前只有crm的一些需要过滤，以后有需求需要写成配置原因如下：
	 * crm的权限设置移动到了前台
	 * 所以在后台保存权限节点的时候，就会把crm的data节点给删掉
	 * 这里把这些data节点都过滤，在保存的时候data节点不会被删掉，前台的权限也就不会有问题了
	 * 相应的，crm的权限设置那里由于没有后台的一些node节点，也做了一些过滤
	 */
	public function returnExceptRouteA() {
		return array(
			"'crm/lead/%'",
			"'crm/client/%'",
			"'crm/contact/%'",
			"'crm/opportunity/%'",
			"'crm/contract/%'",
			"'crm/receipt/%'",
			"'crm/event/%'",
		);
	}
}
