<?php

/**
 * process表的数据层操作
 *
 * @version $Id$
 * @package application.modules.main.model
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\main\model;

use application\core\model\Model;

class Process extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{process}}';
    }

    public function deleteProcess($name, $time)
    {
        $name = \CHtml::encode($name);
        return $this->deleteAll("processid='{$name}' OR expiry<" . intval($time));
    }

}
