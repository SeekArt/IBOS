<?php

/**
 * user模块全局用户组件文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * user模块全局用户组件,提供用户初始化，登陆，退出等操作
 *
 * @package application.modules.user.components
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: User.php 6891 2016-04-19 03:32:11Z tanghang $
 */

namespace application\modules\user\components;

use application\core\utils as util;
use application\core\utils\IBOS;
use application\modules\main\model as MainModel;
use application\modules\main\utils\Main as MainUtil;
use application\modules\role\model\Role;
use application\modules\role\model\RoleRelated;
use application\modules\user\model as UserModel;
use application\modules\user\utils\User as UserUtil;
use CWebUser;

class User extends CWebUser {

    /**
     * 允许自动登录
     * @var boolean
     */
    public $allowAutoLogin = true;

    /**
     * 账户安全设置
     * @var array
     */
    protected $account = array();

    /**
     * 调用全局程序组件基类的初始化方法，取消了父类CWebUser的init方法中关于session的处理
     * 提供基本的cookie验证方法（如果可用），同时更新session
     * @return void
     */
    public function init() {
        $account = util\IBOS::app()->setting->get( 'setting/account' );
        $this->account = $account;
        $isAutologin = MainUtil::getCookie( 'autologin' );
        if ( !$isAutologin ) {
            $this->authTimeout = (int) $account['timeout'] * 60;
        }
        parent::init();
    }

    /**
     * 覆盖父类登陆后调用方法，这里更新userstatus表里的最后访问属性
     * @param type $fromCookie 兼容属性
     * @return void
     */
    public function afterLogin( $fromCookie ) {
        $uid = $this->getId();
        MainUtil::setCookie( 'lastactivity', TIMESTAMP );
        // 更新用户登录状态
        UserModel\UserStatus::model()->updateByPk( $uid, array(
            'lastip' => util\Env::getClientIp(),
            'lastvisit' => TIMESTAMP,
            'lastactivity' => TIMESTAMP,
            'invisible' => 1 )
        );
        if ( !$fromCookie ) {
            util\IBOS::app()->session->isNew = true;
            util\IBOS::app()->session->updateSession();
        }
    }

    /**
     * 登出前处理操作，删除session数据库记录，更新在线状态。
     * @return boolean
     */
    public function beforeLogout() {
        $uid = $this->getId();
        MainModel\Session::model()->deleteAllByAttributes( array( 'uid' => $uid ) );
        UserModel\UserStatus::model()->updateByPk( $uid, array( 'invisible' => 0 ) );
        return true;
    }

    /**
     * 检查权限
     * @param string $operation 权限验证项目，一般为模块+控制器+动作 module/controller/action
     * @param array $params 验证规则的参数
     * @param boolean $allowCaching 是否缓存起来
     * @return boolean
     */
    public function checkAccess( $operation, $params = array(), $allowCaching = true ) {
        // 管理员角色全部放行
        if ( $this->isadministrator ) {
            return true;
        }
        $purv = UserUtil::getUserPurv( $this->uid );
        return isset( $purv[$operation] );
    }

    /**
     * 重写更新在线状态判定。先检查当前连接是否ajax操作，是则跳过
     */
    protected function updateAuthStatus() {
        if ( !util\IBOS::app()->request->getIsAjaxRequest() ) {
            // 多人同时登录同一账号的机制实现
            if ( $this->account['allowshare'] != 1 && !$this->getIsGuest() ) {
                // 查找session表是否有相同用户数据
                $criteria = array(
                    'condition' => sprintf( "`uid` = %d", $this->uid ),
                );
                $session = MainModel\Session::model()->fetch( $criteria );
                // 如果有但不等于当前的sid,表明已经被重复登录，退出当前用户
                if ( $session && $session['sid'] != util\IBOS::app()->setting->get( 'sid' ) ) {
                    util\IBOS::app()->getRequest()->getCookies()->remove( $this->getStateKeyPrefix() );
                    util\IBOS::app()->getSession()->destroy();
                }
            }
            parent::updateAuthStatus();
        }
    }

    /**
     * 是否需要重置密码
     * @return boolean
     */
    protected function getIsNeedReset() {
        $neededReset = false;
        if ( $this->account['expiration'] != 0 ) {
            if ( util\IBOS::app()->user->lastchangepass == 0 ) {
                $neededReset = true;
            } else {
                $time = TIMESTAMP - util\IBOS::app()->user->lastchangepass;
                switch ( $this->account['expiration'] ) {
                    case '1': // month
                        if ( $time / 86400 > 30 ) {
                            $neededReset = true;
                        }
                        break;
                    case '2':
                        if ( $time / 86400 > 60 ) {
                            $neededReset = true;
                        }
                        break;
                    case '3':
                        if ( $time / 86400 > 180 ) {
                            $neededReset = true;
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $neededReset;
    }

}
