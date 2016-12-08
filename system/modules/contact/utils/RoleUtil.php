<?php
/**
 * 角色权限工具类
 *
 * @namespace application\modules\contact\utils
 * @filename RoleUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/8 17:24
 */

namespace application\modules\contact\utils;


use application\core\utils\Ibos;
use application\core\utils\System;
use application\modules\contact\model\ContactHide;
use application\modules\department\utils\Department as DepartmentUtil;

/**
 * Class RoleUtil
 *
 * @package application\modules\contact\utils
 */
class RoleUtil extends System
{
    /**
     * @param string $className
     * @return RoleUtil
     */
    public static function getInstance($className = __CLASS__)
    {
        static $instance = null;

        if (empty($instance)) {
            $instance = parent::getInstance($className);
        }

        return $instance;
    }
    
    
    /**
     * 判断用户是否某个部门的管理员
     *
     * @param integer $deptId 部门 id
     * @param integer $uid 用户 uid
     * @return bool
     */
    public function isDeptAdmin($deptId, $uid)
    {
        $manageGroupUidArr = DepartmentUtil::fetchAllManageGroupUidArr();
        
        if (isset($manageGroupUidArr[$deptId])) {
            $deptManageUsers = $manageGroupUidArr[$deptId];
            if (in_array($uid, $deptManageUsers)) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * 判断是否能查看用户的手机号码
     *
     * @param integer $uid 用户 uid
     * @return bool
     */
    public function canViewMobile($uid)
    {
        $currentUid = Ibos::app()->user->uid;
        
        // 自己可以查看自己的手机号码
        if ($currentUid == $uid) {
            return true;
        }
        
        // 查看用户是否设置了隐藏号码
        $isHide = ContactHide::model()->isHide($uid, ContactHide::MOBILE_COLUMN);
        
        if ($isHide === true) {
            return false;
        }
        
        return true;
    }
}