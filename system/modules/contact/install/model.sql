DROP TABLE IF EXISTS `{{contact}}`;
CREATE TABLE `{{contact}}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `cuid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '常联系人id',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `cuid` (`cuid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='常联系人表';

INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('0','通讯录','contact/default/index','0','1','0','8','contact');

