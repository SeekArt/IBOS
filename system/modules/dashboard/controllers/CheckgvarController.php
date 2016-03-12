<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\IBOS;

class CheckgvarController extends BaseController {

    public function actionIndex() {
        if ( IBOS::app()->user->uid == '1' ) {
            $request = IBOS::app()->getRequest();
            $name = $request->getQuery( 'name' );
            $isSet = (bool) ($request->getQuery( 'set' ));
            if ( $isSet ) {
                $value = $request->getQuery( 'value' );
                IBOS::app()->setting->set( $name, $value );
            }
            $g = IBOS::app()->setting->get( $name );
            var_dump( $g );
        } else {
            var_dump( 'forbidden' );
        }
    }

}
