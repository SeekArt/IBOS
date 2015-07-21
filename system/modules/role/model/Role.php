<?php

/**
 * 角色表数据层
 * @package application.modules.role.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\model;

use application\core\model\Model;
use application\modules\user\utils\User;

class Role extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{role}}';
    }

    /**
     * 查找所有角色，带用户
     * @return array
     */
    public function fetchRolesWithUser() {
        $roles = $this->fetchAllSortByPk( 'roleid' );
        $relatedUsers = RoleRelated::model()->fecthAllUserGroudByRoleId();
        foreach ( $roles as $k => $role ) {
            $roles[$k]['users'] = isset( $relatedUsers[$role['roleid']] ) ? $relatedUsers[$role['roleid']] : array();
        }
        $users = User::loadUser();
        foreach ( $users as $user ) {
            $uid = $user['uid'];
            $roleid = isset( $user['roleid'] ) ? $user['roleid'] : 0;
            if ( isset( $roles[$roleid] ) ) { // 主角色
                $roles[$roleid]['users'][$uid] = $user;
            } else {
                
            }
        }
        return $roles;
    }

}
