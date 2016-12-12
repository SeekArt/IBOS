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
 * @version $Id: CategoryController.php 8886 2016-10-31 07:34:02Z php_lwd $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\controllers;

use application\modules\article\core\ArticleCategory;

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
            $this->_category = new ArticleCategory('application\modules\article\model\ArticleCategory');
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function actions()
    {
        $actions = array(
            'index' => 'application\modules\article\actions\category\Index',
            'add' => 'application\modules\article\actions\category\Add',
            'edit' => 'application\modules\article\actions\category\Edit',
            'del' => 'application\modules\article\actions\category\Delete',
            'move' => 'application\modules\article\actions\category\Move',
            'getapproval' => 'application\modules\article\actions\category\GetApproval',
            'getcurapproval' => 'application\modules\article\actions\category\GetCurApproval',
        );
        return $actions;
    }

}
