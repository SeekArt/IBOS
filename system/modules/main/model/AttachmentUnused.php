<?php

namespace application\modules\main\model;

use application\core\model\Model;

class AttachmentUnused extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{attachment_unused}}';
    }

}
