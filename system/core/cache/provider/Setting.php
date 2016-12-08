<?php

/**
 * 系统设置更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 系统设置更新缓存类,提供后台所有设置的更新并存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: setting.php 2413 2014-02-15 02:41:36Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\core\utils\StringUtil;
use application\modules\dashboard\model\Credit;
use application\modules\dashboard\model\Syscache;
use application\modules\dashboard\utils\Dashboard;
use application\modules\main\model\Setting as SettingModel;
use CBehavior;

class Setting extends CBehavior
{

    private $_setting = array();

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleSetting'));
    }

    /**
     * 处理全局setting缓存
     * @param type $event
     */
    public function handleSetting($event)
    {
        $settings = SettingModel::model()->fetchAllSetting();
        $this->_setting = &$settings;
        // credits
        $this->handleCredits();
        // 积分公式转换
        $this->handleCreditsFormula();
        // verhash
        $this->_setting['verhash'] = StringUtil::random(3);
        Syscache::model()->modifyCache('setting', $settings);
    }

    /**
     * 过滤积分公式
     * @param string $value
     * @return string
     */
    private function handleCreditsFormula()
    {
        if (!Dashboard::checkFormulaCredits($this->_setting['creditsformula'])) {
            $this->_setting['creditsformula'] = '$user[\'extcredits1\']';
        } else {
            $this->_setting['creditsformula'] = preg_replace("/(extcredits[1-5])/", "\$user['\\1']", $this->_setting['creditsformula']);
        }
    }

    /**
     * 处理积分
     * @return array
     */
    private function handleCredits()
    {
        $criteria = array('condition' => '`enable` = 1', 'order' => '`cid` ASC', 'limit' => 5);
        $record = Credit::model()->fetchAll($criteria);
        if (!empty($record)) {
            $index = 1;
            foreach ($record as $credit) {
                $this->_setting['extcredits'][$index] = $credit;
                $this->_setting['creditremind'] && $this->_setting['creditnames'][] = str_replace("'", "\'", StringUtil::ihtmlSpecialChars($credit['cid'] . '|' . $credit['name']));
                $index++;
            }
        }
        $this->_setting['creditnames'] = $this->_setting['creditremind'] ? @implode(',', $this->_setting['creditnames']) : '';
    }

}
