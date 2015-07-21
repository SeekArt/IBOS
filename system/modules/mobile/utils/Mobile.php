<?php

namespace application\modules\mobile\utils;

use application\core\utils\Env;

class Mobile {
	
	/**
	 * 返回类型
	 */
	public static function dataType(){
		$dataType = 'JSON';
		$callback = Env::getRequest( 'callback' );
		if ( isset( $callback ) ) {
			$dataType = 'JSONP';
		}
		return $dataType;
	}
}

