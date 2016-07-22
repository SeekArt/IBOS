DROP TABLE IF EXISTS `{{cron}}`;
CREATE TABLE `{{cron}}` (
  `cronid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `available` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system') NOT NULL DEFAULT 'user',
  `module` varchar(30) NOT NULL DEFAULT '' COMMENT '所属模块',
  `name` char(50) NOT NULL DEFAULT '',
  `filename` char(50) NOT NULL DEFAULT '',
  `lastrun` int(10) unsigned NOT NULL DEFAULT '0',
  `nextrun` int(10) unsigned NOT NULL DEFAULT '0',
  `weekday` tinyint(1) NOT NULL DEFAULT '0',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `hour` tinyint(2) NOT NULL DEFAULT '0',
  `minute` char(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`cronid`),
  KEY `nextrun` (`available`,`nextrun`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{process}}`;
CREATE TABLE `{{process}}` (
  `processid` char(32) NOT NULL COMMENT '进程id',
  `expiry` int(10) DEFAULT NULL DEFAULT '0' COMMENT '过期时间',
  `extra` int(10) DEFAULT NULL DEFAULT '0' COMMENT '扩展字段',
  PRIMARY KEY (`processid`),
  KEY `expiry` (`expiry`) USING HASH
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{session}}`;
CREATE TABLE `{{session}}` (
  `sid` char(6) NOT NULL DEFAULT '',
  `ip1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` char(15) NOT NULL DEFAULT '',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `invisible` tinyint(1) NOT NULL DEFAULT '0',
  `action` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  `lastolupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`),
  UNIQUE KEY `sid` (`sid`),
  KEY `uid` (`uid`) USING HASH
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment}}`;
CREATE TABLE `{{attachment}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件id',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `tableid` tinyint(1) unsigned NOT NULL default '0' COMMENT '所属表id',
  `downloads` mediumint(8) unsigned NOT NULL default '0' COMMENT '下载次数',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` (`aid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_0}}`;
CREATE TABLE `{{attachment_0}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_1}}`;
CREATE TABLE `{{attachment_1}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_2}}`;
CREATE TABLE `{{attachment_2}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_3}}`;
CREATE TABLE `{{attachment_3}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_4}}`;
CREATE TABLE `{{attachment_4}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_5}}`;
CREATE TABLE `{{attachment_5}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_6}}`;
CREATE TABLE `{{attachment_6}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_7}}`;
CREATE TABLE `{{attachment_7}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_8}}`;
CREATE TABLE `{{attachment_8}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_9}}`;
CREATE TABLE `{{attachment_9}}` (
  `aid` mediumint(8) unsigned NOT NULL auto_increment COMMENT '附件ID',
  `uid` mediumint(8) unsigned NOT NULL default '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL,
  `filesize` int(10) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  `attachment` varchar(255) NOT NULL,
  `isimage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  UNIQUE KEY `aid` USING BTREE (`aid`),
  FULLTEXT KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_edit}}`;
CREATE TABLE `{{attachment_edit}}` (
 `aid` mediumint(8) unsigned NOT NULL default '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '当前编辑用户',
  `lastvisit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间戳'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{attachment_unused}}`;
CREATE TABLE `{{attachment_unused}}` (
 `aid` mediumint(8) unsigned NOT NULL auto_increment,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `dateline` int(10) unsigned NOT NULL default '0' COMMENT '时间戳',
  `filename` varchar(255) NOT NULL default '' COMMENT '文件名',
  `filesize` int(10) unsigned NOT NULL default '0',
  `attachment` varchar(255) NOT NULL default '' COMMENT '附件真实地址',
  `isimage` tinyint(1) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '' COMMENT '附件描述',
  PRIMARY KEY  (`aid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{setting}}`;
CREATE TABLE `{{setting}}` (
  `skey` varchar(255) NOT NULL DEFAULT '' COMMENT '键',
  `svalue` text NOT NULL COMMENT '值',
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{module}}`;
CREATE TABLE `{{module}}` (
  `module` varchar(30) NOT NULL COMMENT '模块',
  `name` varchar(20) NOT NULL COMMENT '模块名',
  `url` varchar(100) NOT NULL COMMENT '链接地址',
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否核心模块',
  `version` varchar(50) NOT NULL DEFAULT '' COMMENT '版本号',
  `icon` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '图标文件存在与否',
  `category` varchar(30) NOT NULL COMMENT '模块所属分类',
  `description` varchar(255) NOT NULL COMMENT '模块描述',
  `config` text NOT NULL COMMENT '模块配置，数组形式',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序 ',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已禁用',
  `installdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '安装日期',
  `updatedate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{regular}}`;
CREATE TABLE `{{regular}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '验证规则',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '类型索引',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '验证规则说明',
  `regex` text NOT NULL COMMENT '正则表达式',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` USING BTREE (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{syscache}}`;
CREATE TABLE `{{syscache}}` (
  `name` varchar(32) NOT NULL COMMENT '缓存类型名称',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型，1为数组，其余为0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间戳',
  `value` mediumblob NOT NULL COMMENT '值',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{module_guide}}`;
CREATE TABLE `{{module_guide}}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `route` varchar(32) NOT NULL DEFAULT '' COMMENT '引导的页面id',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '已经引导过的uid',
  PRIMARY KEY (`id`),
  KEY `route` (`route`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{menu_common}}`;
CREATE TABLE `{{menu_common}}` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `module` varchar(30) NOT NULL DEFAULT '' COMMENT '模块',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '模块名',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '链接地址',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '菜单显示描述',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `iscommon` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已设置为常用菜单',
  `iscustom` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是自定义的快捷导航',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
  `openway` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '打开连接方式:0为新窗口,1当前页打开',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'icon文件名,在./data/icon/目录下',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '首页通用菜单设置';

DROP TABLE IF EXISTS `{{menu_personal}}`;
CREATE TABLE `{{menu_personal}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '设置菜单的uid',
  `common` text NOT NULL COMMENT '常用菜单，按顺序逗号隔开的menu_common模块id名称',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '首页个人菜单设置';

INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('appclosed', '0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('unit', 'a:9:{s:7:"logourl";s:0:"";s:8:"fullname";s:9:"总公司";s:9:"shortname";s:9:"总公司";s:5:"phone";s:0:"";s:3:"fax";s:0:"";s:7:"zipcode";s:0:"";s:7:"address";s:0:"";s:9:"systemurl";s:0:"";s:10:"adminemail";s:0:"";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('creditremind', '1');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('creditsformula', 'extcredits2+extcredits1*2+extcredits3*3');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('creditsformulaexp', '<span data-type="entry" data-value="经验" class="entry disabled">经验</span><span data-type="operator" data-value="+" class="operator">+</span><span data-type="entry" data-value="金钱" class="entry disabled">金钱</span><span data-type="operator" data-value="*" class="operator">*</span><span data-type="number" data-value="2" class="number">2</span><span data-type="operator" data-value="+" class="operator">+</span><span data-type="entry" data-value="威望" class="entry disabled">威望</span><span data-type="operator" data-value="*" class="operator">*</span><span data-type="number" data-value="3" class="number">3</span>');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxhost', '');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxport', '');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxon', 'a:3:{s:5:"email";i:0;s:5:"diary";i:0;s:7:"article";i:0;}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxsubindex', 'a:3:{s:5:"email";s:0:"";s:5:"diary";s:0:"";s:7:"article";s:0:"";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxmsgindex', 'a:3:{s:5:"email";s:0:"";s:5:"diary";s:0:"";s:7:"article";s:0:"";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxmaxquerytime', 'a:3:{s:5:"email";s:0:"";s:5:"diary";s:0:"";s:7:"article";s:0:"";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxlimit', 'a:3:{s:5:"email";s:0:"";s:5:"diary";s:0:"";s:7:"article";s:0:"";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('sphinxrank', 'a:3:{s:5:"email";s:23:"SPH_RANK_PROXIMITY_BM25";s:5:"diary";s:23:"SPH_RANK_PROXIMITY_BM25";s:7:"article";s:23:"SPH_RANK_PROXIMITY_BM25";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('dateformat', 'Y-n-j');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('dateconvert', '1');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('timeoffset', '8');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('timeformat', 'H:i');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('attachdir', 'data/attachment');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('attachurl', 'data/attachment');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('watermarkstatus', '0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('thumbquality', '100');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('waterconfig', '{"watermarkminwidth":"120","watermarkminheight":"40","watermarktype":"text","watermarktrans":"50","watermarktext":{"text":"Welcome to use the IBOS!","size":"12","color":"0070c0","fontpath":"FetteSteinschrift.ttf"},"watermarkquality":"90","watermarkimg":"static/image/watermark_preview.jpg","watermarkposition":"9"}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('watermodule', '[]');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('attachsize', '20');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('filetype', 'chm, pdf, zip, rar, tar, gz, bzip2, gif, jpg, jpeg, png, txt, doc, xls, ppt, docx, xlsx, pptx');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('im', 'a:2:{s:3:"rtx";a:8:{s:4:"open";i:0;s:6:"server";s:9:"127.0.0.1";s:7:"appport";i:8006;s:7:"sdkport";i:6000;s:4:"push";a:2:{s:4:"note";i:0;s:3:"msg";i:0;}s:3:"sso";i:0;s:14:"reverselanding";i:0;s:8:"syncuser";i:0;}s:2:"qq";a:10:{s:4:"open";i:0;s:2:"id";s:0:"";s:5:"token";s:0:"";s:5:"appid";s:0:"";s:9:"appsecret";s:0:"";s:3:"sso";i:0;s:4:"push";a:2:{s:4:"note";i:0;s:3:"msg";i:0;}s:8:"syncuser";i:0;s:7:"syncorg";i:0;s:10:"showunread";i:0;}}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('custombackup', '');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('mail', 'a:5:{s:8:"mailsend";i:1;s:6:"server";a:0:{}s:13:"maildelimiter";i:2;s:12:"mailusername";i:1;s:14:"sendmailsilent";i:1;}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('account', 'a:9:{s:10:"expiration";i:0;s:9:"minlength";i:5;s:5:"mixed";i:0;s:10:"errorlimit";i:1;s:11:"errorrepeat";i:5;s:9:"errortime";i:15;s:9:"autologin";i:0;s:10:"allowshare";i:1;s:7:"timeout";i:30;}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('license', '');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('upgrade', '');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('verhash', '');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('smsenabled', '0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('smsinterface', '1');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('smssetup', 'a:2:{s:9:"accesskey";s:0:"";s:9:"secretkey";s:0:"";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('smsmodule', 'a:0:{}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('emailtable_info', 'a:2:{i:0;a:1:{s:4:"memo";s:0:"";}i:1;a:2:{s:4:"memo";s:0:"";s:11:"displayname";s:12:"默认归档";}}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('emailtableids', 'a:2:{i:0;i:0;i:1;i:1;}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('diarytable_info', 'a:2:{i:0;a:1:{s:4:"memo";s:0:"";}i:1;a:2:{s:4:"memo";s:0:"";s:11:"displayname";s:12:"默认归档";}}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('diarytableids', 'a:2:{i:0;i:0;i:1;i:1;}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('cronarchive', 'a:3:{s:11:"cronarchive";a:1:{s:5:"diary";a:3:{s:13:"sourcetableid";i:0;s:13:"targetabletid";s:1:"1";s:10:"conditions";a:2:{s:13:"sourcetableid";s:1:"0";s:9:"timerange";i:3;}}}s:5:"email";a:3:{s:13:"sourcetableid";i:0;s:13:"targettableid";s:1:"1";s:10:"conditions";a:2:{s:13:"sourcetableid";s:1:"0";s:9:"timerange";i:6;}}s:5:"diary";a:3:{s:13:"sourcetableid";i:0;s:13:"targettableid";s:1:"1";s:10:"conditions";a:2:{s:13:"sourcetableid";s:1:"0";s:9:"timerange";i:6;}}}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('logtableid', '0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('iboscloud', 'a:5:{s:5:"appid";s:0:"";s:6:"secret";s:0:"";s:6:"isopen";i:0;s:7:"apilist";a:0:{}s:3:"url";s:21:"http://cloud.ibos.cn/";}');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('websiteuid', '0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('skin', 'black');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('aeskey','0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('corpid','0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('qrcode','0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('cobinding','0');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('coinfo','');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('cacheuserstatus','1');
INSERT INTO `{{setting}}` (`skey`, `svalue`) VALUES ('cacheuserconfig','{"offset":"0","limit":"1000","uid":"1"}');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('notempty', '不能为空', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('chinese', '只能为中文', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('letter', '只能为英文', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('num', '只能为数字', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('idcard', '身份证', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('mobile', '手机号码', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('money', '金额', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('tel', '电话号码', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('zipcode', '邮政编码', '');
INSERT INTO `{{regular}}` (`type`, `desc`, `regex`) VALUES('email', 'Email', '');
