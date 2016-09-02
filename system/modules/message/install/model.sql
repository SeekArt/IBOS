DROP TABLE IF EXISTS `{{comment}}`;
CREATE TABLE `{{comment}}` (
  `cid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键，评论编号',
  `module` char(30) NOT NULL DEFAULT '' COMMENT '所属模块',
  `table` varchar(50) NOT NULL DEFAULT '' COMMENT '被评论的内容所存储的表',
  `rowid` int(11) NOT NULL DEFAULT '0' COMMENT '应用进行评论的内容的编号',
  `moduleuid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '模块内进行评论的内容的作者的UID',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '评论者UID',
  `content` text NOT NULL COMMENT '评论内容',
  `tocid` int(11) NOT NULL DEFAULT '0' COMMENT '被回复的评论的编号',
  `touid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '被回复的评论的作者的UID',
  `data` text NOT NULL COMMENT '所评论的内容的相关参数（序列化存储）',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '标记删除（0：没删除，1：已删除）',
  `from` tinyint(2) NOT NULL DEFAULT '0' COMMENT '客户端类型，0：网站；1：手机网页版；2：android；3：iphone',
  `commentcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '该评论回复数',
  `attachmentid` text NOT NULL COMMENT '附件id',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '连接地址',
  `detail` varchar(255) NOT NULL DEFAULT '' COMMENT '详细来源信息描述',
  PRIMARY KEY (`cid`),
  KEY `module` (`table`,`isdel`,`rowid`) USING BTREE,
  KEY `module2` (`uid`,`isdel`,`table`) USING BTREE,
  KEY `module3` (`uid`,`touid`,`isdel`,`table`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{notify_node}}`;
CREATE TABLE `{{notify_node}}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `node` varchar(50) NOT NULL COMMENT '节点名称',
  `nodeinfo` varchar(50) NOT NULL COMMENT '节点描述',
  `module` char(30) NOT NULL COMMENT '模块名称',
  `titlekey` varchar(50) NOT NULL COMMENT '标题key',
  `contentkey` varchar(50) NOT NULL COMMENT '内容key',
  `sendemail` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发送邮件',
  `sendmessage` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发送短消息',
  `sendsms` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送短信',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '信息类型：1 表示用户发送的 2表示是系统发送的',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{notify_message}}`;
CREATE TABLE `{{notify_message}}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `node` varchar(50) NOT NULL COMMENT '节点名称',
  `module` char(30) NOT NULL COMMENT '模块名称',
  `title` varchar(250) NOT NULL COMMENT '标题',
  `body` text NOT NULL COMMENT '内容',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `isread` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '链接地址',
  PRIMARY KEY (`id`),
  KEY `uid_read` (`uid`,`isread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{notify_sms}}`;
CREATE TABLE `{{notify_sms}}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `touid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `node` varchar(50) NOT NULL COMMENT '节点名称',
  `module` char(30) NOT NULL COMMENT '模块名称',
  `mobile` char(11) NOT NULL COMMENT '手机号码',
  `content` varchar(255) NOT NULL COMMENT '消息内容',
  `return` varchar(255) NOT NULL,
  `posturl` varchar(255) NOT NULL,
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

DROP TABLE IF EXISTS `{{notify_email}}`;
CREATE TABLE `{{notify_email}}` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户UiD',
  `node` varchar(50) NOT NULL COMMENT '节点名称',
  `module` char(30) NOT NULL COMMENT '模块名称',
  `email` varchar(250) NOT NULL COMMENT '邮件接受地址',
  `issend` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否已经发送',
  `title` varchar(250) NOT NULL COMMENT '邮件标题',
  `body` text NOT NULL COMMENT '邮件内容',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `sendtime` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{message_content}}`;
