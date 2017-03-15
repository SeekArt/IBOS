DROP TABLE IF EXISTS `{{auth_item}}`;
CREATE TABLE `{{auth_item}}` (
  `name` varchar(64) NOT NULL COMMENT '项目名字',
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL COMMENT '项目描述',
  `bizrule` text NOT NULL COMMENT '关联到这个项目的业务逻辑',
  `data` text NOT NULL COMMENT '当执行业务规则的时候所传递的额外的数据',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{auth_assignment}}`;
CREATE TABLE `{{auth_assignment}}` (
  `itemname` varchar(64) NOT NULL,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `bizrule` text NOT NULL COMMENT '关联到这个项目的业务逻辑',
  `data` text NOT NULL COMMENT '当执行业务规则的时候所传递的额外的数据',
  PRIMARY KEY (`itemname`,`userid`)
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
  `roletype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '角色类型，默认0，普通角色0，普通管理员1',
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

INSERT INTO `{{role}}` ( `roleid`, `rolename` ) VALUES ('1', '管理员');
INSERT INTO `{{role}}` ( `roleid`, `rolename` ) VALUES ('2', '编辑人员');
INSERT INTO `{{role}}` ( `roleid`, `rolename` ) VALUES ('3', '普通成员');

INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'recruit', 'bgchecks', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'recruit', 'statistics', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'vote', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'vote', 'publish', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'vote', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'workflow', 'use', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'workflow', 'entrust', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'workflow', 'destroy', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'workflow', 'flow', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'workflow', 'form', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'recruit', 'interview', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'recruit', 'contact', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'recruit', 'resume', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'contact', 'contact', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'thread', 'thread', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'thread', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'meeting', 'room', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'meeting', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'attendance', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'meeting', 'apply', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'attendance', 'arrange', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'attendance', 'shift', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'report', 'statistics', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'report', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'report', 'report', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'file', 'company', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'file', 'persoanl', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'email', 'webinbox', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'email', 'inbox', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'diary', 'statistics', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'diary', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'diary', 'diary', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'calendar', 'loop', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'calendar', 'task', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'calendar', 'schedule', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'assignment', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'assignment', 'assignment', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'officialdoc', 'manager', 'del', '8');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'officialdoc', 'manager', 'edit', '8');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'officialdoc', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'officialdoc', 'category', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'officialdoc', 'publish', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'officialdoc', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'article', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'article', 'publish', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'article', 'category', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'article', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'article', 'manager', 'edit', '8');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('1', 'article', 'manager', 'del', '8');

INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'workflow', 'form', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'workflow', 'flow', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'workflow', 'destroy', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'thread', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'contact', 'contact', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'vote', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'vote', 'publish', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'vote', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'workflow', 'use', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'workflow', 'entrust', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'thread', 'thread', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'meeting', 'room', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'meeting', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'meeting', 'apply', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'report', 'statistics', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'report', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'report', 'report', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'file', 'company', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'file', 'persoanl', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'email', 'webinbox', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'email', 'inbox', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'diary', 'statistics', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'diary', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'diary', 'diary', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'calendar', 'loop', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'calendar', 'task', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'calendar', 'schedule', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'assignment', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'assignment', 'assignment', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'officialdoc', 'manager', 'del', '8');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'officialdoc', 'manager', 'edit', '8');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'officialdoc', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'officialdoc', 'publish', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'officialdoc', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'article', 'manager', 'del', '8');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'article', 'manager', 'edit', '8');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'article', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'article', 'publish', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('2', 'article', 'view', '', '0');

INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'workflow', 'destroy', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'workflow', 'entrust', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'workflow', 'use', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'vote', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'contact', 'contact', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'thread', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'thread', 'thread', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'meeting', 'room', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'meeting', 'manager', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'meeting', 'apply', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'report', 'statistics', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'report', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'report', 'report', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'file', 'company', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'file', 'persoanl', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'email', 'inbox', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'email', 'webinbox', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'diary', 'statistics', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'diary', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'diary', 'diary', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'calendar', 'loop', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'calendar', 'task', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'calendar', 'schedule', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'assignment', 'review', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'assignment', 'assignment', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'officialdoc', 'view', '', '0');
INSERT INTO `{{node_related}}` (`roleid`, `module`, `key`, `node`, `val`) VALUES ('3', 'article', 'view', '', '0');





