<?php

/**
 * 组织架构模块岗位分类控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块岗位分类
 *
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: CategoryController.php 4064 2014-09-03 09:13:16Z zhangrong $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\position\components\PositionCategory as ICPositionCategory;
use application\modules\position\model\PositionCategory;
use application\modules\position\model\Position;

class PositioncategoryController extends OrganizationbaseController
{

    /**
     * 当前分类对象
     * @var mixed
     */
    private $_category;

    /**
     * 初始化当前分类对象
     * @return void
     */
    public function init()
    {
        $this->useConfig = true;
        if ($this->_category === null) {
            $this->_category = new ICPositionCategory('application\modules\position\model\PositionCategory');
        }
    }

    /**
     * 获取分类树
     * @return void
     */
    public function actionIndex()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $data = $this->_category->getData();
            $this->ajaxReturn($this->_category->getAjaxCategory($data), 'json');
        }
    }

    /**
     * 新建分类
     * @return void
     */
    public function actionAdd()
    {
        $pid = Env::getRequest('pid');
        $name = \CHtml::encode(trim(Env::getRequest('name')));
        $id = $this->_category->add($pid, $name);
        if ($id) {
            $data = array(
                'id' => $id,
                'pId' => $pid,
                'name' => $name,
                'target' => '_self',
                'url' => Ibos::app()->urlManager->createUrl("dashboard/position/index") . '&catid=' . $id,
                'open' => true
            );
            $this->ajaxReturn(array('isSuccess' => true, 'data' => $data), 'json');
        } else {
            $this->ajaxReturn(array('isSuccess' => false));
        }
    }

    /**
     * 编辑分类
     * @return void
     */
    public function actionEdit()
    {
        $pid = Env::getRequest('pid');
        $catid = Env::getRequest('catid');
        if (Env::getRequest('op') === 'move') {
            $index = Env::getRequest('index'); // 排序后位置,0表示第一位，1表示第二位...
            return $this->move($index, $catid, $pid);
        }
        $name = \CHtml::encode(trim(Env::getRequest('name')));
        $ret = $this->_category->edit($catid, $pid, $name);
        $data = array(
            'id' => $catid,
            'pId' => $pid,
            'name' => $name,
            'target' => '_self',
            'url' => Ibos::app()->urlManager->createUrl("dashboard/position/index") . '&catid=' . $catid,
            'open' => true
        );
        $this->ajaxReturn(array('isSuccess' => !!$ret, 'data' => $data), 'json');
    }

    /**
     * 删除分类
     * @return void
     */
    public function actionDelete()
    {
        $catid = Env::getRequest('catid');
        // 判断顶级分类少于一个不给删除
        $category = PositionCategory::model()->fetchByPk($catid);
        $supCategoryNum = PositionCategory::model()->countByAttributes(array('pid' => '0'));
        if (!empty($category) && $category['pid'] == '0' && $supCategoryNum == '1') {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Leave at least a Category')), 'json');
        }
        // 判断分类下有子类，不给删除
        $subCategoryNum = PositionCategory::model()->countByAttributes(array('pid' => $catid));
        if ($subCategoryNum != '0') {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Please delete subCategory first')), 'json');
        }
        $ret = $this->_category->delete($catid);
        Position::model()->updateAll(array('catid' => 1), '`catid` = ' . $catid);
        $msg = $ret ? Ibos::lang('Operation succeed', 'message') : Ibos::lang('Operation failure', 'message');
        $this->ajaxReturn(array('isSuccess' => !!$ret, 'msg' => $msg), 'json');
    }

    /**
     * 移动分类
     * @return void
     */
    protected function move($index, $catid, $pid)
    {
        $cates = PositionCategory::model()->fetchAll(array('condition' => "`pid`={$pid} AND `catid`!={$catid}", 'order' => "`sort` ASC")); // 把移动到的父级原有的分类找出来
        foreach ($cates as $k => $cate) {
            $newSort = $k;
            if ($newSort >= $index) {
                $newSort = $k + 1; // 比新插入的分类后，排序加1
            }
            PositionCategory::model()->modify($cate['catid'], array('sort' => $newSort + 1)); // 排序从1开始的，所以+1
        }
        PositionCategory::model()->modify($catid, array('sort' => $index + 1, 'pid' => $pid));
        $this->ajaxReturn(array('isSuccess' => true), 'json');
    }

}
