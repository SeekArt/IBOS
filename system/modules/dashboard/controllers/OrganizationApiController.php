<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\message\core\IMFactory;
use application\modules\message\utils\Message;

class OrganizationApiController extends OrganizationBaseController {

    public function filterRoutes( $routes ) {
        return true;
    }

    public function actionSyncUser() {
        $type = Env::getRequest( 'type' );
        $uid = String::filterStr( Env::getRequest( 'uid' ) );
        $flag = intval( Env::getRequest( 'flag' ) );
        $pwd = Env::getRequest( 'pwd' );
        if ( Message::getIsImOpen( $type ) ) {
            $im = IBOS::app()->setting->get( 'setting/im' );
            $imCfg = $im[$type];
            $className = 'application\modules\message\core\IM' . ucfirst( $type );
            $factory = new IMFactory();
            $properties = array(
                'uid' => explode( ',', $uid ),
                'syncFlag' => $flag
            );
            if ( $type == 'rtx' ) {
                $properties['pwd'] = $pwd;
            }
            $adapter = $factory->createAdapter( $className, $imCfg, $properties );
            return $adapter !== false ? $adapter->syncUser() : Env::iExit( '初始化IM组件失败' );
        } else {
            Env::iExit( '未开启IM绑定' );
        }
    }

}
