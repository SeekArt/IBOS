DROP TABLE IF EXISTS `{{contact}}`;
CREATE TABLE `{{contact}}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `cuid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '常联系人id',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `cuid` (`cuid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='常联系人表';

DROP TABLE IF EXISTS `{{contact_hide}}`;
CREATE TABLE `{{contact_hide}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `deptid` text NOT NULL COMMENT '发布范围部门',
  `positionid` text NOT NULL COMMENT '发布范围职位',
  `roleid` text NOT NULL COMMENT '发布范围角色',
  `uid` text NOT NULL COMMENT '发布范围人员',
  `column` char(35) NOT NULL DEFAULT '' COMMENT '需要隐藏的字段名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COMMENT='用户字段隐藏记录表';


INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('3','通讯录','contact/default/index','0','1','0','5','contact');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('通讯录','0','contact','dashboard','index','','2','0');

