<?php

namespace application\modules\user\model;

use application\core\model\Model;

class FailedLogin extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{failedlogin}}';
    }

    /**
     * 
     * @param type $ip
     * @return type
     */
    public function fetchIp( $ip ) {
        $criteria = array(
            'condition' => sprintf( "ip='%s'", $ip )
        );
        return $this->fetch( $criteria );
    }

    /**
     * 
     * @param type $time
     */
    public function deleteOld( $time ) {
        $criteria = array(
            'condition' => sprintf( 'lastupdate<%d', TIMESTAMP - intval( $time ) )
        );
        return $this->deleteAll( $criteria );
    }

    /**
     * 
     * @param string $ip
     */
    public function updateFailed( $ip ) {
        return $this->getDbConnection()->createCommand()
                        ->setText( sprintf( "UPDATE %s SET count=count+1, lastupdate=%d WHERE ip='%s'", $this->tableName(), TIMESTAMP, $ip ) )
                        ->execute();
    }

}
