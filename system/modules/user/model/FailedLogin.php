<?php

namespace application\modules\user\model;

use application\core\model\Model;

class FailedLogin extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{failedlogin}}';
    }

    /**
     *
     * @param type $username
     * @return type
     */
    public function fetchUsername($username)
    {
        $criteria = array(
            'condition' => sprintf("username='%s'", $username)
        );
        return $this->fetch($criteria);
    }

    /**
     *
     * @param type $time
     */
    public function deleteOld($time)
    {
        $criteria = array(
            'condition' => sprintf('lastupdate<%d', TIMESTAMP - intval($time))
        );
        return $this->deleteAll($criteria);
    }

    /**
     *
     * @param string $username
     */
    public function updateFailed($username)
    {
        return $this->getDbConnection()->createCommand()
            ->setText(sprintf("UPDATE %s SET count=count+1, lastupdate=%d WHERE username='%s'", $this->tableName(), TIMESTAMP, $username))
            ->execute();
    }

}
