<?php

namespace application\modules\message\model;

use application\core\model\Model;

class FeedData extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{feed_data}}';
    }

}
