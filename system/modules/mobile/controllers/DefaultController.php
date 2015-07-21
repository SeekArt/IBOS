<?php

/**
 * 移动端默认控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端模块默认控制器类
 * 
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: DefaultController.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\mobile\controllers;

use application\core\model\Log;
use application\core\utils\Cloud;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\String;
use application\core\utils\WebSite;
use application\modules\department\utils\Department as DeptUtils;
use application\modules\main\utils\Main;
use application\modules\main\model\Setting;
use application\modules\message\model\UserData;
use application\modules\mobile\utils\Mobile;
use application\modules\position\utils\Position as PositionUtils;
use application\modules\user\components\UserIdentity;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\utils\User as UserUtil;

class DefaultController extends BaseController {

	/**
	 * 登陆处理
	 * @return void 
	 */
	public function actionLogin() {
		if ( !IBOS::app()->user->isGuest ) {
			$return = array(
				'login' => true,
				'formhash' => FORMHASH,
				'uid' => IBOS::app()->user->uid,
				'user' => User::model()->fetchByUid( IBOS::app()->user->uid ),
				'APPID' => IBOS::app()->setting->get( 'setting/iboscloud/appid' )
			);
			if ( Env::getRequest( 'issetuser' ) != "true" ) {
				$userData = UserUtil::getUserByPy();
				$return['userData'] = $userData;
			}
			if ( Module::getIsEnabled( 'weibo' ) ) {
				$udata = UserData::model()->getUserData();
			}
			$return['user']['following_count'] = isset( $udata['following_count'] ) ? $udata['following_count'] : 0;
			$return['user']['follower_count'] = isset( $udata['follower_count'] ) ? $udata['follower_count'] : 0;
			$return['user']['weibo_count'] = isset( $udata['weibo_count'] ) ? $udata['weibo_count'] : 0;
			$return['departmentData'] = DeptUtils::getUserByPy();
			$return['positionData'] = PositionUtils::getUserByPy();
			$this->ajaxReturn( $return, Mobile::dataType() );
		}
		$account = IBOS::app()->setting->get( 'setting/account' );
		// 用户名
		$userName = Env::getRequest( 'username' );
		// 密码
		$passWord = Env::getRequest( 'password' );
		$gps = Env::getRequest( 'gps' );
		$address = Env::getRequest( 'address' );
		// 日志
		$ip = IBOS::app()->setting->get( 'clientip' );

		$cookieTime = 0;

		if ( !$passWord || $passWord != addslashes( $passWord ) ) {
			$this->ajaxReturn( array( 'login' => false, 'msg' => IBOS::lang( 'Passwd illegal', 'user.default' ) ), Mobile::dataType() );
		}
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
		$result = $identity->authenticate( false );

		if ( $result > 0 ) {
			$user = IBOS::app()->user;
			// 是否允许多个账户同时登录
			if ( $account['allowshare'] != 1 ) {
				$user->setStateKeyPrefix( IBOS::app()->setting->get( 'sid' ) );
			}
			// 设置会话过期时间
			Main::setCookie( 'autologin', 1, $cookieTime );
			$user->login( $identity, $cookieTime );
			$urlForward = Env::referer();
			$log = array(
				'terminal' => 'app',
				'password' => String::passwordMask( $passWord ),
				'ip' => $ip,
				'user' => $userName,
				'loginType' => "username",
				'address' => $address,
				'gps' => $gps
			);
			Log::write( $log, 'login', sprintf( 'module.user.%d', IBOS::app()->user->uid ) );

			$return = array(
				'login' => true,
				'formhash' => Env::formHash(),
				'uid' => IBOS::app()->user->uid,
				'user' => User::model()->fetchByUid( IBOS::app()->user->uid ),
				'APPID' => IBOS::app()->setting->get( 'setting/iboscloud/appid' )
			);

			if ( Module::getIsEnabled( 'weibo' ) ) {
				$udata = UserData::model()->getUserData();
			}
			$return['user']['following_count'] = isset( $udata['following_count'] ) ? $udata['following_count'] : 0;
			$return['user']['follower_count'] = isset( $udata['follower_count'] ) ? $udata['follower_count'] : 0;
			$return['user']['weibo_count'] = isset( $udata['weibo_count'] ) ? $udata['weibo_count'] : 0;

			if ( Env::getRequest( 'issetuser' ) != "true" ) {
				$userData = UserUtil::getUserByPy();
				$return['userData'] = $userData;
			}
			$return['departmentData'] = DeptUtils::getUserByPy();
			$return['positionData'] = PositionUtils::getUserByPy();

			$this->sendLoginNotify();
			$this->ajaxReturn( $return, Mobile::dataType() );
		} else {
			if ( $result === 0 ) {
				$this->ajaxReturn( array( 'login' => false, 'msg' => IBOS::lang( 'User not fount', 'user.default', array( '{username}' => $userName ) ) ), Mobile::dataType() );
			} else if ( $result === -1 ) {
				$this->ajaxReturn( array( 'login' => false, 'msg' => IBOS::lang( 'User lock', 'user.default', array( '{username}' => $userName ) ) ), Mobile::dataType() );
			} else if ( $result === -2 ) {
				$this->ajaxReturn( array( 'login' => false, 'msg' => IBOS::lang( 'User disabled', '', array( '{username}' => $userName ) ) ), Mobile::dataType() );
			} else if ( $result === -3 ) {
				$log = array(
					'user' => $userName,
					'password' => String::passwordMask( $passWord ),
					'ip' => $ip
				);
				Log::write( $log, 'illegal', 'module.user.login' );
				$this->ajaxReturn( array( 'login' => false, 'msg' => IBOS::lang( 'User name or password is not correct', 'user.default' ) ), Mobile::dataType() );
			}
		}
	}

	/**
	 * 登出操作
	 * @return void
	 */
	public function actionLogout() {
		IBOS::app()->user->logout();
		Main::setCookie( 'autologin', 0, 0 );
		$this->ajaxReturn( array( 'login' => false ), Mobile::dataType() );
	}

	/**
	 * 默认页,主要用来判断是否登录
	 * @return void
	 */
	public function actionIndex() {
		$access = parent::getAccess();
		if ( $access > 0 ) {
			$this->ajaxReturn( array( 'login' => true, 'formhash' => FORMHASH, 'uid' => IBOS::app()->user->uid, 'user' => user::model()->fetchByUid( IBOS::app()->user->uid ) ), Mobile::dataType() );
		} else {
			$this->ajaxReturn( array( 'login' => false, 'msg' => '登录已超时，请重新登录' ), Mobile::dataType() );
			exit();
		}
	}

	public function actionToken() {
		$devtoken = Env::getRequest( 'devtoken' );
		$platform = Env::getRequest( 'platform' );
		$uniqueid = Env::getRequest( 'uniqueid' );
//		app.CLOUDURL + "?s=/api/push/token&type=jsonp&callback=?&appid="+ app.APPID +"&token="+ app.TOKEN +"&uid=" + uid + "&devtoken=" + result + "&platform=ios&uniqueid=";
		$param = array(
			'uid' => IBOS::app()->user->uid,
			'devtoken' => $devtoken,
			'platform' => $platform,
			'uniqueid' => $uniqueid
		);

		$rs = Cloud::getInstance()->fetch( 'Api/Push/Token', $param, 'post' );
		if ( substr( $rs, 0, 5 ) !== 'error' ) {
			$this->ajaxReturn( array( 'isSucess' => true ), Mobile::dataType() );
		}
		$this->ajaxReturn( array( 'isSucess' => false ), Mobile::dataType() );
	}

	/**
	 * 发送登陆消息
	 */
	protected function sendLoginNotify() {
		$uid = IBOS::app()->user->uid;
		$app = 'wxqy';
		$bdVal = UserBinding::model()->fetchBindValue( $uid, $app );
		if ( !empty( $bdVal ) ) {
			$corpid = Setting::model()->fetchSettingValueByKey( 'corpid' );
			$msg = '您的账号在' . date( 'Y年m月d日 H:i:s', TIMESTAMP ) . '通过手机端登陆。登陆IP地址为：' . IBOS::app()->setting->get( 'clientip' );
			$param = array(
				'userIds' => array( $bdVal ),
				'appFlag' => 'helper',
				'var' => array(
					'message' => $msg,
				),
				'corpid' => $corpid,
			);
			$route = 'Api/WxPush/push';
			$res = WebSite::getInstance()->fetch( $route, json_encode( $param ), 'post' );
		}
	}

}
