<?php

use application\core\utils\Model;
use application\core\utils\Module;

$isInstallCalendar = Module::getIsEnabled('calendar');
if ($isInstallCalendar) {
    $sql = "DROP TABLE IF EXISTS {{calendar_rep_record}};
			CREATE TABLE IF NOT EXISTS {{calendar_rep_record}} (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`cid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '日程的id',
			`rid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '来自日志的计划id',
			`repid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '总结的id',
			PRIMARY KEY (`id`),
			KEY `cid` (`cid`) USING BTREE,
			KEY `rid` (`rid`) USING BTREE,
			KEY `repid` (`repid`) USING BTREE
		  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";
    Model::executeSqls($sql);
}