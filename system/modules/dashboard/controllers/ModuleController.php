<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Module;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module as ModuleUtil;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Menu;
use application\modules\dashboard\model\Nav;

/**
 * 后台模块模块管理控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */

/**
 * 后台模块管理控制器,管理已安装和未安装模块,实现安装与卸载功能
 *
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id$
 */
class ModuleController extends BaseController
{

    /**
     * 管理操作
     * @return void
     */
    public function actionManager()
    {
        // 管理的类型只有已安装和未安装
        $moduleType = Env::getRequest('op');
        if (!in_array($moduleType, array('installed', 'uninstalled'))) {
            $moduleType = 'installed';
        }
        if ($moduleType == 'uninstalled') {
            $moduleDirs = ModuleUtil::getModuleDirs();
            if (!empty($moduleDirs)) {
                $moduleDirs = ModuleUtil::filterInstalledModule(Module::model()->fetchAllSortByPk('module'), $moduleDirs);
            }
            $modules = ModuleUtil::initModuleParameters($moduleDirs);
        } else {
            $modules = Module::model()->fetchAll(array('order' => 'iscore ,installdate desc'));
            // 获取模块管理菜单
            foreach ($modules as $index => $module) {
                $menu = Menu::model()->fetchByModule($module['module']);
                if (!empty($menu)) {
                    $route = $menu['m'] . '/' . $menu['c'] . '/' . $menu['a'];
                    $param = StringUtil::splitParam($menu['param']);
                    $module['managerUrl'] = Ibos::app()->urlManager->createUrl($route, $param);
                } else {
                    $module['managerUrl'] = '';
                }
                $modules[$index] = $module;
            }
        }
        $data = array(
            'modules' => $modules
        );
        $this->render('module' . ucfirst($moduleType), $data);
    }

    /**
     * 执行安装操作
     * @return void
     */
    public function actionInstall()
    {
        $moduleName = Env::getRequest('module');
        $status = ModuleUtil::install($moduleName);
        if ($status) {
            $jumpUrl = $this->createUrl('permissions/setup', array('module' => $moduleName));
            Cache::update();
            // 清除缓存文件
            Cache::clear();
            $this->success(Ibos::lang('Install module success'), $jumpUrl);
        } else {
            $this->error(Ibos::lang('Install module failed'));
        }
    }

    /**
     * 执行卸载操作
     * @return void
     */
    public function actionUninstall()
    {
        $moduleName = Env::getRequest('module');
        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $status = ModuleUtil::uninstall($moduleName);
            Cache::update();
            // 清除缓存文件
            Cache::clear();
            $this->ajaxReturn(array('IsSuccess' => (boolean)$status), 'json');
        }
    }

    /**
     * 执行启用禁用操作
     * @return void
     */
    public function actionStatus()
    {
        $moduleStatus = Env::getRequest('type');
        $module = Env::getRequest('module');
        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            $status = 0;
            if ($moduleStatus == 'disabled') {
                $status = 1;
            }
            $changeStatus = Module::model()->modify($module, array('disabled' => $status));
            // 更新导航
            Nav::model()->updateAll(array('disabled' => $status), "module = :module", array(':module' => $module));
            Menu::model()->updateAll(array('disabled' => $status), "m = :m", array(':m' => $module));
            Cache::update(array('setting', 'nav'));
            // 更新模块配置
            ModuleUtil::updateConfig($module);
            $this->ajaxReturn(array('IsSuccess' => $changeStatus), 'json');
        }
    }

}
