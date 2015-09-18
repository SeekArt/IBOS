<?php

/**
 * user模块用户登录验证文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * user模块用户登录验证类,根据登陆类型验证用户并返回验证状态
 * 
 * @package application.modules.user.components
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: UserIdentity.php 5083 2015-05-19 06:00:00Z tanghang $
 */

namespace application\modules\user\components;

use application\modules\user\model\User as UserModel;
use CUserIdentity;

class UserIdentity extends CUserIdentity {

	const USER_NOT_FOUND = 0; //无法找到用户
	const USER_LOCK = -1; // 用户已锁定
	const USER_DISABLED = -2; //用户已禁用
	const USER_PASSWORD_INCORRECT = -3; //密码不正确
	const USER_NO_ACCESS = -4; //管理员身份验证失败
	const LOGIN_BY_USERNAME = 1; // 账号类型：用户名
	const LOGIN_BY_EMAIL = 2; //账号类型：email
	const LOGIN_BY_MOBILE = 4; //账号类型：手机

	/**
	 * 账号登录类型
	 * @var integer 
	 */

	private $loginType;

	/**
	 * 用户id
	 * @var integer 
	 */
	private $uid = 0;

	/**
	 * 重写父类方法，增加登录类型loginType
	 * @param integer $loginType 登录类型
	 * @param string $username 用户账号，可以是用户名，email,手机,工号
	 * @param string $password 密码
	 * @return void
	 */
	public function __construct( $username, $password, $loginType = self::LOGIN_BY_MOBILE ) {
		$this->loginType = (int) $loginType;
		parent::__construct( $username, $password );
	}

	/**
	 * 重写父类方法，返回uid
	 * @return boolean
	 */
	public function getId() {
		return $this->uid;
	}

	public function setId( $uid ) {
		$this->uid = $uid;
	}

	/**
	 * 重写登录验证方法，该方法只返回验证状态码
	 * @return integer 验证状态码
	 */
	public function authenticate( $isAdministrator = false ) {
		$username = $this->username;
		$password = $this->password;
		switch ( $this->loginType ) {
			case self::LOGIN_BY_USERNAME:
				$user = UserModel::model()->fetch( '`username` = :username or `jobnumber` = :username', array( ':username' => $username ) );
				break;
			case self::LOGIN_BY_EMAIL:
				$user = UserModel::model()->fetch( '`email` = :email', array( ':email' => $username ) );
				break;
			case self::LOGIN_BY_MOBILE:
				$user = UserModel::model()->fetch( '`mobile` = :mobile', array( ':mobile' => $username ) );
				break;
			default:
				$user = array();
				break;
		}
		if ( empty( $user ) ) {
			$status = self::USER_NOT_FOUND;
		} else if ( $user['status'] == 1 ) {
			$status = self::USER_LOCK;
		} else if ( $user['status'] == 2 ) {
			$status = self::USER_DISABLED;
		} else {
			// MD5 加密
			$passwordMd5 = preg_match( '/^\w{32}$/', $password ) ? $password : md5( md5( $password ) . $user['salt'] );
			if ( $user['password'] != $passwordMd5 ) {
			$status = self::USER_PASSWORD_INCORRECT;
		} else if ( $isAdministrator && $user['isadministrator'] !== '1' ) {
			$status = self::USER_NO_ACCESS;
		} else {
			$status = $user['uid'];
			}
		}
		// 登录成功
		if ( $status > 0 ) {
			$cache = UserModel::model()->fetchByUid( $status );
			$user = array_merge( $user, $cache );
			$this->uid = $status;
			$this->persistentStates = $user;
		}
		return $status;
	}

}
