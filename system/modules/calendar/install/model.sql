DROP TABLE IF EXISTS {{calendars}};
CREATE TABLE IF NOT EXISTS {{calendars}} (
	`calendarid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '日程ID',
	`taskid` varchar(50) NOT NULL DEFAULT '' COMMENT '任务ID' ,
	`isfromdiary` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否来自日志的计划提醒',
	`subject` varchar(255) NOT NULL DEFAULT '' COMMENT '主题',
	`location` varchar(200) NOT NULL DEFAULT '' COMMENT '地点(暂无用)',
	`mastertime` char(10) NOT NULL DEFAULT '' COMMENT '未被实例之前的时间，格式（年月日）：2012-10-01',
	`masterid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '被实例周期性事务的ID',
	`description` varchar(255) NOT NULL DEFAULT '' COMMENT '详细(未用)',
	`calendartype` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '日程类型( 个人 , 部门 )',
	`starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
	`endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
	`isalldayevent` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否整天日程',
	`hasattachment` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '包含附件',
	`category` varchar(30) NOT NULL DEFAULT '-1' COMMENT '颜色分类',
	`instancetype` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '实例类型( 1循环主日程 , 2循环实例 , 异常 , 邀请 )',
	`recurringtype` varchar(255) NOT NULL DEFAULT '' COMMENT '循环类型(年、月、周)',
	`recurringtime` varchar(255) NOT NULL DEFAULT '' COMMENT '循环的具体时间',
	`status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '日程状态（未进行，完成，删除）',
	`recurringbegin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周期开始时间',
	`recurringend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周期结束时间',
	`attendees` varchar(255) NOT NULL DEFAULT '' COMMENT '参与人地址',
	`attendeenames` varchar(255) NOT NULL DEFAULT '' COMMENT '参与人姓名',
	`otherattendee` varchar(255) NOT NULL DEFAULT '' COMMENT '其它参与人',
	`upuid` varchar(50) NOT NULL DEFAULT '' COMMENT '添加人帐号(上司或自己)',
	`upname` varchar(50) NOT NULL DEFAULT '' COMMENT '更新人姓名',
	`uptime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
	`recurringrule` varchar(255) NOT NULL DEFAULT '' COMMENT '循环规则',
	`uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
	`lock` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否被锁定，锁定不能操作，只能看(0为未锁定，1为锁定)' ,
	PRIMARY KEY (`calendarid`),
	KEY `uid` (`uid`, `starttime`, `endtime`, `masterid`, `taskid` ) USING BTREE 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{tasks}};
CREATE TABLE IF NOT EXISTS {{tasks}} (
`id` varchar(50) NOT NULL DEFAULT '' COMMENT '任务ID，由前台传递' ,
`text` varchar(255) NOT NULL DEFAULT '' COMMENT '任务主题' ,
`pid` varchar(50) NOT NULL DEFAULT '' COMMENT '父级任务的ID' ,
`addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间' ,
`date` varchar(10) NOT NULL DEFAULT '' COMMENT '截止日期' ,
`complete` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '完成状态(0为未完成，1为完成)' ,
`allcomplete` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父、子级所有任务完成状态(0为未完成，1为完成)' ,
`completetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '完成时间',
`uid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任务所属的用户ID' ,
`upuid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上司的ID(添加人是上司时)' ,
`isupper` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否是上属添加的任务(0为自己添加的任务，1为上属添加的任务)' ,
`mark` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否标记',
`sort` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序',
PRIMARY KEY (`id`),
KEY `pid` (`pid`,`complete`,`uid`,`upuid`,`isupper`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {{calendar_setup}};
CREATE TABLE IF NOT EXISTS {{calendar_setup}} (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
	`uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户id' ,
	`mintime` char(10) NOT NULL DEFAULT '' COMMENT '日程开始时间（小时）',
	`maxtime` char(10) NOT NULL DEFAULT '' COMMENT '日程结束时间（小时）',
	`hiddendays` char(100) NOT NULL DEFAULT '' COMMENT '隐藏日期（星期几）',
	`viewsharing` varchar(50) NOT NULL DEFAULT '' COMMENT '阅读权限分享人员，（1,2,3...）',
	`editsharing` varchar(50) NOT NULL DEFAULT '' COMMENT '编辑权限分享人员，（1,2,3...）',
	PRIMARY KEY (`id`),
	KEY `uid` (`uid`) USING BTREE 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

REPLACE INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('calendaraddschedule', '1');
REPLACE INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('calendareditschedule', '0');
REPLACE INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('calendarworkingtime', '8,18');
REPLACE INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('calendaredittask', '0');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('add_calendar_message','日程添加提醒','calendar','calendar/default/Add schedule message title','calendar/default/Add schedule message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('calendar_message','日程消息提醒','calendar','calendar/default/New schedule message title','calendar/default/New schedule message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('task_message','任务消息提醒','calendar','calendar/default/New task message title','calendar/default/New task message content','1','1','1','2');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('日程安排','0','calendar','dashboard','index','','11','0');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('0','日程','calendar/schedule/index','0','1','0','3','calendar');
-- INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('3','待办','calendar/task/index','0','1','0','3','calendar');
INSERT INTO `{{cron}}` (`available`, `type`,`module`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ( '1', 'system','calendar', '日程提醒', 'CronCalendarRemind.php', '1393516800', '1393603200', '-1', '-1', '-1', '0');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('calendar','日程','calendar/schedule/index','提供企业工作日程安排','4','1');