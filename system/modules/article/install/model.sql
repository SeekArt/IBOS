DROP TABLE IF EXISTS {{article}};
CREATE TABLE IF NOT EXISTS {{article}} (
  `articleid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `subject` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '内容类型 0为文章 1为图片 2为链接',
  `author` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '作者',
  `approver` text NOT NULL  COMMENT '审批人',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `uptime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `clickcount` int(10) unsigned NOT NULL DEFAULT '0',
  `attachmentid` text NOT NULL COMMENT '附件ID',
  `commentstatus` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '评论状态，1为开启0为关闭',
  `votestatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '投票状态 1为开启0为关闭',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '超链接地址',
  `catid` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '所属分类',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '文章状态，1为公开2为审核3为草稿0为退回',
  `deptid` text NOT NULL COMMENT '阅读范围部门',
  `positionid` text NOT NULL COMMENT '阅读范围职位',
  `roleid` text NOT NULL COMMENT '阅读范围角色',
  `uid` text NOT NULL COMMENT '阅读范围人员',
  `istop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶，1代表置顶，0为不置顶',
  `toptime` int(10) NOT NULL DEFAULT '0' COMMENT '置顶时间',
  `topendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '置顶过期时间',
  `ishighlight` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否高亮',
  `highlightstyle` char(50) NOT NULL DEFAULT '' COMMENT '高亮样式',
  `highlightendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '高亮过期时间',
  `commentcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  PRIMARY KEY (`articleid`),
  KEY `SUBJECT` (`subject`) USING BTREE,
  KEY `PROVIDER` (`author`) USING BTREE,
  KEY `NEWS_TIME` (`addtime`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{article_category}};
CREATE TABLE IF NOT EXISTS {{article_category}} (
  `catid` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '父分类id',
  `name` char(20) NOT NULL COMMENT '文章分类名称',
  `sort` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `aid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '审批流程id',
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{article_approval}};
CREATE TABLE IF NOT EXISTS {{article_approval}} (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `articleid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '新闻id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '签收人id',
  `step` varchar(10) NOT NULL DEFAULT '' COMMENT '签收步骤(1,2,3,4,5对应approval表level1,level2,level3,level4,level5)',
  PRIMARY KEY (`id`),
  KEY `ARTICLEID` (`articleid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{article_picture}};
CREATE TABLE IF NOT EXISTS {{article_picture}} (
  `picid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '图片ID ',
  `articleid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '图片所属相册ID ',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '附件所属ID ',
  `sort` mediumint(3) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片上传时间戳',
  `postip` varchar(255) NOT NULL DEFAULT '' COMMENT '图片上传ip',
  `filename` varchar(255) NOT NULL DEFAULT '' COMMENT '图片文件名',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '图片标题',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '图片类型',
  `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片大小',
  `filepath` varchar(255) NOT NULL DEFAULT '' COMMENT '图片路径',
  `thumb` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有缩略图',
  `remote` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '图片状态 1-审核',
  PRIMARY KEY (`picid`),
  KEY `articleid` (`articleid`,`sort`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章图片表' ;

DROP TABLE IF EXISTS {{article_reader}};
CREATE TABLE IF NOT EXISTS {{article_reader}} (
  `readerid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '读者表id',
  `articleid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '阅读者UID',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `readername` varchar(30) NOT NULL,
  PRIMARY KEY (`readerid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{article_back}};
CREATE TABLE IF NOT EXISTS {{article_back}} (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `articleid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '操作者UID',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退回时间',
  `reason` text NOT NULL COMMENT '退回理由',
  PRIMARY KEY (`id`),
  KEY `ARTICLEID` (`articleid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('articleapprover', '0');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('articlecommentenable', '1');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('articlevoteenable', '1');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('articlemessageenable', '1');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('articlethumbenable', '1');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('articlethumbwh', '160,120');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('5','信息公告','article/default/index','0','1','0','1','article');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('信息中心','0','article','dashboard','index','','10','0');
INSERT INTO `{{article_category}}`(`pid`, `name`, `sort`) VALUES ('0','默认分类','0');
INSERT INTO `{{syscache}}`(`name`, `type`, `dateline`, `value`) VALUES ('articlecategory','1','0','');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('article_message','信息中心消息提醒','article','article/default/New message title','article/default/New message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('article_verify_message','信息中心新闻审核提醒','article','article/default/New verify message title','article/default/New verify message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('article_back_message','信息中心审核退回提醒','article','article/default/New back title','article/default/New back content','1','1','1','2');
REPLACE INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`,`extcredits2`, `extcredits3`) VALUES ('发表信息公告', 'addarticle', '3', '2', '0', '2','1');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('article','信息中心','article/default/index','提供企业新闻信息发布','5','1');
INSERT INTO `{{auth_item}}` (`name`, `type`, `description`, `bizrule`, `data`) VALUES ('article/default/move', '0', '', 'return UserUtil::checkDataPurv($purvId);', 's:0:\"\";');
INSERT INTO `{{auth_item_child}}` (`parent`, `child`) VALUES ('1', 'article/default/move');
UPDATE `{{node}}` SET  `routes`='article/default/edit,article/default/move' WHERE (`node`='edit' AND `module`='article');
