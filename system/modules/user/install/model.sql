DROP TABLE IF EXISTS `{{onlinetime}}`;
CREATE TABLE `{{onlinetime}}` (
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `thismonth` smallint(6) unsigned NOT NULL default '0',
  `total` mediumint(8) unsigned NOT NULL default '0',
  `lastupdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{user}}`;
CREATE TABLE `{{user}}` (
  `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` char(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `isadministrator` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id标识: 0为非管理员，1为管理员',
  `deptid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '部门id',
  `positionid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '职位id',
  `roleid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '角色id',
  `upuid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '直属领导id ',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户组id',
  `jobnumber` char(20) NOT NULL DEFAULT '' COMMENT '工号',
  `realname` char(20) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `gender` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别\n(0女1男)',
  `weixin` varchar(100) NOT NULL DEFAULT '' COMMENT '微信号',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` char(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户状态，0正常、1锁定、2禁用',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `credits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总积分',
  `newcomer` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否新成员标识',
  `salt` char(10) NOT NULL DEFAULT '' COMMENT '用户身份验证码',
  `validationemail` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否验证了邮件地址( (1为已验证0为未验证)',
  `validationmobile` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否验证了手机号码 (1为已验证0为未验证)',
  `lastchangepass` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改密码的时间',
  `guid` char(36) NOT NULL DEFAULT '' COMMENT '用户的唯一ID',
  PRIMARY KEY (`uid`),
  KEY `groupid` (`groupid`) USING BTREE,
  KEY `mobile` (`mobile`),
  KEY `email` (`email`),
  KEY `jobnumber` (`jobnumber`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{user_count}}`;
CREATE TABLE `{{user_count}}` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户id',
  `extcredits1` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分1',
  `extcredits2` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分2',
  `extcredits3` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分3',
  `extcredits4` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分4',
  `extcredits5` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分5',
  `attachsize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总附件大小',
  `oltime` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '在线时间',
  `feeds` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '动态数',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{user_group}}`;
CREATE TABLE `{{user_group}}` (
  `gid` smallint(6) unsigned NOT NULL auto_increment COMMENT '用户组ID',
  `grade` tinyint(3) unsigned NOT NULL default '0' COMMENT '等级',
  `title` varchar(255) NOT NULL default '' COMMENT '组头衔',
  `system` tinyint(1) unsigned NOT NULL default '0' COMMENT '是否为系统自带：1为是；0为否',
  `creditshigher` int(10) NOT NULL default '0' COMMENT '该组的积分上限',
  `creditslower` int(10) NOT NULL default '0' COMMENT '该组的积分下限',
  PRIMARY KEY  (`gid`),
  KEY `creditsrange` USING BTREE (`creditshigher`,`creditslower`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{user_status}}`;
CREATE TABLE `{{user_status}}` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户UID',
  `regip` char(15) NOT NULL default '' COMMENT '注册IP',
  `lastip` char(15) NOT NULL default '' COMMENT '最后登录IP',
  `lastvisit` int(10) unsigned NOT NULL default '0' COMMENT '最后访问',
  `lastactivity` int(10) unsigned NOT NULL default '0' COMMENT '最后活动',
  `invisible` tinyint(1) NOT NULL default '0' COMMENT '是否隐身登录',
  PRIMARY KEY  (`uid`),
  KEY `lastactivity` USING BTREE (`lastactivity`,`invisible`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户状态表';
DROP TABLE IF EXISTS `{{user_profile}}`;
CREATE TABLE `{{user_profile}}` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户id',
  `birthday` int(11) unsigned NOT NULL DEFAULT '0',
  `telephone` varchar(255) NOT NULL DEFAULT '' COMMENT '住宅电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `qq` varchar(255) NOT NULL DEFAULT '' COMMENT 'QQ',
  `bio` varchar(255) NOT NULL DEFAULT '' COMMENT '自我介绍',
  `remindsetting` text COMMENT '提醒设置',
  `avatar_big` varchar(255) NOT NULL DEFAULT '' COMMENT '大头像',
  `avatar_middle` varchar(255) NOT NULL DEFAULT '' COMMENT '中头像',
  `avatar_small` varchar(255) NOT NULL DEFAULT '' COMMENT '小头像',
  `bg_big` varchar(255) NOT NULL DEFAULT '' COMMENT '大背景',
  `bg_middle` varchar(255) NOT NULL DEFAULT '' COMMENT '中背景',
  `bg_small` varchar(255) NOT NULL DEFAULT '' COMMENT '小背景',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户资料表';

DROP TABLE IF EXISTS `{{user_binding}}`;
CREATE TABLE `{{user_binding}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `bindvalue` text NOT NULL COMMENT '绑定的值',
  `app` char(30) NOT NULL COMMENT '绑定的类型',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidandapp` (`uid`,`app`),
  KEY `uid` (`uid`),
  KEY `app` (`app`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{failedlogin}}`;
CREATE TABLE `{{failedlogin}}` (
  `ip` char(15) NOT NULL DEFAULT '',
  `username` char(32) NOT NULL DEFAULT '',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{failedip}}`;
CREATE TABLE `{{failedip}}` (
  `ip` char(7) NOT NULL DEFAULT '',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`lastupdate`),
  KEY `lastupdate` (`lastupdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{bg_template}}`;
CREATE TABLE `{{bg_template}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `desc` varchar(50) NOT NULL DEFAULT '' COMMENT '背景图描述',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否选中',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统图片',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '背景图片地址',
  `image_path` varchar(100) NOT NULL DEFAULT '' COMMENT '背景图片地址相对路径',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{cache_user_detail}}`;
CREATE TABLE `{{cache_user_detail}}` (
  `uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'UID' ,
  `detail`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '详细信息' ,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户状态',
  `deadline`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '过期时间' ,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;


INSERT INTO `{{user_group}}` (`grade`, `title`, `system`, `creditshigher`, `creditslower`) VALUES ('1', '乞丐', '1', '-9999999', '0');
INSERT INTO `{{user_group}}` (`grade`, `title`, `system`, `creditshigher`, `creditslower`) VALUES ('2', '初入江湖', '1', '0', '50');
INSERT INTO `{{user_group}}` (`grade`, `title`, `system`, `creditshigher`, `creditslower`) VALUES ('3', '小有名气', '1', '50', '200');
INSERT INTO `{{user_group}}` (`grade`, `title`, `system`, `creditshigher`, `creditslower`) VALUES ('4', '江湖少侠', '1', '200', '500');
INSERT INTO `{{user_group}}` (`grade`, `title`, `system`, `creditshigher`, `creditslower`) VALUES ('5', '江湖大侠', '1', '500', '1000');
INSERT INTO `{{user_group}}` (`grade`, `title`, `system`, `creditshigher`, `creditslower`) VALUES ('6', '一派掌门', '1', '1000', '3000');
INSERT INTO `{{user_group}}` (`grade`, `title`, `system`, `creditshigher`, `creditslower`) VALUES ('7', '一代宗师', '1', '3000', '999999999');

INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `extcredits2`) VALUES ('每天登录', 'daylogin', '3', '2');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `extcredits1`, `extcredits2`, `extcredits3`) VALUES ('验证邮箱', 'verifyemail', '1', '10', '10', '2');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `extcredits1`, `extcredits2`, `extcredits3`) VALUES ('验证手机', 'verifymobile', '1', '10', '10', '2');
INSERT INTO `{{notify_node}}` (`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('user_group_upgrade', '用户组升级', 'user', 'user/default/User group upgrade title', 'user/default/User group upgrade content', '0', '1', '0', '2');
INSERT INTO `{{cron}}` (`available`, `type`,`module`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ( '1', 'system','user', '清空本月在线时间', 'CronOnlinetimeMonthly.php', '1391184000', '1393603200', '-1', '1', '0', '0');

INSERT INTO `{{bg_template}}` (`id`, `desc`, `status`, `system`, `image`) VALUES ('1', '默认背景', '0', '1', 'data/home/template1_bg_big.jpg');
INSERT INTO `{{bg_template}}` (`id`, `desc`, `status`, `system`, `image`) VALUES ('2', '大气磅礴', '0', '1', 'data/home/template2_bg_big.jpg');
INSERT INTO `{{bg_template}}` (`id`, `desc`, `status`, `system`, `image`) VALUES ('3', '青葱时光', '0', '1', 'data/home/template3_bg_big.jpg');