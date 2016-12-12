<?php

namespace application\modules\dashboard\controllers;

use application\core\utils as util;
use application\modules\role\model\AuthItemChild;
use application\modules\role\model\Node;
use application\modules\role\model\NodeRelated;
use application\modules\role\model\Role;
use application\modules\role\utils\Auth as AuthUtil;
use application\modules\role\utils\Role as RoleUtil;

class PermissionsController extends BaseController
{

    /**
     * 权限列表
     */
    public function actionSetup()
    {
        $module = util\Env::getRequest('module');
        if (empty($module)) {
            $module = 'app';
        }
        $modules = util\Ibos::app()->db->createCommand()
            ->select("*")
            ->from("{{node}} as n")
            ->leftJoin("{{module}} as m", "n.module = m.module")
            ->where("n.module = m.module GROUP BY n.module")
            ->queryAll();
        $data = array(
            'modulesList' => $modules,
            'contentList' => $this->indexInfo($module)
        );
        $this->render('limitIndex', $data);
    }

    /**
     * 权限添加页面内容
     */
    public function actionAdd()
    {
        $module = util\Env::getRequest('module');
        if (util\Env::getRequest('addsubmit')) {
            $this->addOrEditpermissions();
        } else {
            $alias = "application.modules.dashboard.views.permissions.limitAdd";
            $params = array(
                'lang' => util\Ibos::getLangSource("dashboard.default"),
                'assetUrl' => util\Ibos::app()->assetManager->getAssetsUrl("dashboard"),
                'moduleList' => $this->getModuleNode($module),
                'module' => $module,
                'roles' => $this->getRoles()
            );
            $view = $this->renderPartial($alias, $params, true);
            echo $view;
        }
    }

    /**
     * 权限修改页面内容
     */
    public function actionEdit()
    {
        $roleid = util\Env::getRequest('id');
        $module = util\Env::getRequest('module');
        if (util\Env::getRequest('editsubmit')) {
            $roleids = explode(',', util\Env::getRequest('roleid'));
            foreach ($roleids as $roleid) {
                NodeRelated::model()->deleteAll("roleid = {$roleid} and module = '{$module}'");
                AuthItemChild::model()->deleteAll("parent = {$roleid} and child LIKE '{$module}%'");
            }
            $this->addOrEditpermissions();
        } else {
            $nodeRelated = NodeRelated::model()->fetchAll("roleid = {$roleid} and module = '{$module}'");
            $nodeRCombine = RoleUtil::combineRelated($nodeRelated);
            $alias = "application.modules.dashboard.views.permissions.limitEdit";
            $params = array(
                'lang' => util\Ibos::getLangSource("dashboard.default"),
                'assetUrl' => util\Ibos::app()->assetManager->getAssetsUrl("dashboard"),
                'roleid' => $roleid,
                'module' => $module,
                'moduleList' => $this->getModuleNode($module),
                'nodeRelated' => $nodeRCombine,
                'roles' => $this->getRoles()
            );
            $view = $this->renderPartial($alias, $params, true);
            echo $view;
        }
    }

    /**
     * 删除权限
     */
    public function actionDel()
    {
        $roleid = util\Env::getRequest('id');
        $module = util\Env::getRequest('module');
        AuthItemChild::model()->deleteAll("parent = {$roleid} and child LIKE '{$module}%'");
        $nr = NodeRelated::model()->deleteAll("roleid = {$roleid} and module = '{$module}'");
        RoleUtil::cleanPurvCache($roleid);
        util\Cache::update('role');
        if ($nr > 0 || $nr == false) {
            $bool = true;
        } else {
            $bool = false;
        }
        $this->ajaxReturn(array('isSuccess' => $bool));
    }

    /**
     * 根据模块查询数据节点
     * @param string $module
     * @return array
     */
    public function getModuleNode($module)
    {
        $nodeList = Node::model()->fetchAll("module = '{$module}'");
        $authItem = AuthUtil::loadAuthItem();
        $moduleList = $temp = array();
        foreach ($authItem as $auth) {
            foreach ($nodeList as $node) {
                if ($auth['category'] == $node['category']) {
                    foreach ($auth['group'] as $group) {
                        if ($group['groupName'] == $node['group'] && in_array($group['groupName'], $temp) == false) {
                            $moduleList[$node['id']] = $group;
                            $temp[] = $group['groupName'];
                        }
                    }
                }
            }
        }
        return $moduleList;
    }

    /**
     * 根据模块查询权限设置页信息
     * @param string $module
     * @return array
     */
    public function indexInfo($module)
    {
        $content = util\Ibos::app()->db->createCommand()
            ->select("*,GROUP_CONCAT(`name`) as names")
            ->from("{{role}} r")
            ->leftJoin("{{node_related}} nr", "r.roleid = nr.roleid")
            ->leftJoin("{{node}} n", "nr.module = n.module and nr.key = n.key and nr.node= n.node")
            ->where("n.module = '{$module}' and nr.module = n.module and nr.key = n.key and nr.node= n.node GROUP BY rolename")
            ->queryAll();
        return $content;
    }

    /**
     * 权限添加或修改方法
     */
    public function addOrEditpermissions()
    {
        $roleids = explode(',', util\Env::getRequest('roleid'));
        $nodes = util\Env::getRequest('nodes');
        $privilege = util\Env::getRequest('data-privilege');
        if (!empty($nodes)) {
            foreach ($roleids as $roleid) {
                $nodeList = Node::model()->fetchAllSortByPk('id');
                $auth = util\Ibos::app()->authManager;
                $role = $auth->getAuthItem($roleid);
                foreach ($nodes as $key => $nodeId) {
                    $nodeL = Node::model()->fetch("id = '{$key}'");
                    $nodeRelated = NodeRelated::model()->fetch("roleid = {$roleid} and module ='{$nodeL['module']}' and `key` = '{$nodeL['key']}' and node = '{$nodeL['node']}'");
                    $node = $nodeList[$key];
                    if (empty($nodeRelated)) {
                        if (strcasecmp($key, $nodeId) !== 0 && $nodeId === 'data') {
                            $vals = array_filter($privilege[$key]);
                            if (is_array($vals)) {
                                NodeRelated::model()->addRelated('', $roleid, $node);
                                foreach ($vals as $id => $val) {
                                    $childNode = Node::model()->fetchByPk($id);
                                    NodeRelated::model()->addRelated($val, $roleid, $childNode);
                                    AuthUtil::addRoleChildItem($role, $childNode, explode(',', $childNode['routes']));
                                }
                            }
                        } else {
                            NodeRelated::model()->addRelated('', $roleid, $node);
                            $routes = explode(',', $node['routes']);
                            AuthUtil::addRoleChildItem($role, $node, $routes);
                        }
                    }
                }
                RoleUtil::cleanPurvCache($roleid);
            }
        }
        util\Cache::update('Role');
        util\Cache::clear();
        $this->ajaxReturn(array('isSuccess' => true));
    }

    /**
     * 获取角色数据
     * @return array
     */
    private function getRoles()
    {
        $res = array();
        $roles = Role::model()->fetchAll();
        foreach ($roles as $role) {
            $res[] = array(
                'id' => $role['roleid'],
                'text' => $role['rolename']
            );
        }
        return $res;
    }

}
