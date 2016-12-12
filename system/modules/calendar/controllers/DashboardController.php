<?php

namespace application\modules\calendar\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;

class DashboardController extends BaseController
{

    /**
     * 配置项
     * @var array
     */
    private $_fields = array('calendaraddschedule', 'calendareditschedule', 'calendarworkingtime', 'calendaredittask');

    public function getAssetUrl($module = '')
    {
        $module = 'dashboard';
        return Ibos::app()->assetManager->getAssetsUrl($module);
    }

    /**
     * 日程后台设置页面
     */
    public function actionIndex()
    {
        $calendarSetting = array();
        $setting = Ibos::app()->setting->get('setting');
        foreach ($this->_fields as $field) {
            $calendarSetting[$field] = $setting[$field];
        }
        $data['setting'] = $calendarSetting;
        $this->render('index', $data);
    }

    public function actionUpdate()
    {
        if (Env::submitCheck('calendarSubmit')) {
            $setting = array();
            foreach ($this->_fields as $field) {
                if (array_key_exists($field, $_POST)) {
                    $setting[$field] = $_POST[$field];
                } else {
                    $setting[$field] = 0;
                }
            }
            foreach ($setting as $key => $value) {
                Setting::model()->updateSettingValueByKey($key, $value);
            }
            Cache::update('setting');
            $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('dashboard/index'));
        }
    }

}
