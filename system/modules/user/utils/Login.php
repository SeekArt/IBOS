<?php

/**
 * 用户模块登录工具类
 *
 * @package application.modules.user.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\user\utils;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\System;
use application\core\utils\WebSite;
use application\modules\main\model\Setting;
use application\modules\user\model\UserBinding;

class Login extends System
{

    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 发送网页登录提醒
     * @param integer $uid 用户ID
     */
    public function sendWebLoginNotify($uid)
    {
        $ip = Env::getClientIp();
        $msg = '您的账号在' . date('Y年m月d日 H:i:s', TIMESTAMP) . '通过网页端登陆。登陆地点为：' . Convert::convertIp($ip) . ',IP地址为：' . $ip;
        $this->sendWxNotify($uid, $msg);
    }

    /**
     * 发送扫码登录提醒
     * @param integer $uid 用户ID
     */
    public function sendWxLoginNotify($uid)
    {
        $ip = Env::getClientIp();
        $msg = '您的账号在' . date('Y年m月d日 H:i:s', TIMESTAMP) . '通过微信企业号扫码登陆。登陆地点为：' . Convert::convertIp($ip) . ',IP地址为：' . $ip;
        $this->sendWxNotify($uid, $msg);
    }

    /**
     * 发送微信安全助手登录提醒
     * @param integer $uid 用户ID
     * @param string $msg 提醒的消息
     */
    protected function sendWxNotify($uid, $msg = '')
    {
        $bdVal = UserBinding::model()->fetchBindValue($uid, 'wxqy');
        if (!empty($bdVal)) {
            $corpid = Setting::model()->fetchSettingValueByKey('corpid');
            $param = array(
                'userIds' => array($bdVal),
                'appFlag' => 'helper',
                'var' => array(
                    'message' => $msg,
                ),
                'corpid' => $corpid,
            );
            $route = 'Api/WxPush/push';
            $res = WebSite::getInstance()->fetch($route, json_encode($param), 'post');
        }
    }

}
