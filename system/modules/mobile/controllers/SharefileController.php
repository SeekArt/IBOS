<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\file\model\File;
use application\modules\file\utils\FileData;
use application\modules\file\controllers\FromShareController;

class ShareFileController extends FromShareController
{
    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     * @param string $routes
     * @return boolean
     */
    public function filterRoutes($routes)
    {
        return true;
    }
}
