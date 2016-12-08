<?php

/**
 * 认证选项表数据层
 * @package application.modules.role.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\role\model;

use application\core\model\Model;
use application\core\utils\Ibos;

class AuthItem extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{auth_item}}';
    }

    public function checkIsInByRoute($route)
    {
        $condition = sprintf(" `name` = '%s' ", $route);
        $row = Ibos::app()->db->createCommand()
            ->select('name')
            ->from($this->tableName())
            ->where($condition)
            ->queryRow();
        return !empty($row);
    }

}
