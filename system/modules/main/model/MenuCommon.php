<?php

/**
 * menu_common表的数据层文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  menu_common表的数据层操作
 *
 * @package application.modules.main.model
 * @version $Id: MenuCommon.php 553 2013-06-06 03:22:53Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\main\model;

use application\core\model\Model;
use application\core\model\Module;

class MenuCommon extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{menu_common}}';
    }

    /**
     * 获得所有能用的模块菜单
     * @return type
     */
    public function fetchAllEnabledMenu()
    {
        $allEnabledModules = Module::model()->fetchAllEnabledModule();
        $enabledModStr = implode(',', array_keys($allEnabledModules));
        $criteria = array(
            'condition' => "(FIND_IN_SET(`module`, '{$enabledModStr}') OR iscustom=1) AND disabled=0",
            'order' => '`sort` ASC'
        );
        $menus = $this->fetchAllSortByPk('id', $criteria);
        return $menus;
    }

    /**
     * 获取所有系统常用和非常用菜单
     * @return type
     */
    public function fetchCommonAndNotUsed()
    {
        $allMenus = $this->fetchAllEnabledMenu();
        $commonMenu = $notUsedMenu = array();
        foreach ($allMenus as $moduleName => $menuInfo) {
            if ($menuInfo['iscommon'] == 1) {
                $commonMenu[$moduleName] = $menuInfo;
            } else {
                $notUsedMenu[$moduleName] = $menuInfo;
            }
        }
        $ret = array(
            'commonMenu' => $commonMenu,
            'notUsedMenu' => $notUsedMenu
        );
        return $ret;
    }

}
