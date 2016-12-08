<?php

/**
 * Credit表的数据层操作文件
 *
 * @author Ring <Ring@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  Credit表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Ibos;

class Credit extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{credit}}';
    }

    /**
     * 更改表的自增值
     * @param type $value
     */
    public function alterAutoIncrementValue($value)
    {
        $sql = "ALTER table " . $this->tableName() . " auto_increment =" . $value;
        Ibos::app()->db->createCommand()->setText($sql)->execute();
    }


}
