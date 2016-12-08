DROP TABLE IF EXISTS `{{vote}}`;
CREATE TABLE `{{vote}}` (
  `voteid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '投票描述',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `isvisible` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票结果查看权限，0：所有人可见、1：投票后可见',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '发布者UID',
  `deadlinetype` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '截至日期类型：0自定义，1周，2月，3半年，4年',
  `relatedmodule` varchar(64) NOT NULL DEFAULT '' COMMENT '模块名称',
  `relatedid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '该模块表下id列的值',
  `deptid` text NOT NULL COMMENT '阅读范围部门',
  `positionid` text NOT NULL COMMENT '阅读范围职位',
  `roleid` text NOT NULL COMMENT '阅读范围角色',
  `scopeuid` text NOT NULL COMMENT '阅读范围人员',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`voteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{vote_item}}`;
CREATE TABLE `{{vote_item}}` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '投票项id',
  `voteid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '投票id',
  `topicid` int(11) unsigned NOT NULL COMMENT '投票题目 id',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '投票项内容',
  `number` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '投票数',
  `picpath` varchar(255) NOT NULL DEFAULT '' COMMENT '图片路径',
  PRIMARY KEY (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{vote_item_count}}`;
CREATE TABLE `{{vote_item_count}}` (
  `voteid` mediumint(9) unsigned NOT NULL COMMENT '投票 id',
  `topicid` mediumint(9) unsigned NOT NULL COMMENT '投票话题 id',
  `itemid` mediumint(8) unsigned NOT NULL COMMENT '投票项ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  UNIQUE KEY `itemid` (`itemid`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{vote_topic}}`;
CREATE TABLE `{{vote_topic}}` (
  `topicid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `voteid` mediumint(8) unsigned NOT NULL COMMENT '投票 id',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '投票题目标题',
  `type` tinyint(4) NOT NULL COMMENT '投票题目类型：1、内容；2、图片',
  `maxselectnum` tinyint(4) unsigned NOT NULL COMMENT '是否多选: 0：单选；1：多选',
  `itemnum` tinyint(4) NOT NULL COMMENT '选项个数',
  PRIMARY KEY (`topicid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{{reader}}`  (
  `readerid` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `module` char(20) NOT NULL COMMENT '模块名',
  `moduleid` mediumint(8) unsigned NOT NULL COMMENT '关联模块 id',
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户 id',
  PRIMARY KEY (`readerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='阅读记录表';

REPLACE INTO `{{setting}}` (`skey`,`svalue`) VALUES ('votethumbenable' , '0');
REPLACE INTO `{{setting}}` (`skey`,`svalue`) VALUES ('votethumbwh' , '0,0');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('5','调查投票','vote/default/index','0','1','0','7','vote');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('调查投票','0','vote','dashboard','index','','15','0');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('vote_publish_message','投票发布提醒','vote','vote/default/New message title','vote/default/New message content','1','1','1','2');
INSERT INTO `{{notify_node}}`(`node`, `nodeinfo`, `module`, `titlekey`, `contentkey`, `sendemail`, `sendmessage`, `sendsms`, `type`) VALUES ('vote_update_message','投票更新提醒','vote','vote/default/Update message title','vote/default/Update message content','1','1','1','2');

