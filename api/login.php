<?php

use application\core\model\Log;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Syscache;
use application\modules\main\utils\Main;
use application\modules\user\components\UserIdentity;
use application\modules\user\model\FailedIp;
use application\modules\user\model\FailedLogin;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

/**
 * 执行登录操作
 * @param type $uid
 * @throws Exception
 */
function dologin( $uid, $log = '' ) {
    //$config = @include PATH_ROOT . '/system/config/config.php';
    //if ( empty( $config ) ) {
    //    throw new Exception( Ibos::Lang( 'Config not found', 'error' ) );
    //} else {
        $config = Ibos::engine()->getMainConfig();

        define( 'IN_MOBILE', Env::checkInMobile() );
        $global = array(
            'clientip' => Env::getClientIp(),
            'config' => $config,
            'timestamp' => time()
        );
        Ibos::app()->setting->copyFrom( $global );
        LoadSysCache();

        $saltkey = Main::getCookie( 'saltkey' );
        if ( empty( $saltkey ) ) {
            $saltkey = StringUtil::random( 8 );
            Main::setCookie( 'saltkey', $saltkey, 86400 * 30, 1, 1 );
        }
        $curUser = User::model()->fetchByUid( $uid );
        // 开始登录
        // 登录类型
        $loginType = 4;
        $identity = new UserIdentity( $curUser['mobile'], $curUser['password'], $loginType );
        $result = $identity->authenticate();
        $ip = Ibos::app()->setting->get( 'clientip' );
        if ( $result > 0 ) {
            if ( Ibos::app()->user->isGuest || Ibos::app()->user->uid != $uid ) {
                $identity->setId( $uid );
                $identity->setPersistentStates( $curUser );
                // 先删除cookie，否则初始化user组件会出错
                foreach ( $_COOKIE as $k => $v ) {
                    $cookiePath = $config['cookie']['cookiepath'];
                    $cookieDomain = $config['cookie']['cookiedomain'];
                    $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
                    @setcookie( $k, "", time() - 86400, $cookiePath, $cookieDomain, $secure, false );
                }
                // 是否允许多个账户同时登录
                $account = Ibos::app()->setting->get( 'setting/account' );
                $user = Ibos::app()->user;
                if ( $account['allowshare'] != 1 ) {
                    $user->setStateKeyPrefix( Ibos::app()->setting->get( 'sid' ) );
                }
                $loginStatus = $user->login( $identity );
                if ( !empty( $log ) ) {
                    $logArr = array(
                        'terminal' => $log,
                        'password' => '',
                        'ip' => $ip,
                        'user' => $curUser['username'],
                        'loginType' => $identity::LOGIN_BY_USERNAME,
                        'address' => '',
                        'gps' => ''
                    );
                    Log::write( $logArr, 'login', sprintf( 'module.user.%d', $uid ) );
                    $rule = UserUtil::updateCreditByAction( 'daylogin', $uid );
                    if ( !$rule['updateCredit'] ) {
                        UserUtil::checkUserGroup( $uid );
                    }
                }
                return array(
                    'code' => $loginStatus,
                    'msg' => $loginStatus ? '登录成功' : '登录失败',
                );
            } else {
                return array(
                    'code' => $result,
                    'msg' => '已登录'
                );
            }
        } else {
            switch ( $result ) {
                case 0:
                    $msg = Ibos::lang( 'User not fount', 'user.default', array( '{username}' => $curUser['username'] ) );
                    break;
                case -1:
                    $msg = Ibos::lang( 'User lock', 'user.default', array( '{username}' => $curUser['username'] ) );
                    break;
                case -2:
                    $msg = Ibos::lang( 'User disabled', 'user.default', array( '{username}' => $curUser['username'] ) );
                    break;
                case -3:
                    FailedLogin::model()->updateFailed( $curUser['username'] );
                    list($ip1, $ip2) = explode( '.', $ip );
                    $newIp = $ip1 . '.' . $ip2;
                    FailedIp::model()->insertIp( $newIp );
                    $log = array(
                        'user' => $curUser['username'],
                        'password' => StringUtil::passwordMask( $curUser['password'] ),
                        'ip' => $ip
                    );
                    Log::write( $log, 'illegal', 'module.user.login' );
                    $msg = Ibos::lang( 'User name or password is not correct', 'user.default' );
                    break;
            }
            return array(
                'code' => $result,
                'msg' => $msg,
            );
        }
    //}
}

/**
 * 加载系统设置
 * @param mixed $event
 */
function LoadSysCache() {
    $caches = Syscache::model()->fetchAll();
    foreach ( $caches as $cache ) {
        $value = $cache['type'] == '1' ? StringUtil::utf8Unserialize( $cache['value'] ) : $cache['value'];
        if ( $cache['name'] == 'setting' ) {
            Ibos::app()->setting->set( 'setting', $value );
        } else {
            Ibos::app()->setting->set( 'cache/' . $cache['name'], $value );
        }
    }
}
