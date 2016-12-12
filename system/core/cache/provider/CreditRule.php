<?php

/**
 * 积分规则更新缓存文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
/**
 * 积分规则更新缓存类,处理积分规则存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: creditRule.php 2413 2014-02-15 02:41:36Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\core\utils\Convert;
use application\modules\dashboard\model\CreditRule as CRModel;
use application\modules\dashboard\model\Syscache;
use CBehavior;

class CreditRule extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleCreditRule'));
    }

    /**
     * 处理积分规则缓存
     * @param object $event
     * @return void
     */
    public function handleCreditRule($event)
    {
        $rules = array();
        $records = CRModel::model()->fetchAll();
        if (!empty($records)) {
            foreach ($records as $rule) {
                $rule['rulenameuni'] = urlencode(Convert::iIconv($rule['rulename'], CHARSET, 'UTF-8', true));
                $rules[$rule['action']] = $rule;
            }
        }
        Syscache::model()->modifyCache('creditrule', $rules);
    }

}
