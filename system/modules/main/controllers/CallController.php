<?php

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\Cloud;
use application\core\utils\Env;
use application\core\utils\Ibos;

class CallController extends Controller
{

    const BILATERAL_URL = 'Api/Ivr/Confirmconf';
    const COMM_CALL_URL = 'Api/Comm/Call';

    /**
     * 语音会议视图
     */
    public function actionMeeting()
    {
        $alia = 'application.theme.default.views.layouts.call';
        $view = $this->renderPartial($alia, array(), true);
        $this->ajaxReturn(array('isSuccess' => true, 'html' => $view));
    }

    /**
     * 单向呼叫
     */
    public function actionUnidirec()
    {
        $data = Env::getRequest('data');
        $user = $data[0];
        $siteUrl = Ibos::app()->setting->get('siteurl');
        $user['avatar'] = $siteUrl . $user['avatar'];
        $this->redirect(Cloud::getInstance()->build(self::COMM_CALL_URL, array('data' => $user)));
    }

    /**
     * 双向呼叫
     */
    public function actionBilateral()
    {
        $data = Env::getRequest('data');
        $this->redirect(Cloud::getInstance()->build(self::BILATERAL_URL, array('data' => $data)));
    }

    /**
     * 检查是否开通云服务
     */
    public function actionChkConf()
    {
        if (Cloud::getInstance()->isOpen()) {
            $this->ajaxReturn(array('isSuccess' => true));
        } else {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => '抱歉，请先开通云服务！'));
        }
    }

}
