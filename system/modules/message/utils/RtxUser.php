<?php

/**
 * rtx用户管理工具类
 *
 * @filename RtxUser.php
 * @encoding UTF-8
 * @author gzdzl
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2010-2015 IBOS Inc
 * @datetime 2015-7-28  15:02:57
 */

namespace application\modules\message\utils;

use application\modules\message\utils\Rtx;

/**
 * rtx用户工具类
 */
class RtxUser extends Rtx
{

    /**
     * rtx 用户管理对象
     * @var type
     */
    private $_userManagerObj;

    /**
     * rtx 部门管理对象
     * @var type
     */
    private $_deptMangerObj;

    /**
     * 调用父类构造并初始化rtx用户和部门管理对象
     *
     * @param type $server
     * @param type $port
     * @param type $logicName
     */
    public function __construct($server, $port, $logicName = 'USERMANAGER')
    {
        parent::__construct($server, $port, $logicName);

        $this->initRtx();
        $this->_userManagerObj = $this->_rootObj->UserManager;
        $this->_deptMangerObj = $this->_rootObj->DeptManager;
    }

    /**
     * 添加用户
     * @param type $deptId 部门编号
     * @param type $uid 用户编号
     * @param type $name 登陆名（中文需要是GBK编码）
     * @param type $password 登陆密码
     * @return boolean 成功返回true，否则返回false
     */
    public function addUser($deptId, $uid, $name, $password)
    {
        //设置用户信息
        $this->_collection->Add("DEPTID", $deptId); //部门编号
        $this->_collection->Add("NICK", $name); //登录名
        $this->_collection->Add("PWD", $password); //密码
        $this->_collection->Add("UIN", $uid); //编号
        //Call2第一个参数是rtx api规定的，有相应的文档说明
        $result = $this->_rtxObj->Call2(1, $this->_collection);

        return $this->verifyResult($result);
    }

    /**
     * 删除用户
     * @param type $uid rtx中的用户编号
     * @return boolean 删除成功返回true，否则返回false
     */
    public function deleteUser($uid)
    {
        $this->_collection->Add('USERNAME', $uid);
        $result = $this->_rtxObj->Call2(2, $this->_collection);

        return $this->verifyResult($result);
    }

    /**
     * 检查用户是否已经存在
     * @param type $name 要检查的登陆名
     * @return boolean 已经存在返回true，否则返回false
     */
    public function isExistUser($name)
    {
        if ($this->_userManagerObj->IsUserExist($name)) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $deptId 部门编号
     * @param type $name 登陆名
     * @param type $realName 真实姓名
     * @param type $gender 性别：0男，1是女
     * @param type $mobile 手机号码
     * @return boolean 成功返回true，否则返回false
     */
    public function editUser($deptId, $name, $realName, $gender, $mobile)
    {
        $this->_collection->Add("DEPTID", $deptId);
        $this->_collection->Add("NICK", $name);
        $this->_collection->Add("NAME", $realName);
        $this->_collection->Add("GENDER", $gender);
        $this->_collection->Add("MOBILE", $mobile);
        $result = $this->_rtxObj->Call2(3, $this->_collection);

        return $this->verifyResult($result);
    }

    /**
     * 修改密码
     *
     * @param type $name 登陆名
     * @param type $password 新密码
     * @return boolean 成功返回true，否则返回false
     */
    function editPassword($name, $password)
    {
        $result = $this->_userManagerObj->SetUserPwd($name, $password);
        return $this->verifyResult($result);
    }

}
