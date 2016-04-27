<?php

namespace application\modules\assignment\utils;

use application\core\utils\IBOS;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
use application\modules\message\utils\MessageApi;

class AssignmentApi extends MessageApi {

	private $_indexTab = array( 'charge', 'designee' );

	/**
	 * 提供给接口的模块首页配置方法
	 * @return array
	 */
	public function loadSetting() {
		return array(
			'name' => 'assignment/assignment',
			'title' => '任务指派',
			'style' => 'in-assignment',
			'tab' => array(
				array(
					'name' => 'charge',
					'title' => '负责',
					'icon' => 'o-ol-am-user'
				),
				array(
					'name' => 'designee',
					'title' => '指派',
					'icon' => 'o-ol-am-appoint'
				)
			)
		);
	}

	/**
	 * 渲染首页视图
	 * @return array
	 */
	public function renderIndex() {
		$return = array();
		$viewAlias = 'application.modules.assignment.views.indexapi.assignment';
		$uid = IBOS::app()->user->uid;
		$chargeData = Assignment::model()->fetchUnfinishedByChargeuid( $uid ); // 我负责的
		$designeeData = Assignment::model()->fetchUnfinishedByDesigneeuid( $uid );  //我指派的
		$data = array(
			'chargeData' => AssignmentUtil::handleListData( $chargeData, $uid ),
			'designeeData' => AssignmentUtil::handleListData( $designeeData, $uid ),
			'lang' => IBOS::getLangSource( 'assignment.default' ),
			'assetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'assignment' )
		);
		foreach ( $this->_indexTab as $tab ) {
			$data['tab'] = $tab;
			$data[$tab] = IBOS::app()->getController()->renderPartial( $viewAlias, $data, true );
		}
		return $data;
	}

	/**
	 * 获取最新任务数
	 * @return integer
	 */
	public function loadNew() {
		$uid = IBOS::app()->user->uid;
		return Assignment::model()->getUnfinishCountByUid( $uid );
	}

}
