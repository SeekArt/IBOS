<?php

/**
 * 总结计划模块------calendar_rep_record表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 总结计划、日程关联表------calendar_rep_record表操作类，继承ICModel
 * @package application.modules.report.model
 * @version $Id: CalendarRepRecord.php 873 2013-07-25 00:46:15Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\model;

use application\core\model\Model;

class CalendarRepRecord extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{calendar_rep_record}}';
    }

}
