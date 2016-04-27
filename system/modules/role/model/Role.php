<?php

/**
 * 角色表数据层
 * @package application.modules.role.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\modules\user\model\User;

class Role extends Model {

    const ADMIN_TYPE = '1'; //管理员角色
    const NORMAL_TYPE = '0';

    public function init() {
        $this->cacheLife = 0;
        parent::init();
    }

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{role}}';
    }

    public function afterSave() {
        CacheUtil::update( 'Role' );
        CacheUtil::load( 'Role' );
        parent::afterSave();
    }

    public function roleid_find_in_set( $roleidX, $pre = '' ) {
        $preString = empty( $pre ) ? $pre : '`' . $pre . '`.';
        $roleidString = is_array( $roleidX ) ? implode( ',', $roleidX ) : $roleidX;
        return " FIND_IN_SET( {$preString}`roleid`, '{$roleidString}') ";
    }

    /**
     * 查找所有角色，带用户
     * @return array
     */
    public function fetchRolesWithUser( $roletype = self::NORMAL_TYPE ) {
        $roles = $this->fetchAllSortByPk( 'roleid', sprintf(
                        " `roletype` = '%s' ", $roletype ) );
        $relatedUsers = RoleRelated::model()->fecthAllUserGroudByRoleId();
        foreach ( $roles as $k => $role ) {
            $roles[$k]['users'] = isset( $relatedUsers[$role['roleid']] ) ? $relatedUsers[$role['roleid']] : array();
        }
        User::model()->setSelect( 'uid,roleid,realname' );
        $users = User::model()->findUserIndexByUid();
        $roleidArray = $roleArray = array();
        if ( !empty( $users ) ) {
            foreach ( $users as $user ) {
                $roleidArray[] = $user['roleid'];
            }
            $roleArray = $this->getRoleNameIndexByRoleidX( $roleidArray );
        }
        foreach ( $users as $user ) {
            $uid = $user['uid'];
            $roleid = isset( $user['roleid'] ) ? $user['roleid'] : 0;
            if ( isset( $roles[$roleid] ) ) { // 主角色
                $roles[$roleid]['users'][$uid] = array(
                    'uid' => $user['uid'],
                    'roleid' => $user['roleid'],
                    'rolename' => !empty( $roleArray[$user['roleid']] ) ? $roleArray[$user['roleid']] : '',
                    'realname' => $user['realname'],
                    'avatar_small' => Org::getDataStatic( $user['uid'], 'avatar', 'small' )
                );
            }
        }
        return $roles;
    }

    /**
     * 通过roleid获取角色名
     * @param type $roleid
     * @return type
     */
    public function getRoleNameByRoleid( $roleid ) {
        $rolename = IBOS::app()->db->createCommand()
                ->select( 'rolename' )
                ->from( $this->tableName() )
                ->where( sprintf( " `roleid` = '%s' ", $roleid ) )
                ->queryScalar();
        return $rolename;
    }

    public function getRoleNameIndexByRoleidX( $roleidX ) {
        $roleArray = IBOS::app()->db->createCommand()
                ->select()
                ->from( $this->tableName() )
                ->where( $this->roleid_find_in_set( $roleidX ) )
                ->queryAll();
        $return = array();
        foreach ( $roleArray as $role ) {
            $return[$role['roleid']] = $role['rolename'];
        }
        return $return;
    }

}
