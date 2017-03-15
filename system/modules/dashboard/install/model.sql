DROP TABLE IF EXISTS `{{menu}}`;
CREATE TABLE `{{menu}}` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `name` char(20) NOT NULL COMMENT '菜单显示名字',
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `m` char(20) NOT NULL DEFAULT '' COMMENT '模块',
  `c` char(20) NOT NULL DEFAULT '' COMMENT '控制器',
  `a` char(20) NOT NULL DEFAULT '' COMMENT '动作',
  `param` char(100) NOT NULL DEFAULT '' COMMENT '要传递的参数',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{credit}}`;
CREATE TABLE `{{credit}}` (
  `cid` int(10) unsigned NOT NULL auto_increment COMMENT '积分id',
  `system` tinyint(1) unsigned NOT NULL default '0' COMMENT '是否为系统自带：1为是；0为否',
  `name` varchar(50) NOT NULL COMMENT '积分名字',
  `initial` int(10) unsigned NOT NULL default '0' COMMENT '初始积分',
  `lower` int(10) unsigned NOT NULL default '0' COMMENT '积分下限',
  `enable` tinyint(1) unsigned NOT NULL default '1' COMMENT '是否启动：1为启动，0为不启用',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{credit_log}}`;
CREATE TABLE `{{credit_log}}` (
  `logid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `operation` char(3) NOT NULL DEFAULT '',
  `relatedid` int(10) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `extcredits1` int(10) NOT NULL DEFAULT '0',
  `extcredits2` int(10) NOT NULL DEFAULT '0',
  `extcredits3` int(10) NOT NULL DEFAULT '0',
  `extcredits4` int(10) NOT NULL DEFAULT '0',
  `extcredits5` int(10) NOT NULL DEFAULT '0',
  `curcredits` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`logid`),
  KEY `uid` (`uid`),
  KEY `operation` (`operation`),
  KEY `relatedid` (`relatedid`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{credit_rule}}`;
CREATE TABLE `{{credit_rule}}` (
  `rid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `rulename` varchar(20) NOT NULL DEFAULT '',
  `action` varchar(20) NOT NULL DEFAULT '',
  `cycletype` tinyint(1) NOT NULL DEFAULT '0',
  `cycletime` int(10) NOT NULL DEFAULT '0',
  `rewardnum` smallint(5) NOT NULL DEFAULT '1',
  `norepeat` tinyint(1) NOT NULL DEFAULT '0',
  `extcredits1` int(10) NOT NULL DEFAULT '0',
  `extcredits2` int(10) NOT NULL DEFAULT '0',
  `extcredits3` int(10) NOT NULL DEFAULT '0',
  `extcredits4` int(10) NOT NULL DEFAULT '0',
  `extcredits5` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `action` (`action`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{credit_rule_log}}`;
CREATE TABLE `{{credit_rule_log}}` (
  `clid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则记录id',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户ID',
  `rid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '规则ID',
  `total` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '总积分',
  `cyclenum` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '周期次数',
  `extcredits1` int(10) NOT NULL DEFAULT '0' COMMENT '积分1',
  `extcredits2` int(10) NOT NULL DEFAULT '0' COMMENT '积分2',
  `extcredits3` int(10) NOT NULL DEFAULT '0' COMMENT '积分3',
  `extcredits4` int(10) NOT NULL DEFAULT '0' COMMENT '积分4',
  `extcredits5` int(10) NOT NULL DEFAULT '0' COMMENT '积分5',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录时间',
  PRIMARY KEY (`clid`),
  KEY `dateline` (`dateline`),
  KEY `uid` (`uid`,`rid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{credit_rule_log_field}}`;
CREATE TABLE `{{credit_rule_log_field}}` (
  `clid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `info` text NOT NULL,
  `user` text NOT NULL,
  `app` text NOT NULL,
  PRIMARY KEY (`clid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{syscode}}`;
CREATE TABLE `{{syscode}}` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `number` varchar(50) NOT NULL COMMENT '代码',
  `sort` mediumint(8) unsigned NOT NULL COMMENT '排序',
  `name` varchar(50) NOT NULL COMMENT '系统代码描述',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统代码',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '系统代码图标',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index` (`pid`,`number`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{nav}}`;
CREATE TABLE `{{nav}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `pid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `name` varchar(30) NOT NULL COMMENT '导航名字',
  `url` varchar(255) NOT NULL COMMENT '链接URL',
  `targetnew` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0为本窗口打开，1为新窗口打开',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统内置',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
  `sort` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `module` varchar(15) NOT NULL DEFAULT '' COMMENT '模块名',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0为超链接，1为单页图文',
  `pageid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '单页图文关联id',
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{page}}`;
CREATE TABLE `{{page}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `template` varchar(50) NOT NULL DEFAULT '' COMMENT '模板',
  `content` text NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{announcement}}`;
CREATE TABLE `{{announcement}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `author` varchar(15) NOT NULL DEFAULT '' COMMENT '作者',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型，0为内容，1为链接',
  `sort` tinyint(3) NOT NULL DEFAULT '0' COMMENT '排序号',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `message` text NOT NULL COMMENT '公告内容',
  PRIMARY KEY (`id`),
  KEY `timespan` (`starttime`,`endtime`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{ipbanned}}`;
CREATE TABLE `{{ipbanned}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `ip1` smallint(3) NOT NULL DEFAULT '0',
  `ip2` smallint(3) NOT NULL DEFAULT '0',
  `ip3` smallint(3) NOT NULL DEFAULT '0',
  `ip4` smallint(3) NOT NULL DEFAULT '0',
  `admin` varchar(15) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{stamp}}`;
CREATE TABLE `{{stamp}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `code` varchar(30) NOT NULL DEFAULT '' COMMENT '显示名称',
  `stamp` varchar(100) NOT NULL DEFAULT '' COMMENT '图章地址',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '图标地址',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统自带图章',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{login_template}}`;
CREATE TABLE `{{login_template}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统图片',
  `image` varchar(100) NOT NULL COMMENT '背景图片地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{cache}}`;
CREATE TABLE `{{cache}}` (
  `cachekey` varchar(255) NOT NULL DEFAULT '',
  `cachevalue` mediumblob NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cachekey)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{syscache}}`;
CREATE TABLE `{{syscache}}` (
  `name` varchar(32) NOT NULL COMMENT '缓存类型名称',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '缓存类型，1为数组，其余为0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间戳',
  `value` mediumblob NOT NULL COMMENT '值',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{approval}}`;
CREATE TABLE `{{approval}}` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水id',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '审批流程名称',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '审核等级,1-5级',
  `free` text NOT NULL COMMENT '免审核人uid，逗号隔开',
  `desc` text NOT NULL COMMENT '描述',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{approval_step}}`;
CREATE TABLE `{{approval_step}}` (
`id`  int NOT NULL AUTO_INCREMENT COMMENT '主键，自增 ID' ,
`aid`  int NOT NULL COMMENT '审批流程 ID' ,
`step`  tinyint NOT NULL COMMENT '处于流程中第几级步骤' ,
`uids`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '当前步骤审核人员 ID 串 1,2,3....' ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{{approval_record}}`;
CREATE TABLE `{{approval_record}}` (
`id`  int NOT NULL AUTO_INCREMENT COMMENT '主键，自增 ID' ,
`module` varchar(255) NOT NULL DEFAULT '' COMMENT '关联模型',
`relateid` int NOT NULL  COMMENT '关联ID',
`uid` int NOT NULL  COMMENT '用户ID',
`step` int NOT NULL  COMMENT '步骤',
`time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
`status` int NOT NULL  COMMENT '审核状态,0表示退回，1表示通过，2表示流程结束，3表示发起',
`reason` text NOT NULL COMMENT '原因，通过可以没有原因，退回一定有原因',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO {{credit}} VALUES ('1', '1', '经验', '0', '0', '1');
INSERT INTO {{credit}} VALUES ('2', '1', '金钱', '0', '0', '1');
INSERT INTO {{credit}} VALUES ('3', '1', '贡献', '0', '0', '1');

INSERT INTO `{{syscode}}` VALUES ('1', '0', 'RESUME_JOB_EDU', '1', '学历', '1', '');
INSERT INTO `{{syscode}}` VALUES ('2', '1', 'DOCTOR', '2', '博士', '1', '');
INSERT INTO `{{syscode}}` VALUES ('3', '1', 'MASTER', '3', '硕士', '1', '');
INSERT INTO `{{syscode}}` VALUES ('4', '1', 'BACHELOR_DEGREE', '4', '本科', '1', '');
INSERT INTO `{{syscode}}` VALUES ('5', '1', 'COLLEGE', '5', '大专', '1', '');
INSERT INTO `{{syscode}}` VALUES ('6', '1', 'SENIOR_HIGH', '6', '高中', '1', '');
INSERT INTO `{{syscode}}` VALUES ('7', '1', 'CHUNGCHI', '7', '中技', '1', '');
INSERT INTO `{{syscode}}` VALUES ('8', '1', 'TECHNICAL_SECONDARY', '8', '中专', '1', '');
INSERT INTO `{{syscode}}` VALUES ('9', '1', 'JUNIOR_HIGH', '9', '初中', '1', '');
INSERT INTO `{{syscode}}` VALUES ('10', '0', 'ENGLISH_LEVEL', '10', '英语水平', '1', '');
INSERT INTO `{{syscode}}` VALUES ('11', '10', 'VERY_GOOD', '11', '很好', '1', '');
INSERT INTO `{{syscode}}` VALUES ('12', '10', 'GOOD', '12', '较好', '1', '');
INSERT INTO `{{syscode}}` VALUES ('13', '10', 'ORDINARY', '13', '一般', '1', '');
INSERT INTO `{{syscode}}` VALUES ('14', '10', 'VERY_POOR', '14', '很差', '1', '');
INSERT INTO `{{syscode}}` VALUES ('15', '10', 'POOR', '15', '较差', '1', '');
INSERT INTO `{{syscode}}` VALUES ('16', '0', 'CONTACT_TYPE', '16', '联系类型', '1', '');
INSERT INTO `{{syscode}}` VALUES ('17', '16', 'GTALK', '17', 'Gtalk', '1', '');
INSERT INTO `{{syscode}}` VALUES ('18', '16', 'YY', '18', 'YY', '1', '');
INSERT INTO `{{syscode}}` VALUES ('19', '16', 'SKYPE', '19', 'Skype', '1', '');
INSERT INTO `{{syscode}}` VALUES ('20', '16', 'QQ', '20', 'QQ', '1', '');
INSERT INTO `{{syscode}}` VALUES ('21', '16', 'MSN', '21', 'MSN', '1', '');
INSERT INTO `{{syscode}}` VALUES ('22', '16', 'FETION', '22', '飞信', '1', '');
INSERT INTO `{{syscode}}` VALUES ('23', '16', 'BAIDU_HI', '23', '百度Hi', '1', '');
INSERT INTO `{{syscode}}` VALUES ('24', '16', 'WANGWANG', '24', '旺旺', '1', '');
INSERT INTO `{{syscode}}` VALUES ('25', '16', 'PAOPAO', '25', '泡泡', '1', '');
INSERT INTO `{{syscode}}` VALUES ('26', '16', 'UC', '26', 'UC', '1', '');
INSERT INTO `{{syscode}}` VALUES ('27', '0', 'SUPPLIER_TYPE', '27', '供应商类型', '1', '');
INSERT INTO `{{syscode}}` VALUES ('28', '27', 'INTERNAL_REFERRAL', '28', '内部推荐', '1', '');
INSERT INTO `{{syscode}}` VALUES ('29', '27', 'INTERMEDIARY', '29', '人才中介机构', '1', '');
INSERT INTO `{{syscode}}` VALUES ('30', '27', 'RECRUITMENT_SITE', '30', '招聘网站', '1', '');
INSERT INTO `{{syscode}}` VALUES ('31', '27', 'HEAD_HUNTING_COMPANY', '31', '猎头公司', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('32', '0', 'VEHICLE_MIANTAIN_TYPE', '32', '车辆维护类型', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('33', '32', 'MAINTENANCE', '33', '保养', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('34', '32', 'WASH', '34', '洗车', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('35', '32', 'MAINTAIN', '35', '维护', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('36', '32', 'ANNUAL_SURVEY', '36', '年检', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('37', '32', 'DISCARD', '37', '报废', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('38', '0', 'VEHICLE_COLOR', '38', '车辆颜色', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('39', '38', 'WHITE', '39', '白色', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('40', '38', 'BLUE', '40', '蓝色', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('41', '38', 'GREEN', '41', '绿色', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('42', '38', 'YELLOW', '42', '黄色', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('43', '38', 'RED', '43', '红色', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('44', '38', 'BLACK', '44', '黑色', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('45', '0', 'VEHICLE_STATUS', '45', '车辆状态', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('46', '45', 'AVAILABLE', '46', '可用', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('47', '45', 'STOPPAGE', '47', '损坏', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('48', '45', 'MAINTAIN', '48', '维修中', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('49', '45', 'DISCARD', '49', '报废', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('50', '0', 'VEHICLE_TYPE', '50', '车辆类型', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('51', '50', 'MINIBUS', '51', '面包车', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('52', '50', 'TRUCK', '52', '货车', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('53', '50', 'BUS', '53', '巴士', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('54', '50', 'SEDAN', '54', '轿车', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('55', '0', 'VEHICLE_RUN_COST', '55', '车辆运行费用类型', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('56', '55', 'TOLL', '56', '过路费', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('57', '0', 'VEHICLE_MAINTAIN_COST', '57', '车辆维护费用类型', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('58', '57', 'RENEWAL_PARTS', '58', '更换配件', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('59', '57', 'SERVICE', '59', '修理', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('60', '57', 'WASH', '60', '洗车', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('61', '0', 'VEHICLE_FUEL_TYPE', '61', '车辆燃油规格', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('62', '61', 'GASOLINE_90#', '62', '90# 汽油', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('63', '61', 'GASOLINE_93#', '63', '93# 汽油', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('64', '61', 'GASOLINE_97#', '64', '97# 汽油', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('65', '61', 'GASOLINE_98#', '65', '98# 汽油', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('66', '61', 'DIESEL_-10#', '66', '-10# 柴油', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('67', '61', 'DIESEL_10#', '67', '10# 柴油', '1', '');
-- INSERT INTO `{{syscode}}` VALUES ('68', '61', 'DIESEL_0#', '68', '0# 柴油', '1', '');
INSERT INTO `{{syscode}}` VALUES ('69', '0', 'MEETING_ROOM_TYPE', '69', '会议室类型', '1', '');
INSERT INTO `{{syscode}}` VALUES ('70', '69', 'INTERVIEW_FORM', '70', '接见式', '1', '');
INSERT INTO `{{syscode}}` VALUES ('71', '69', 'T-SHAPED', '71', 'T型', '1', '');
INSERT INTO `{{syscode}}` VALUES ('72', '69', 'STEAMED_FORM', '72', '围桌', '1', '');
INSERT INTO `{{syscode}}` VALUES ('73', '69', 'THEATRE', '73', '剧院', '1', '');
INSERT INTO `{{syscode}}` VALUES ('74', '69', 'U-SHAPED', '74', 'U型', '1', '');
INSERT INTO `{{syscode}}` VALUES ('75', '69', 'BANQUET_HALL', '75', '宴会厅', '1', '');
INSERT INTO `{{syscode}}` VALUES ('76', '69', 'BOARD_OF_DIRECTORS_FORM', '76', '董事会式', '1', '');
INSERT INTO `{{syscode}}` VALUES ('77', '69', 'AMPHITHEATRE', '77', '阶梯型', '1', '');
INSERT INTO `{{syscode}}` VALUES ('78', '0', 'MEETING_ROOM_DEVICE_TYPE', '78', '会议室设备类型', '1', '');
INSERT INTO `{{syscode}}` VALUES ('79', '78', 'VIDICON', '79', '摄像机', '1', 'vidicon.png');
INSERT INTO `{{syscode}}` VALUES ('80', '78', 'NETBOOK', '80', '笔记本电脑', '1', 'netbook.png');
INSERT INTO `{{syscode}}` VALUES ('81', '78', 'RECORDER_PEN', '81', '录音笔', '1', 'recorder_pen.png');
INSERT INTO `{{syscode}}` VALUES ('82', '78', 'MICPHONE', '82', '麦克风', '1', 'micphone.png');
INSERT INTO `{{syscode}}` VALUES ('83', '78', 'WHITE_BOARD', '83', '白板', '1', 'white_board.png');
INSERT INTO `{{syscode}}` VALUES ('84', '78', 'WIRED_NETWORK', '84', '有线网络', '1', 'wired_network.png');
INSERT INTO `{{syscode}}` VALUES ('85', '78', 'WIRELESS_NETWORK', '85', '无线网络', '1', 'wireless_network.png');
INSERT INTO `{{syscode}}` VALUES ('86', '78', 'PROJECTOR', '86', '投影仪', '1', 'projector.png');
INSERT INTO `{{syscode}}` VALUES ('87', '78', 'LOUDSPEAKER_BOX', '87', '音箱', '1', 'loudspeaker_box.png');
INSERT INTO `{{syscode}}` VALUES ('88', '78', 'CAMERA', '88', '相机', '1', 'camera.png');
INSERT INTO `{{syscode}}` VALUES ('89', '78', 'SLIDE', '89', '幻灯片', '1', 'slide.png');

INSERT INTO `{{nav}}` (`id`, `pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`, `type`, `pageid`) VALUES ('1', '0', '首页', 'javascript:void(0)', '0', '1', '0', '1','','0', '0');
INSERT INTO `{{nav}}` (`id`, `pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`, `type`, `pageid`) VALUES ('3', '0', '个人办公', 'javascript:void(0);', '0', '1', '0', '5','','0', '0');
INSERT INTO `{{nav}}` (`id`, `pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`, `type`, `pageid`) VALUES ('5', '0', '综合办公', 'javascript:void(0);', '0', '1', '0', '6','','0', '0');

INSERT INTO `{{nav}}` (`id`, `pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`, `type`, `pageid`) VALUES ('9', '0', '人力资源', 'javascript:void(0)', '0', '1', '1', '9','','0', '0');

INSERT INTO `{{nav}}` (`id`, `pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`, `type`, `pageid`) VALUES ('10', '1', '个人门户', 'weibo/home/index', '0', '1', '0', '2','','0', '0');
INSERT INTO `{{nav}}` (`id`, `pid`, `name`, `url`, `targetnew`, `system`, `disabled`, `sort`, `module`, `type`, `pageid`) VALUES ('11', '1', '办公门户', 'main/default/index', '0', '1', '0', '1','','0', '0');

INSERT INTO `{{stamp}}` VALUES ('1', '1', '已阅', 'data/stamp/001.png', 'data/stamp/001.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('2', '2', '有进步', 'data/stamp/002.png', 'data/stamp/002.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('3', '3', '继续努力', 'data/stamp/003.png', 'data/stamp/003.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('4', '4', '干得不错', 'data/stamp/004.png', 'data/stamp/004.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('5', '5', '很给力', 'data/stamp/005.png', 'data/stamp/005.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('6', '6', '非常赞', 'data/stamp/006.png', 'data/stamp/006.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('7', '7', '待提高', 'data/stamp/007.png', 'data/stamp/007.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('8', '8', '不理想', 'data/stamp/008.png', 'data/stamp/008.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('9', '9', '不给力', 'data/stamp/009.png', 'data/stamp/009.small.png', '1');
INSERT INTO `{{stamp}}` VALUES ('10', '10', '没完成', 'data/stamp/010.png', 'data/stamp/010.small.png', '1');

INSERT INTO `{{login_template}}` VALUES ('1', '1', '1', 'data/login/ibos_login1.jpg');
INSERT INTO `{{login_template}}` VALUES ('2', '0', '1', 'data/login/ibos_login2.jpg');

INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('setting', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('nav', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('creditrule', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('ipbanned', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('department', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('notifyNode', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('role', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('position', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('positioncategory', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('authitem', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('users', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('usergroup', '1', '0', '');
INSERT INTO `{{syscache}}` (`name`, `type`, `dateline`, `value`) VALUES ('cronnextrun', '1', '1393603200', '1393603200');

INSERT INTO `{{approval}}` (`id`, `name`, `level`, `free`, `desc`, `addtime`) VALUES (1, '一级审核', '1', '', '', '1402631014');
INSERT INTO `{{approval}}` (`id`, `name`, `level`, `free`, `desc`, `addtime`) VALUES (2, '二级审核', '2', '', '', '1402631014');

INSERT INTO `{{approval_step}}` (`aid`, `step`, `uids`) VALUES (1, 1, 1);
INSERT INTO `{{approval_step}}` (`aid`, `step`, `uids`) VALUES (2, 1, 1);
INSERT INTO `{{approval_step}}` (`aid`, `step`, `uids`) VALUES (2, 2, 1);

INSERT INTO `{{announcement}}` (`id`, `author`, `subject`, `type`, `sort`, `starttime`, `endtime`, `message`) VALUES (1, '管理员', '<span style=\'color: rgb(226, 111, 80);\'>请使用支持HTML5的浏览器登录！</span>', 0, 0, 1401552000, 1433088000, '');
INSERT INTO `{{cron}}` (`available`, `type`,`module`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ('1', 'system', 'dashboard', '自动同步 IBOS 绑定酷办公用户列表', 'CronAutoSync.php', '1457661663', '1457748000', '-1', '-1', '10', '0');
INSERT INTO `{{cron}}` (`available`, `type`,`module`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES ('1', 'system', 'dashboard', '自动补丁', 'CronAutoPatch.php', '1457661663', '1457748000', '-1', '-1', '10', '0');