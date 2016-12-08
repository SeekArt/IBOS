<?php

namespace application\modules\dashboard\controllers;

use application\modules\role\model\Role;

class RoleController extends RoletypeController
{

    /**
     * 浏览操作
     * @return void
     */
    public function actionIndex()
    {
        $this->roleType = Role::NORMAL_TYPE;
        parent::actionIndex();
    }

    /**
     * 新增操作
     * @return void
     */
    public function actionAdd()
    {
        $this->roleType = Role::NORMAL_TYPE;
        parent::actionAdd();
    }

    /**
     * 角色编辑
     * @return void
     */
    public function actionEdit()
    {
        parent::actionEdit();
    }

    /**
     * 删除操作
     * @return void
     */
    public function actionDel()
    {
        parent::actionDel();
    }

    protected function filterAuth(&$authItem)
    {
        foreach ($authItem as $key => $auth) {
            if (isset($auth['group'])) {
                foreach ($auth['group'] as $k => $row) {
                    if (isset($row['node'])) {
                        foreach ($row['node'] as $node) {
                            if ($node['module'] == 'crm' && $node['type'] == 'data') {
                                unset($authItem[$key]['group'][$k]);
                            }
                            if ($node['module'] == 'dashboard') {
                                unset($authItem[$key]['group'][$k]);
                            }
                        }
                    }
                }
            }
            if (empty($authItem[$key]['group'])) {
                unset($authItem[$key]);
            }
        }
    }

}
