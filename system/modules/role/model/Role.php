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
use application\modules\role\model\AuthItemChild;
use application\modules\role\model\Node;
use application\modules\role\model\NodeRelated;
use application\modules\role\model\RoleRelated;
use application\modules\role\utils\Auth;
use application\modules\role\utils\Role as RoleUtil;
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

    /**
     * 拿到所有角色id
     * @return array
     */
    public function fetchAllId() {
        $roleIds = IBOS::app()->db->createCommand()
                ->select('roleid')
                ->from( $this->tableName() )
                ->queryColumn();
        return $roleIds;
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
    
    /**
     * 安装时分配角色的默认权限 参考dashboard\controllers\RoletypeController\edit方法
     * @return void
     */
    public function defaultAuth() {
        // 拿到所有角色id
        $roleIds = $this->fetchAllId();
        foreach ( $roleIds as $roleId) {
            $related = NodeRelated::model()->fetchAllByRoleId( $roleId );
            // 合并为一个较容易输出视图的格式
            $relateCombine = RoleUtil::combineRelated( $related );
            $authItem = Node::model()->fetchAll();
            $nodes = $this->toFilterAuth( $authItem );
            $authItem = $nodes['0'];
            $auth = $nodes['1'];
            $new = $datas = array();
            foreach ( $authItem as $key => $node ) {
                $isData = $node['type'] === 'data';
                $checked = isset( $relateCombine[$node['module']][$node['key']] );
                if ( $checked ) {
                    $new[$node['id']] = $isData ? 'data' : $node['id'];
                }
                if ( $isData && !empty( $auth ) ) {
                    foreach ( $auth as $k => $data ) {
                        $checked = ( isset( $relateCombine[$data['module']][$data['key']][$data['node']] ) && $node['module'] === $data['module'] && $node['key'] === $data['key'] );
                        if ( $checked ) {
                            $datas[$node['id']][$data['id']] = $relateCombine[$data['module']][$data['key']][$data['node']];
                        }
                    }
                }
            }
            $this->updateAuthItem( $roleId, $new, $datas );
        }
        return true;
    }

    /**
    * 过滤授权认证项
    * @param array $authItem 节点
    * @return array $nodes 数据类型节点的值,包括子节点
    */
    public function toFilterAuth( &$authItem ) {
        $auth = $nodes = array();
        foreach ( $authItem as $key => $node ) {
            if ( $node['module'] == 'crm' && $node['type'] == 'data' ) {
                unset( $authItem[$key] );
            }
            if ( $node['module'] == 'dashboard' ) {
                unset( $authItem[$key] );
            }
            if ( isset($authItem[$key]) && $node['node']!= '' && $node['type'] === 'data' ) {
                $auth[$key] = $node;
                unset( $authItem[$key] );
            }

        }
        $nodes[] = $authItem;
        $nodes[] = $auth;
        return $nodes;
    }


}
