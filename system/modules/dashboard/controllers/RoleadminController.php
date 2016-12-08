<?php

namespace application\modules\dashboard\controllers;

use application\modules\role\model\Role;

class RoleadminController extends RoletypeController
{

    /**
     * 浏览操作
     * @return void
     */
    public function actionIndex()
    {
        $this->roleType = Role::ADMIN_TYPE;
        parent::actionIndex();
    }

    /**
     * 新增操作
     * @return void
     */
    public function actionAdd()
    {
        $this->roleType = Role::ADMIN_TYPE;
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

    protected function filterAuthType(&$authItem)
    {
        foreach ($authItem as $key => $auth) {
            if (isset($auth['group'])) {
                foreach ($auth['group'] as $k => $row) {
                    if (isset($row['node'])) {
                        foreach ($row['node'] as $node) {
                            if ($node['module'] != 'dashboard') {
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
