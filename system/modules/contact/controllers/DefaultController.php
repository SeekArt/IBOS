<?php

/**
 * contact模块公司通讯录控制器
 *
 * @package application.modules.contact.controllers
 * @version $Id: ContactDefaultController.php 2434 2014-03-11 10:38:13Z gzhzh $
 */

namespace application\modules\contact\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;

/**
 * Class DefaultController
 *
 * @package application\modules\contact\controllers
 */
class DefaultController extends BaseController
{
    
    /**
     * 公司通讯录列表
     */
    public function actionIndex()
    {
        $op = Env::getRequest('op');
        $op = in_array($op, array('dept', 'letter')) ? $op : 'letter';
        $this->setPageTitle(Ibos::lang('Contact'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Contact'), 'url' => $this->createUrl('defalut/index')),
            array('name' => Ibos::lang('Company contact'))
        ));
        $view = $op == 'letter' ? 'letter' : 'dept';
        $this->render($view);
    }
    
    /**
     * 异步请求入口
     */
    public function actionAjaxApi()
    {
        $this->ajaxApi();
    }
    
    /**
     * 导出通讯录
     */
    public function actionExport()
    {
        $this->export();
    }
    
    public function actionPrintContact()
    {
        $this->printContact();
    }
    
}
