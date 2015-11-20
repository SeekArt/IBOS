DROP TABLE IF EXISTS `{{report}}`;
CREATE TABLE IF NOT EXISTS `{{report}}` (
  `repid` int(11) unsigned NOT NULL auto_increment COMMENT '总结id',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '汇报人',
  `begindate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '汇报区间开始时间',
  `enddate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '汇报区间结束时间',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '汇报时间',
  `typeid` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '汇报类型id',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '汇报标题',
  `content` text NOT NULL COMMENT '汇报内容',
  `attachmentid` text NOT NULL COMMENT '附件id',
  `toid` text NOT NULL COMMENT '汇报对象',
  `readeruid` text NOT NULL COMMENT '阅读人uid',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '汇报状态',
  `remark` text NOT NULL COMMENT '备注',
  `stamp` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '图章',
  `isreview` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已评阅',
  `lastcommenttime` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` text NOT NULL COMMENT '评阅内容',
  `commentline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评阅时间戳',
  `replyer` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '评阅人',
  `reminddate` int(10) NOT NULL DEFAULT '0',
  `commentcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  PRIMARY KEY  (`repid`),
  UNIQUE KEY `REP_ID` (`repid`) USING BTREE,
  KEY `USER_ID` (`uid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{report_record}}`;
CREATE TABLE IF NOT EXISTS `{{report_record}}` (
  `recordid` int(11) unsigned NOT NULL auto_increment COMMENT '记录id',
  `repid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '汇报id',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '记录内容',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '完成标记(0为未完成1为已完成)',
  `planflag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '计划标记(0为原计划,1为计划外,2为下次计划)',
  `process` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '完成进度',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间戳',
  `exedetail` varchar(255) NOT NULL DEFAULT '' COMMENT '执行情况',
  `begindate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始区间',
  `enddate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束区间',
  `reminddate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提醒日期，时间戳',
  PRIMARY KEY  (`recordid`),
  KEY `REP_ID` (`repid`) USING BTREE,
  KEY `USER_ID` (`uid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{report_type}}`;
CREATE TABLE IF NOT EXISTS `{{report_type}}` (
  `typeid` int(11) unsigned NOT NULL auto_increment COMMENT '汇报类型id',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型排序',
  `typename` varchar(255) NOT NULL DEFAULT '' COMMENT '类型名字',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `intervaltype` tinyint(1) unsigned NOT NULL COMMENT '区间(0:周 1:月 2:季 3:半年 4:年 5:其他)',
  `intervals` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '自定义的间隔日期',
  `issystype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是系统自带类型',
  PRIMARY KEY  (`typeid`),
  UNIQUE KEY `TYPE_ID` (`typeid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{report_statistics}}`;
CREATE TABLE IF NOT EXISTS {{report_statistics}} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `repid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总结ID',
  `typeid` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '汇报类型id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `stamp` smallint(3) unsigned NOT NULL DEFAULT '0'COMMENT '图章id',
  `integration` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `scoretime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `REPORT_ID` (`repid`) USING BTREE,
  KEY `USER_ID` (`uid`) USING BTREE,
  KEY `SCORE_TIME` (`scoretime`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('reportconfig', 'a:6:{s:16:"reporttypemanage";s:0:"";s:11:"stampenable";i:1;s:11:"pointsystem";i:5;s:12:"stampdetails";s:40:"0:10,0:9,0:8,0:7,0:6,4:5,5:4,2:3,1:2,8:1";s:10:"autoreview";i:1;s:15:"autoreviewstamp";i:1;}');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('3','总结计划','report/default/index','0','1','0','2','report');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('总结计划','0','report','dashboard','index','','10','0');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('report_message','工作总结与计划消息提醒','report','report/default/New message title','report/default/New message content','1','1','1','2');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`,`extcredits2`, `extcredits3`) VALUES ('发表工作总结与计划', 'addreport', '3', '2', '0', '2','1');
INSERT INTO `{{report_type}}`(`sort`, `typename`, `uid`, `intervaltype`, `intervals`, `issystype`) VALUES ('0','周总结与下周计划','0','0','0','1');
INSERT INTO `{{report_type}}`(`sort`, `typename`, `uid`, `intervaltype`, `intervals`, `issystype`) VALUES ('0','月总结与下月计划','0','1','0','1');
INSERT INTO `{{report_type}}`(`sort`, `typename`, `uid`, `intervaltype`, `intervals`, `issystype`) VALUES ('0','季总结与下季计划','0','2','0','1');
INSERT INTO `{{report_type}}`(`sort`, `typename`, `uid`, `intervaltype`, `intervals`, `issystype`) VALUES ('0','年总结与下年计划','0','4','0','1');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('report','总结','report/default/index','提供企业工作总结与计划','3','1');