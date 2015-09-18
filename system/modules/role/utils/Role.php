<?php

/**
 * 角色模块工具类
 * @package application.modules.role.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\utils;

use application\core\utils\Cache;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\core\utils\String;
use application\modules\role\model\NodeRelated;
use application\modules\role\model\RoleRelated;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class Role {

    public static function loadRole() {
        return IBOS::app()->setting->get( 'cache/role' );
    }

    /**
     * 组合某岗位id的节点关联数据，返回适合格式以便编辑页面判断是否有选中权限
     * @param array $related
     * @return array
     */
    public static function combineRelated( $related ) {
        $return = array();
        foreach ( $related as $value ) {
            $return[$value['module']][$value['key']][$value['node']] = $value['val'];
        }
        return $return;
    }

    /**
     * 从岗位维度设置用户的岗位
     * @param integer $roleId 角色id
     * @param array $users 
     * @return boolean
     */
    public static function setRole( $roleId, $users ) {
        // 该岗位原有的用户
        $oldUids = User::model()->fetchUidByRoleId( $roleId, false );
        // 这一次提交的用户
        $userId = explode( ',', trim( $users, ',' ) );
        $newUids = String::getUid( $userId );
        // 找出两种差别
        $delDiff = array_diff( $oldUids, $newUids );
        $addDiff = array_diff( $newUids, $oldUids );
        // 没有可执行操作，直接跳过
        if ( !empty( $addDiff ) || !empty( $delDiff ) ) {
            $updateUser = false;
            // 获取所有用户数据
            $userData = UserUtil::loadUser();
            // 给该角色添加人员
            if ( $addDiff ) {
                foreach ( $addDiff as $newUid ) {
                    $record = $userData[$newUid];
                    // 如果该用户没有设置主角色，设之
                    if ( empty( $record['roleid'] ) ) {
                        User::model()->modify( $newUid, array( 'roleid' => $roleId ) );
                        $updateUser = true;
                    } else if ( strcmp( $record['roleid'], $roleId ) !== 0 ) {
                        // 如果要设置的角色不是该用户当前角色，把该角色添加到辅助角色去
                        RoleRelated::model()->add( array( 'roleid' => $roleId, 'uid' => $newUid ), false, true );
                    }
                }
            }
            // 删除人员
            if ( $delDiff ) {
                foreach ( $delDiff as $diffId ) {
                    $record = $userData[$diffId];
                    RoleRelated::model()->deleteAll( "`roleid`={$roleId} AND `uid`={$diffId}" );
                    if ( strcmp( $roleId, $record['roleid'] ) == 0 ) {
                        User::model()->modify( $diffId, array( 'roleid' => 0 ) );
                        $updateUser = true;
                    }
                }
            }
            // 更新操作
            $updateUser && Cache::update( 'users' );
            Org::update();
        }
    }

    /**
     * 清除指定角色ID的权限缓存
     * @param integer $roleId 角色ID
     * @return void 
     */
    public static function cleanPurvCache( $roleId ) {
        Cache::rm( 'purv_' . $roleId );
    }

    /**
     * 获取指定岗位ID的权限
     * @param integer $roleId 角色ID
     * @return array 角色权限数组，键是路由 (e.g:module/controller/action),值为>0的升序数值
     */
    public static function getPurv( $roleId ) {
        //$access = Cache::get( 'purv_' . $roleId );
        //if ( !$access ) {
            $access = IBOS::app()->getAuthManager()->getItemChildren( $roleId );
            Cache::set( 'purv_' . $roleId, array_flip( array_map( 'strtolower', array_keys( $access ) ) ) );
        //}
        return $access;
    }

    /**
     * 获取住角色和辅助角色中最大的权限(0,1,2,4,8)
     * @param integer $uid 用户id
     * @param string $url 权限路由 (organization/user/manager或organization/user/view等等的1248权限)
     * @return integer 最大权限
     */
    public static function getMaxPurv( $uid, $url ) {
        $user = User::model()->fetchByUid( $uid );
        $roleIds = explode( ',', $user['allroleid'] ); // 所有角色id
        $purvs = array();
        foreach ( $roleIds as $roleId ) {
            $p = NodeRelated::model()->fetchDataValByIdentifier( $url, $roleId );
            $purvs[] = intval( $p );
        }
        $viewPurv = max( $purvs );
        return $viewPurv;
    }

}
