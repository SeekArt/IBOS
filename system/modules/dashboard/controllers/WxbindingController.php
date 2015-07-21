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

class WxbindingController extends WxController {

	/**
	 * 获取企业号绑定视图
	 */
	public function actionIndex() {
		// 是否已绑定微信企业号
		$this->render( 'index' );
	}

}
