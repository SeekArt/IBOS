<?php

/**
 *  部门模块-岗位分类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author banyanCheung <banyan@ibos.com.cn>
 */
/**
 * 部门模块-分类组件 继承自ICCategory
 * @package application.modules.position.components
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\department\components;

use application\core\components\Category;
use application\core\utils\Ibos;
use application\modules\department\model\Department;

class DepartmentCategory extends Category
{

    /**
     * 部门分类 - 获取zTree ajax树数据
     * @param array $data
     * @return array
     */
    public function getAjaxCategory($data = array())
    {
        foreach ($data as &$row) {
            $row['id'] = $row['deptid'];
            $row['pId'] = $row['pid'];
            $row['name'] = $row['deptname'];
            $row['target'] = '_self';
            $row['url'] = Ibos::app()->urlManager->createUrl("dashboard/user/index") . '&deptid=' . $row['deptid'];
            $row['open'] = true;
        }
        // merge一下返回重新排序的数组以适应zTree,也顺便对返回值做一个强制转换
        return array_merge((array)$data, array());
    }

    /**
     * 部门分类树获取数据方法
     * @param string $condition 兼容
     * @return array
     */
    public function getData($condition = '')
    {
        return Department::model()->findDeptmentIndexByDeptid();
    }

}
