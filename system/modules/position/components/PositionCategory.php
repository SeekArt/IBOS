<?php

/**
 * 岗位模块-岗位分类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author banyanCheung <banyan@ibos.com.cn>
 */
/**
 * 岗位模块-分类组件 继承自ICCategory
 * @package application.modules.position.components
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\position\components;

use application\core\components\Category;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Ibos;
use application\core\utils\Org;
use application\modules\position\utils\Position as PositionUtil;

class PositionCategory extends Category
{

    /**
     * 岗位分类 - 获取zTree ajax树数据
     * @param array $data
     * @return array
     */
    public function getAjaxCategory($data = array())
    {
        foreach ($data as &$row) {
            $row['id'] = $row['catid'];
            $row['pId'] = $row['pid'];
            $row['name'] = $row['name'];
            $row['target'] = '_self';
            $row['url'] = Ibos::app()->urlManager->createUrl("dashboard/position/index") . '&catid=' . $row['catid'];
            $row['open'] = true;
        }
        return array_merge((array)$data, array());
    }

    /**
     * 岗位分类树获取数据方法
     * @param string $condition 兼容
     * @return array
     */
    public function getData($condition = '')
    {
        return PositionUtil::loadPositionCategory();
    }

    /**
     * 更新组织架构缓存
     */
    public function afterAdd()
    {
        CacheUtil::update('PositionCategory');
        Org::update();
    }

    /**
     * 更新组织架构缓存
     */
    public function afterEdit()
    {
        CacheUtil::update('PositionCategory');
        Org::update();
    }

    /**
     * 更新组织架构缓存
     */
    public function afterDelete()
    {
        CacheUtil::update('PositionCategory');
        Org::update();
    }

}
