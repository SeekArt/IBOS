<?php

/**
 * 授权认证工具类
 * @package application.modules.crm.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\utils;

use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\modules\role\model\AuthItemChild;
use application\modules\role\model\Node;
use application\modules\role\model\NodeRelated;
use application\modules\role\model\Role as RoleModel;
use application\modules\user\model\User;
use CAuthItem;

class Auth
{

    /**
     * 加载授权认证项目缓存
     * @return array
     */
    public static function loadAuthItem()
    {
        return Ibos::app()->setting->get('cache/authitem');
    }

    /**
     * 获取认证时的参数（如果有）
     * @param string $route 认证的路由
     * @return array 参数数组
     */
    public static function getParams($route)
    {
        $roleidA = explode(',', Ibos::app()->user->allroleid);
        if (!empty($roleidA)) {
            $dataItems = Node::model()->fetchAllDataNode();
            $param = array();
            foreach ($roleidA as $roleid) {
                if (isset($dataItems[$route])) {
                    $identifier = $dataItems[$route];
                    $param[] = NodeRelated::model()->fetchDataValByIdentifier($identifier, $roleid);
                }
            }
            if (!empty($param)) {
                return max($param);
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
    public static function updateAuthorization($authItem, $moduleName, $category)
    {
        foreach ($authItem as $key => $node) {
            $data['type'] = $node['type'];
            $data['category'] = $category;
            $data['module'] = $moduleName;
            $data['key'] = $key;
            $data['name'] = $node['name'];
            $data['node'] = '';
            if (isset($node['group'])) {
                $data['group'] = $node['group'];
            } else {
                $data['group'] = '';
            }
            $condition = "`module` = '{$moduleName}' AND `key` = '{$key}'";
            // 先删除(父)节点
            Node::model()->deleteAll($condition);
            //NodeRelated::model()->deleteAll( $condition ); //TODO:: 年前临时屏弊 2014年1月28日
            // 数据节点处理
            if ($node['type'] === 'data') {
                // 先插入父节点
                Node::model()->add($data);
                // 再处理子节点
                foreach ($node['node'] as $nKey => $subNode) {
                    $dataCondition = $condition . " AND `node` = '{$nKey}'";
                    //NodeRelated::model()->deleteAll( $dataCondition ); //TODO:: 年前临时屏弊 2014年1月28日
                    Node::model()->deleteAll($dataCondition);
                    $data['name'] = $subNode['name'];
                    $routes = self::wrapControllerMap($moduleName, $subNode['controllerMap']);
                    $data['routes'] = $routes;
                    $data['node'] = $nKey;
                    self::updateAuthItem(explode(',', $routes), true);
                    Node::model()->add($data);
                }
            } else {
                // 普通节点处理
                $data['routes'] = self::wrapControllerMap($moduleName, $node['controllerMap']);
                self::updateAuthItem(explode(',', $data['routes']), false);
                Node::model()->add($data);
            }
        }
        Cache::update('authItem');
    }

    /**
     * 赋予角色权限 （增加角色认证项子节点）
     * @param CAuthItem $role 当前角色认证项
     * @param array $currentNode 当前节点
     * @param array $routes 路由数组
     */
    public static function addRoleChildItem($role, $currentNode, $routes = array())
    {
        if (!empty($routes)) {
            foreach ($routes as $route) {
                if (!($role->hasChild($route))) {
                    $role->addChild($route, $currentNode['name'], '', $currentNode['node']);
                }
            }
        }
    }

    /**
     * 更新认证项目，用于提交与新建岗位权限时的处理
     * @param string $module 模块名称
     * @param boolean $isData 是否数据节点
     * @param array $routes 路由数组
     */
    public static function updateAuthItem($routes, $isData = false)
    {
        if (!empty($routes)) {
            // 创建认证对象
            $auth = Ibos::app()->authManager;
            foreach ($routes as $route) {
                $bizRule = $isData ? 'return UserUtil::checkDataPurv($purvId);' : '';
                $auth->removeAuthItem($route);
                $auth->createOperation($route, '', $bizRule, '');
            }
        }
    }

    /**
     * 封装控制器与动作映射
     * @param string $module 模块名
     * @param array $map 控制器与动作的映射数组
     * @return string
     */
    private static function wrapControllerMap($module, $map)
    {
        $routes = array();
        foreach ($map as $controller => $actions) {
            foreach ($actions as $action) {
                $routes[] = sprintf('%s/%s/%s', $module, $controller, $action);
            }
        }
        return implode(',', $routes);
    }

    /**
     * 更新授权认证项(新增or编辑)
     * @param integer $roleId 角色ID
     * @param array $authItem 节点
     * @param array $dataVal 数据类型节点的值
     * @return void
     */
    public static function updateAuthItemByRole($roleId, $authItem = array(), $dataVal = array())
    {
        // 所有节点数据
        $nodes = Node::model()->fetchAllSortByPk('id');
        // 更新关联节点数据
        NodeRelated::model()->deleteAllByRoleId($roleId);
        // 创建认证对象
        $auth = Ibos::app()->authManager;
        $role = $auth->getAuthItem($roleId);
        if ($role === null) {
            // 为该角色创建认证项目
            $role = $auth->createRole($roleId, '', '', '');
        }
        // 删除当前授权角色所有子项
        AuthItemChild::model()->deleteByParentExceptRouteA($roleId, AuthItemChild::model()->returnExceptRouteA());
        if (!empty($authItem)) {
            foreach ($authItem as $key => $nodeId) {
                $node = $nodes[$key];
                // id相同为普通节点，反之为数据节点
                if (strcasecmp($key, $nodeId) !== 0 && $nodeId === 'data') {
                    $vals = $dataVal[$key];
                    foreach ($vals as $valsKey => $valsValue) {
                        if (empty($valsValue)) {
                            unset($vals[$valsKey]);
                        }
                    }
                    if (is_array($vals)) {
                        NodeRelated::model()->addRelated('', $roleId, $node);
                        foreach ($vals as $id => $val) {
                            $childNode = Node::model()->fetchByPk($id);
                            NodeRelated::model()->addRelated($val, $roleId, $childNode);
                            Auth::addRoleChildItem($role, $childNode, explode(',', $childNode['routes']));
                        }
                    }
                } else {
                    NodeRelated::model()->addRelated('', $roleId, $node);
                    // 处理普通类型节点操作认证项
                    $routes = explode(',', $node['routes']);
                    Auth::addRoleChildItem($role, $node, $routes);
                }
            }
        }
    }

    public static function getAuthItem()
    {
        $roles = RoleModel::model()->findAll();
        $authItem = array();
        foreach ($roles as $role){
            $authItemRoles = Ibos::app()->db->createCommand()->from('{{node}} node')
                ->select('node.id, related.module, related.key, related.node, related.val')
                ->join('{{node_related}} related',
                    'node.module = related.module AND node.key = related.key AND node.node = related.node')
                ->where('related.roleid = :roleid', array(':roleid' => $role['roleid']))
                ->queryAll();
            foreach ($authItemRoles as $authItemRole){
                if (empty($authItemRole['node'])){
                    if ($authItemRole['key'] == 'manager' && Node::model()->isEditAndDel($authItemRole['module'])){
                        $authItem[$role['roleid']][$authItemRole['id']] = 'data';
                    }else{
                        $authItem[$role['roleid']][$authItemRole['id']] = $authItemRole['id'];
                    }
                }
            }
        }
        return $authItem;
    }

    public static function getDataVal()
    {
        $roles = RoleModel::model()->findAll();
        $dataVal = array();
        $nodeManager = Ibos::app()->db->createCommand()
            ->select('id,module,key,node')
            ->from('{{node}}')
            ->where("`node` IN ('edit','del') AND `key` = 'manager'")
            ->queryAll();
        foreach ($roles as $role){
            foreach ($nodeManager as $manager){
                $parentNode = NodeRelated::model()->find('`module` =:module AND `key` = :key AND `roleid` = :roleid', array(
                    ':module' => $manager['module'],
                    ':key' => $manager['key'],
                    ':roleid' => $role['roleid'],
                ));
                $parentNodeId = Node::model()->getNodeId($manager['module'], $manager['key']);
                if (empty($parentNode)){
                    $dataVal[$role['roleid']][$parentNodeId][$manager['id']] = '';
                }else{
                    $nodeVal = NodeRelated::model()->getNodeVal($manager['module'], $manager['key'], $role['roleid'], $manager['node']);
                    if (empty($nodeVal)){
                        $dataVal[$role['roleid']][$parentNodeId][$manager['id']] = '';
                    }else{
                        $dataVal[$role['roleid']][$parentNodeId][$manager['id']] =  $nodeVal;
                    }
                }
            }
        }
        return $dataVal;
    }
}
