<?php

use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Menu;
use application\modules\dashboard\model\Nav;
use application\modules\main\model\MenuCommon;
use application\modules\main\model\Setting;
use application\modules\message\model\Notify;
use application\modules\message\model\NotifyMessage;
use application\modules\role\model\AuthItem;
use application\modules\role\model\AuthItemChild;
use application\modules\role\model\Node;
use application\modules\role\model\NodeRelated;

// 卸载邮件模块
// step1:删除setting表键值
$settingFields = 'emailexternalmail,emailrecall,emailsystemremind,emailroleallocation,emaildefsize';
Setting::model()->deleteAll("FIND_IN_SET(skey,'{$settingFields}')");
Setting::model()->updateSettingValueByKey('emailtableids', 'a:2:{i:0;i:0;i:1;i:1;}');
Setting::model()->updateSettingValueByKey('emailtable_info', 'a:2:{i:0;a:1:{s:4:"memo";s:0:"";}i:1;a:2:{s:4:"memo";s:0:"";s:11:"displayname";s:12:"默认归档";}}');
// step2:删除冗余数据
Nav::model()->deleteAllByAttributes(array('module' => 'email'));
Menu::model()->deleteAllByAttributes(array('m' => 'email'));
MenuCommon::model()->deleteAllByAttributes(array('module' => 'email'));
Notify::model()->deleteAllByAttributes(array('node' => 'email_message'));
NotifyMessage::model()->deleteAllByAttributes(array('module' => 'email'));
Cache::set('notifyNode', null);
Node::model()->deleteAllByAttributes(array('module' => 'email'));
NodeRelated::model()->deleteAllByAttributes(array('module' => 'email'));
// step3:删除授权信息
AuthItem::model()->deleteAll("name LIKE 'email%'");
AuthItemChild::model()->deleteAll("child LIKE 'email%'");
// step4:删除所有相关表
$db = Ibos::app()->db->createCommand();
$prefix = $db->getConnection()->tablePrefix;
$tables = $db->setText("SHOW TABLES LIKE '" . str_replace('_', '\_', $prefix . 'email_%') . "'")
    ->queryAll(false);
foreach ($tables as $table) {
    $tableName = $table[0];
    !empty($tableName) && $db->dropTable($tableName);
}