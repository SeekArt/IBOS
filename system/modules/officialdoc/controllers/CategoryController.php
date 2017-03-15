<?php

/**
 * 信息中心模块------ 分类控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 信息中心模块------  分类控制器类，继承ArticleBaseController
 * @package application.modules.comment.controllers
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Approval;
use application\modules\officialdoc\core\OfficialdocCategory as ICOfficialdocCategory;
use application\modules\officialdoc\model\OfficialdocCategory;
use CHtml;

class CategoryController extends BaseController
{

    /**
     * 分类对象
     * @var object
     * @access private
     */
    private $_category = null;

    /**
     * 初始化当前分类对象
     * @return void
     */
    public function init()
    {
        if ($this->_category === null) {
            $this->_category = new ICOfficialdocCategory('application\modules\officialdoc\model\OfficialdocCategory');
        }
    }

    /**
     * 默认动作
     */
    public function actionIndex()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $data = OfficialdocCategory::model()->fetchAll(array('order' => 'sort ASC'));
            $this->ajaxReturn($this->_category->getAjaxCategory($data), 'json');
        }
    }

    /**
     * 新建
     */
    public function actionAdd()
    {
        $pid = intval(Env::getRequest('pid'));
        $name = CHtml::encode(Env::getRequest('name'));
        $aid = intval(Env::getRequest('aid'));
        // 查询出最大的sort
        $cond = array('select' => 'sort', 'order' => "`sort` DESC");
        $sortRecord = OfficialdocCategory::model()->fetch($cond);
        if (empty($sortRecord)) {
            $sortId = 0;
        } else {
            $sortId = $sortRecord['sort'];
        }
        // 排序号默认在最大的基础上加1，方便上移下移操作
        $newSortId = $sortId + 1;
        $ret = OfficialdocCategory::model()->add(
            array(
                'sort' => $newSortId,
                'pid' => $pid,
                'name' => $name,
                'aid' => $aid
            ), true);
        $this->ajaxReturn(array('IsSuccess' => !!$ret, 'id' => $ret, 'url' => 'javascript:;', 'aid' => $aid, 'catid' => $ret, 'target' => '_self'), 'json');
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $option = empty($op) ? 'default' : $op;
        if ($option == 'default') {
            $pid = intval(Env::getRequest('pid'));
            $name = CHtml::encode(Env::getRequest('name'));
            $catid = intval(Env::getRequest('catid'));
            $aid = intval(Env::getRequest('aid'));
            if ($pid == $catid) {
                $this->error(Ibos::lang('Parent and current can not be the same'));
            }
            OfficialdocCategory::model()->modify($catid, array('pid' => $pid, 'name' => $name, 'aid' => $aid));
            $this->ajaxReturn(array('IsSuccess' => true, 'aid' => $aid), 'json');
        } else {
            $this->$option();
        }
    }

    /**
     * 删除
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $catid = Env::getRequest('catid');
            // 判断顶级分类少于一个不给删除
            $category = OfficialdocCategory::model()->fetchByPk($catid);
            $supCategoryNum = OfficialdocCategory::model()->countByAttributes(array('pid' => 0));
            if (!empty($category) && $category['pid'] == 0 && $supCategoryNum == 1) {
                $this->ajaxReturn(array('IsSuccess' => false, 'msg' => Ibos::lang('Leave at least a Category')), 'json');
            }
            $ret = $this->_category->delete($catid);
            if ($ret == -1) {
                $this->ajaxReturn(array('IsSuccess' => false, 'msg' => Ibos::lang('Contents under this classification only be deleted when no content')), 'json');
            }
            $this->ajaxReturn(array('IsSuccess' => !!$ret), 'json');
        }
    }

    /**
     * 移动
     */
    protected function move()
    {
        $moveType = Env::getRequest('type');
        $pid = Env::getRequest('pid');
        $catid = Env::getRequest('catid');
        $ret = $this->_category->move($moveType, $catid, $pid);
        $this->ajaxReturn(array('IsSuccess' => !!$ret), 'json');
    }

    /**
     * 获得所有审批流程
     */
    protected function getApproval()
    {
        $approvals = Approval::model()->fetchAllApproval();
        $this->ajaxReturn(array('approvals' => $approvals));
    }

    /**
     * 获取某个分类的审批流程
     */
    protected function getCurApproval()
    {
        $catid = Env::getRequest('catid');
        $category = OfficialdocCategory::model()->fetchByPk($catid);
        $approval = Approval::model()->fetchByPk($category['aid']);
        $this->ajaxReturn(array('approval' => $approval));
    }

}
