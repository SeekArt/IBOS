<?php

/**
 * 岗位职责表数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位职责表的数据层操作
 *
 * @package application.modules.position.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\position\model;

use application\core\model\Model;

class PositionResponsibility extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{position_responsibility}}';
    }

    /**
     * 查找所有岗位关联记录
     * @param integer $id 岗位id
     * @return array
     */
    public function fetchAllByPosId($id)
    {
        return $this->fetchAll('`positionid` = :id', array(':id' => $id));
    }

}
