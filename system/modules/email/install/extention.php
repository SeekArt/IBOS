<?php

use application\modules\dashboard\model\CreditRule;

$creditExists = CreditRule::model()->countByAttributes(array('action' => 'postmail'));
if (!$creditExists) {
    $data = array(
        'rulename' => '写邮件',
        'action' => 'postmail',
        'cycletype' => '3',
        'rewardnum' => '4',
        'extcredits1' => '0',
        'extcredits2' => '2',
        'extcredits3' => '1',
    );
    CreditRule::model()->add($data);
}