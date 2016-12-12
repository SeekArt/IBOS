<?php

/**
 * CreditRuleLogField表的数据层操作文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  CreditRuleLogField表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class CreditRuleLogField extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{credit_rule_log_field}}';
    }

}
