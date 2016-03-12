<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\modules\dashboard\model\Stamp;
use application\modules\dashboard\utils\Dashboard;

class SysstampController extends BaseController {

    public function actionIndex() {
        $formSubmit = Env::submitCheck( 'stampSubmit' );
        $stampPath = Stamp::STAMP_PATH;
        if ( $formSubmit ) {
            if ( isset( $_POST['stamps'] ) ) {
                // 更新图章
                foreach ( $_POST['stamps'] as $id => $stamp ) {
                    if ( File::fileExists( $stamp['stamp'] ) ) {
                        Stamp::model()->delImg( $id, 'stamp' );
                        $stamp['stamp'] = Dashboard::moveTempFile( $stamp['stamp'], $stampPath );
                    }
                    if ( File::fileExists( $stamp['icon'] ) ) {
                        Stamp::model()->delImg( $id, 'icon' );
                        $stamp['icon'] = Dashboard::moveTempFile( $stamp['icon'], $stampPath );
                    }
                    $stamp['code'] = \CHtml::encode( $stamp['code'] );
                    Stamp::model()->modify( $id, $stamp );
                }
            }
            // 新建图章与图标
            if ( isset( $_POST['newstamps'] ) ) {
                foreach ( $_POST['newstamps'] as $value ) {
                    if ( !empty( $value['stamp'] ) ) {
                        $value['stamp'] = Dashboard::moveTempFile( $value['stamp'], $stampPath );
                    }
                    if ( !empty( $value['icon'] ) ) {
                        $value['icon'] = Dashboard::moveTempFile( $value['icon'], $stampPath );
                    }
                    Stamp::model()->add( $value );
                }
            }
            // 删除
            if ( !empty( $_POST['removeId'] ) ) {
                $removeIds = explode( ',', trim( $_POST['removeId'], ',' ) );
                Stamp::model()->deleteByIds( $removeIds );
            }
            clearstatcache();
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            if ( Env::getRequest( 'op' ) === 'upload' ) {
                $fakeUrl = $this->imgUpload( 'stamp' );
                $realUrl = File::fileName( $fakeUrl );
                return $this->ajaxReturn( array( 'fakeUrl' => $fakeUrl, 'url' => $realUrl ) );
            }
            $data = array(
                'stampUrl' => $stampPath,
                'list' => Stamp::model()->fetchAll(),
                'maxSort' => Stamp::model()->getMaxSort()
            );
            $this->render( 'index', $data );
        }
    }

}
