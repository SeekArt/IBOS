DROP TABLE IF EXISTS `{{email}}`;
CREATE TABLE `{{email}}` (
  `emailid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `toid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '发送给谁',
  `isread` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已阅读 (0为未阅读，1为已阅读)',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除标记(0为未删除，1为已删除)',
  `fid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹id',
  `bodyid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '邮件主体id',
  `isreceipt` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '回执标识 (0未回执，1已回执，2不回执)',
  `ismark` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否标为待办 (0为不待办，1为标记待办)',
  `isweb` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否外部邮件 (0不是，1是)',
  PRIMARY KEY (`emailid`),
  UNIQUE KEY `emailid` (`emailid`) USING BTREE,
  KEY `bodyid` (`bodyid`) USING BTREE,
  KEY `toid` (`toid`) USING BTREE,
  KEY `fid` (`fid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{email_1}}`;
CREATE TABLE `{{email_1}}` (
  `emailid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `toid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '发送给谁',
  `isread` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已阅读 (0为未阅读，1为已阅读)',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除标记(0为未删除，1为已删除)',
  `fid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹id',
  `bodyid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '邮件主体id',
  `isreceipt` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '回执标识 (0未回执，1已回执，2不回执)',
  `ismark` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否标为待办 (0为不待办，1为标记待办)',
  `isweb` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否外部邮件 (0不是，1是)',
  PRIMARY KEY (`emailid`),
  UNIQUE KEY `emailid` (`emailid`) USING BTREE,
  KEY `bodyid` (`bodyid`) USING BTREE,
  KEY `toid` (`toid`) USING BTREE,
  KEY `fid` (`fid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{email_body}}`;
CREATE TABLE `{{email_body}}` (
  `bodyid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '邮件内容ID',
  `fromid` text NOT NULL COMMENT '发件人ID',
  `toids` text NOT NULL COMMENT '收件人',
  `copytoids` text NOT NULL COMMENT '抄送人',
  `secrettoids` text NOT NULL COMMENT '密送人',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '邮件内容',
  `sendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `attachmentid` text NOT NULL COMMENT '附件id',
  `issend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已发送(0为未发送，1为已发送)',
  `important` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '重要程度 (0:一般1:重要2:紧急)',
  `size` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '邮件大小，单位为字节',
  `fromwebmail` varchar(255) NOT NULL COMMENT '外部邮件的来源',
  `towebmail` text NOT NULL COMMENT '发送的外部邮箱，以分号;为分隔',
  `issenderdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送者删除(0为未删除1为已删除)',
  `isneedreceipt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要回执(0为不需要1为需要)',
  PRIMARY KEY (`bodyid`),
  UNIQUE KEY `bodyid` (`bodyid`),
  KEY `sendtime` (`sendtime`) USING BTREE,
  KEY `subject` (`subject`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{email_body_1}}`;
CREATE TABLE `{{email_body_1}}` (
  `bodyid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '邮件内容ID',
  `fromid` text NOT NULL COMMENT '发件人ID',
  `toids` text NOT NULL COMMENT '收件人',
  `copytoids` text NOT NULL COMMENT '抄送人',
  `secrettoids` text NOT NULL COMMENT '密送人',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '邮件内容',
  `sendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `attachmentid` text NOT NULL COMMENT '附件id',
  `issend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已发送(0为未发送，1为已发送)',
  `important` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '重要程度 (0:一般1:重要2:紧急)',
  `size` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '邮件大小，单位为字节',
  `fromwebmail` varchar(255) NOT NULL COMMENT '外部邮件的来源',
  `towebmail` text NOT NULL COMMENT '发送的外部邮箱，以分号;为分隔',
  `issenderdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送者删除(0为未删除1为已删除)',
  `isneedreceipt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要回执(0为不需要1为需要)',
  PRIMARY KEY (`bodyid`),
  UNIQUE KEY `bodyid` (`bodyid`),
  KEY `sendtime` (`sendtime`) USING BTREE,
  KEY `subject` (`subject`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{email_folder}}`;
CREATE TABLE `{{email_folder}}` (
  `fid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统自带；1为是；0为否',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `name` char(100) NOT NULL DEFAULT '' COMMENT '邮箱名',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `webid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '外部邮箱文件夹id',
  PRIMARY KEY (`fid`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

DROP TABLE IF EXISTS `{{email_web}}`;
CREATE TABLE `{{email_web}}` (
  `webid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '外部邮箱id',
  `address` varchar(255) NOT NULL COMMENT 'emai地址',
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '邮箱密码',
  `smtpserver` varchar(255) NOT NULL COMMENT '发信服务器地址',
  `smtpport` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'smtp服务器端口',
  `smtpssl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'smtp服务器是否使用ssl',
  `server` varchar(255) NOT NULL DEFAULT '0' COMMENT '服务器地址',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '服务器端口',
  `ssl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '服务器是否使用ssl链接',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户id',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '发信昵称',
  `lastrectime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后接收时间',
  `fid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹id',
  `isdefault` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认发信箱(0不是，1是)',
  PRIMARY KEY (`webid`),
  UNIQUE KEY `webid` (`webid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('emailexternalmail', '0');
INSERT INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('emailrecall', '0');
INSERT INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('emailsystemremind', '0');
INSERT INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('emailroleallocation', '0');
INSERT INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('emaildefsize', '50');
INSERT INTO `{{email_folder}}`(`fid`, `system`, `sort`, `name`, `uid`, `webid`) VALUES ('1','1','0','inbox','0','0');
INSERT INTO `{{email_folder}}`(`fid`, `system`, `sort`, `name`, `uid`, `webid`) VALUES ('2','1','0','draft','0','0');
INSERT INTO `{{email_folder}}`(`fid`, `system`, `sort`, `name`, `uid`, `webid`) VALUES ('3','1','0','send','0','0');
INSERT INTO `{{email_folder}}`(`fid`, `system`, `sort`, `name`, `uid`, `webid`) VALUES ('4','1','0','del','0','0');
INSERT INTO `{{nav}}`( `pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('0','邮件','email/list/index','0','1','0','2','email');

INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('邮件','0','email','dashboard','index','','2','0');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('email_message','邮件消息提醒','email','email/default/New message title','email/default/New message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('email_receive_message','邮件回执提醒','email','email/default/Already receive','','1','1','1','2');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('email','邮件','email/list/index','提供企业内外邮件沟通','1','1');