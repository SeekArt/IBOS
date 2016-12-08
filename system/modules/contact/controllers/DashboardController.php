<?php
/**
 * 通讯录后台配置控制器
 *
 * @namespace application\modules\contact\controllers
 * @filename DashboardController.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/9 16:03
 */

namespace application\modules\contact\controllers;


use application\modules\dashboard\controllers\BaseController;


/**
 * Class DashboardController
 *
 * @package application\modules\contact\controllers
 */
class DashboardController extends BaseController
{
    /**
     * 视图：渲染后台配置
     */
    public function actionIndex()
    {
        $this->render('index');
    }
    
}
