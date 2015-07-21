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
