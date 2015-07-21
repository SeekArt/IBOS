<?php

/**
 * contact模块公司通讯录控制器
 * @package application.modules.contact.controllers
 * @version $Id: ContactDefaultController.php 2434 2014-03-11 10:38:13Z gzhzh $
 */

namespace application\modules\contact\controllers;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\contact\model\Contact;

class DefaultController extends BaseController {

	/**
	 * 公司通讯录列表
	 */
	public function actionIndex() {
		$op = Env::getRequest( 'op' );
		$op = in_array( $op, array( 'dept', 'letter' ) ) ? $op : 'letter';
		$params = array();
		if ( $op == 'letter' ) { // 按拼音排列
			$params['datas'] = $this->getDataByLetter();
		} else {
			$params['datas'] = $this->getDataByDept();
		}
		$userDatas = array();
		if ( !empty( $params['datas'] ) ) {
			foreach ( $params['datas'] as $datas ) {
				$userDatas = $op == 'dept' ? array_merge( $userDatas, $datas['users'] ) : array_merge( $userDatas, $datas );
			}
		}
		$params['uids'] = implode( ',', Convert::getSubByKey( $userDatas, 'uid' ) ); // 符合要求的所有uid
		$uid = IBOS::app()->user->uid;
		$params['cuids'] = Contact::model()->fetchAllConstantByUid( $uid ); // 常联系人id数组
		$this->setPageTitle( IBOS::lang( 'Contact' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => IBOS::lang( 'Contact' ), 'url' => $this->createUrl( 'defalut/index' ) ),
			array( 'name' => IBOS::lang( 'Company contact' ) )
		) );
		$view = $op == 'letter' ? 'letter' : 'dept';
		$params['allLetters'] = $this->allLetters;
		$this->render( $view, $params );
	}

	/**
	 * 异步请求入口
	 */
	public function actionAjaxApi() {
		$this->ajaxApi();
	}

	/**
	 * 导出通讯录
	 */
	public function actionExport() {
		$this->export();
	}

	public function actionPrintContact() {
		$this->printContact();
	}

}
