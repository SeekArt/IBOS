<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Ibos;

class CheckgvarController extends BaseController
{

    public function actionIndex()
    {
        if (Ibos::app()->user->uid == '1') {
            $request = Ibos::app()->getRequest();
            $name = $request->getQuery('name');
            $isSet = (bool)($request->getQuery('set'));
            if ($isSet) {
                $value = $request->getQuery('value');
                Ibos::app()->setting->set($name, $value);
            }
            $g = Ibos::app()->setting->get($name);
            var_dump($g);
        } else {
            var_dump('forbidden');
        }
    }

}