CREATE TABLE `{{message_content}}` (
  `messageid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信内对话ID',
  `listid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '私信ID',
  `fromuid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '会话发布者UID',
  `content` text COMMENT '会话内容',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除，0：否；1：是',
  `mtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会话发布时间',
  PRIMARY KEY (`messageid`),
  KEY `listid` (`listid`,`isdel`,`mtime`),
  KEY `listid2` (`listid`,`mtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{message_user}}`;
CREATE TABLE `{{message_user}}` (
  `listid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `new` smallint(8) NOT NULL DEFAULT '0' COMMENT '未读消息数',
  `messagenum` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '消息总数',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '该参与者最后会话时间',
  `listctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '私信最后会话时间',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除（假删）',
  PRIMARY KEY (`listid`,`uid`),
  KEY `new` (`new`),
  KEY `ctime` (`ctime`),
  KEY `listctime` (`listctime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

DROP TABLE IF EXISTS `{{message_list}}`;
CREATE TABLE `{{message_list}}` (
  `listid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信ID',
  `fromuid` mediumint(8) unsigned NOT NULL COMMENT '私信发起者UID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '私信类别，1：一对一；2：多人',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `usernum` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '参与者数量',
  `minmax` varchar(255) DEFAULT NULL COMMENT '参与者UID正序排列，以下划线“_”链接',
  `mtime` int(11) unsigned NOT NULL COMMENT '发起时间戳',
  `lastmessage` text NOT NULL COMMENT '最新的一条会话',
  PRIMARY KEY (`listid`),
  KEY `type` (`type`),
  KEY `min_max` (`minmax`),
  KEY `fromuid` (`fromuid`,`mtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{atme}}`;
CREATE TABLE `{{atme}}` (
  `atid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键，@我的编号',
  `module` char(30) NOT NULL COMMENT '所属模块',
  `table` char(15) NOT NULL COMMENT '存储内容的表名',
  `rowid` int(11) NOT NULL DEFAULT '0' COMMENT '模块内含有@的内容的编号',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '被@的用户编号',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '连接地址',
  `detail` varchar(255) NOT NULL DEFAULT '' COMMENT '详细来源信息描述',
  PRIMARY KEY (`atid`),
  KEY `module2` (`uid`,`table`),
  KEY `module3` (`table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

DROP TABLE IF EXISTS `{{feed}}`;
CREATE TABLE `{{feed}}` (
  `feedid` int(11) NOT NULL AUTO_INCREMENT COMMENT '动态ID',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '产生动态的用户UID',
  `type` char(50) DEFAULT NULL COMMENT 'feed类型.由发表feed的程序控制',
  `module` char(30) NOT NULL DEFAULT 'microblog' COMMENT 'feed来源的module',
  `table` varchar(50) NOT NULL DEFAULT 'feed' COMMENT '关联资源所在的表',
  `rowid` int(11) NOT NULL DEFAULT '0' COMMENT '关联的来源ID（如文章的id）',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '产生时间戳',
  `isdel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 默认为0',
  `from` tinyint(2) NOT NULL DEFAULT '0' COMMENT '客户端类型，0：网站；1：手机网页版；2：android；3：iphone',
  `commentcount` int(10) unsigned DEFAULT '0' COMMENT '评论数',
  `repostcount` int(10) DEFAULT '0' COMMENT '分享数',
  `commentallcount` int(10) DEFAULT '0' COMMENT '全部评论数目',
  `diggcount` int(11) unsigned DEFAULT '0' COMMENT '赞数',
  `isrepost` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否转发 0-否  1-是',
  `view` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '微博可见性 (0全公司可见 1仅自己可见 2我所在的部门可见 3自定义范围)',
  `userid` text NOT NULL COMMENT '可见用户ID',
  `deptid` text NOT NULL COMMENT '可见部门ID',
  `positionid` text NOT NULL COMMENT '可见岗位ID',
  PRIMARY KEY (`feedid`),
  KEY `isdel` (`isdel`,`ctime`),
  KEY `uid` (`uid`,`isdel`,`ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{feed_data}}`;
CREATE TABLE `{{feed_data}}` (
  `feedid` int(11) unsigned NOT NULL COMMENT '关联feed表，feedid',
  `feeddata` text COMMENT '关联feed表，动态数据，序列化保存',
  `clientip` char(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '客户端IP',
  `feedcontent` text COMMENT '纯微博内容',
  `fromdata` text COMMENT '微博来源',
  PRIMARY KEY (`feedid`),
  KEY `feedid` (`feedid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{{feed_digg}}`;
CREATE TABLE `{{feed_digg}}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `feedid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产生动态的ID',
  `ctime` int(11) DEFAULT NULL DEFAULT '0' COMMENT '赞的时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

DROP TABLE IF EXISTS `{{user_data}}`;
CREATE TABLE `{{user_data}}` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` MEDIUMINT(8) NOT NULL DEFAULT '0' COMMENT '用户UID',
  `key` varchar(50) NOT NULL COMMENT 'Key',
  `value` text COMMENT '对应Key的 值',
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '当前时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user-key` (`uid`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('message_digg', '微博的赞', 'message', 'message/default/Digg message title', 'message/default/Digg message content','1','1','1','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('user_follow', '新粉丝提醒', 'message', 'message/default/Follow message title', 'message/default/Follow message content','1','1','1','2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`,`sendemail`,`sendmessage`,`sendsms`,`type`) VALUES ('comment', '评论我的', 'message', 'message/default/Notify comment title', 'message/default/Notify comment content','0','0','0','1');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`,`extcredits1`, `extcredits2`) VALUES ('评论', 'addcomment', '3', '40','3','1');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`,`extcredits1`, `extcredits2`) VALUES ('被评论', 'getcomment', '3', '20', '2','1');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`,`extcredits1`, `extcredits2`) VALUES ('删除评论', 'delcomment', '3', '20', '-3','1');
INSERT INTO `{{cron}}` (`available`, `type`,`module`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ( '1', 'system','message', '更新企业QQ授权有效期', 'CronUpdateBQQToken.php', '1391184000', '1393603200', '1', '-1', '0', '0');