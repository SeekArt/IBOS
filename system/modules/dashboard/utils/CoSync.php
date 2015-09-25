<?php

/**
 * 同步用户以及组织架构工具类
 * @version 1.0  2015-9-11 15:06:25
 * @author Sam  <gzxgs@ibos.com.cn>
 */

namespace application\modules\dashboard\utils;

use application\core\utils\IBOS;
use application\core\utils\String;
use application\core\utils\Cache as CacheUtil;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\model\UserCount;
use application\modules\user\utils\User as UserUtil;

class CoSync {

	/**
	 * 创建用户
	 * @param type $userdata 用户数据
	 * @param type $success 保存成功信息数组
	 * @param type $error 保存错误信息数组
	 * @param type $break 是否需要跳出循环，用户区分是接口请求还是一般操作
	 * @return type
	 */
	public static function CreateUser( $userdata, $success = array(), $error = array(), $break = false ) {

		//此处加载缓存以及更新用户缓存必须，要不然会出错或者会产生重复数据。
		CacheUtil::load( 'usergroup' ); // 要注意小写
		CacheUtil::update( 'users' ); // 用户缓存依赖usergroup缓存，单独更新
		foreach ( $userdata as $key => $param ) {
			$checkIsExist = User::model()->checkIsExistByMobile( $param['mobile'] );
			//判断手机号不存在,执行创建用户
			//从酷办公获取用户在本地创建新的用户，暂时是没有部门关联的
			if ( $checkIsExist === false ) {
				$param['salt'] = !empty( $param['salt'] ) ? $param['salt'] : String::random( 6 );
				$param['password'] = !empty( $param['password'] ) ? $param['password'] : md5( md5( $param['mobile'] ) . $param['salt'] );
				$param['groupid'] = !empty( $param['groupid'] ) ? $param['groupid'] : '2';
				$param['createtime'] = TIMESTAMP;
				$param['guid'] = String::createGuid();
				$data = User::model()->create( $param );
				unset( $data['uid'] );
				$newId = User::model()->add( $data, true );
				if ( $newId ) {
					UserCount::model()->add( array( 'uid' => $newId ) );
					$ip = IBOS::app()->setting->get( 'clientip' );
					UserStatus::model()->add( array( 'uid' => $newId, 'regip' => $ip, 'lastip' => $ip ) );
					UserProfile::model()->add( array( 'uid' => $newId ) ); //用户user_profile一定要有相关的用户数据，即使为空，要不然会出错
					//创建用户绑定
					$binding = UserBinding::model()->add( array( 'uid' => $newId, 'bindvalue' => $param['guid'], 'app' => 'co' ) );
					if ( !$binding ) {
						$error[$key]['uid'] = $param['uid'];
						$error[$key]['realname'] = $param['realname'];
						$error[$key]['mobile'] = $param['mobile'];
						$error[$key]['errormsg'] = '绑定用户出错';
					}
					$success[$param['uid']] = $param['mobile'];
					$newUser = User::model()->fetchByPk( $newId );
					$newusers = UserUtil::loadUser();
					$newusers[$newId] = UserUtil::wrapUserInfo( $newUser );
					User::model()->makeCache( $newusers );
				} else {
					$error[$key]['uid'] = $param['uid'];
					$error[$key]['realname'] = $param['realname'];
					$error[$key]['mobile'] = $param['mobile'];
					$error[$key]['errormsg'] = '创建用户出错';
				}
			}
			if ( $break === true ) {
				unset( $userdata[$key] );
				break;
			}
		}
		return array( 'isSuccess' => true, 'data' => array( 'error' => $error, 'success' => $success, 'users' => $userdata ) );
	}

}
