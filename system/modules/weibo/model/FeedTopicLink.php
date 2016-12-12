<?php

namespace application\modules\weibo\model;

use application\core\model\Model;

class FeedTopicLink extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{feed_topic_link}}';
    }

}
