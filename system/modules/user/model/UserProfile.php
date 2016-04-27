<?php

namespace application\modules\user\model;

use application\core\model\Model;
use application\core\utils\IBOS;

class UserProfile extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{user_profile}}';
    }

    public function findUserProfileIndexByUid( $uidX ) {
        $return = $userProfileArray = array();
        $userProfileArray = IBOS::app()->db->createCommand()
                ->select()
                ->from( $this->tableName() )
                ->where( User::model()->uid_find_in_set( $uidX ) )
                ->queryAll();
        if ( !empty( $userProfileArray ) ) {
            foreach ( $userProfileArray as $userProfile ) {
                $return[$userProfile['uid']] = $userProfile;
            }
        }
        return $return;
    }

}
