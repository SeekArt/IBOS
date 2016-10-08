<?php

/**
 * CoController.class.file
 *
 * @author mumu <2317216477@qq.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2015 IBOS Inc
 */
/**
 * 酷办公中心控制器
 *
 * @package application.modules.dashboard.controllers
 * @author mumu <2317216477@qq.com>
 *
 */

namespace application\modules\dashboard\controllers;

use application\modules\main\model\Setting;
use application\modules\message\core\co\CoApi;

class CoController extends BaseController {

    protected $isBinding = false;

    public function init() {
        parent::init();
        $this->chkBinding();
    }

    /**
     * 如果没有绑定，则：
     * $this->isBinding、$this->accesstoken、$this->coInfo都是初始值
     */
    protected function chkBinding() {
        $isBinding = Setting::model()->fetchSettingValueByKey( 'cobinding' );
        if ( !!$isBinding ) {
            $this->isBinding = true;
        }
    }

    /**
     * 通过accesstoken获取co用户的信息
     * 如果成功，认为用户验证成功
     * @param string $accesstoken
     * @return array or boolean 如果accesstoken有效，则返回用户信息数组，失败返回false
     */
    protected function getCoUser( $accesstoken ) {
        return CoApi::getInstance()->getUserInfo( $accesstoken );
    }

}
