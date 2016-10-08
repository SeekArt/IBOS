<?php

/**
 * 角色关联数据层
 * @package application.modules.role.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\user\model\User;

class RoleRelated extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{role_related}}';
	}

	/**
	 * 查询所有辅助角色的用户，以roleid分组
	 * @return array
	 */
	public function fecthAllUserGroudByRoleId() {
		$res = array();
		$records = $this->fetchAll();
		foreach ( $records as $row ) {
			$res[$row['roleid']][$row['uid']] = User::model()->fetchByUid( $row['uid'] );
		}
		return $res;
	}

	/**
	 * 根据uid查找辅助角色ID
	 * @param integer $uid 用户id
	 * @return array
	 */
	public function fetchAllRoleIdByUid( $uid ) {
		static $uids = array();
		if ( !isset( $uids[$uid] ) ) {
			$roleids = Ibos::app()->db->createCommand()
					->select( 'roleid' )
					->from( $this->tableName() )
					->where( " `uid` = '{$uid}' " )
					->queryColumn();
			$uids[$uid] = $roleids;
		}
		return $uids[$uid];
	}

	/**
	 *
	 * @param type $roleId
	 * @return type
	 */
	public function countByRoleId( $roleId ) {
		return $this->count( '`roleid` = :roleid', array( ':roleid' => $roleId ) );
	}

	public function findRoleidIndexByUidX( $uidX = NULL ) {
		if ( NULL === $uidX ) {
			$condition = 1;
		} else if ( empty( $uidX ) ) {
			return array();
		} else {
			$condition = User::model()->uid_find_in_set( $uidX );
		}
		$related = Ibos::app()->db->createCommand()
				->select( 'uid,roleid' )
				->from( $this->tableName() )
				->where( $condition )
				->queryAll();
		$return = array();
		if ( !empty( $related ) ) {
			foreach ( $related as $row ) {
				$return[$row['uid']][] = $row['roleid'];
			}
		}
		return $return;
	}

}
