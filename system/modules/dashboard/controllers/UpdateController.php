<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\main\utils\Main;
use application\modules\main\utils\Update;

class UpdateController extends BaseController {

	public function actionIndex() {

		if ( IBOS::app()->getRequest()->getIsAjaxRequest() ) {
			if ( LOCAL ) {
				@set_time_limit( 0 );
			}
			$op = Env::getRequest( 'op' );
			if ( !in_array( $op, array( 'data', 'static', 'module' ) ) ) {
				return $this->ajaxReturn( array(
							'isSuccess' => false,
							'data' => array(),
							'msg' => '错误的op参数，确定你是正常操作？',
						) );
			}
			$offset = Env::getRequest( 'offset' );
			$update = Main::getCookie( IBOS::app()->user->uid . '_update_lock' );
			if ( $offset == '0' && empty( $update ) ) {
				Main::setCookie( IBOS::app()->user->uid . '_update_lock', 1 );
				Cache::update();
			}
			$method = $op . 's';
			return $this->ajaxReturn( Update::$method( $offset ) );
		} else {
			return $this->render( 'index' );
		}
	}

}
