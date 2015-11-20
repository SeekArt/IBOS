<?php

/**
 * ReportStatsController class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * @package application.modules.report.controllers
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\report\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\statistics\utils\StatCommon;
use application\modules\user\utils\User;

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
        $uid = IBOS::app()->user->uid;
        $deptArr = User::getManagerDeptSubUserByUid( $uid );
        $sidebarAlias = 'application.modules.report.views.stats.sidebar';
        $params = array(
            'lang' => IBOS::getLangSource( 'report.default' ),
            'deptArr' => $deptArr,
            'dashboardConfig' => $this->getReportConfig(),
            'statModule' => IBOS::app()->setting->get( 'setting/statmodules' ),
        );
        $sidebarView = $this->renderPartial( $sidebarAlias, $params, false );
        return $sidebarView;
    }

    /**
     * 个人统计
     */
    public function actionPersonal() {
        $this->setPageTitle( IBOS::lang( 'Personal statistics' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Work report' ), 'url' => $this->createUrl( 'default/index' ) ),
            array( 'name' => IBOS::lang( 'Personal statistics' ) )
        ) );
        $this->render( 'stats', array_merge( array( 'type' => 'personal' ), $this->getData() ) );
    }

    /**
     * 评阅统计
     */
    public function actionReview() {
        $this->setPageTitle( IBOS::lang( 'Review statistics' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Work report' ), 'url' => $this->createUrl( 'default/index' ) ),
            array( 'name' => IBOS::lang( 'Review statistics' ) )
        ) );
        $this->render( 'stats', array_merge( array( 'type' => 'review' ), $this->getData() ) );
    }

    /**
     * 获取通用视图数据
     * @return array
     */
    protected function getData() {
        $typeid = Env::getRequest( 'typeid' );
        return array(
            'typeid' => empty( $typeid ) ? 1 : $typeid,
            'statAssetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'statistics' ),
            'widgets' => StatCommon::getWidget( 'report' )
        );
    }

}
