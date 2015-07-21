<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\modules\dashboard\controllers\BaseController;
use application\modules\dashboard\model\LoginTemplate;
use application\modules\dashboard\utils\Dashboard;

class LoginController extends BaseController {

    public function actionIndex() {
        $formSubmit = Env::submitCheck( 'loginSubmit' );
        $bgPath = LoginTemplate::BG_PATH;
        if ( $formSubmit ) {
            if ( isset( $_POST['bgs'] ) ) {
                // 更新背景
                foreach ( $_POST['bgs'] as $id => $bg ) {
                    if ( File::fileExists( $bg['image'] ) ) {
                        LoginTemplate::model()->delImg( $id );
                        $bg['image'] = Dashboard::moveTempFile( $bg['image'], $bgPath );
                    }
                    $bg['disabled'] = isset( $bg['disabled'] ) ? 0 : 1;
                    LoginTemplate::model()->modify( $id, $bg );
                }
            }
            // 新建背景
            if ( isset( $_POST['newbgs'] ) ) {
                foreach ( $_POST['newbgs'] as $value ) {
                    if ( !empty( $value['image'] ) ) {
                        $value['image'] = Dashboard::moveTempFile( $value['image'], $bgPath );
                    }
                    LoginTemplate::model()->add( $value );
                }
            }
            // 删除
            if ( !empty( $_POST['removeId'] ) ) {
                $removeIds = explode( ',', trim( $_POST['removeId'], ',' ) );
                LoginTemplate::model()->deleteByIds( $removeIds, $bgPath );
            }
            clearstatcache();
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            if ( Env::getRequest( 'op' ) === 'upload' ) {
                $fakeUrl = $this->imgUpload( 'bg' );
                $realUrl = File::fileName( $fakeUrl );
                return $this->ajaxReturn( array( 'fakeUrl' => $fakeUrl, 'url' => $realUrl ) );
            }
            $data = array(
                'list' => LoginTemplate::model()->fetchAll(),
                'bgpath' => $bgPath
            );
            $this->render( 'index', $data );
        }
    }

}
