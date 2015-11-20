<?php

namespace application\modules\diary\widgets;

use application\core\utils\IBOS;
use application\modules\diary\utils\Diary as DiaryUtil;
use CWidget;

class DiaryReviewSidebar extends CWidget {

	const VIEW = 'application.modules.diary.views.widget.reviewSidebar';

	/**
	 * 
	 * @return type
	 */
	public function run() {
		$data = array(
			'hasSub' => DiaryUtil::checkIsHasSub(),
			'statModule' => IBOS::app()->setting->get( 'setting/statmodules' ), 
			'config' => DiaryUtil::getSetting(),
			'id' => $this->getController()->getId(),
		);
		$this->render( self::VIEW, $data );
	}

}
