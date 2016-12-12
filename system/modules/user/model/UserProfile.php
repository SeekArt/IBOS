<?php

namespace application\modules\user\model;

use application\core\model\Model;
use application\core\utils\Ibos;

class UserProfile extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_profile}}';
    }

    public function findUserProfileIndexByUid($uidX = null)
    {
        if (null === $uidX) {
            $condition = 1;
        } else if (empty($uidX)) {
            return array();
        } else {
            $condition = User::model()->uid_find_in_set($uidX);
        }
        $return = array();
        $userProfileArray = Ibos::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where($condition)
            ->queryAll();
        if (!empty($userProfileArray)) {
            foreach ($userProfileArray as $userProfile) {
                $return[$userProfile['uid']] = $userProfile;
            }
        }
        return $return;
    }

    public function findUserInfoByUid($uidX = null)
    {
        if (null === $uidX) {
            $condition = 1;
        } else if (empty($uidX)) {
            return array();
        } else {
            $condition = User::model()->uid_find_in_set($uidX);
        }
        $userInfo = Ibos::app()->db->createCommand()
            ->select('*')
            ->from('{{user}} u')
            ->leftJoin('{{user_profile}} up', " `u`.`uid` = `up`.`uid` ")
            ->where($condition)
            ->queryAll();
        return $userInfo;
    }

}
