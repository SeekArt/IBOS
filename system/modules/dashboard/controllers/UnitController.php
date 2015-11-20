<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS; 
use application\core\utils\String;
use application\modules\main\model\Setting;

class UnitController extends BaseController {

    /**
     * 单位管理
     * @return mixed 
     */
    public function actionIndex() {
        $unit = Setting::model()->fetchSettingValueByKey( 'unit' );
        $formSubmit = Env::submitCheck( 'unitSubmit' );
        // 是否提交
        if ( $formSubmit ) {
            $postData = array();
            if ( isset( $_FILES['logo'] ) && !empty( $_FILES['logo']['name'] ) ) {
                    if ( $_FILES['logo']['error'] != 0 ) {
                            die( '抱歉，设置失败，请您重试！' );
                    }
                    $ext = strtolower( pathinfo( strip_tags( $_FILES['logo']['name'] ), PATHINFO_EXTENSION ) );
                    if ( in_array( $ext, array( 'gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf' ) ) && !empty( $ext ) ) {
                            $imginfo = getimagesize( $_FILES['logo']['tmp_name'] );
                            if ( empty( $imginfo ) || ($ext == 'gif' && empty( $imginfo['bits'] )) ) {
                                    die( '非法图像文件！' );
                            }
                    }
                    else {
                            die( '不是有效的图片文件' );
                    }
            }//15-7-27 下午2:09 gzdzl
            if ( !empty( $_FILES['logo']['name'] ) ) {
                !empty( $unit['logourl'] ) && File::deleteFile( $unit['logourl'] );
                $postData['logourl'] = $this->imgUpload( 'logo' );
            } else {
                if ( !empty( $_POST['logourl'] ) ) {
                    $postData['logourl'] = $_POST['logourl'];
                } else {
                    $postData['logourl'] = '';
                }
            }
            $keys = array(
                'phone', 'fullname',
                'shortname', 'fax', 'zipcode',
                'address', 'adminemail', 'systemurl','corpcode'
            );
            foreach ( $keys as $key ) {
                if ( isset( $_POST[$key] ) ) {
                    $postData[$key] = String::filterCleanHtml( $_POST[$key] );
                } else {
                    $postData[$key] = '';
                }
            }
            Setting::model()->updateSettingValueByKey( 'unit', $postData );
            Cache::update( array( 'setting' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $license = Setting::model()->fetchSettingValueByKey( 'license' );
            $data = array( 'unit' => unserialize( $unit ), 'license' => $license );
            $this->render( 'index', $data );
        }
    }

}
