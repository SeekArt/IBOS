<?php

/**
 * WxCenterController.class.file
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 微信企业号应用中心控制器
 * 
 * @package application.modules.dashboard.controllers
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @version $Id: WxCenterController.php 2052 2014-09-22 10:05:11Z gzhzh $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\WebSite;
use application\modules\main\model\Setting;
use application\modules\message\core\wx\WxApi;
use CJSON;

class WxController extends BaseController {

	protected $isBinding = false;
	protected $msg = '';
	protected $wxqyInfo = array();

	public function init() {
		parent::init();
		//通过aeskey向官网发起请求，查询是否有对应记录
		$this->chkBinding();
	}

	protected function unbindRender() {
		return $this->render( 'application.modules.dashboard.views.wx.unbindtip', array( 'msg' => $this->msg ) );
	}

	/**
	 * 通过aeskey向官网发起请求，查询是否有对应记录
	 */
	protected function chkBinding() {
		$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
		$url = 'Api/WxCorp/isBinding';
		$res = WebSite::getInstance()->fetch( $url, array( 'aeskey' => $aeskey ) );
		if ( !is_array( $res ) ) {
			$result = CJSON::decode( $res, true );
			switch ( $result['type'] ) {
				case 1 :
					Setting::model()->updateSettingValueByKey( 'corpid', $result['corpid'] );
					Setting::model()->updateSettingValueByKey( 'qrcode', urldecode( $result['qrcode'] ) );
					$this->isBinding = true;
					$this->wxqyInfo['name'] = $result['name'];
					$this->wxqyInfo['corpid'] = $result['corpid'];
					$this->wxqyInfo['logo'] = $result['logo'];
					$this->wxqyInfo['qrcode'] = $result['qrcode'];
					$this->wxqyInfo['mobile'] = $result['mobile'];
					$this->wxqyInfo['app'] = $result['app'];
					$this->wxqyInfo['uid'] = $result['uid'];
					break;
				case 2 :
					$this->isBinding = false;
					$this->msg = $result['msg'];
					break;
				case 3 :
					WxApi::getInstance()->resetCorp();
					$this->isBinding = false;
					$this->msg = $result['msg'];
					break;
			}
		}
	}

}
