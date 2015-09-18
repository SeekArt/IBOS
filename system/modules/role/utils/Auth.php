<?php

/**
 * 授权认证工具类
 * @package application.modules.crm.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\utils;

use application\core\utils\Cache;
use application\core\utils\IBOS;
use application\modules\role\model\Node;
use application\modules\role\model\NodeRelated;
use application\modules\user\model\User;
use CAuthItem;

class Auth {

	/**
	 * 加载授权认证项目缓存
	 * @return array
	 */
	public static function loadAuthItem() {
		return IBOS::app()->setting->get( 'cache/authitem' );
	}

	/**
	 * 获取认证时的参数（如果有）
	 * @param string $route 认证的路由
	 * @return array 参数数组
	 */
	public static function getParams( $route ) {
		/**
		 * <del>$roleId = IBOS::app()->user->roleid;</del>
		 * 原来的时候是根据自己的角色去拿权限的data
		 * 实际上应该是拿自己的所有角色，包括“辅助角色”去拿data
		 * @author mm
		 */
		$user = User::model()->fetchByUid( IBOS::app()->user->uid );
		$roleidA = explode( ',', $user['allroleid'] );
		if ( !empty( $roleidA ) ) {
		$dataItems = Node::model()->fetchAllDataNode();
			$param = array();
			foreach ( $roleidA as $roleid ) {
				if ( isset( $dataItems[$route] ) ) {
					$identifier = $dataItems[$route];
					$param[] = NodeRelated::model()->fetchDataValByIdentifier( $identifier, $roleid );
				}
			}
			if ( !empty( $param ) ) {
				return max( $param );
			} else {
				return '';
			}
		} else {
			return '';
		}
	}

	/**
	 * 更新配置文件中的认证项数据，做如下操作：
	 * 更新授权项目信息 auth_item
	 * 更新授权节点关联表信息 node_related
	 * @param array $authItem 认证项数组，详见config.php里的authorization一节
	 * @param string $moduleName 模块名
	 */
	public static function updateAuthorization( $authItem, $moduleName, $category ) {
		foreach ( $authItem as $key => $node ) {
			$data['type'] = $node['type'];
			$data['category'] = $category;
			$data['module'] = $moduleName;
			$data['key'] = $key;
			$data['name'] = $node['name'];
			$data['node'] = '';
			if ( isset( $node['group'] ) ) {
				$data['group'] = $node['group'];
			} else {
				$data['group'] = '';
			}
			$condition = "`module` = '{$moduleName}' AND `key` = '{$key}'";
			// 先删除(父)节点
			Node::model()->deleteAll( $condition );
			//NodeRelated::model()->deleteAll( $condition ); //TODO:: 年前临时屏弊 2014年1月28日
			// 数据节点处理
			if ( $node['type'] === 'data' ) {
				// 先插入父节点
				Node::model()->add( $data );
				// 再处理子节点
				foreach ( $node['node'] as $nKey => $subNode ) {
					$dataCondition = $condition . " AND `node` = '{$nKey}'";
					//NodeRelated::model()->deleteAll( $dataCondition ); //TODO:: 年前临时屏弊 2014年1月28日
					Node::model()->deleteAll( $dataCondition );
					$data['name'] = $subNode['name'];
					$routes = self::wrapControllerMap( $moduleName, $subNode['controllerMap'] );
					$data['routes'] = $routes;
					$data['node'] = $nKey;
					self::updateAuthItem( explode( ',', $routes ), true );
					Node::model()->add( $data );
				}
			} else {
				// 普通节点处理
				$data['routes'] = self::wrapControllerMap( $moduleName, $node['controllerMap'] );
				self::updateAuthItem( explode( ',', $data['routes'] ), false );
				Node::model()->add( $data );
			}
		}
		Cache::update( 'authItem' );
	}

	/**
	 * 赋予角色权限 （增加角色认证项子节点）
	 * @param CAuthItem $role 当前角色认证项
	 * @param array $currentNode 当前节点
	 * @param array $routes 路由数组
	 */
	public static function addRoleChildItem( $role, $currentNode, $routes = array() ) {
		if ( !empty( $routes ) ) {
			foreach ( $routes as $route ) {
				$role->addChild( $route, $currentNode['name'], '', $currentNode['node'] );
			}
		}
	}

	/**
	 * 更新认证项目，用于提交与新建岗位权限时的处理
	 * @param string $module 模块名称
	 * @param boolean $isData 是否数据节点
	 * @param array $routes 路由数组
	 */
	public static function updateAuthItem( $routes, $isData = false ) {
		if ( !empty( $routes ) ) {
			// 创建认证对象
			$auth = IBOS::app()->authManager;
			foreach ( $routes as $route ) {
				$bizRule = $isData ? 'return UserUtil::checkDataPurv($purvId);' : '';
				$auth->removeAuthItem( $route );
				$auth->createOperation( $route, '', $bizRule, '' );
			}
		}
	}

	/**
	 * 封装控制器与动作映射
	 * @param string $module 模块名
	 * @param array $map 控制器与动作的映射数组
	 * @return string
	 */
	private static function wrapControllerMap( $module, $map ) {
		$routes = array();
		foreach ( $map as $controller => $actions ) {
			foreach ( $actions as $action ) {
				$routes[] = sprintf( '%s/%s/%s', $module, $controller, $action );
			}
		}
		return implode( ',', $routes );
	}

}
