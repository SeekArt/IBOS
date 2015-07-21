<?php

namespace application\modules\user\model;

use application\core\model\Model;

class UserProfile extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{user_profile}}';
    }

    /**
     * 根据用户id查找一条用户数据
     * @param integer $uid
     * @return array
     */
    public function fetchByUid( $uid ) {
        static $users = array();
        if ( !isset( $users[$uid] ) ) {
            $user = $this->fetchByPk( $uid );
            $users[$uid] = $user;
        }
        return $users[$uid];
    }

}
