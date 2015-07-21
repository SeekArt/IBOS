<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\IBOS;

class StatusController extends BaseController {

    public function actionIndex() {
        die( IBOS::app()->performance->endClockAndGet() );
    }

}
