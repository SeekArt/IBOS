DROP TABLE IF EXISTS `{{user_follow}}`;
CREATE TABLE `{{user_follow}}` (
  `followid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '关注者ID',
  `fid` int(11) NOT NULL DEFAULT '0' COMMENT '被关注者ID',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '关注时间',
  PRIMARY KEY (`followid`),
  UNIQUE KEY `uid-fid` (`uid`,`fid`),
  UNIQUE KEY `fid-uid` (`fid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{feed_topic}}`;
CREATE TABLE `{{feed_topic}}` (
  `topicid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '话题ID',
  `topicname` varchar(150) NOT NULL COMMENT '话题标题',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '关联的动态数',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定',
  `domain` varchar(100) NOT NULL COMMENT '个性化地址',
  `recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `recommend_time` int(11) DEFAULT '0' COMMENT '推荐时间',
  `des` text COMMENT '详细内容',
  `outlink` varchar(100) DEFAULT NULL COMMENT '关联链接',
  `pic` varchar(255) DEFAULT NULL COMMENT '关联图片',
  `essence` tinyint(1) DEFAULT '0' COMMENT '是否精华',
  `note` varchar(255) DEFAULT NULL COMMENT '摘要',
  `topic_user` varchar(255) DEFAULT NULL COMMENT '话题人物推荐',
  PRIMARY KEY (`topicid`),
  KEY `count` (`count`),
  KEY `recommend` (`recommend`,`lock`,`count`),
  KEY `name` (`topicname`,`count`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{feed_topic_link}}`;
CREATE TABLE `{{feed_topic_link}}` (
  `linkid` int(11) NOT NULL AUTO_INCREMENT,
  `feedid` int(11) NOT NULL DEFAULT '0' COMMENT '动态ID',
  `topicid` int(11) NOT NULL DEFAULT '0' COMMENT '话题ID',
  `type` varchar(255) NOT NULL DEFAULT '0' COMMENT '动态类型ID',
  PRIMARY KEY (`linkid`),
  KEY `topic_type` (`topicid`,`type`),
  KEY `weibo` (`feedid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wbmovement', 'a:0:{}');
INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wbnums', '140');
INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wbpostfrequency', '10');
INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wbposttype', 'a:3:{s:5:"image";i:1;s:5:"topic";i:0;s:6:"praise";i:0;}');
INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wbwatermark', '0');
INSERT INTO {{setting}} (`skey` ,`svalue`) VALUES ('wbwcenabled', '0');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('企业微博','0','weibo','dashboard','setup','','0','0');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`, `extcredits2`) VALUES ('发布微博', 'addweibo', '3', '10', '2', '2');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`, `extcredits2`) VALUES ('删除微博', 'deleteweibo', '3', '10', '-1', '1');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`, `extcredits2`) VALUES ('转发微博', 'forwardweibo', '3', '10','1', '2');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits1`, `extcredits2`) VALUES ('微博被转发', 'forwardedweibo', '3', '10','3', '2');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits2`) VALUES ('顶微博', 'diggweibo', '3', '5', '1');
INSERT INTO `{{credit_rule}}` (`rulename`, `action`, `cycletype`, `rewardnum`, `extcredits2`) VALUES ('微博被顶', 'diggedweibo', '3', '200', '5');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('weibo','微博','weibo/home/index','企业微博','8','1');
