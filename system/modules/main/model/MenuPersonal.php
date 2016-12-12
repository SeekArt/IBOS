<?php

/**
 * menu_personal表的数据层文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  menu_personal表的数据层操作
 *
 * @package application.modules.main.model
 * @version $Id: MenuPersonal.php 553 2013-06-06 03:22:53Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\main\model;

use application\core\model\Model;
use application\core\utils\Convert;

class MenuPersonal extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{menu_personal}}';
    }

    /**
     * 通过uid获取设置的常用菜单
     * @param integer $uid 设置菜单的uid
     * @return array
     */
    public function fetchMenuByUid($uid)
    {
        $menu = $this->fetch("uid = {$uid}");
        if (empty($menu)) {
            $ret = MenuCommon::model()->fetchCommonAndNotUsed();
        } else {
            $allMenus = MenuCommon::model()->fetchAllEnabledMenu();
            $allIds = Convert::getSubByKey($allMenus, 'id');
            $menuIds = explode(',', $menu['common']);
            $commonMenu = $notUsedMenu = array();
            // 两次循环只为保证保证输出菜单的顺序
            foreach ($menuIds as $id) {
                if (in_array($id, $allIds)) {
                    $commonMenu[$id] = $allMenus[$id];
                }
            }
            foreach ($allMenus as $id => $menuInfo) {
                if (!in_array($menuInfo['id'], $menuIds)) {
                    $notUsedMenu[$id] = $menuInfo;
                }
            }
            $ret = array(
                'commonMenu' => $commonMenu,
                'notUsedMenu' => $notUsedMenu
            );
        }
        return $ret;
    }

}
