DROP TABLE IF EXISTS {{resume}};
CREATE TABLE IF NOT EXISTS {{resume}} (
  `resumeid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '简历id',
  `input` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '添加简历者uid',
  `positionid` smallint(6) NOT NULL DEFAULT '0' COMMENT '适合职位id',
  `entrytime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '录入时间',
  `uptime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `remark` char(255) NOT NULL DEFAULT '' COMMENT '备注',
  `remarktime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '备注时间',
  `flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '标记',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '简历状态(1:面试中 2:录取3:入职4:待安排5：淘汰)',
  `statustime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '改变状态的日期时间戳，取0点',
  PRIMARY KEY (`resumeid`),
  UNIQUE KEY `ID` (`resumeid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `flag` (`flag`) USING BTREE,
  KEY `entrytime` (`entrytime`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{resume_bgchecks}};
CREATE TABLE IF NOT EXISTS {{resume_bgchecks}} (
  `checkid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `resumeid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '简历id',
  `company` varchar(255) NOT NULL DEFAULT '' COMMENT '公司名称',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '公司地址',
  `phone` varchar(255) NOT NULL DEFAULT '' COMMENT '电话',
  `fax` varchar(255) NOT NULL DEFAULT '' COMMENT '传真',
  `contact` varchar(255) NOT NULL DEFAULT '' COMMENT '联系人',
  `position` varchar(255) NOT NULL DEFAULT '' COMMENT '职务',
  `entrytime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '入职时间',
  `quittime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '离职时间',
  `detail` text NOT NULL COMMENT '详细内容',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  PRIMARY KEY (`checkid`),
  UNIQUE KEY `checkid` (`checkid`) USING BTREE,
  KEY `resumeid` (`resumeid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


DROP TABLE IF EXISTS {{resume_contact}};
CREATE TABLE IF NOT EXISTS {{resume_contact}} (
  `contactid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '联系id',
  `resumeid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '简历id',
  `input` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '录入者',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '录入时间',
  `contact` varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式',
  `purpose` varchar(255) NOT NULL DEFAULT '' COMMENT '联系目的',
  `detail` text NOT NULL COMMENT '沟通内容',
  PRIMARY KEY (`contactid`),
  UNIQUE KEY `contactid` (`contactid`) USING BTREE,
  KEY `resumeid` (`resumeid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


DROP TABLE IF EXISTS {{resume_detail}};
CREATE TABLE IF NOT EXISTS {{resume_detail}} (
  `detailid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '详细信息id',
  `resumeid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '对应的简历id',
  `positionid` smallint(6) NOT NULL  DEFAULT '0' COMMENT '目标职位id',
  `realname` varchar(20) NOT NULL  DEFAULT '' COMMENT '名字',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别\n(0:不详 1:男 2:女)',
  `birthday` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '出生日期',
  `maritalstatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '婚姻状况\n(0:未婚 1:已婚 2:不详)',
  `residecity` varchar(255) NOT NULL DEFAULT '' COMMENT '居住城市',
  `idcard` varchar(255) NOT NULL DEFAULT '' COMMENT '证件号码',
  `birthplace` varchar(255) NOT NULL  DEFAULT '' COMMENT '籍贯',
  `height` varchar(255) NOT NULL DEFAULT '' COMMENT '身高',
  `weight` varchar(255) NOT NULL DEFAULT '' COMMENT '体重',
  `workyears` varchar(10) NOT NULL DEFAULT '' COMMENT '工作年限',
  `education` varchar(255) NOT NULL DEFAULT '' COMMENT '学历',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '电子邮件',
  `qq` varchar(20) NOT NULL DEFAULT '' COMMENT 'QQ号码',
  `msn` varchar(64) NOT NULL DEFAULT '' COMMENT 'msn号码',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号码',
  `telephone` varchar(255) NOT NULL DEFAULT '' COMMENT '固定电话',
  `zipcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邮政编码',
  `selfevaluation` text NOT NULL COMMENT '自我评价',
  `workexperience` text NOT NULL COMMENT '工作经历',
  `computerskill` text NOT NULL COMMENT '计算机技能',
  `eduexperience` text NOT NULL COMMENT '教育经历',
  `langskill` text NOT NULL COMMENT '语言水平',
  `professionskill` text NOT NULL COMMENT '职业技能',
  `trainexperience` text NOT NULL COMMENT '培训经历',
  `attachmentid` text NOT NULL COMMENT '附件ID',
  `recchannel` varchar(255) NOT NULL DEFAULT '' COMMENT '简历来源',
  `projectexperience` text NOT NULL COMMENT '项目经验',
  `relevantcertificates` text NOT NULL COMMENT '相关证书',
  `expectsalary` varchar(255) NOT NULL DEFAULT '' COMMENT '期望月薪',
  `workplace` varchar(255) NOT NULL DEFAULT '' COMMENT '工作地点',
  `beginworkday` varchar(255) NOT NULL DEFAULT '' COMMENT '到岗时间',
  `socialpractice` text NOT NULL COMMENT '社会实践',
  `avatarid` text NOT NULL COMMENT '头像',
  PRIMARY KEY (`detailid`),
  UNIQUE KEY `detailid` (`detailid`) USING BTREE,
  KEY `resumeid` (`resumeid`) USING BTREE,
  KEY `realname` (`realname`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{resume_interview}};
CREATE TABLE IF NOT EXISTS {{resume_interview}} (
  `interviewid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '面试id',
  `resumeid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '简历id',
  `interviewtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '面试时间',
  `interviewer` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '面试人',
  `method` varchar(255) NOT NULL DEFAULT '' COMMENT '面试方法',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '面试类型',
  `process` text NOT NULL COMMENT '面试过程',
  PRIMARY KEY (`interviewid`),
  UNIQUE KEY `interviewid` (`interviewid`) USING BTREE,
  KEY `resumeid` (`resumeid`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS {{resume_statistics}};
CREATE TABLE IF NOT EXISTS {{resume_statistics}} (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `new` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增数量',
  `pending` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '待安排数量',
  `interview` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '面试数量',
  `employ` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '录用数量',
  `eliminate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '淘汰数量',
  `datetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日期时间戳',
  PRIMARY KEY (`id`),
  KEY `datetime` (`datetime`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

REPLACE INTO `{{setting}}` (`skey` ,`svalue`) VALUES ('recruitconfig', 'a:33:{s:15:"recruitrealname";s:10:"1,notempty";s:10:"recruitsex";s:16:"1,notrequirement";s:15:"recruitbirthday";s:10:"1,notempty";s:17:"recruitbirthplace";s:16:"1,notrequirement";s:16:"recruitworkyears";s:16:"1,notrequirement";s:16:"recruiteducation";s:16:"1,notrequirement";s:13:"recruitstatus";s:10:"1,notempty";s:13:"recruitidcard";s:8:"0,idcard";s:13:"recruitheight";s:5:"0,num";s:13:"recruitweight";s:5:"0,num";s:20:"recruitmaritalstatus";s:16:"0,notrequirement";s:17:"recruitresidecity";s:16:"1,notrequirement";s:14:"recruitzipcode";s:9:"1,zipcode";s:13:"recruitmobile";s:8:"1,mobile";s:12:"rucruitemail";s:7:"1,email";s:16:"recruittelephone";s:5:"1,tel";s:9:"recruitqq";s:5:"0,num";s:10:"recruitmsn";s:16:"0,notrequirement";s:19:"recruitbeginworkday";s:16:"1,notrequirement";s:21:"recruittargetposition";s:16:"1,notrequirement";s:19:"recruitexpectsalary";s:16:"1,notrequirement";s:16:"recruitworkplace";s:16:"1,notrequirement";s:17:"recruitrecchannel";s:16:"1,notrequirement";s:21:"recruitworkexperience";s:16:"1,notrequirement";s:24:"recruitprojectexperience";s:16:"1,notrequirement";s:20:"recruiteduexperience";s:16:"1,notrequirement";s:16:"recruitlangskill";s:16:"0,notrequirement";s:20:"recruitcomputerskill";s:16:"0,notrequirement";s:22:"recruitprofessionskill";s:16:"0,notrequirement";s:22:"recruittrainexperience";s:16:"0,notrequirement";s:21:"recruitselfevaluation";s:16:"0,notrequirement";s:27:"recruitrelevantcertificates";s:16:"0,notrequirement";s:21:"recruitsocialpractice";s:16:"0,notrequirement";}');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('招聘管理','0','recruit','dashboard','index','','11','0');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('0','招聘管理','recruit/resume/index','0','1','0','7','recruit');
REPLACE INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`,`extcredits2`, `extcredits3`) VALUES ('添加简历', 'addresume', '3', '1', '0', '1','1');
INSERT INTO `{{cron}}` (`available`, `type`,`module`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ( '1', 'system','recruit', '每日招聘统计', 'CronRecruitStatistics.php', '1393516800', '1393603200', '-1', '-1', '0', '0');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('recruit','招聘','recruit/resume/index','提供企业招聘信息','0','0');