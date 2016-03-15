<?php

/**
 * user模块默认控制器
 * @package application.modules.user.controllers
 * @version $Id: DefaultController.php 6533 2016-03-07 08:18:31Z gzhyj $
 */

namespace application\modules\user\controllers;

use application\core\controllers\Controller;
use application\core\model\Log;
use application\core\utils as util;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\dashboard\model\Announcement;
use application\modules\dashboard\model\LoginTemplate;
use application\modules\message\core\co\CoApi;
use application\modules\main\model\Setting;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\components\User as ICUser;
use application\modules\user\components\UserIdentity;
use application\modules\user\model\FailedIp;
use application\modules\user\model\FailedLogin;
use application\modules\user\model\User;
use application\modules\user\utils\Login;
use application\modules\user\utils\User as UserUtil;

class DefaultController extends Controller {

    public $layout = '';

    public function init() {
        return false;
    }

    /**
     * ajax检查当前用户登录状态
     * @return void 
     */
    public function actionCheckLogin() {
        $islogin = false;
        $isAutologin = MainUtil::getCookie( 'autologin' );
        $isGuest = util\IBOS::app()->user->isGuest;
        $expires = util\IBOS::app()->user->getState( ICUser::AUTH_TIMEOUT_VAR );
        if ( $isAutologin ) {
            $islogin = true;
        } else if ( !$isGuest && ( $expires == null || $expires > time() ) ) {
            $islogin = true;
        }
        $this->ajaxReturn( array( 'islogin' => $islogin ) );
    }

    /**
     * ajax提交登录
     * @return void
     */
    public function actionAjaxLogin() {
        $account = UserUtil::getAccountSetting();
        if ( util\Env::submitCheck( 'formhash' ) ) {
            // 用户名
            $userName = util\Env::getRequest( 'username' );
            // 登录类型
            if ( String::isMobile( $userName ) ) {
                $loginType = 4;
            } else if ( String::isEmail( $userName ) ) {
                $loginType = 2;
            } else {
                $loginType = 1;
            };
            // 密码
            $passWord = util\Env::getRequest( 'password' );
            return $this->doLogin( $userName, $passWord, $loginType, $account, 1, 0, 1 );
        }
    }

    /**
     * 登陆处理动作
     */
    public function actionLogin() {
        if ( !util\IBOS::app()->user->isGuest ) {
            $this->redirect( util\IBOS::app()->urlManager->createUrl( 'main/default/index' ) );
        }
        $account = UserUtil::getAccountSetting();
        if ( !util\Env::submitCheck( 'loginsubmit', 1 ) ) {
            $corpid = Setting::model()->fetchSettingValueByKey( 'corpid' );
            $cobinding = Setting::model()->fetchSettingValueByKey( 'cobinding' );
            $announcement = Announcement::model()->fetchByTime( TIMESTAMP );
            $qr = IBOS::app()->setting->get( 'setting/qrcode' );
            $qrcode = isset( $qr ) ? $qr : '';
            $data = array(
                'assetUrl' => $this->getAssetUrl( 'user' ),
                'lang' => util\IBOS::getLangSources(),
                'unit' => util\IBOS::app()->setting->get( 'setting/unit' ),
                'account' => $account,
                'cookietime' => $account['cookietime'],
                'announcement' => $announcement,
                'loginBg' => LoginTemplate::model()->fetchAll( "`disabled`= 0 AND `image`!=''" ),
                'qrcode' => $qrcode,
                'wxbinding' => !empty( $corpid ),
                'cobinding' => !empty( $cobinding ),
                'coUrl' => util\Api::getInstance()->buildUrl( CoApi::CO_URL . 'fastlogin', array( 'aeskey' => util\IBOS::app()->setting->get( 'aeskey' ) ) ),
            );
            $this->setTitle( util\IBOS::lang( 'Login page' ) );
            $this->renderPartial( 'login', $data );
        } else {
            // 用户名
            $userName = util\Env::getRequest( 'username' );

            /**
             * 添加转义
             * 如果有单引号就直接当做用户名不存在错误
             * userName中存在奇数个单引号会报错，使用addslashes转义后
             * 在没有找到对应账号时，会添加进登录失败表记录下来
             * 在同一个ip和同一个错误账号登录失败时，添加进表中会报错（数据完整性，主键重复）
             * 为了简单起见，直接把userName中含有单引号的都当做错误账号
             */
            if ( preg_match( '/[\']+/', $userName ) ) {
                $this->error( util\IBOS::lang( 'User not fount', '', array( '{username}' => $userName ) ), '', array(), array( 'error' => 0 ) );
            }

            // 登录类型
            if ( String::isMobile( $userName ) ) {
                $loginType = 4;
            } else if ( String::isEmail( $userName ) ) {
                $loginType = 2;
            } else {
                $loginType = 1;
            };
            // 密码
            $passWord = util\Env::getRequest( 'password' );
            // 是否勾选自动登录
            $autoLogin = util\Env::getRequest( 'autologin' );
            // cookie
            $cookieTime = util\Env::getRequest( 'cookietime' );
            $this->doLogin( $userName, $passWord, $loginType, $account, $autoLogin, $cookieTime );
        }
    }

