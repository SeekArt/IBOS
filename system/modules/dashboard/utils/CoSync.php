<?php

/**
 * 同步用户以及组织架构工具类
 * @version 1.0  2015-9-11 15:06:25
 * @author Sam  <gzxgs@ibos.com.cn>
 */

namespace application\modules\dashboard\utils;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;

class CoSync {

    /**
     * 根据酷办公新增的用户列表，创建 IBOS 的用户同步绑定关系
     * @param  array $userList 跟绑定表对比，酷办公新增的用户列表
     * @return array
     */
    public static function createUserAndBindRelation( $userList ) {
        $result = array();
        foreach ( $userList as $key => $user ) {
            $checkIsExist = User::model()->checkIsExistByMobile( $user['mobile'] );
            // 手机号不存在，创建一个新用户并建立绑定关系
            if ( $checkIsExist === FALSE ) {
                $user['salt'] = !empty( $user['salt'] ) ? $user['salt'] : StringUtil::random( 6 );
                $user['realname'] = !empty( $user['realname'] ) ? $user['realname'] : '';
                $user['password'] = !empty( $user['password'] ) ? $user['password'] : md5( md5( $user['mobile'] ) . $user['salt'] );
                $user['groupid'] = !empty( $user['groupid'] ) ? $user['groupid'] : '2';
                $user['guid'] = !empty( $user['guid'] ) ? $user['guid'] : StringUtil::createGuid();
                $user['deptid'] = !empty( $user['deptid'] ) ? $user['deptid'] : '';
                $user['createtime'] = TIMESTAMP;
                $data = User::model()->create( $user );
                unset( $data['uid'] );
                $data['roleid'] = 3;
                $newId = User::model()->add( $data, true );
                if ( $newId ) {
                    UserCount::model()->add( array( 'uid' => $newId ) );
                    $ip = Ibos::app()->setting->get( 'clientip' );
                    UserStatus::model()->add( array( 'uid' => $newId, 'regip' => $ip, 'lastip' => $ip ) );
                    UserProfile::model()->add( array( 'uid' => $newId ) ); //用户user_profile一定要有相关的用户数据，即使为空，要不然会出错
                    //创建用户绑定
                    $condition = "`uid` = :uid AND `bindvalue` = :bindvalue AND `app` = 'co'";
                    $params = array( ':uid' => $newId, ':bindvalue' => $user['guid'] );
                    $userBind = UserBinding::model()->fetch( $condition, $params );
                    if ( empty( $userBind ) ) {
                        $binding = UserBinding::model()->add( array( 'uid' => $newId, 'bindvalue' => $user['guid'], 'app' => 'co' ) );
                        if ( $binding ) {
                            $newUser = User::model()->fetchByPk( $newId );
                            $result[] = array( 'uid' => $user['uid'], 'bindvalue' => $newId );
                        }
                    }
                    // if ( !$binding ) {
                    // 	$error[$key]['uid']			= $user['uid'];
                    // 	$error[$key]['realname']	= $user['realname'];
                    // 	$error[$key]['mobile']		= $user['mobile'];
                    // 	$error[$key]['errormsg']	= '绑定用户出错';
                    // }
                }
                // else {
                // 	$error[$key]['uid']			= $user['uid'];
                // 	$error[$key]['realname']	= $user['realname'];
                // 	$error[$key]['mobile']		= $user['mobile'];
                // 	$error[$key]['errormsg']	= '创建用户出错';
                // }
            }
            // 酷办公用户手机号已存在 IBOS
            // 什么都不管，先把 IBOS 对应的用户设为启用（原来可能启用可能没启用）
            // 然后添加对应的绑定关系
            else {
                $userInfo = User::model()->fetch( '`mobile` = :mobile', array( ':mobile' => $user['mobile'] ) );
                User::model()->updateByPk( $userInfo['uid'], array( 'status' => 0 ) );
                //创建用户绑定关系
                $condition = "`uid` = :uid AND `app` = 'co'";
                $params = array( ':uid' => $userInfo['uid'] );
                $userBind = UserBinding::model()->fetch( $condition, $params );
                if ( !empty( $userBind ) ) {
                    UserBinding::model()->deleteAll( sprintf( "`uid` = %d AND `app` = 'co'", $userInfo['uid'] ) );
                }
                $binding = UserBinding::model()->add( array( 'uid' => $userInfo['uid'], 'bindvalue' => $user['guid'], 'app' => 'co' ) );
                if ( $binding ) {
                    $result[] = array( 'uid' => $user['uid'], 'bindvalue' => $userInfo['uid'] );
                }
            }
        }
        return $result;
    }

    /**
     * 根据酷办公移除的用户，删除 IBOS 的用户绑定关系
     * @param  array $userList 跟绑定表对比，酷办公移除的用户列表
     * @return array
     */
    public static function removeUserAndBindRelation( $userList ) {
        $result = array();
        foreach ( $userList as $user ) {
            $bindRelation = UserBinding::model()->fetch( "`bindvalue` = :bindvalue AND `app` = 'co'", array( ':bindvalue' => $user['guid'] ) );
            if ( !empty( $bindRelation ) ) {
                User::model()->updateByPk( $bindRelation['uid'], array( 'status' => 2 ) );
                $userInfo = User::model()->fetchByPk( $bindRelation['uid'] );
                if ( $userInfo ) {
                    // 解绑用户
                    $unbinding = UserBinding::model()->deleteAll( sprintf( "`uid` = %d AND `app` = 'co'", $bindRelation['uid'] ) );
                    if ( !$unbinding ) {
                        User::model()->updateByPk( $bindRelation['uid'], array( 'status' => 0 ) );
                    }
                    // 解绑 & 禁用 成功后将该绑定关系保存，用于调用酷办公删除对应绑定关系的接口
                    else {
                        $result[] = array( 'uid' => $user['uid'], 'bindvalue' => $bindRelation['uid'] );
                    }
                }
            }
        }
        return $result;
    }

}
