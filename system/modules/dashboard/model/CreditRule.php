<?php

/**
 * CreditRule表的数据层操作文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  CreditRule表的数据层操作
 * 
 * @package application.modules.dashboard.model
 * @version $Id: CreditRule.php 4064 2014-09-03 09:13:16Z zhangrong $
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class CreditRule extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{credit_rule}}';
    }

}
