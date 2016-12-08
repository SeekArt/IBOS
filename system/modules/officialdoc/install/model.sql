DROP TABLE IF EXISTS {{doc}};
CREATE TABLE IF NOT EXISTS {{doc}} (
  `docid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `subject` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `author` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '作者',
  `approver` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '审批人',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `uptime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `clickcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `attachmentid` text NOT NULL COMMENT '附件ID',
  `docno` text NOT NULL COMMENT '通知号', 
  `commentstatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '评论状态，1为开启0为关闭',
  `catid` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '所属分类',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '文章状态，1为公开2为审核3为草稿',
  `deptid` text NOT NULL COMMENT '阅读范围部门',
  `positionid` text NOT NULL COMMENT '阅读范围职位',
  `roleid` text NOT NULL COMMENT '阅读范围角色',
  `uid` text NOT NULL COMMENT '阅读范围人员',
  `readers` text NOT NULL COMMENT '阅读人uid',
  `istop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶，1代表置顶，0为不置顶',
  `toptime` int(10) NOT NULL DEFAULT '0' COMMENT '置顶时间',
  `topendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '置顶过期时间',
  `ishighlight` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否高亮',
  `highlightstyle` char(50) NOT NULL DEFAULT '' COMMENT '高亮样式',
  `highlightendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '高亮过期时间',
  `rcid` smallint(5) NOT NULL DEFAULT '0' COMMENT '套红id',
  `ccdeptid` text NOT NULL COMMENT '抄送部门id',
  `ccpositionid` text NOT NULL COMMENT '抄送职位id',
  `ccroleid` text NOT NULL COMMENT '抄送角色id',
  `ccuid` text NOT NULL COMMENT '抄送uid',
  `version` smallint(5) NOT NULL DEFAULT '1' COMMENT '版本号',
  `commentcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  PRIMARY KEY (`docid`),
  KEY `SUBJECT` (`subject`) USING BTREE,
  KEY `PROVIDER` (`author`) USING BTREE,
  KEY `NEWS_TIME` (`addtime`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{doc_category}};
CREATE TABLE IF NOT EXISTS {{doc_category}} (
  `catid` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '父分类id',
  `name` char(20) NOT NULL COMMENT '文章分类名称',
  `sort` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `aid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '审批流程id',
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{doc_approval}};
CREATE TABLE IF NOT EXISTS {{doc_approval}} (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `docid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '通知id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '签收人id',
  `step` varchar(10) NOT NULL DEFAULT '' COMMENT '签收步骤(1,2,3,4,5对应approval表level1,level2,level3,level4,level5)',
  PRIMARY KEY (`id`),
  KEY `DOCID` (`docid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{doc_reader}};
CREATE TABLE IF NOT EXISTS {{doc_reader}} (
  `readerid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '读者表id',
  `docid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '阅读者UID',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `readername` varchar(30) NOT NULL,
  `issign` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已签收:1为已签收，0为未签收',
  `signtime` int(10) NOT NULL DEFAULT '0' COMMENT '签收时间',
  `frommobile` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否签收与手机端',
  PRIMARY KEY (`readerid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{doc_version}};
CREATE TABLE IF NOT EXISTS {{doc_version}} (
  `versionid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '//版本号id',
  `docid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `content` text NOT NULL COMMENT '内容',
  `author` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '作者uid',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `uptime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `clickcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `commentstatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '评论状态，1为开启0为关闭',
  `catid` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '所属分类',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '文章状态，1为公开2为审核3为草稿',
  `deptid` text NOT NULL COMMENT '阅读范围部门',
  `positionid` text NOT NULL COMMENT '阅读范围职位',
  `roleid` text NOT NULL COMMENT '阅读范围角色',
  `uid` text NOT NULL COMMENT '阅读范围人员',
  `readers` text NOT NULL COMMENT '阅读人uid',
  `rcid` smallint(5) NOT NULL DEFAULT '0' COMMENT '套红id',
  `ccdeptid` text NOT NULL COMMENT '抄送部门id',
  `ccpositionid` text NOT NULL COMMENT '抄送职位id',
  `ccroleid` text NOT NULL COMMENT '抄送角色id',
  `ccuid` text NOT NULL COMMENT '抄送uid',
  `version` smallint(5) NOT NULL DEFAULT '1' COMMENT '版本号',
  `commentcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  `editor` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '修改人uid',
  `reason` varchar(255) NOT NULL DEFAULT '' COMMENT '修改理由',
  PRIMARY KEY (`versionid`),
  KEY `NEWS_TIME` (`addtime`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{rc_type}};
CREATE TABLE IF NOT EXISTS {{rc_type}} (
  `rcid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(64) NOT NULL COMMENT '名称',
  `classname` varchar(255) NOT NULL COMMENT '标题和文号css样式',
  `content` text NOT NULL COMMENT '原生的内容',
  `escape_content` text NOT NULL COMMENT '转义后的内容',
  PRIMARY KEY (`rcid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{doc_back}};
CREATE TABLE IF NOT EXISTS {{doc_back}} (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `docid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '操作者UID',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退回时间',
  `reason` text NOT NULL COMMENT '退回理由',
  PRIMARY KEY (`id`),
  KEY `ARTICLEID` (`docid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

REPLACE INTO `{{setting}}` (`skey`,`svalue`) VALUES ('docconfig','a:2:{s:11:"docapprover";i:0;s:16:"doccommentenable";i:1;}');
REPLACE INTO `{{syscache}}`(`name`, `type`, `dateline`, `value`) VALUES ('officialdoccategory','1','0','');
INSERT INTO `{{doc_category}}`(`pid`, `name`, `sort`) VALUES ('0','默认分类','0');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('5','通知公告','officialdoc/officialdoc/index','0','1','0','2','officialdoc');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('通知公告','0','officialdoc','dashboard','index','','10','0');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('officialdoc_message','通知消息提醒','officialdoc','officialdoc/default/New message title','officialdoc/default/New message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('officialdoc_verify_message','信息中心通知审核提醒','officialdoc','officialdoc/default/New verify message title','officialdoc/default/New verify message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('officialdoc_sign_remind','通知签收提醒','officialdoc','officialdoc/default/Sign message title','officialdoc/default/Sign message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('official_back_message','通知中心审核退回提醒','officialdoc','officialdoc/default/New back title','officialdoc/default/New back content','1','1','1','2');
REPLACE INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`,`extcredits2`, `extcredits3`) VALUES ('发表通知', 'addofficialdoc', '3', '2', '0', '2','1');
INSERT INTO `{{rc_type}}` (`name`, `classname`, `content`, `escape_content`) VALUES 
('默认套红', '', '套红A模板xxx( xxx )xx号关于 xxx 的通知xxxx公司   xxxx年xx月xx日', '<div><div style=\"border-bottom:4px solid #e26f50;\"><h2 style=\"text-align:center;font:700 36px/1.8 &#39;fangsong&#39;,&#39;simsun&#39;;color:#e26f50;margin-top:40px;margin-bottom:15px;\">套红A模板</h2><div style=\"padding:15px 0;text-align:center;\"><em style=\"font-style:normal;font-size:20px;font-family:&#39;fangsong&#39;,&#39;simsun&#39;;letter-spacing:3px\">xxx( xxx )xx号</em></div></div><div style=\"border-top:1px solid #e26f50;margin-top:4px;\"><div><h1 style=\"text-align:center;font:700 24px/2 &#39;fangsong&#39;,&#39;simsun&#39;;color:#666\">关于 xxx 的通知</h1><div id=\"original-content\" style=\"min-height:400px;font:16px/2 &#39;fangsong&#39;,&#39;simsun&#39;;color:#666;\"></div><div style=\"border-top:1px dotted #ddd;margin:20px 0;\"></div><div style=\"padding:20px 0;text-align:right;color:#666;letter-spacing:3px;font:18px/2 &#39;fangsong&#39;,&#39;simsun&#39;\">xxxx公司 &nbsp; <br />xxxx年xx月xx日</div></div></div></div>');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('officialdoc','通知公告','officialdoc/officialdoc/index','提供企业通知信息发布，以及版本记录','6','1');
INSERT INTO `{{auth_item}}` (`name`, `type`, `description`, `bizrule`, `data`) VALUES ('officialdoc/officialdoc/move', '0', '', 'return UserUtil::checkDataPurv($purvId);', 's:0:\"\";');
INSERT INTO `{{auth_item_child}}` (`parent`, `child`) VALUES ('1', 'officialdoc/officialdoc/move');
UPDATE `{{node}}` SET `routes`='officialdoc/officialdoc/edit,officialdoc/officialdoc/move' WHERE (`node`='edit' AND `module`='officialdoc');

