<?php

/**
 * main模块的Api控制器
 * 
 * @version $Id: ApiController.php 4438 2014-10-24 03:44:29Z gzpjh $
 * @package application.modules.main.controllers
 */

namespace application\modules\main\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\modules\main\utils\Main as MainUtil;

class ApiController extends Controller {

    /**
     * 初始化模块数据
     * @return void
     */
    public function actionLoadModule() {
        $moduleStr = Env::getRequest( 'module' );
        $moduleStr = urldecode( $moduleStr );
        $moduleArr = explode( ',', $moduleStr );
        $data = MainUtil::execLoadSetting( 'renderIndex', $moduleArr );
        $this->ajaxReturn( $data );
    }

    /**
     * 加载最新数据
     * @return void
     */
    public function actionLoadNew() {
        $moduleStr = Env::getRequest( 'module' );
        $moduleStr = urldecode( $moduleStr );
        $moduleArr = explode( ',', $moduleStr );
        $data = MainUtil::execLoadSetting( 'loadNew', $moduleArr );
		$res = array();
		foreach ($data as $widget => $count){
			$info = explode( '/', $widget );
			if ( count( $info ) == 2 ) {
				$module = $info[0];
				$res[$module] = $count;
			}
		}
        $this->ajaxReturn( $res );
    }

}
