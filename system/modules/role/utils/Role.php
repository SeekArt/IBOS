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
use application\core\utils\StringUtil;
use application\modules\role\model\NodeRelated;
use application\modules\role\model\RoleRelated;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\role\model\AuthItem;
use CHttpException;
use Guzzle\Common\Exception\InvalidArgumentException;

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
		$oldUids = User::model()->fetchAllUidByRoleids( $roleId, false, true );
		// 这一次提交的用户
		$userId = explode( ',', trim( $users, ',' ) );
		$newUids = StringUtil::getUid( $userId );
		// 找出两种差别
		$delDiff = array_diff( $oldUids, $newUids );
		$addDiff = array_diff( $newUids, $oldUids );
		// 没有可执行操作，直接跳过
		if ( !empty( $addDiff ) || !empty( $delDiff ) ) {
			$updateUser = false;
			// 获取所有用户数据
			User::model()->setSelect( 'uid,roleid' );
			$userData = User::model()->findUserIndexByUid( NULL, true );
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
			$uidArray = array_unique( array_merge( $addDiff, $delDiff ) );
			UserUtil::wrapUserInfo( $uidArray, false, true );
			// 更新操作
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
	 * todo::我去瞄一眼缓存机制，这里暂时注释掉，因为这个缓存有点问题
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


	/**
	 * 判断当前用户是否有访问某个路由的权限
	 *
	 * @param $routes
	 * @return bool
	 * @throws CHttpException
	 */
	public static function checkRouteAccess($routes) {
		// 创建对应的控制器
		$ca = IBOS::app()->createController($routes);

		// 找不到对应的控制器。有可能是路由有误
		if (empty($ca) || count($ca) != 2) {
			throw new \CHttpException(404, 'Oops. Not found.');
		}

		list($controller, $actionId) = $ca;

		// 备注：某些控制器在 init 方法下面做权限验证。会让用户跳转到特定的页面，而当前方法只需要知道用户是否有访问路由的权限
		// 所以，如果遇到上面的情况，可以将非权限验证的代码放在 initBase 中
		if (method_exists($controller, 'initBase')) {
			$controller->initBase();
		} else {
			$controller->init();
		}

		$module = $controller->getModule()->getId();
		// step1
		if (!$controller->filterNotAuthModule($module)) {
			$routes = strtolower($controller->getUniqueId() . '/' . $actionId);
			if ($controller->isFilterRoute) {
				$check = false;
				// step2：是否使用config里的配置路由去验证
				// 当useConfig被设置成true时，只有在config里设置的才会验证
				// 当useConfig被设置成false时，将会通过filterRoutes去过滤不需要验证的route
				if (!$controller->useConfig) {
					$check = !$controller->filterRoutes($routes) ? true : false;
				} else {
					$check = AuthItem::model()->checkIsInByRoute($routes) ? true : false;
				}
				if (true === $check) {
					// step3
					if (!Ibos::app()->user->checkAccess($routes, Auth::getParams($routes))) {
						// 没有权限
						return false;
					}
				}
			}
		}
		return true;
	}

}
