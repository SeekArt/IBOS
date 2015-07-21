<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\Org;

class UpdateController extends BaseController {

    public function actionIndex() {
        $types = Env::getRequest( 'updatetype' );
        $data = array();
        // 处理提交
        if ( Env::submitCheck( 'formhash' ) ) {
            $type = implode( ',', $types );
            if ( !empty( $type ) ) {
                $this->redirect( $this->createUrl( 'update/index', array( 'doupdate' => 1, 'updatetype' => $type ) ) );
            }
        }
        // 执行更新缓存操作
        if ( IBOS::app()->request->getIsAjaxRequest() ) {
            $op = Env::getRequest( 'op' );
            // 保险起见，设置执行时间为两分钟，更长一些
            if ( LOCAL ) {
                @set_time_limit( 0 );
            }
            if ( $op == 'data' ) {
                Cache::update();
            }
            if ( $op == 'static' ) {
                LOCAL && IBOS::app()->assetManager->republicAll();
                Org::update();
            }
            if ( $op == 'module' ) {
                Module::updateConfig();
            }
            // 清除缓存文件
            IBOS::app()->cache->clear();
            $this->ajaxReturn( array( 'isSuccess' => true ) );
        }
        // 处理提交上来的动作项
        if ( Env::getRequest( 'doupdate' ) == 1 ) {
            $type = explode( ',', trim( $types, ',' ) );
            $data['doUpdate'] = true;
            foreach ( $type as $index => $act ) {
                if ( !empty( $act ) ) {
                    // 数据缓存
                    if ( in_array( 'data', $type ) ) {
                        unset( $type[$index] );
                        $data['typedesc'] = IBOS::lang( 'Update' ) . IBOS::lang( 'Data cache' );
                        $data['op'] = 'data';
                        break;
                    }
                    // 静态文件重发布
                    if ( in_array( 'static', $type ) ) {
                        unset( $type[$index] );
                        $data['typedesc'] = IBOS::lang( 'Update' ) . IBOS::lang( 'Static cache' );
                        $data['op'] = 'static';
                        break;
                    }
                    // 更新模块配置文件
                    if ( in_array( 'module', $type ) ) {
                        $data['typedesc'] = IBOS::lang( 'Update' ) . IBOS::lang( 'Module setting' );
                        $data['op'] = 'module';
                        unset( $type[$index] );
                        break;
                    }
                }
            }
            $data['next'] = $this->createUrl( 'update/index', array( 'doupdate' => intval( !empty( $type ) ), 'updatetype' => implode( ',', $type ) ) );
        } else {
            $data['doUpdate'] = false;
        }
        $this->render( 'index', $data );
    }

}
