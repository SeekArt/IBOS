<?php

/**
 * 权限节点关联表数据层
 * @package application.modules.role.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\model;

use application\core\model\Model;

class NodeRelated extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{node_related}}';
	}

	public function deleteAllByRoleIdWithKeys( $roleid, $exceptKeyS, $module ) {
		$arr = explode( ',', $exceptKeyS );
		$str = "'" . implode( "','", $arr ) . "'";
		$con = sprintf( "`roleid` = %d AND `module` = '%s' AND `key` NOT IN ( %s ) ", $roleid, $module, $str );
		$this->deleteAll( $con );
	}
	/**
	 * 三个值组成唯一值，根据此值查找所属数据权限ID
	 * @param string $id
	 * @param string $roleId 角色id
	 * @return string
	 */
	public function fetchDataValByIdentifier( $id, $roleId ) {
		$record = $this->fetchDataByIdentifier( $id, $roleId );
		return $record ? $record['val'] : '';
	}
	/**
	 * 看懂↑（方法fetchDataValByIdentifier）的注释了么，反正我没看懂
	 * 意思是说：根据由node_related表里的module/key/node（也就是这里的id，把module，key，node换成表里的值）的值，以及roleid获取val的值
	 * 而这个函数是得到整条记录
	 * @param string $id module/key/node的格式的字符串
	 * @param string $roleId 角色id
	 * @return array
	 */
	public function fetchDataByIdentifier( $id, $roleId ) {
		list($module, $key, $node) = explode( '/', $id );
		$criteria = array(
			'select' => 'val',
			'condition' => '`module` = :module AND `key`= :key AND `node` = :node AND `roleid` = :roleid',
			'params' => array( ':module' => $module, ':key' => $key, ':node' => $node, ':roleid' => $roleId )
		);
		$record = $this->fetch( $criteria );
		return $record;
	}

	/**
	 * 根据角色ID查找所有该角色关联记录
	 * @param integer $id 角色id
	 * @return array
	 */
	public function fetchAllByRoleId( $id ) {
		return $this->fetchAllSortByPk( 'id', '`roleid` = :id', array( ':id' => $id ) );
	}

	/**
	 * 根据角色ID删除所有节点关联记录
	 * @param integer $id 角色ID
	 * @return integer
	 */
	public function deleteAllByRoleId( $id ) {
		return $this->deleteAll( sprintf( "roleid = %d AND ( module NOT IN ('crm') OR module = 'crm' AND val = 0 )", $id ) );
	}

	/**
	 * 更新关联记录
	 * @param string $val
	 * @param integer $roleId
	 * @param array $node
	 * @return integer
	 */
	public function addRelated( $val = '', $roleId = 0, $node = array() ) {
		unset( $node['id'] );
		$relatedData = array( 'val' => $val, 'roleid' => $roleId );
		// 处理节点与岗位关联
		$related = array_merge( $node, $relatedData );
		return $this->add( $related, true );
	}

}
