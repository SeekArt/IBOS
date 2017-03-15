<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\main\utils\Update;

class UpdateController extends BaseController
{

    public function actionIndex()
    {

        if (Ibos::app()->getRequest()->getIsAjaxRequest()) {
            if (LOCAL) {
                @set_time_limit(0);
            }
            $op = Env::getRequest('op');
            if (!in_array($op, array('data', 'static', 'module'))) {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'data' => array(),
                    'msg' => '错误的op参数，确定你是正常操作？',
                ));
            }
            $offset = Env::getRequest('offset');
            if ($offset == '0') {
                Cache::update();
            }
            $method = $op . 's';
            return $this->ajaxReturn(Update::$method($offset));
        } else {
            return $this->render('index');
        }
    }

}
