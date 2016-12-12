<?php

/**
 * 通讯录模块函数库
 *
 * @package application.app.contact.utils
 * @version $Id: ContactUtil.php 2764 2014-03-14 11:03:53Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\contact\utils;

/**
 * Class Contact
 *
 * @package application\modules\contact\utils
 */
class Contact
{
    
    /*
     * 按部门排列数据
     */
    static public $deptList = array();
    
    /**
     * 处理部门输出顺序
     *
     * @param array $deptArr 要输出的部门
     * @param integer $pid 输出部门中的顶级部门id
     * @return array
     */
    public static function handleDeptData($deptArr, $pid = 0)
    {
        if (empty($deptArr)) {
            array();
        }
        foreach ($deptArr as $k => $dept) {
            if ($dept['pid'] == $pid) {
                self::$deptList[] = $dept;
                unset($deptArr[$k]);
                self::handleDeptData($deptArr, $dept['deptid']);
            }
        }
        return self::$deptList;
    }
    
    /**
     * 处理输出的拼音排列的用户数组
     *
     * @param array $data
     * @return mixed
     */
    public static function handleLetterGroup($data)
    {
        $group = $data['group'];
        foreach ($group as $letter => $value) {
            foreach ($value as $index => $uid) {
                $group[$letter][$index] = $data['datas'][$uid];
            }
        }
        return $group;
    }
    
}