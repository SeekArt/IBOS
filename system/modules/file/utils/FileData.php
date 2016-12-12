<?php

/**
 * 文件柜模块
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------  数据处理工具类
 * @package application.modules.assignment.util
 * @version $Id: FileData.php 3297 2014-06-19 09:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\utils;

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\System;
use application\modules\file\model\File;
use application\modules\file\model\FileCapacity;
use application\modules\file\model\FileShare;
use application\modules\user\model\User;

class FileData extends System
{

    /**
     * 处理是否已分享
     * @param array $list
     * @return array
     */
    public static function handleIsShared($list)
    {
        $shares = FileShare::model()->fetchAll();
        $shareFids = Convert::getSubByKey($shares, 'fid');
        foreach ($list as $k => $li) {
            $list[$k]['isShared'] = 0;
            if (in_array($li['fid'], $shareFids)) {
                $list[$k]['isShared'] = 1;
            }
        }
        return $list;
    }

    /**
     * 连接条件语句
     * @param string $condition1 条件1
     * @param string $condition2 条件2
     * @return string
     */
    public static function joinCondition($condition1, $condition2)
    {
        if (empty($condition1)) {
            return $condition2;
        } else {
            return $condition1 . ' AND ' . $condition2;
        }
    }

    /**
     * 递归组合子文件
     * @param array $files 文件数组
     * @param integer $pid 父级id
     * @return array
     */
    public static function hanldleLevelChild($files, $pid = 0)
    {
        $res = array();
        foreach ($files as $f) {
            if ($f['pid'] == $pid) {
                $f['child'] = self::hanldleLevelChild($files, $f['fid']);
                $res[] = $f;
            }
        }
        return $res;
    }

    /**
     * 获取某个uid的容量设置(单位为M)
     * @param integer $uid 用户uid
     * @return integer 返回容量设置大小，优先度：用户、部门、岗位、默认
     */
    public static function getUserSize($uid)
    {
        // 指定用户容量
        $uidSize = FileCapacity::model()->fetchSizeByUid($uid);
        if ($uidSize) {
            return $uidSize;
        }
        $user = User::model()->fetchByUid($uid);
        // 部门容量
        $deptSize = FileCapacity::model()->fetchSizeByDeptids($user['alldeptid'] . ',alldept');
        if ($deptSize) {
            return $deptSize;
        }
        // 岗位容量
        $posSize = FileCapacity::model()->fetchSizeByPosids($user['allposid']);
        if ($posSize) {
            return $posSize;
        }
        $roleSize = FileCapacity::model()->fetchSizeByRoleids($user['allroleid']);
        if ($roleSize) {
            return $roleSize;
        }
        // 默认容量
        $defSize = Ibos::app()->setting->get('setting/filedefsize');
        return intval($defSize);
    }

    /**
     * 获取文件/文件夹信息，用于页面显示
     * @param integer $fid
     */
    public static function getDirInfo($fid)
    {
        $file = File::model()->fetchByFid($fid);
        if (!empty($file)) {
            if ($file['type'] == File::FOLDER) {
                $file['size'] = File::model()->countSizeByFid($file['fid']);
            }
            $file['formattedsize'] = Convert::sizeCount($file['size']);
            $file['formattedaddtime'] = date('Y/m/d', $file['addtime']);
        } else {
            $file['formattedsize'] = 0;
            $file['formattedaddtime'] = '';
        }
        return $file;
    }

}
