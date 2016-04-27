DROP TABLE IF EXISTS `{{assignment}}`;
CREATE TABLE IF NOT EXISTS `{{assignment}}` (
	`assignmentid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '指派任务id',
	`subject` varchar(255) NOT NULL DEFAULT '' COMMENT '任务主题',
	`description` varchar(255) NOT NULL DEFAULT '' COMMENT '任务描述',
	`designeeuid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '指派人uid',
	`chargeuid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '负责人uid',
	`participantuid` text NOT NULL COMMENT '参与者uid,逗号隔开字符串',
	`attachmentid` text NOT NULL COMMENT '附件id',
	`addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发起时间',
	`updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
	`starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
	`endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
	`status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '任务状态(0:未读,1:进行中,2:已完成,3:已评价,4:取消)',
	`finishtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
	`stamp` tinyint(3) unsigned NOT NULL DEFAULT '0'COMMENT '图章',
	`commentcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
	PRIMARY KEY (`assignmentid`),
	KEY `SUBJECT` (`subject`) USING BTREE,
	KEY `DESIGNEEUID` (`designeeuid`) USING BTREE,
	KEY `CHARGEUID` (`chargeuid`) USING BTREE,
	KEY `FINISHTIME` (`finishtime`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{assignment_apply}}`;
CREATE TABLE IF NOT EXISTS `{{assignment_apply}}` (
	`applyid` mediumint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
	`assignmentid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '指派任务id',
	`uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '申请人uid',
	`isdelay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否任务延时申请',
	`delaystarttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请延时开始时间',
	`delayendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请延时结束时间',
	`delayreason` varchar(255) NOT NULL DEFAULT '' COMMENT '申请延时理由',
	`iscancel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否任务取消申请',
	`cancelreason` varchar(255) NOT NULL DEFAULT '' COMMENT '申请取消理由',
	PRIMARY KEY (`applyid`),
	KEY `ASSIGNMENTID` (`assignmentid`) USING BTREE,
	KEY `ISDELAY` (`isdelay`) USING BTREE,
	KEY `ISCANCEL` (`iscancel`) USING BTREE
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{assignment_remind}}`;
CREATE TABLE IF NOT EXISTS `{{assignment_remind}}` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '记录id',
  `assignmentid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '指派任务id',
  `calendarid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '日程的id',
  `remindtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提醒时间，时间戳',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '提醒人uid',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '提醒内容',
	`status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态。0 未提醒，1 已提醒',
  PRIMARY KEY  (`id`),
  KEY `A_ID` (`assignmentid`) USING BTREE,
  KEY `C_ID` (`calendarid`) USING BTREE,
  KEY `U_ID` (`uid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{assignment_log}}`;
CREATE TABLE IF NOT EXISTS `{{assignment_log}}` (
	`logid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
	`assignmentid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '指派任务id',
	`uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '操作人ID',
	`time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录日志时间',
	`ip` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人IP地址',
	`type` varchar(20) NOT NULL DEFAULT '' COMMENT '日志类型：(add-新建,del-删除,edit-修改,view-查看,push-推办任务,finish-完成任务,stamp-评价任务,restart-重启任务,delay-延期,applydelay-申请延期,agreedelay-同意延期,refusedelay-拒绝延期,cancel-取消,applycancel-申请取消,agreecancel-同意取消,refusecancel-拒绝取消)',
	`content` text NOT NULL COMMENT '日志信息',
	PRIMARY KEY (`logid`),
	KEY `ASSIGNMENTID` (`assignmentid`) USING BTREE
)ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_timing_message','任务提醒','assignment','assignment/default/Timing assign title','assignment/default/Timing assign content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_new_message','任务指派新任务提醒','assignment','assignment/default/New assign title','assignment/default/New assign content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_push_message','任务催办提醒','assignment','assignment/default/Push assign title','assignment/default/Push assign content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_finish_message','任务完成消息','assignment','assignment/default/Finish assign title','assignment/default/Finish assign content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_applydelay_message','任务延期申请消息','assignment','assignment/default/Applydelay assign title','assignment/default/Applydelay assign content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_applydelayresult_message','任务延期申请结果','assignment','assignment/default/Applydelayresult title','assignment/default/Applydelayresult content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_applycancel_message','任务取消申请消息','assignment','assignment/default/Applycancel assign title','assignment/default/Applycancel assign content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_applycancelresult_message','任务取消申请结果','assignment','assignment/default/Applycancelresult title','assignment/default/Applycancelresult content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('assignment_appraisal_message','任务评价消息','assignment','assignment/default/Appraisal assign title','assignment/default/Appraisal assign content','1','1','1','2');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('0','任务指派','assignment/unfinished/index','0','1','0','4','assignment');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('assignment','任务指派','assignment/unfinished/index','提供企业工作任务指派','10','0');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`,`extcredits2`, `extcredits3`) VALUES ('完成任务指派', 'finishassignment', '1', '0', '0', '1','1');