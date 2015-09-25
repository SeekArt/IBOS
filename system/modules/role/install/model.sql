DROP TABLE IF EXISTS `{{auth_assignment}}`;
CREATE TABLE `{{auth_assignment}}` (
  `itemname` varchar(64) NOT NULL,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `bizrule` text NOT NULL COMMENT '关联到这个项目的业务逻辑',
  `data` text NOT NULL COMMENT '当执行业务规则的时候所传递的额外的数据',
  PRIMARY KEY (`itemname`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{auth_item}}`;
CREATE TABLE `{{auth_item}}` (
  `name` varchar(64) NOT NULL COMMENT '项目名字',
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL COMMENT '项目描述',
  `bizrule` text NOT NULL COMMENT '关联到这个项目的业务逻辑',
  `data` text NOT NULL COMMENT '当执行业务规则的时候所传递的额外的数据',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{auth_item_child}}`;
CREATE TABLE `{{auth_item_child}}` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{node}}`;
CREATE TABLE `{{node}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `module` varchar(30) NOT NULL COMMENT '模块名',
  `key` varchar(20) NOT NULL COMMENT '授权节点key',
  `node` varchar(20) NOT NULL COMMENT '子节点(如果有)',
  `name` varchar(20) NOT NULL COMMENT '节点名称',
  `group` varchar(20) NOT NULL COMMENT '分组',
  `category` varchar(20) NOT NULL COMMENT '分类',
  `type` enum('data','node') NOT NULL DEFAULT 'node' COMMENT '节点类型',
  `routes` text NOT NULL COMMENT '路由',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{node_related}}`;
CREATE TABLE `{{node_related}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `roleid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '角色id',
  `module` varchar(30) NOT NULL COMMENT '模块名称',
  `key` varchar(20) NOT NULL COMMENT '授权节点key',
  `node` varchar(20) NOT NULL COMMENT '节点（如果有）',
  `val` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '数据权限',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{role}}`;
CREATE TABLE `{{role}}` (
  `roleid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `rolename` char(20) NOT NULL COMMENT '角色名称',
  PRIMARY KEY (`roleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{role_related}}`;
CREATE TABLE `{{role_related}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `roleid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '角色id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;