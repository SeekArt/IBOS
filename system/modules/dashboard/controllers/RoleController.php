<?php

/**
 * 组织架构模块岗位控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块岗位控制器类
 * 
 * @package application.modules.dashboard.controllers
 * @version $Id$
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache as CacheUtil;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\Org;
use application\core\utils\Page;
use application\modules\role\model\AuthItemChild;
use application\modules\role\model\Node;
use application\modules\role\model\NodeRelated;
use application\modules\role\model\Role;
use application\modules\role\model\RoleRelated;
use application\modules\role\utils\Auth;
use application\modules\role\utils\Role as RoleUtil;
use application\modules\user\model\User;

class RoleController extends OrganizationBaseController {

    /**
     * 浏览操作
     * @return void
     */
    public function actionIndex() {
        $roles = Role::model()->fetchRolesWithUser();
        $this->render( 'index', array( 'data' => $roles ) );
    }

    /**
     * 新增操作
     * @return void 
     */
    public function actionAdd() {
        if ( Env::submitCheck( 'posSubmit' ) ) {
            // 获取基本数据
            $data = array(
                'rolename' => Env::getRequest( 'rolename' )
            );
            // 获取插入ID，以便后续处理
            $newId = Role::model()->add( $data, true );
            // 权限处理
			if ( Env::getRequest( 'nodes', 'P' ) ) {
				$this->updateAuthItem( $newId, Env::getRequest( 'nodes', 'P' ), Env::getRequest( 'data-privilege', 'P' ) );
            }
            CacheUtil::update( 'role' );
            $newId && Org::update();
            $this->success( IBOS::lang( 'Save succeed', 'message' ), $this->createUrl( 'role/edit', array( 'op' => 'member', 'id' => $newId ) ) );
        } else {
            $authItem = Auth::loadAuthItem();
			$this->filterAuth( $authItem );
            $data['authItem'] = $authItem;
            $this->render( 'add', $data );
        }
    }

    /**
     * 角色编辑
     * @return void
     */
    public function actionEdit() {
        $id = Env::getRequest( 'id' );
        if ( Env::getRequest( 'op' ) == 'member' ) {
            $this->member();
            exit;
        }
        if ( Env::submitCheck( 'posSubmit' ) ) {
            if ( isset( $_POST['rolename'] ) ) {
                $data["rolename"] = $_POST['rolename'];
                Role::model()->modify( $id, $data );
            }
            // 权限处理
			if ( Env::getRequest( 'nodes', 'P' ) ) {
				$this->updateAuthItem( $id, Env::getRequest( 'nodes', 'P' ), Env::getRequest( 'data-privilege', 'P' ) );
            } else {
				$this->updateAuthItem( $id, '', Env::getRequest( 'data-privilege', 'P' ) );
            }
            CacheUtil::update( 'role' );
            Org::update();
            IBOS::app()->cache->clear();
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $role = Role::model()->fetchByPk( $id );
            // 关联角色的权限节点
            $related = NodeRelated::model()->fetchAllByRoleId( $id );
            // 合并为一个较容易输出视图的格式
            $relateCombine = RoleUtil::combineRelated( $related );
            $data['id'] = $id;
            $data['role'] = $role;
            $data['related'] = $relateCombine;
            // 所有权限节点
            $authItem = Auth::loadAuthItem();
			$this->filterAuth( $authItem );
            $data['authItem'] = $authItem;
            $this->render( 'edit', $data );
        }
	}

	/**
	 * 过滤crm的饼
	 * @param type $related
	 */
	private function filterAuth( &$authItem ) {
		foreach ( $authItem as $key => $auth ) {
			if ( isset( $auth['group'] ) ) {
				foreach ( $auth['group'] as $k => $row ) {
					if ( isset( $row['node'] ) ) {
						foreach ( $row['node'] as $node ) {
							if ( $node['module'] == 'crm' && $node['type'] == 'data' ) {
								unset( $authItem[$key]['group'][$k] );
							}
						}
					}
				}
			}
		}
	}

    /**
     * 删除操作
     * @return void 
     */
    public function actionDel() {
        if ( IBOS::app()->request->getIsAjaxRequest() ) {
            $id = Env::getRequest( 'id' );
            $ids = explode( ',', trim( $id, ',' ) );
            foreach ( $ids as $roleId ) {
                // 删除角色
                Role::model()->deleteByPk( $roleId );
				$isInstallCrm = Module::getIsEnabled( 'crm' );
                // 删除角色对应授权
                IBOS::app()->authManager->removeAuthItem( $roleId );
                // 删除辅助角色关联
                RoleRelated::model()->deleteAll( 'roleid = :roleid', array( ':roleid' => $roleId ) );
                // 删除节点与角色关联表
                NodeRelated::model()->deleteAll( 'roleid = :roleid', array( ':roleid' => $roleId ) );
                $related = User::model()->fetchAll( array( 'select' => 'uid', 'condition' => "`roleid`={$roleId}" ) );
                $relatedIds = Convert::getSubByKey( $related, 'uid' );
                // 更新用户岗位信息
                if ( !empty( $relatedIds ) ) {
                    User::model()->updateByUids( $relatedIds, array( 'roleid' => 0 ) );
                }
            }
            CacheUtil::update( 'role' );
            Org::update();
            IBOS::app()->cache->clear();
            $this->ajaxReturn( array( 'isSuccess' => true ), 'json' );
        }
    }

    /**
     * 成员
     */
    public function member() {
        $id = Env::getRequest( 'id' );

        if ( !empty( $id ) ) {
            if ( Env::submitCheck( 'postsubmit' ) ) {
                $member = Env::getRequest( 'member' );
                RoleUtil::setRole( $id, $member );
                $this->success( IBOS::lang( 'Save succeed', 'message' ) );
            } else {
                // 该角色下人员
                $uids = User::model()->fetchUidByRoleId( $id, false );
                // 搜索处理
                if ( Env::submitCheck( 'search' ) ) {
                    $key = $_POST['keyword'];
                    $uidStr = implode( ',', $uids );
                    $users = User::model()->fetchAll( "`realname` LIKE '%{$key}%' AND FIND_IN_SET(`uid`, '{$uidStr}')" );
                    $pageUids = Convert::getSubByKey( $users, 'uid' );
                } else {
                    $count = count( $uids );
                    $pages = Page::create( $count, self::MEMBER_LIMIT );
                    $offset = $pages->getOffset();
                    $limit = $pages->getLimit();
                    $pageUids = array_slice( $uids, $offset, $limit );
                    $data['pages'] = $pages;
                }
                $data['id'] = $id;
                // for input
                $data['uids'] = $uids;
                // for js
                $data['uidString'] = '';
                foreach ( $uids as $uid ) {
                    $data['uidString'] .= "'u_" . $uid . "',";
                }
                $data['uidString'] = trim( $data['uidString'], ',' );
                // 当前页要显示的uid（只作显示，并不为实际表单提交数据）
                $data['pageUids'] = $pageUids;
                $this->render( 'member', $data );
            }
        } else {
            $this->error( '该角色不存在或已删除！' );
        }
    }

    /**
     * 更新授权认证项(新增or编辑)
     * @param integer $roleId 角色ID
     * @param array $authItem 节点
     * @param array $dataVal 数据类型节点的值
     * @return void 
     */
    private function updateAuthItem( $roleId, $authItem = array(), $dataVal = array() ) {
        // 所有节点数据
        $nodes = Node::model()->fetchAllSortByPk( 'id' );
        // 更新关联节点数据
        NodeRelated::model()->deleteAllByRoleId( $roleId );
        // 创建认证对象
        $auth = IBOS::app()->authManager;
        $role = $auth->getAuthItem( $roleId );
        if ( $role === null ) {
            // 为该角色创建认证项目
            $role = $auth->createRole( $roleId, '', '', '' );
        }
        // 删除当前授权角色所有子项
		AuthItemChild::model()->deleteByParentExceptRouteA( $roleId, AuthItemChild::model()->returnExceptRouteA() );
        if ( !empty( $authItem ) ) {
            foreach ( $authItem as $key => $nodeId ) {
                $node = $nodes[$key];
                // id相同为普通节点，反之为数据节点
                if ( strcasecmp( $key, $nodeId ) !== 0 && $nodeId === 'data' ) {
                    $vals = $dataVal[$key];
					foreach ( $vals as $valsKey => $valsValue ) {
						if ( empty( $valsValue ) ) {
							unset( $vals[$valsKey] );
						}
					}
                    if ( is_array( $vals ) ) {
                        NodeRelated::model()->addRelated( '', $roleId, $node );
                        foreach ( $vals as $id => $val ) {
                            $childNode = Node::model()->fetchByPk( $id );
                            NodeRelated::model()->addRelated( $val, $roleId, $childNode );
                            Auth::addRoleChildItem( $role, $childNode, explode( ',', $childNode['routes'] ) );
                        }
                    }
                } else {
                    NodeRelated::model()->addRelated( '', $roleId, $node );
                    // 处理普通类型节点操作认证项
                    $routes = explode( ',', $node['routes'] );
                    Auth::addRoleChildItem( $role, $node, $routes );
                }
            }
        }
    }

}
