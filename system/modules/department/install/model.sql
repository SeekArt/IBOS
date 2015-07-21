DROP TABLE IF EXISTS `{{department}}`;
CREATE TABLE `{{department}}` (
  `deptid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `deptname` char(20) NOT NULL COMMENT '部门名称',
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '上级部门ID',
  `manager` mediumint(8) NOT NULL DEFAULT '0' COMMENT '部门主管',
  `leader` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `subleader` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tel` char(15) NOT NULL COMMENT '部门电话',
  `fax` char(15) NOT NULL COMMENT '部门传真',
  `addr` char(100) NOT NULL COMMENT '部门地址',
  `func` char(255) NOT NULL COMMENT '部门职能',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  `isbranch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否作为分支机构',
  PRIMARY KEY (`deptid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{department_related}}`;
CREATE TABLE `{{department_related}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `deptid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '部门id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
