<?php

/**
 * StatisticsDashboardController class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * @author banyan <banyan@ibos.com.cn>
 * @package application.modules.statistics.controllers
 * @version $Id$
 */

namespace application\modules\statistics\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;
use application\modules\statistics\utils\StatCommon as StatCommonUtil;

class DashboardController extends BaseController
{

    /**
     * 开关统计设置
     * @return void
     */
    public function actionIndex()
    {
        if (Env::submitCheck('formhash')) {
            if (isset($_POST['statmodules'])) {
                // do nothing
            } else {
                $_POST['statmodules'] = array();
            }
            Setting::model()->updateSettingValueByKey('statmodules', $_POST['statmodules']);
            Cache::update('setting');
            $this->success(Ibos::lang('Operation succeed', 'message'));
        } else {
            $res = Setting::model()->fetchSettingValueByKey('statmodules');
            $statModules = $res ? StringUtil::utf8Unserialize($res) : array();
            $data = array(
                'statModules' => $statModules,
                'enabledModules' => StatCommonUtil::getStatisticsModules()
            );
            $this->render('index', $data);
        }
    }

}
