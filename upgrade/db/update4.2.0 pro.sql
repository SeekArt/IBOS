ALTER TABLE `{{article}}`  MODIFY COLUMN `approver`  text NOT NULL COMMENT '审批人' AFTER `author`;

ALTER TABLE `{{article}}` MODIFY COLUMN `status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '文章状态，1为公开2为审核3为草稿0为退回' AFTER `catid`;

ALTER TABLE `{{article_approval}}`
DROP COLUMN `time`,
ADD COLUMN `time`  int(10) NULL COMMENT '审核时间' AFTER `step`;

ALTER TABLE `{{article_approval}}`
DROP COLUMN `isdel`,
ADD COLUMN `isdel`  tinyint(1) NULL DEFAULT 0 COMMENT '是否删除' AFTER `time`;

DROP TABLE IF EXISTS `{{approval_record}}`;
CREATE TABLE `{{approval_record}}` (
`id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键，自增 ID',
`module` varchar(255) NOT NULL DEFAULT '' COMMENT '关联模型',
`relateid` int(11) NOT NULL COMMENT '关联ID',
`uid` int(11) NOT NULL COMMENT '用户ID',
`step` int(11) NOT NULL COMMENT '步骤',
`time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
`status` int(11) NOT NULL COMMENT '审核状态,0表示退回，1表示通过，2表示流程结束，3表示发起',
`reason` text NOT NULL COMMENT '原因，通过可以没有原因，退回一定有原因',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

