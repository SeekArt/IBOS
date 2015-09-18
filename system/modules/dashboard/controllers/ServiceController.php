<?php

/**
 * 后台服务文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 后台服务控制器
 * 
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: ServiceController.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Api;
use application\core\utils\Cache;
use application\core\utils\Cloud;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\WebSite;
use application\modules\main\model\Setting;
use CJSON;

class ServiceController extends BaseController {

	const WEBSITE = 'http://www.ibos.com.cn';
	const CHECK_LOGIN_ROUTE = 'Api/Service/ChkIsLogin';
	const OPEN_ROUTE = 'Api/Service/Open';
	const LOGIN_ROUTE = 'Api/Service/Login';

	public function actionIndex() {
		$setting = IBOS::app()->setting->get( 'setting/iboscloud' );
		$data = array(
			'website' => self::WEBSITE
		);
		if ( empty( $setting['appid'] ) ) {
			$data['isOpen'] = !preg_match( '/Pro/is', VERSION );
			$data['loginInfo'] = $this->getLoginInfo();
			$data['authkey'] = IBOS::app()->setting->get( 'config/security/authkey' );
			$this->render( 'guide', $data );
		} else {
			$isOpen = Api::getInstance()->fetchResult( WebSite::SITE_URL . 'product/open', Cloud::getInstance()->getCloudAuthParam() );
			if ( !empty( $isOpen ) ) {
				Setting::model()->SetIbosCloudIsOpen( $isOpen );
			}
			$signature = Cloud::getInstance()->getCloudAuthParam( true );
			$this->redirect( self::WEBSITE . '/product/cloud?' . $signature );
		}
	}

	/**
	 * 登陆官网
	 */
	public function actionLogin() {
		$param = array(
			'username' => Env::getRequest( 'username' ),
			'password' => Env::getRequest( 'password' ),
			'authkey' => IBOS::app()->setting->get( 'config/security/authkey' )
		);
		$res = WebSite::getInstance()->fetch( self::LOGIN_ROUTE, $param );
		if ( !is_array( $res ) ) {
			$info = CJSON::decode( $res, true );
			$this->ajaxReturn( $info );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => '操作失败，请重试！' ) );
		}
	}

	/**
	 * 登陆并获得授权信息
	 */
	public function actionOpen() {
		$authkey = IBOS::app()->setting->get( 'config/security/authkey' );
		$res = WebSite::getInstance()->fetch( self::OPEN_ROUTE, array( 'authkey' => $authkey ), 'post' );
		if ( !is_array( $res ) ) {
			$result = CJSON::decode( $res, true );
			if ( isset( $result['appid'] ) && isset( $result['secret'] ) ) {
				$iboscloud = IBOS::app()->setting->get( 'setting/iboscloud' );
				$iboscloud['isopen'] = 1;
				$iboscloud['appid'] = $result['appid'];
				$iboscloud['secret'] = $result['secret'];
				Setting::model()->updateSettingValueByKey( 'iboscloud', serialize( $iboscloud ) );
				Cache::update( 'setting' );
				$this->ajaxReturn( array( 'isSuccess' => true ) );
			} else {
				$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => $result['msg'] ) );
			}
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => '操作失败，请重试！' ) );
		}
	}

	/**
	 * 获取官网登陆信息
	 * @return boolean
	 */
	public function getLoginInfo() {
		$authkey = IBOS::app()->setting->get( 'config/security/authkey' );
		$res = WebSite::getInstance()->fetch( self::CHECK_LOGIN_ROUTE, array( 'authkey' => $authkey ) );
		if ( !is_array( $res ) ) {
			return CJSON::decode( $res, true );
		}
		return array();
	}

}
