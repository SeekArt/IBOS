<?php

/**
 * contact模块常用联系人控制器
 * @package application.modules.contact.controllers
 * @version $Id: ContactConstantController.php 2434 2014-03-11 10:38:13Z gzhzh $
 */

namespace application\modules\contact\controllers;

use application\core\utils\Convert;
use application\core\utils\IBOS;
use application\modules\contact\model\Contact;
use application\modules\contact\utils\Contact as ContactUtil;
use application\modules\user\utils\User as UserUtil;

class ConstantController extends BaseController {

	/**
	 * 常用联系人列表
	 */
	public function actionIndex() {
		$uid = IBOS::app()->user->uid;
		$cuids = Contact::model()->fetchAllConstantByUid( $uid );
		$res = UserUtil::getUserByPy( $cuids );
		$group = ContactUtil::handleLetterGroup( $res );
		$userDatas = array();
		foreach ( $group as $users ) {
			$userDatas = array_merge( $userDatas, $users );
		}
		$params = array(
			'datas' => $group,
			'letters' => array_keys($group),
			'allLetters' => $this->allLetters,
			'uids' => implode( ',', Convert::getSubByKey( $userDatas, 'uid' ) ) // 符合要求的所有uid
		);
		$this->setPageTitle( IBOS::lang( 'Regular contact' ) );
		$this->setPageState( 'breadCrumbs', array(
			array( 'name' => IBOS::lang( 'Contact' ), 'url' => $this->createUrl( 'default/index' ) ),
			array( 'name' => IBOS::lang( 'Regular contact' ) )
		) );
		$this->render( 'index', $params );
	}

}
