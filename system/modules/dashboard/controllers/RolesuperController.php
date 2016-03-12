<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\user\model\User as UserModel;
use application\modules\user\utils\User;

class RolesuperController extends BaseController {

    private $userA = array();

    public function init() {
        parent::init();
        $isadministrator = IBOS::app()->user->isadministrator;
        if ( empty( $isadministrator ) ) {
            $this->error( IBOS::lang( 'Valid access', 'error' ), '', $this->errorParam );
        }
        $this->userA = User::loadUser( '', array( 'isadministrator' => 1 ) );
    }

    /**
     * 浏览操作
     * @return void
     */
    public function actionIndex() {
        $param = array(
            'userA' => $this->userA,
            'count' => count( $this->userA ),
        );
        $this->render( 'index', $param );
    }

    /**
     * 角色编辑
     * @return void
     */
    public function actionEdit() {
        $u = Env::getRequest( 'uid' );
        $uidATemp = String::getUidAByUDPX( $u );
        $uidA = array_unique( $uidATemp );
        if ( count( $uidA ) > '3' ) {
            $this->error( IBOS::lang( 'superadmin cannot beyond 3' ) );
        }
        if ( count( $uidA ) == '0' ) {
            $this->error( IBOS::lang( 'superadmin must set at least one' ) );
        }
        $uid = IBOS::app()->user->uid;
        if ( !in_array( $uid, $uidA ) ) {
            $this->error( IBOS::lang( 'superadmin setting must contain yourself' ) );
        }
        $uidS = implode( ',', $uidA );
        $where = sprintf( " FIND_IN_SET( `uid`, '%s' ) ", $uidS );
        UserModel::model()->updateAll( array( 'isadministrator' => 0 ) );
        $counter = UserModel::model()->updateAll( array( 'isadministrator' => 1, ), $where );
        if ( !empty( $counter ) ) {
            Cache::update( 'users' );
        }
        $this->ajaxReturn( array(
            'isSuccess' => !empty( $counter ),
            'msg' => !empty( $counter ) ?
                    IBOS::lang( 'Edit success' ) :
                    IBOS::lang( 'Edit failed' ),
        ) );
    }

}
