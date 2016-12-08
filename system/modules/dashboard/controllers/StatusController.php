<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Ibos;

class StatusController extends BaseController
{

    public function actionIndex()
    {
        die(Ibos::app()->performance->endClockAndGet());
    }

}
