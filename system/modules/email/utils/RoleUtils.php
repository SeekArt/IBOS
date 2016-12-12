<?php
/**
 * 邮件模块角色权限工具类
 *
 * @namespace application\modules\email\utils
 * @filename RoleUtils.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/25 9:24
 */

namespace application\modules\email\utils;


use application\core\utils\StringUtil;
use application\core\utils\System;

/**
 * Class RoleUtils
 *
 * @package application\modules\email\utils
 */
class RoleUtils extends System
{
    /**
     * @param string $className
     * @return RoleUtils
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 判断用户 $uid 是否有权限阅读邮件
     *
     * @param integer $uid 需要判断权限的用户 uid
     * @param integer $toId 收信人 uid
     * @param integer $fromId 发信人 uid
     * @param string $copyToIds 抄送人 uid 列表（逗号分隔）。Example：1,2,3
     * @param string $toIds 收信人 uid 列表（逗号分隔）。Example：4,5,6
     * @return bool
     */
    public function canRead($uid, $toId, $fromId, $copyToIds, $toIds)
    {
        // 判断用户是否邮件的合法接收人
        $isReceiver = $toId == $uid ||
            $fromId == $uid ||
            StringUtil::findIn($copyToIds, $uid) ||
            StringUtil::findIn($toIds, $uid);

        return $isReceiver;
    }
}