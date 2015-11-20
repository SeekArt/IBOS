DROP TABLE IF EXISTS {{messageboard}};
CREATE TABLE `{{messageboard}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户ID',
  `time` int(10) unsigned NOT NULL COMMENT '留言创建时间',
  `content` mediumtext CHARACTER SET utf8 NOT NULL COMMENT '留言内容',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '留言状态 (0为禁止 1为正常 -1为删除)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {{messageboard_reply}};
CREATE TABLE `{{messageboard_reply}}` (
  `rid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '回复ID',
  `mid` mediumint(8) unsigned NOT NULL COMMENT '留言ID',
  `uid` mediumint(8) unsigned NOT NULL COMMENT '回复者ID',
  `time` int(10) unsigned NOT NULL COMMENT '回复时间',
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;