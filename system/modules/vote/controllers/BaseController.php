<?php
/**
 * @namespace application\modules\vote\controllers
 * @filename BaseController.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/16 20:19
 */

namespace application\modules\vote\controllers;


use application\core\controllers\ApiController;
use application\core\utils\Ibos;

class BaseController extends ApiController
{
    public function init()
    {
        parent::init();
        $this->setPageTitle(Ibos::lang('module name'));
    }
}