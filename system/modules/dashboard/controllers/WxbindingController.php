<?php

/**
 * WxBindingController.class.file
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 微信企业号设置控制器
 * 
 * @package application.modules.dashboard.controllers
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @version $Id: WxBindingController.php 2052 2014-09-22 10:05:11Z gzhzh $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Ibos;
use application\core\utils\WebSite;
use CJSON;

class WxbindingController extends WxController {

	/**
	 * 获取企业号绑定视图
	 */
	public function actionIndex() {
		$unit = Ibos::app()->setting->get( 'setting/unit' );
		$aeskey = Ibos::app()->setting->get( 'setting/aeskey' );
		$params = array(
			'fullname' => $unit['fullname'],
			'shortname' => $unit['shortname'],
			'logo' => $unit['logourl'],
			'domain' => $unit['systemurl'],
			'aeskey' => $aeskey,
			'isBinding' => $this->isBinding,
		);
		$currentUid = Ibos::app()->user->uid;
		$loginWebUid = Ibos::app()->cache->get( $currentUid . 'loginWeb' );
		if ( !$loginWebUid ) {
			$view = 'login';
		} else {
			$view = 'index';
			$res = WebSite::getInstance()->fetch( 'Api/Api/checkAccess', array(
				'domain' => $unit['systemurl'],
				'aeskey' => $aeskey,
					), 'post' );
			if ( is_array( $res ) ) {
				$isSuccess = false;
				$msg = $res['error'];
			} else {
				$result = CJSON::decode( $res );
				$isSuccess = $result['isSuccess'];
				$msg = $result['msg'];
			}
			$params['access'] = $isSuccess;
			$params['msg'] = $msg;
			$params['url'] = $isSuccess ? WebSite::getInstance()->build( 'Wxapi/Api/toWx', array(
						'state' => base64_encode( json_encode( array(
							'domain' => $params['domain'],
							'uid' => $loginWebUid,
							'aeskey' => $params['aeskey'],
							'version' => strtolower( implode( ',', array( ENGINE, VERSION, VERSION_DATE ) ) )
						) ) )
					) ) : '';
			$params['url'] .= '&' . rand( 0, 999 );
		}
		if ( true === $this->isBinding ) {
			$params['wxlogo'] = $this->wxqyInfo['logo'];
			$params['wxcorpid'] = $this->wxqyInfo['corpid'];
			$params['wxname'] = $this->wxqyInfo['name'];
			$params['mobile'] = $this->wxqyInfo['mobile'];
			$params['app'] = $this->wxqyInfo['app'];
		}
		return $this->render( $view, $params );
	}

	public function actionLogin() {
		$request = Ibos::app()->getRequest();
		$mobile = $request->getPost( 'mobile' );
		$password = $request->getPost( 'password' );
		$res = WebSite::getInstance()->fetch( 'Api/Api/login', array(
			'mobile' => $mobile,
			'password' => $password,
				), 'post' );
		$ajaxReturn = $this->ajaxReturnArray( $res );
		if ( true === $ajaxReturn['isSuccess'] ) {
			$uid = $ajaxReturn['data']['uid'];
			$currentUid = Ibos::app()->user->uid;
			Ibos::app()->cache->set( $currentUid . 'loginWeb', $uid );
		}
		return $this->ajaxReturn( $ajaxReturn );
	}

	public function actionLogout() {
		$currentUid = Ibos::app()->user->uid;
		Ibos::app()->cache->set( $currentUid . 'loginWeb', null );
		return $this->redirect( $this->createUrl( 'wxbinding/index' ) );
	}

	public function actionRegister() {
		$request = Ibos::app()->getRequest();
		$mobile = $request->getPost( 'mobile' );
		$password = $request->getPost( 'password' );
		$username = $request->getPost( 'realname' );
		$res = WebSite::getInstance()->fetch( 'Api/Api/register', array(
			'mobile' => $mobile,
			'password' => $password,
			'username' => $username,
				), 'post' );
		$ajaxReturn = $this->ajaxReturnArray( $res );
		if ( true === $ajaxReturn['isSuccess'] ) {
			$uid = $ajaxReturn['data']['uid'];
			$currentUid = Ibos::app()->user->uid;
			Ibos::app()->cache->set( $currentUid . 'loginWeb', $uid );
		}
		return $this->ajaxReturn( $ajaxReturn );
	}

	public function actionSendCode() {
		$request = Ibos::app()->getRequest();
		$mobile = $request->getPost( 'mobile' );
		$res = WebSite::getInstance()->fetch( 'Api/Api/sendCode', array(
			'mobile' => $mobile,
				), 'post' );
		$ajaxReturn = $this->ajaxReturnArray( $res );
		return $this->ajaxReturn( $ajaxReturn );
	}

	public function actionCheckCode() {
		$request = Ibos::app()->getRequest();
		$mobile = $request->getPost( 'mobile' );
		$code = $request->getPost( 'code' );
		$res = WebSite::getInstance()->fetch( 'Api/Api/checkCode', array(
			'mobile' => $mobile,
			'code' => $code,
				), 'post' );
		$ajaxReturn = $this->ajaxReturnArray( $res );
		return $this->ajaxReturn( $ajaxReturn );
	}

	public function actionCheckMobile() {
		$request = Ibos::app()->getRequest();
		$mobile = $request->getPost( 'mobile' );
		$res = WebSite::getInstance()->fetch( 'Api/Api/checkMobile', array(
			'mobile' => $mobile,
				), 'post' );
		$ajaxReturn = $this->ajaxReturnArray( $res );
		return $this->ajaxReturn( $ajaxReturn );
	}

	private function ajaxReturnArray( $res ) {
		if ( is_array( $res ) ) {
			return array(
				'isSuccess' => false,
				'msg' => $res['error'],
			);
		} else {
			$result = CJSON::decode( $res );
			return array(
				'isSuccess' => $result['isSuccess'],
				'msg' => isset( $result['msg'] ) ? $result['msg'] : '',
				'data' => isset( $result['data'] ) ? $result['data'] : array(),
			);
		}
	}

}
