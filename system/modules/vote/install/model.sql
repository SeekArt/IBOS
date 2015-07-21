DROP TABLE IF EXISTS `{{vote}}`;
CREATE TABLE `{{vote}}` (
  `voteid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `ismulti` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否多选: 0：单选；1：多选',
  `isvisible` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票结果查看权限，0：所有人可见、1：投票后可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态  0：有效 、1：无效、2：结束',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '发布者UID',
  `deadlinetype` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '截至日期类型：0自定义，1周，2月，3半年，4年', 
  `maxselectnum` tinyint(2) NOT NULL DEFAULT '1' COMMENT '最大可选择数',
  `relatedmodule` varchar(64) NOT NULL DEFAULT '' COMMENT '模块名称',
  `relatedid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '该模块表下id列的值',
  PRIMARY KEY (`voteid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{vote_item}}`;
CREATE TABLE `{{vote_item}}` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '投票项id',
  `voteid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '投票id',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '投票项内容',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票项类型：1、内容；2、图片',
  `number` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '投票数',
  `picpath` varchar(255) NOT NULL DEFAULT '' COMMENT '图片路径',
  PRIMARY KEY (`itemid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{vote_item_count}}`;
CREATE TABLE `{{vote_item_count}}` (
  `itemid` mediumint(8) unsigned NOT NULL COMMENT '投票项ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  UNIQUE KEY `itemid` (`itemid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

REPLACE INTO `{{setting}}` (`skey`,`svalue`) VALUES ('votethumbenable' , '0');
REPLACE INTO `{{setting}}` (`skey`,`svalue`) VALUES ('votethumbwh' , '0,0');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('投票','0','vote','dashboard','index','','15','0');


