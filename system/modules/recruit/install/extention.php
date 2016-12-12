<?php

use application\core\utils\Cache;
use application\modules\dashboard\model\CreditRule;
use application\modules\recruit\model\ResumeStats;

defined('IN_MODULE_ACTION') or die('Access Denied');
Cache::update(array('setting', 'nav'));

$creditExists = CreditRule::model()->countByAttributes(array('action' => 'addresume'));
if (!$creditExists) {
    $data = array(
        'rulename' => '添加简历',
        'action' => 'addresume',
        'cycletype' => '3',
        'rewardnum' => '1',
        'extcredits1' => '0',
        'extcredits2' => '1',
        'extcredits3' => '1'
    );
    CreditRule::model()->add($data);
}
// 添加一条过去的空统计数据，用于招聘统计定时任务的参照物
ResumeStats::model()->add(array(
    'new' => 0,
    'pending' => 0,
    'interview' => 0,
    'employ' => 0,
    'eliminate' => 0,
    'datetime' => strtotime(date('Y-m-d')) - 86400
));