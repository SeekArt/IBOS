<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;


class BackgroundController extends BaseController
{

    /**
     * 系统背景设置
     */
    public function actionIndex()
    {
        $data = array(
            'skin' => Ibos::app()->setting->get('setting/skin'),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('dashboard'),
        );
        $this->render('index', $data);
    }

    /**
     * 更换系统背景
     */
    public function actionSkin()
    {
        $type = Env::getRequest('type');
        $bool = false;
        $setting = Setting::model()->fetchSettingValueByKey('skin');
        if (empty($setting)) {
            $settingAdd = Setting::model()->add(array('skey' => 'skin', 'svalue' => $type));
            if ($settingAdd) {
                $bool = true;
            }
        } else {
            $settingUpdate = Setting::model()->updateSettingValueByKey('skin', $type);
            if ($settingUpdate) {
                $bool = true;
            }
        }
        Cache::update('setting');
        $this->ajaxReturn(array('isSuccess' => $bool));
    }
}

