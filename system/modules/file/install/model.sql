DROP TABLE IF EXISTS `{{file}}`;
CREATE  TABLE IF NOT EXISTS `{{file}}` (
  `fid` INT(11) NOT NULL AUTO_INCREMENT COMMENT '文件id' ,
  `pid` MEDIUMINT(8) NOT NULL DEFAULT '0' COMMENT '所属文件夹id' ,
  `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id' ,
  `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件名' ,
  `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '类型：0文件，1文件夹' ,
  `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注' ,
  `addtime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间' ,
  `idpath` TEXT NOT NULL COMMENT '层级标识' ,
  `size` INT(10) NOT NULL DEFAULT '0' COMMENT '大小(单位kb)' ,
  `isdel` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否已删除到回收站' ,
  `belong` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属：0个人文件，1公司文件' ,
  `cloudid` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '对应云盘id（0表示本地）',
  PRIMARY KEY (`fid`) ,
  KEY `PID` (`pid`) USING BTREE,
  KEY `UID` (`uid`) USING BTREE,
  KEY `ISDEL` (`isdel`) USING BTREE,
  KEY `BELONG` (`belong`) USING BTREE,
  KEY `CLOUDID` (`cloudid`) USING BTREE
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '文件柜文件/文件夹信息表';

DROP TABLE IF EXISTS `{{file_detail}}`;
CREATE  TABLE IF NOT EXISTS `{{file_detail}}` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '流水id' ,
  `fid` INT(11) NOT NULL DEFAULT '0' COMMENT '关联file表fid' ,
  `attachmentid` INT(11) NOT NULL DEFAULT '0' COMMENT '附件id' ,
  `filetype` CHAR(10) NOT NULL DEFAULT '' COMMENT '文件类型' ,
  `mark` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标记' ,
  `thumb` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '缩略图地址',
  PRIMARY KEY (`id`) ,
  UNIQUE KEY (`fid`),
  KEY `MARK` (`mark`) USING BTREE
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '文件柜文件信息表';

DROP TABLE IF EXISTS `{{file_share}}`;
CREATE  TABLE IF NOT EXISTS `{{file_share}}` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '共享id' ,
  `fid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关联file表fid' ,
  `fromuid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '共享人uid' ,
  `touids` TEXT NOT NULL COMMENT '共享给哪些uid' ,
  `todeptids` TEXT NOT NULL COMMENT '共享给哪些部门' ,
  `toposids` TEXT NOT NULL COMMENT '共享给哪些岗位' ,
  `toroleids` TEXT NOT NULL COMMENT '共享给哪些岗位角色' ,
  `uptime` INT(10) NOT NULL DEFAULT '0' COMMENT '更新共享时间（只针对添加文件而记录的更新时间）' ,
  PRIMARY KEY (`id`) ,
  KEY `FID` (`fid`) USING BTREE,
  KEY `FROMUID` (`fromuid`) USING BTREE,
  KEY `UPTIME` (`uptime`) USING BTREE
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '文件柜共享';

DROP TABLE IF EXISTS `{{file_reader}}`;
CREATE  TABLE IF NOT EXISTS `{{file_reader}}` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '流水id' ,
  `fromuid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '共享人uid' ,
  `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '读者uid' ,
  `viewtime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '查看时间' ,
  PRIMARY KEY (`id`),
  KEY `UID` (`uid`) USING BTREE,
  KEY `VIEWTIME` (`viewtime`) USING BTREE
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '文件柜文件共享已读信息存储';

DROP TABLE IF EXISTS `{{file_dir_access}}`;
CREATE  TABLE IF NOT EXISTS `{{file_dir_access}}` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '流水id' ,
  `fid` INT(11) NOT NULL COMMENT '文件夹id' ,
  `rdeptids` TEXT NOT NULL COMMENT '可读的部门ids' ,
  `rposids` TEXT NOT NULL COMMENT '可读的岗位ids' ,
  `ruids` TEXT NOT NULL COMMENT '可读的uids' ,
  `rroleids` TEXT NOT NULL COMMENT '可读的角色roleids' ,
  `wdeptids` TEXT NOT NULL COMMENT '可写的部门ids' ,
  `wposids` TEXT NOT NULL COMMENT '可写的岗位ids' ,
  `wuids` TEXT NOT NULL COMMENT '可写的uids' ,
  `wroleids` TEXT NOT NULL COMMENT '可写的roleids' ,
  PRIMARY KEY (`id`),
  UNIQUE KEY( `fid` )
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '公司文件柜读写权限（包括云盘权限）';

DROP TABLE IF EXISTS `{{file_trash}}`;
CREATE  TABLE IF NOT EXISTS `{{file_trash}}` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '流水' ,
  `fid` INT(11) NOT NULL DEFAULT '0' COMMENT '关联file表fid' ,
  `deltime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间' ,
  PRIMARY KEY (`id`),
  KEY `FID` (`fid`) USING BTREE,
  KEY `DELTIME` (`deltime`) USING BTREE
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '回收站';

DROP TABLE IF EXISTS `{{file_cloud_set}}`;
CREATE  TABLE IF NOT EXISTS `{{file_cloud_set}}` (
  `id` SMALLINT(5) NOT NULL AUTO_INCREMENT COMMENT '流水id' ,
  `server` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '服务器' ,
  `keyid` CHAR(20) NOT NULL DEFAULT '' COMMENT '验证keyid' ,
  `keysecret` CHAR(50) NOT NULL DEFAULT '' COMMENT '验证码' ,
  `endpoint` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '终端请求连接' ,
  `bucket` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '云盘唯一标识符' ,
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否开通成功' ,
  PRIMARY KEY (`id`)
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '云盘设置';

DROP TABLE IF EXISTS `{{file_capacity}}`;
CREATE  TABLE IF NOT EXISTS `{{file_capacity}}` (
  `id` SMALLINT(5) NOT NULL AUTO_INCREMENT COMMENT '流水id' ,
  `size` INT(10) NOT NULL DEFAULT '0' COMMENT '大小(单位MB)' ,
  `deptids` TEXT NOT NULL COMMENT '部门ids' ,
  `posids` TEXT NOT NULL COMMENT '岗位ids' ,
  `uids` TEXT NOT NULL COMMENT 'uids' ,
  `roleids` TEXT NOT NULL COMMENT '角色ids' ,
  `addtime` INT(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `ADDTIME` (`addtime`) USING BTREE
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '容量分配表';

DROP TABLE IF EXISTS `{{file_dynamic}}`;
CREATE  TABLE IF NOT EXISTS `{{file_dynamic}}` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '流水id' ,
  `fid` INT(11) NOT NULL DEFAULT '0' COMMENT '关联file表fid' ,
  `uid` MEDIUMINT(8) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `content` TEXT NOT NULL COMMENT '动态内容' ,
  `touids` TEXT NOT NULL COMMENT '用户id串' ,
  `todeptids` TEXT NOT NULL COMMENT '部门id串' ,
  `toposids` TEXT NOT NULL COMMENT '岗位id串' ,
  `time` INT(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `FID` (`fid`) USING BTREE,
  KEY `TIME` (`time`) USING BTREE
)ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COMMENT = '文件柜动态表';

REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('filedefsize', '50');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('filecompmanager', '');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('filecloudopen', '0');
REPLACE INTO {{setting}} (`skey` ,`svalue`) VALUES ('filecloudid', '0');
INSERT INTO `{{menu}}`(`name`, `pid`, `m`, `c`, `a`, `param`, `sort`, `disabled`) VALUES ('文件柜','0','file','dashboard','index','','4','0');
INSERT INTO `{{nav}}`(`pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`) VALUES ('3','文件柜','file/default/index','0','1','0','3','file');
INSERT INTO `{{menu_common}}`( `module`, `name`, `url`, `description`, `sort`, `iscommon`) VALUES ('file','文件柜','file/default/index','提供企业文件存储','13','0');
INSERT INTO `{{cron}}` (`available`, `type`,`module`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ( '1', 'system','file', '清空文件柜回收站15天前的文件', 'CronFileTrash.php', '1393516800', '1393603200', '-1', '-1', '0', '0');