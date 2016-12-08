<?php

/**
 * rtx部门管理工具类
 *
 * @filename DeptRtx.php
 * @encoding UTF-8
 * @author gzdzl
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2010-2015 IBOS Inc
 * @datetime 2015-7-28  15:42:33
 */

namespace application\modules\message\utils;

use application\modules\message\utils\Rtx;

/**
 * rtx部门管理类
 */
class RtxDept extends Rtx
{

    public function __construct($server, $port, $logicName = 'USERMANAGER')
    {
        parent::__construct($server, $port, $logicName);
        $this->initRtx();
    }

    /**
     * 新增加部门
     *
     * @param type $parentDeptId 父部门编号
     * @param type $deptId 部门编号
     * @param type $name 部门名称（中文需要GBK编码）
     * @param type $info 部门说明（中文需要GBK编码）
     * @return boolean 成功返回true
     */
    function addDept($parentDeptId, $deptId, $name, $info)
    {
        return $this->deptManage('add', $parentDeptId, $deptId, $name, $info);
    }

    /**
     * 编辑部门
     *
     * @param type $parentDeptId 父部门编号
     * @param type $deptId 部门编号
     * @param type $name 部门名称（中文需要GBK编码）
     * @param type $info 部门说明（中文需要GBK编码）
     * @return boolean 成功返回true
     */
    function editDept($parentDeptId, $deptId, $name, $info)
    {
        return $this->deptManage('edit', $parentDeptId, $deptId, $name, $info);
    }

    /**
     * 删除部门
     *
     * @param integer $deptId 部门ID
     * @return boolean 成功返回true
     */
    function deleteDept($deptId)
    {
        $this->_collection->Add("DEPTID", $deptId);
        $result = $this->_rtxObj->Call2(0x102, $this->_collection);

        return $this->verifyResult($result);
    }

    /**
     * 检查部门是否已经存在
     *
     * @param type $deptName 部门名称（中文需要GBK编码）
     * @return boolean 存在返回true
     */
    function isExistDept($deptName)
    {
        if ($this->_rootObj->DeptManager->IsDeptExist($deptName)) {
            return true;
        }
        return false;
    }

    /**
     * 部门管理
     *
     * @param integer $parentDeptId 父级部门ID
     * @param integer $deptId 部门ID
     * @param string $name 部门名称（中文需要GBK编码）
     * @param string $info 部门描述（中文需要GBK编码）
     * @param boolean
     */
    private function deptManage($action, $parentDeptId, $deptId, $name, $info)
    {
        if ($action == 'add') {
            $commandCode = 0x101;
        } else if ($action == 'edit') {
            $commandCode = 0x103;
        } else {
            return false;
        }
        $this->_collection->Add("PDEPTID", $parentDeptId);
        $this->_collection->Add("DEPTID", $deptId);
        $this->_collection->Add("NAME", $name);
        $this->_collection->Add("INFO", $info);
        //@TODO 同步过去前rtx的组织部门需要为空
        $result = $this->_rtxObj->Call2($commandCode, $this->_collection);

        return $this->verifyResult($result);
    }

}
