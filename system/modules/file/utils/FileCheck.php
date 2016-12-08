<?php

/**
 * 文件柜模块
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------  检测工具类
 * @package application.modules.file.util
 * @version $Id: FileCheck.php 3297 2014-06-19 09:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\utils;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\System;
use application\modules\file\model\File;
use application\modules\file\model\FileDirAccess;
use application\modules\file\model\FileShare;
use application\modules\user\model\User;

class FileCheck extends System
{

    const NONE_ACCESS = 0; // 无权限
    const READABLED = 1; // 只读
    const WRITEABLED = 2; // 读写

    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 检查一个文件是否属于某个人
     * @param integer $fid 文件id
     * @param integer $uid 用户id
     * @return boolean
     */
    public function isOwn($fid, $uid)
    {
        $record = File::model()->fetch("`fid` = {$fid} AND `uid` = {$uid}");
        return !empty($record);
    }

    /**
     * 检测是否存在同名文件或文件夹
     * @param string $name 文件/文件夹名
     * @param integer $pid 父级id
     * @param integer $uid 用户id
     * @param integer $belong 类型，0个人，1公司
     * @return boolean
     */
    public function isExist($name, $pid, $uid, $cloudid, $belong = 0)
    {
        $name = htmlspecialchars(strtolower($name));
        $pid = intval($pid);
        $uid = intval($uid);
        $record = File::model()->fetch("`name` = '{$name}' AND `pid` = {$pid} AND `uid` = {$uid} AND `isdel` = 0 AND `cloudid` = {$cloudid} AND `belong`={$belong}");
        return !empty($record);
    }

    /**
     * 检测阅读权限
     * @param integer $fid 文件id
     * @param integer $uid 用户id
     * @return boolean
     */
    public function isReadable($fid, $uid)
    {
        $fid = intval($fid);
        $uid = intval($uid);
        $user = User::model()->fetchByUid($uid);
        if (!$fid || !$user) {
            return false;
        }
        $file = File::model()->fetchWithShare($fid);
        if ($file['belong'] == File::BELONG_COMPANY) { // 公司网盘
            $access = FileDirAccess::model()->fetchByAttributes(array('fid' => $fid));
            return $this->getAccess($access, $uid) !== self::NONE_ACCESS;
        } else {
            if ($user['uid'] == $file['uid']) {
                return true;
            }
            $isShare = $this->hasUserAccess(array(
                'u' => $file['touids'],
                'd' => $file['todeptids'],
                'p' => $file['toposids'],
                'r' => $file['toroleids'],
            ), $user);
            if ($isShare) {
                return true;
            }
            if ($file['pid'] != 0) { // 特殊情况，某个共享文件夹里面的文件或文件夹
                $findPid = str_replace('/', ',', trim(
                    str_replace('/0/', '', $file['idpath'])
                    , '/'));
                $sqlStr = "FIND_IN_SET({$uid}, fs.`touids`)";
                $deptIdArr = explode(",", $user['alldeptid'] . ',alldept');
                foreach ($deptIdArr as $deptid) {
                    if ($deptid != '') {
                        $sqlStr .= " OR FIND_IN_SET( '{$deptid}', fs.`todeptids`) ";
                    }
                }
                $posIdArr = explode(",", $user['allposid']);
                if (!empty($posIdArr)) {
                    foreach ($posIdArr as $posid) {
                        if ($posid !== '') {
                            $sqlStr .= " OR FIND_IN_SET( '{$posid}', fs.`toposids`) ";
                        }
                    }
                }

                $roleIdArr = explode(',', $user['allroleid']);
                if (!empty($roleIdArr)) {
                    foreach ($roleIdArr as $roleid) {
                        if ($roleid !== '') {
                            $sqlStr .= " OR FIND_IN_SET( '{$roleid}',fs.`toroleids` )";
                        }
                    }
                }
                $record = Ibos::app()->db->createCommand()
                    ->select("*,f.fid AS fid")
                    ->from("{{file_share}} as fs")
                    ->leftJoin("{{file}} f", "f.`fid` = fs.`fid`")
                    ->where(sprintf("FIND_IN_SET(f.`fid`, '%s') AND (%s)", $findPid, $sqlStr));
                return !empty($record);
            }
        }
    }

    /**
     * 获取用户对某个文件/文件夹的权限（针对公司网盘）
     * @param array $access 文件权限数据(由file_dir_access表记录)
     * @param integer $uid 用户id
     * @return integer
     */
    public function getAccess($access, $uid)
    {
        if (empty($access)) {
            return self::WRITEABLED;
        }
        if (empty($access['wdeptids']) &&
            empty($access['wposids']) &&
            empty($access['wuids']) &&
            empty($access['wroleids']) &&
            empty($access['rdeptids']) &&
            empty($access['rposids']) &&
            empty($access['rroleids']) &&
            empty($access['ruids'])
        ) {
            return self::WRITEABLED;
        }
        $user = User::model()->fetchByUid($uid);
        $hasWrite = $this->hasUserAccess(array(
            'u' => $access['wuids'],
            'p' => $access['wposids'],
            'd' => $access['wdeptids'],
            'r' => $access['wroleids'],
        ), $user);
        if ($hasWrite || $this->isManager($uid)) {
            return self::WRITEABLED;
        }
        $hasRead = $this->hasUserAccess(array(
            'u' => $access['ruids'],
            'p' => $access['rposids'],
            'd' => $access['rdeptids'],
            'r' => $access['rroleids'],
        ), $user);
        if ($hasRead) {
            return self::READABLED;
        }
        return self::NONE_ACCESS;
    }

    /**
     * 判断某个uid是否公司网盘管理员
     * @param integer $uid 用户uid
     * @return boolean
     */
    public function isManager($uid)
    {
        if (Ibos::app()->user->isadministrator) {
            return true;
        }
        $manager = Ibos::app()->setting->get('setting/filecompmanager');
        if (empty($manager)) {
            return false;
        }
        $user = User::model()->fetchByUid($uid);
//为了兼容之前没有角色的写法
        $roleid = isset($manager['roleid']) ? $manager['roleid'] : '';
        $isManager = $this->hasUserAccess(array(
            'u' => $manager['uid'],
            'p' => $manager['positionid'],
            'd' => $manager['deptid'],
            'r' => $roleid,
        ), $user);
        if ($isManager) {
            return true;
        }
        return false;
    }

    /**
     * 检查某个文件/文件夹是否已共享
     * @param integer $fid 文件/文件夹id
     * @return boolean
     */
    public function isShare($fid)
    {
        $record = FileShare::model()->fetch(sprintf("fid=%d", intval($fid)));
        return !empty($record);
    }

    /**
     * 判断用户哪个维度有权限
     * @param array $compare 用作对比的一维数组,格式：array('u'=>'','p'=>'','d'=>''))
     * @param array $user 用户数组
     * @return boolean
     */
    protected function hasUserAccess($compare, $user)
    {
        if ($this->findIn($compare['u'], $user['uid'])) {
            return true;
        }
        if ($this->findIn($compare['p'], $user['allposid'])) {
            return true;
        }
        if ($this->findIn($compare['d'], $user['alldeptid'] . ',alldept')) {
            return true;
        }
        if ($this->findIn($compare['r'], $user['allroleid'])) {
            return true;
        }
        return false;
    }

    /**
     * 查找是否包含在内,两边都可以是英文逗号相连的字符串
     * @param string $strId 目标范围
     * @param  string $id 所有值
     * @return boolean
     */
    protected function findIn($strId, $id)
    {
        return StringUtil::findIn($strId, $id);
    }

}
