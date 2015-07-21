<?php

 namespace application\modules\recruit\widgets;

use application\core\utils\IBOS;

class StatRecruitSidebar extends StatRecruitBase {

	const VIEW = 'application.modules.recruit.views.widget.sidebar';

	/**
	 * 
	 * @return type
	 */
	public function run() {
		$data = array(
			'lang' => IBOS::getLangSource( 'recruit.default' )
		);
		$this->render( self::VIEW, $data );
	}

}
