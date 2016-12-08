<?php

/**
 * 文件柜模块------ file_capacity表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------  容量分配表
 * @package application.modules.file.model
 * @version $Id: FileCapacity.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;
use application\core\utils\Ibos;

class FileCapacity extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file_capacity}}';
    }

    /**
     * 根据某个指定uid容量设置
     * @param integer $uid
     * @return integer 若有设置，返回设置的容量大小；没有设置则返回0
     */
    public function fetchSizeByUid($uid)
    {
        $size = 0;
        $record = Ibos::app()->db->createCommand()
            ->select("size")
            ->from("{{file_capacity}}")
            ->where(sprintf("FIND_IN_SET(%d, `uids`)", $uid))
            ->order("addtime DESC")
            ->queryRow();
        if (!empty($record)) {
            $size = intval($record['size']);
        }
        return $size;
    }

    /**
     * 根据指定部门容量设置
     * @param mix $deptids 部门id数组或逗号隔开字符串
     * @return integer 若有设置，返回设置的容量大小；没有设置则返回0
     */
    public function fetchSizeByDeptids($deptids)
    {
        $size = 0;
        $deptids = is_array($deptids) ? $deptids : explode(",", $deptids);
        foreach ($deptids as $deptid) {
            if ($deptid != '') {
                $deptSql [] = "FIND_IN_SET('{$deptid}', `deptids`)";
            }
        }
        if (isset($deptSql)) {
            $where = implode(' OR ', $deptSql);
            $record = Ibos::app()->db->createCommand()
                ->select("size")
                ->from("{{file_capacity}}")
                ->where($where)
                ->order("addtime DESC")
                ->queryRow();
            if (!empty($record)) {
                $size = intval($record['size']);
            }
        }
        return $size;
    }

    /**
     * 根据指定岗位容量设置
     * @param mix $posids 岗位id数组或逗号隔开字符串
     * @return integer 若有设置，返回设置的容量大小；没有设置则返回0
     */
    public function fetchSizeByPosids($posids)
    {
        $size = 0;
        $posids = is_array($posids) ? $posids : explode(",", $posids);
        foreach ($posids as $posid) {
            if ($posid !== '') {
                $posSql[] = "FIND_IN_SET({$posid}, `posids`)";
            }
        }
        if (isset($posSql)) {
            $where = implode(' OR ', $posSql);
            $record = Ibos::app()->db->createCommand()
                ->select("size")
                ->from("{{file_capacity}}")
                ->where($where)
                ->order("addtime DESC")
                ->queryRow();
            if (!empty($record)) {
                $size = intval($record['size']);
            }
        }
        return $size;
    }

    public function fetchSizeByRoleids($roleids)
    {
        $size = 0;
        $roleidArray = is_array($roleids) ? $roleids : explode(',', $roleids);
        if (!empty($roleidArray)) {
            foreach ($roleidArray as $roleid) {
                if (!empty($roleid)) {
                    $roleSql[] = " FIND_IN_SET( '{$roleid}',`roleids` )";
                }
            }
            if (isset($roleSql)) {
                $where = implode(' OR ', $roleSql);
                $record = Ibos::app()->db->createCommand()
                    ->select('size')
                    ->from($this->tableName())
                    ->where($where)
                    ->order(" addtime DESC")
                    ->queryRow();
                if (!empty($record)) {
                    $size = intval($record['size']);
                }
            }
        }
        return $size;
    }

}
