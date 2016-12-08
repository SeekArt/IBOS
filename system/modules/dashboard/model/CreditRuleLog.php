<?php

/**
 * CreditRuleLog表的数据层操作文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  CreditRuleLog表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Ibos;

class CreditRuleLog extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{credit_rule_log}}';
    }

    /**
     * 统计增加操作
     * @param integer $clid
     * @param array $logArr
     * @return int
     */
    public function increase($clid, $logArr)
    {
        if ($clid && !empty($logArr) && is_array($logArr)) {
            $sqlText = 'UPDATE %s SET %s WHERE clid=%d';
            return Ibos::app()->db->createCommand()
                ->setText(sprintf($sqlText, $this->tableName(), implode(',', $logArr), $clid))
                ->execute();
        }
        return 0;
    }

    /**
     * 根据规则ID和用户ID查找一条积分日志记录
     * @param integer $rid 规则ID
     * @param integer $uid 用户ID
     * @return array
     */
    public function fetchRuleLog($rid, $uid)
    {
        $log = array();
        if ($rid && $uid) {
            $log = $this->fetchByAttributes(array('uid' => $uid, 'rid' => $rid));
        }
        return $log;
    }

}
