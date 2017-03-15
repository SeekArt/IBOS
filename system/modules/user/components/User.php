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
 * @version $Id$
 */

namespace application\modules\user\components;

use application\core\utils as util;
use application\core\utils\Ibos;
use application\modules\main\model as MainModel;
use application\modules\main\utils\Main as MainUtil;
use application\modules\role\model\Role;
use application\modules\user\model as UserModel;
use application\modules\user\utils\User as UserUtil;
use CWebUser;

/**
 * @todo 在 system\modules\main\behaviors\InitMainModule.php LINE 289 中，如果是已登录用户，会注入当前的用户信息到 User 组件中。
 *
 * @property integer uid 用户 uid
 * @property string username 用户名
 * @property integer isadministrator 是否管理员，1是、0否
 * @property integer deptid 部门 id
 * @property integer positionid 岗位 id
 * @property integer roleid 角色 id
 * @property integer upuid 直属领导 uid
 * @property integer groupid 用户组 id
 * @property string jobnumber 工号
 * @property string realname 真实姓名
 * @property string password 密码
 * @property integer gender 性别，0女1男
 * @property string weixin 微信号
 * @property string mobile 手机号码
 * @property string email 邮箱
 * @property integer status 用户状态
 * @property integer createtime 创建时间
 * @property integer credits 总积分
 * @property integer newcomer 是否新成员标识
 * @property string salt 用户身份验证码
 * @property integer validationemail 是否验证了邮件地址( (1为已验证0为未验证)
 * @property integer validationmobile 是否验证了手机号码 (1为已验证0为未验证)
 * @property integer lastchangepass 最后修改密码的时间
 * @property string guid 用户的唯一ID
 *
 * @todo 下面是 CWebUser 的属性
 * @property boolean $isGuest Whether the current application user is a guest.
 * @property mixed $id The unique identifier for the user. If null, it means the user is a guest.
 * @property string $name The user name. If the user is not logged in, this will be {@link guestName}.
 * @property string $returnUrl The URL that the user should be redirected to after login.
 * @property string $stateKeyPrefix A prefix for the name of the session variables storing user session data.
 * @property array $flashes Flash messages (key => message).
 *
 */
class User extends CWebUser
{

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
    public function init()
    {
        $account = util\Ibos::app()->setting->get('setting/account');
        $this->account = $account;
        $isAutologin = MainUtil::getCookie('autologin');
        if (!$isAutologin) {
            $this->authTimeout = (int)$account['timeout'] * 60;
        }
        parent::init();
    }

    /**
     * 重写login方法，设置session里的一些用户属性
     * 但是不写入cookie
     *
     * @param UserIdentity $identity
     * @param integer $duration
     * @return bool
     */
    public function login($identity, $duration = 0)
    {
        $this->identityCookie = array(
            'httpOnly' => true,
        );

        $isLogged = parent::login($identity, $duration);
        $user = UserModel\User::model()->fetchByUid($identity->uid);
        $names = array();
        if (is_array($user)) {
            $user['full'] = true;
            foreach ($user as $name => $value) {
                $this->setState($name, $value);
                $names[$name] = true;
            }
        }
        $this->setState(self::STATES_VAR, $names);
        return $isLogged;
    }

    /**
     * 覆盖父类登陆后调用方法，这里更新userstatus表里的最后访问属性
     * @param type $fromCookie 兼容属性
     * @return void
     */
    public function afterLogin($fromCookie)
    {
        $uid = $this->getId();
        MainUtil::setCookie('lastactivity', TIMESTAMP);
        // 更新用户登录状态
        UserModel\UserStatus::model()->updateByPk($uid, array(
                'lastip' => util\Env::getClientIp(),
                'lastvisit' => TIMESTAMP,
                'lastactivity' => TIMESTAMP,
                'invisible' => 1)
        );
        if (!$fromCookie) {
            util\Ibos::app()->session->isNew = true;
            util\Ibos::app()->session->updateSession();
        }
    }

    /**
     * 登出前处理操作，删除session数据库记录，更新在线状态。
     * @return boolean
     */
    public function beforeLogout()
    {
        $uid = $this->getId();
        MainModel\Session::model()->deleteAllByAttributes(array('uid' => $uid));
        UserModel\UserStatus::model()->updateByPk($uid, array('invisible' => 0));
        return true;
    }

    /**
     * 检查权限
     * @param string $operation 权限验证项目，一般为模块+控制器+动作 module/controller/action
     * @param array $params 验证规则的参数
     * @param boolean $allowCaching 是否缓存起来
     * @return boolean
     */
    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        // 管理员角色全部放行
        if ($this->isadministrator) {
            return true;
        }
        $purv = UserUtil::getUserPurv($this->uid);
        return isset($purv[$operation]);
    }

    /**
     * 返回权限列表，饼权限只检查是否有，不涉及具体
     * 如['a/a/a','b/b/b','c/c/c']
     * 返回['a/a/a'=>true,'b/b/b'=>false,'c/c/c'=>true]
     * @param array $operationArray 待检测的路由
     * @return array
     */
    public function returnAccess($operationArray)
    {
        $purv = UserUtil::getUserPurv($this->uid);
        $return = array();
        foreach ($operationArray as $operation) {
            $return[$operation] = isset($purv[$operation]);
        }
        return $return;
    }

    /**
     * 重写更新在线状态判定。先检查当前连接是否ajax操作，是则跳过
     */
    protected function updateAuthStatus()
    {
        if (!util\Ibos::app()->request->getIsAjaxRequest()) {
            // 多人同时登录同一账号的机制实现
            // TODO：管理员在后台关闭多人登录同一帐号功能时，会导致用户在前台显示登录成功，但实际上并没有成功。（session 被删除）
            parent::updateAuthStatus();
        }
    }

    /**
     * 是否需要重置密码
     * @return boolean
     */
    protected function getIsNeedReset()
    {
        $neededReset = false;
        if ($this->account['expiration'] != 0) {
            if (util\Ibos::app()->user->lastchangepass == 0) {
                $neededReset = true;
            } else {
                $time = TIMESTAMP - util\Ibos::app()->user->lastchangepass;
                switch ($this->account['expiration']) {
                    case '1': // month
                        if ($time / 86400 > 30) {
                            $neededReset = true;
                        }
                        break;
                    case '2':
                        if ($time / 86400 > 60) {
                            $neededReset = true;
                        }
                        break;
                    case '3':
                        if ($time / 86400 > 180) {
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

    public function getRoleType()
    {
        $uid = Ibos::app()->user->uid;
        $roleIds = UserModel\User::model()->findAllRoleidByUid($uid);
        $allroleidS = implode(',', array_unique($roleIds));
        $roleType = Ibos::app()->db->createCommand()
            ->select('roletype')
            ->from(Role::model()->tableName())
            ->where(sprintf(" FIND_IN_SET( `roleid`, '%s' ) AND `roletype` = '%s' ", $allroleidS, Role::ADMIN_TYPE))
            ->queryScalar();
        return $roleType;
    }

    public function getFull()
    {
        //默认是false
        return false;
    }

    /**
     * 默认设置user组件的param属性，用来临时存一些参数，保持参数到<del>比赛结束</del>退出登录
     * @return array
     */
    public function getParam()
    {
        return array();
    }
}
