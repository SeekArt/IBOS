<?php

/**
 * 工作总结与计划模块------工作总结与计划后台设置控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------工作总结与计划控制器，继承DashboardBaseController
 * @package application.modules.report.components
 * @version $Id: ReportDashboardController.php 896 2013-07-27 07:48:15Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\dashboard\model\Stamp;
use application\modules\main\model\Setting;

class DashboardController extends BaseController {

    public function actionIndex() {
        //取出所有的配置信息
        $config = IBOS::app()->setting->get( 'setting/reportconfig' );
        $stampDetails = $config['stampdetails'];
        $stamps = array();
        if ( !empty( $stampDetails ) ) {
            $stampidArr = explode( ',', trim( $stampDetails ) );
            if ( count( $stampidArr ) > 0 ) {
                foreach ( $stampidArr as $stampidStr ) {
                    list($stampId, $score) = explode( ':', $stampidStr );
                    $stamps[$score] = intval( $stampId );
                }
            }
        }
        // 所有图章id
        $stampIds = Stamp::model()->fetchAllIds();
        // 没选中的图章id
        $diffStampIds = array_diff( $stampIds, $stamps );
        $this->render( 'index', array( 'config' => $config, 'stamps' => $stamps, 'diffStampIds' => $diffStampIds ) );
    }

    /**
     * 修改参数
     * @return void
     */
    public function actionUpdate() {
        if ( Env::submitCheck( 'formhash' ) ) {
            $fieldArr = array(
                'reporttypemanage' => '',
                'stampenable' => 0,
                'stampdetails' => '',
                'pointsystem' => 5,
                'autoreview' => 0,
                'autoreviewstamp' => 1
            );
            foreach ( $_POST as $key => $value ) {
                if ( in_array( $key, array_keys( $fieldArr ) ) ) {
                    $fieldArr[$key] = $value;
                }
            }
            //图章处理
            $stampStr = '';
            if ( !empty( $fieldArr['stampdetails'] ) ) {
                foreach ( $fieldArr['stampdetails'] as $score => $stampId ) {
                    $stampId = empty( $stampId ) ? 0 : $stampId;
                    $stampStr .= $stampId . ':' . $score . ',';
                }
            }
            $fieldArr['stampdetails'] = rtrim( $stampStr, ',' );
            // 如果自动评阅所选的项并没有图章，或者自动评阅的图章id不存在，取消他的自动评阅
            $apprise = Env::getRequest( 'apprise' );
            if ( empty( $_POST['stampdetails'][$apprise] ) ) {
                $fieldArr['autoreview'] = 0;
            } else { // 自动评阅的图章
                $fieldArr['autoreviewstamp'] = $_POST['stampdetails'][$apprise];
            }
            Setting::model()->modify( 'reportconfig', array( 'svalue' => serialize( $fieldArr ) ) );
            Cache::update( 'setting' );
            $this->success( IBOS::lang( 'Update succeed', 'message' ), $this->createUrl( 'dashboard/index' ) );
        }
    }

}
