DROP TABLE IF EXISTS `{{position}}`;
CREATE TABLE `{{position}}` (
  `positionid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '岗位id',
  `catid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '岗位分类',
  `posname` char(20) NOT NULL COMMENT '职位名称',
  `sort` mediumint(8) NOT NULL DEFAULT '0' COMMENT '排序序号',
  `goal` text NOT NULL COMMENT '职位权限',
  `minrequirement` text NOT NULL COMMENT '最低要求',
  `number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '在职人数',
  PRIMARY KEY (`positionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{position_category}}`;
CREATE TABLE `{{position_category}}` (
  `catid` mediumint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '岗位分类id',
  `pid` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT '岗位分类父id',
  `name` char(20) CHARACTER SET utf8 NOT NULL COMMENT '岗位分类名称',
  `sort` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序id',
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{position_responsibility}}`;
CREATE TABLE `{{position_responsibility}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '职责范围与衡量标准id',
  `positionid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属岗位的id',
  `responsibility` text NOT NULL COMMENT '职责范围',
  `criteria` text NOT NULL COMMENT '衡量标准',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{position_related}}`;
CREATE TABLE `{{position_related}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `positionid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '岗位id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{{position_category}}` (`catid`, `pid`, `name`, `sort`) VALUES ('1', '0', '默认分类', '1');
INSERT INTO `{{position}}` (`catid`, `posname`, `sort`, `goal`, `minrequirement`, `number`) VALUES ('1', '总经理', '1', '', '', '0');
INSERT INTO `{{position}}` (`catid`, `posname`, `sort`, `goal`, `minrequirement`, `number`) VALUES ('1', '部门经理', '2', '', '', '0');
INSERT INTO `{{position}}` (`catid`, `posname`, `sort`, `goal`, `minrequirement`, `number`) VALUES ('1', '职员', '3', '', '', '0');