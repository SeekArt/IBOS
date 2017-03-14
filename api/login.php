<?php

use application\core\model\Log;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Syscache;
use application\modules\main\utils\Main;
use application\modules\user\components\UserIdentity;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

/**
 * 执行登录操作
 *
 * @param integer $uid
 * @param string $terminalName 终端名，不为空即记录登录日志
 * @return array
 */
function doLogin($uid, $terminalName = '')
{
    $config = Ibos::engine()->getMainConfig();

    define('IN_MOBILE', Env::checkInMobile());
    $global = array(
        'clientip' => Env::getClientIp(),
        'config' => $config,
        'timestamp' => time()
    );
    Ibos::app()->setting->copyFrom($global);
    LoadSysCache();

    $saltKey = Main::getCookie('saltkey');
    if (empty($saltKey)) {
        $saltKey = StringUtil::random(8);
        Main::setCookie('saltkey', $saltKey, 86400 * 30, 1, 1);
    }

    $curUser = User::model()->fetchByUid($uid, false, true);

    if (empty($curUser)) {
        return array(
            'code' => UserIdentity::USER_NOT_FOUND,
            'msg' => '找不到该用户，可能系统不存在该用户或该用户被禁用。'
        );
    }

    $identity = new UserIdentity($curUser['username'], $curUser['password'], UserIdentity::LOGIN_BY_USERNAME);
    $ip = Ibos::app()->setting->get('clientip');

    if (Ibos::app()->user->isGuest || Ibos::app()->user->uid != $uid) {
        $identity->setId($uid);
        $identity->setPersistentStates($curUser);
        // 先删除cookie，否则初始化user组件会出错
        foreach ($_COOKIE as $k => $v) {
            $cookiePath = $config['cookie']['cookiepath'];
            $cookieDomain = $config['cookie']['cookiedomain'];
            $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
            @setcookie($k, "", time() - 86400, $cookiePath, $cookieDomain, $secure, false);
        }
        // 是否允许多个账户同时登录
        $account = Ibos::app()->setting->get('setting/account');
        $user = Ibos::app()->user;
        if ($account['allowshare'] != 1) {
            $user->setStateKeyPrefix(Ibos::app()->setting->get('sid'));
        }
        $loginStatus = $user->login($identity);
        if (!empty($terminalName)) {
            $logArr = array(
                'terminal' => $terminalName,
                'password' => '',
                'ip' => $ip,
                'user' => $curUser['username'],
                'loginType' => $identity::LOGIN_BY_USERNAME,
                'address' => '',
                'gps' => ''
            );
            Log::write($logArr, 'login', sprintf('module.user.%d', $uid));
            $rule = UserUtil::updateCreditByAction('daylogin', $uid);
            if (!$rule['updateCredit']) {
                UserUtil::checkUserGroup($uid);
            }
        }
        return array(
            'code' => $loginStatus,
            'msg' => $loginStatus ? '登录成功' : '登录失败',
        );
    } else {
        return array(
            'code' => $uid,
            'msg' => '已登录'
        );
    }

}

/**
 * 加载系统设置
 */
function LoadSysCache()
{
    $caches = Syscache::model()->fetchAll();
    foreach ($caches as $cache) {
        $value = $cache['type'] == '1' ? StringUtil::utf8Unserialize($cache['value']) : $cache['value'];
        if ($cache['name'] == 'setting') {
            Ibos::app()->setting->set('setting', $value);
        } else {
            Ibos::app()->setting->set('cache/' . $cache['name'], $value);
        }
    }
}
