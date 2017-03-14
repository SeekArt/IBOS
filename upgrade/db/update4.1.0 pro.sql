CREATE TABLE IF NOT EXISTS `{{contact_hide}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `deptid` text NOT NULL COMMENT '发布范围部门',
  `positionid` text NOT NULL COMMENT '发布范围职位',
  `roleid` text NOT NULL COMMENT '发布范围角色',
  `uid` text NOT NULL COMMENT '发布范围人员',
  `column` char(35) NOT NULL DEFAULT '' COMMENT '需要隐藏的字段名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户字段隐藏记录表';

CREATE TABLE IF NOT EXISTS {{diary_direct}} (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
`direct` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否设置只看直属下属,1为是,0为否',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `{{vote_topic}}` (
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

UPDATE `{{login_template}}` SET  `disabled`='1' WHERE `image`='data/login/ibos_login1.jpg';