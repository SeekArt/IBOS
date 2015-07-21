<?php

/**
 * RecruitStatsController class file.
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * @package application.modules.recruit.controllers
 * @version $Id$
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\recruit\controllers;

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\modules\statistics\utils\StatCommon;

class StatsController extends BaseController {

    /**
     * 初始化检查有无安装统计模块
     */
    public function init() {
        if ( !Module::getIsEnabled( 'statistics' ) ) {
            $this->error( IBOS::t( 'Module "{module}" is illegal.', 'error', array( '{module}' => IBOS::lang( 'Statistics' ) ) ), $this->createUrl( 'default/index' ) );
        }
    }

    /**
     * 取得侧栏视图
     * @return string
     */
    public function getSidebar() {
        $sidebarAlias = 'application.modules.recruit.views.resume.sidebar';
        $params = array(
            'lang' => IBOS::getLangSource( 'recruit.default' ),
            'statModule' => IBOS::app()->setting->get( 'setting/statmodules' ),
        );
        $sidebarView = $this->renderPartial( $sidebarAlias, $params, false );
        return $sidebarView;
    }

    /**
     * 招聘统计
     */
    public function actionIndex() {
        $this->setPageTitle( IBOS::lang( 'Recruit statistics' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Talent management' ), 'url' => $this->createUrl( 'resume/index' ) ),
            array( 'name' => IBOS::lang( 'Recruit statistics' ) )
        ) );
        $this->render( 'stats', array_merge( array( 'type' => 'personal' ), $this->getData() ) );
    }

    /**
     * 获取视图数据
     * @return array
     */
    protected function getData() {
        $type = Env::getRequest( 'type' );
        $timestr = Env::getRequest( 'time' );
        return array(
            'type' => $type,
            'timestr' => $timestr,
            'statAssetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'statistics' ),
            'widgets' => StatCommon::getWidget( 'recruit' )
        );
    }

}
