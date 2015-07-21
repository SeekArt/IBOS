<?php

/**
 * 重写AJAX判断
 *
 * @package application.core.components
 * @version $Id$
 * @author Aeolus <Aeolus@ibos.com.cn>
 */

namespace application\core\components;

use CHttpRequest;

class Request extends CHttpRequest {
	public function getIsAjaxRequest() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest')||isset($_SERVER['HTTP_ISCORS']);
	}
}
