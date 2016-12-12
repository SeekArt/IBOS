DROP TABLE IF EXISTS `{{diary}}`;
CREATE TABLE IF NOT EXISTS `{{diary}}` (
  `diaryid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `diarytime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日志添加的当日时间',
  `nextdiarytime` int(10) NOT NULL DEFAULT '0' COMMENT '下一个日志添加时间',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `content` text NOT NULL COMMENT '日志内容',
  `attachmentid` text NOT NULL COMMENT '附件ID',
  `shareuid` text NOT NULL COMMENT '分享id',
  `readeruid` text NOT NULL COMMENT '阅读人员',
  `remark` text NOT NULL COMMENT '备注',
  `stamp` tinyint(3) unsigned NOT NULL DEFAULT '0'COMMENT '图章',
  `isreview` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已评阅',
  `attention` text NOT NULL COMMENT '谁关注了这篇日志',
  `commentcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  PRIMARY KEY (`diaryid`),
  UNIQUE KEY `ID` (`diaryid`) USING BTREE,
  KEY `USER_ID` (`uid`) USING BTREE,
  KEY `DIA_DATE` (`diarytime`) USING BTREE,
  KEY `DIA_TIME` (`addtime`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{diary_record}}`;
CREATE TABLE IF NOT EXISTS `{{diary_record}}` (
  `recordid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `diaryid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '日志ID',
  `content` char(255) NOT NULL DEFAULT '' COMMENT '记录内容',
  `uid` int(8) unsigned NOT NULL DEFAULT '0',
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '完成标记(0为未完成1为已完成',
  `planflag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '计划标记,1为原计划、0为计划外)',
  `schedule` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '进度',
  `plantime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划执行的时间',
  `timeremind` char(10) NOT NULL DEFAULT '' COMMENT '设置时间提醒',
  PRIMARY KEY (`recordid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{diary_share}}`;
CREATE TABLE IF NOT EXISTS {{diary_share}} (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `deftoid` text NOT NULL COMMENT '分享给谁',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{diary_attention}}`;
CREATE TABLE IF NOT EXISTS {{diary_attention}} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '登陆用户UID',
  `auid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '关注哪个用户的UID',
  PRIMARY KEY (`id`),
  KEY `USER_ID` (`uid`) USING BTREE,
  KEY `AT_USER_ID` (`auid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `{{diary_statistics}}`;
CREATE TABLE IF NOT EXISTS {{diary_statistics}} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `diaryid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '日志ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '登陆用户UID',
  `stamp` smallint(3) unsigned NOT NULL DEFAULT '0'COMMENT '图章id',
  `integration` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `scoretime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `DIARY_ID` (`diaryid`) USING BTREE,
  KEY `USER_ID` (`uid`) USING BTREE,
  KEY `SCORE_TIME` (`scoretime`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{diary_direct}}`;
CREATE TABLE IF NOT EXISTS {{diary_direct}} (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `direct` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否设置只看直属下属,1为是,0为否',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('diaryconfig', 'a:11:{s:7:"lockday";s:1:"0";s:14:"sharepersonnel";s:1:"1";s:12:"sharecomment";s:1:"1";s:9:"attention";s:1:"1";s:10:"autoreview";s:1:"1";s:15:"autoreviewstamp";s:1:"1";s:13:"remindcontent";s:0:"";s:11:"stampenable";s:1:"1";s:11:"pointsystem";s:1:"5";s:12:"stampdetails";s:40:"0:10,0:9,0:8,0:7,0:6,4:5,5:4,2:3,1:2,8:1";s:10:"reviewlock";i:0;}');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('3','工作日志','diary/default/index','0','1','0','2','diary');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('工作日志','0','diary','dashboard','index','','10','0');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('diary_message','工作日志消息提醒','diary','diary/default/New message title','diary/default/New message content','1','1','1','2');
REPLACE INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`,`extcredits2`, `extcredits3`) VALUES ('发表工作日志', 'adddiary', '3', '2', '0', '2','1');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('diary','日志','diary/default/index','提供企业工作日志发布','2','1');