    /**
     * 登出
     */
    public function actionLogout() {
        util\IBOS::app()->user->logout();
        $loginUrl = util\IBOS::app()->urlManager->createUrl( 'user/default/login' );
        $this->success( util\IBOS::lang( 'Logout succeed' ), $loginUrl );
    }

    /**
     * 登录操作
     * @param string $userName 用户名
     * @param string $passWord 密码
     * @param integer $loginType 登录类型
     * @param array $account 账户安全设置
     * @param integer $autoLogin 是否自动登录
     * @param integer $cookieTime 自动登录的时间
     * @param integer $inajax 是否ajax登录
     */
    protected function doLogin( $userName, $passWord, $loginType, $account, $autoLogin = 1, $cookieTime = 0, $inajax = 0 ) {
        if ( !$passWord || $passWord != \CHtml::encode( $passWord ) ) {
            $this->error( util\IBOS::lang( 'Passwd illegal' ) );
        }
        $errornum = $this->loginCheck( $account, $userName );
        // 日志
        $ip = util\IBOS::app()->setting->get( 'clientip' );
        // 开始验证
        // 登录类型
        if ( String::isMobile( $userName ) ) {
            $loginType = 4;
        } else if ( String::isEmail( $userName ) ) {
            $loginType = 2;
        } else {
            $loginType = 1;
        };
        $identity = new UserIdentity( $userName, $passWord, $loginType );
        $result = $identity->authenticate();
        if ( $result > 0 ) {
            $user = util\IBOS::app()->user;
            // 设置会话过期时间
            if ( empty( $autoLogin ) ) {
                $user->setState( $user::AUTH_TIMEOUT_VAR, TIMESTAMP + ($account['timeout'] ) );
            } else {
                MainUtil::setCookie( 'autologin', 1, $cookieTime );
            }
            $user->login( $identity, $cookieTime );

            // ajax登录不进行日志记录及跳转操作

            if ( !$inajax ) {
                $refer = util\Env::getRequest( 'refer' );
                $urlForward = empty( $refer ) ? util\Env::referer() : $refer;
                $log = array(
                    'terminal' => 'web',
                    'password' => util\String::passwordMask( $passWord ),
                    'ip' => $ip,
                    'user' => $userName,
                    'loginType' => $loginType,
                    'address' => '',
                    'gps' => ''
                );
                Log::write( $log, 'login', sprintf( 'module.user.%d', $user->uid ) );
                $rule = UserUtil::updateCreditByAction( 'daylogin', $user->uid );
                if ( !$rule['updateCredit'] ) {
                    UserUtil::checkUserGroup( $user->uid );
                }

                Login::getInstance()->sendWebLoginNotify( $user->uid );
                $this->success( util\IBOS::lang( 'Login succeed', '', array( '{username}' => $user->realname ) ), $urlForward );
            } else {
                $this->ajaxReturn( array( 'isSuccess' => true ) );
            }
        } else {
            if ( $result === 0 ) {
                $this->error( util\IBOS::lang( 'User not fount', '', array( '{username}' => $userName ) ), '', array(), array( 'error' => $result ) );
            } else if ( $result === -1 ) {
                $this->error( util\IBOS::lang( 'User lock', '', array( '{username}' => $userName ) ), '', array(), array( 'error' => $result ) );
            } else if ( $result === -2 ) {
                $this->error( util\IBOS::lang( 'User disabled', '', array( '{username}' => $userName ) ), '', array(), array( 'error' => $result ) );
            } else if ( $result === -3 ) {
                FailedLogin::model()->updateFailed( $userName );
                list($ip1, $ip2) = explode( '.', $ip );
                $newIp = $ip1 . '.' . $ip2;
                FailedIp::model()->insertIp( $newIp );
                $log = array(
                    'user' => $userName,
                    'password' => util\String::passwordMask( $passWord ),
                    'ip' => $ip
                );
                Log::write( $log, 'illegal', 'module.user.login' );
                if ( $errornum ) {
                    $this->error( '登录失败，您还可以尝试' . ($errornum - 1) . '次' );
                } else {
                    $this->error( util\IBOS::lang( 'User name or password is not correct' ), '', array(), array( 'error' => $result ) );
                }
            }
        }
    }

