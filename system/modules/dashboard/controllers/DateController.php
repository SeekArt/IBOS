<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Cache;
use application\modules\main\model\Setting;

class DateController extends BaseController
{

    public function actionIndex()
    {
        $formSubmit = Env::submitCheck('dateSetupSubmit');
        if ($formSubmit) {
            $data = array(
                'dateformat' => $_POST['dateFormat'],
                'timeformat' => $_POST['timeFormat'],
                'dateconvert' => $_POST['dateConvert'],
                'timeoffset' => $_POST['timeOffset']
            );
            foreach ($data as $sKey => $sValue) {
                Setting::model()->updateSettingValueByKey($sKey, $sValue);
            }
            Cache::update(array('setting'));
            $this->success(Ibos::lang('Save succeed', 'message'));
        } else {
            $date = Setting::model()->fetchSettingValueByKeys('dateformat,dateconvert,timeformat,timeoffset');
            $data = array(
                'timeZone' => Ibos::getLangSource('dashboard.timeZone'),
                'date' => $date,
            );
            $this->render('index', $data);
        }
    }

}