    /**
     * 重置密码
     */
    public function actionReset() {
        if ( util\IBOS::app()->user->isGuest ) {
            util\IBOS::app()->user->loginRequired();
        }
        if ( util\Env::submitCheck( 'formhash' ) ) {
            $original = filter_input( INPUT_POST, 'originalpass', FILTER_SANITIZE_SPECIAL_CHARS );
            $new = filter_input( INPUT_POST, 'newpass', FILTER_SANITIZE_SPECIAL_CHARS );
            $newConfirm = filter_input( INPUT_POST, 'newpass_confirm', FILTER_SANITIZE_SPECIAL_CHARS );
            if ( $original == '' ) {
                // 没有填写原来的密码
                $this->error( util\IBOS::lang( 'Original password require' ) );
            } else if ( strcasecmp( md5( md5( $original ) . util\IBOS::app()->user->salt ), util\IBOS::app()->user->password ) !== 0 ) {
                // 密码跟原来的对不上
                $this->error( util\IBOS::lang( 'Password is not correct' ) );
            } else if ( !empty( $new ) && strcasecmp( $new, $newConfirm ) !== 0 ) {
                // 两次密码不一致
                $this->error( util\IBOS::lang( 'Confirm password is not correct' ) );
            } else {
                $password = md5( md5( $new ) . util\IBOS::app()->user->salt );
                $success = User::model()->updateByUid( util\IBOS::app()->user->uid, array( 'password' => $password, 'lastchangepass' => TIMESTAMP ) );
                $success && util\IBOS::app()->user->logout();
                $this->success( util\IBOS::lang( 'Reset success' ), $this->createUrl( 'default/login' ) );
            }
        } else {
            $userName = util\IBOS::app()->user->realname;
            $data = array(
                'assetUrl' => $this->getAssetUrl( 'user' ),
                'account' => UserUtil::getAccountSetting(),
                'lang' => util\IBOS::getLangSources(),
                'unit' => util\IBOS::app()->setting->get( 'setting/unit' ),
                'user' => $userName
            );
            $this->renderPartial( 'reset', $data );
        }
    }

    /**
     * 登录的微信安全小助手弹窗
     */
    public function actionWxcode() {
        $corpid = Setting::model()->fetchSettingValueByKey( 'corpid' );
        $data = array(
            'assetUrl' => $this->getAssetUrl( 'user' ),
            'wxbinding' => !empty( $corpid ),
            'randomcode' => util\String::random( 11 ),
        );
        $this->renderPartial( 'wxcode', $data );
    }

    /**
     * 登陆检查，主要是登陆错误验证，账号检查
     * @param array $account
     * @return integer
     */
    protected function loginCheck( $account, $username ) {
        $return = 0;
        if ( $account['errorlimit'] != 0 ) {
            $login = FailedLogin::model()->fetchUsername( $username );
            $ip = util\IBOS::app()->setting->get( 'clientip' );
            $errrepeat = intval( $account['errorrepeat'] );
            $errTime = $account['errortime'] * 60;
            $return = (!$login || (TIMESTAMP - $login['lastupdate'] > $errTime)) ? $errrepeat : max( 0, $errrepeat - $login['count'] );
            if ( !$login ) {
                FailedLogin::model()->add( array(
                    'ip' => $ip,
                    'username' => $username,
                    'count' => 0,
                    'lastupdate' => TIMESTAMP
                ) );
            } elseif ( TIMESTAMP - $login['lastupdate'] > $errTime ) {
                FailedLogin::model()->deleteOld( $errTime + 1 );
                FailedLogin::model()->add( array(
                    'ip' => $ip,
                    'username' => $username,
                    'count' => 0,
                    'lastupdate' => TIMESTAMP
                ) );
            }
            if ( $return == 0 ) {
                $this->error( util\IBOS::lang( 'Login check error', '', array( '{minute}' => $account['errortime'] ) ) );
            }
        }
        return $return;
    }

}
