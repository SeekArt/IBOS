<?php

define( 'IBOS_STATIC', '../static/' ); //static路径
define( 'CHARSET', 'utf-8' ); // 字符编码
define( 'DBCHARSET', 'utf8' ); // 数据库编码
define( 'MODULE_PATH', PATH_ROOT . 'system/modules/' ); // 模块文件夹目录
define( 'CONFIG_PATH', PATH_ROOT . 'system/config/' ); // 配置文件目录
define( 'LOCAL', true ); // 本地环境
define( 'IBOS_VERSION_FULL', 'IBOS ' . VERSION . ' ' . VERSION_DATE ); // 版本号
$lockfile = PATH_ROOT . './data/install.lock';
// 核心模块,安装顺序有限制
$coreModules = array( 'main', 'dashboard', 'message', 'user', 'department', 'position', 'role' );
// 核心依赖模块
$sysDependModule = array( 'weibo' );
// 系统模块，把核心模块和核心依赖模块组合
$sysModules = array( 'main', 'dashboard', 'message', 'user', 'department', 'position', 'weibo', 'role' );
// 要检测的函数
$funcItems = array(
	'mysql_connect' => array( 'status' => 1 ),
	'gethostbyname' => array( 'status' => 1 ),
	'file_get_contents' => array( 'status' => 1 ),
	'scandir' => array( 'status' => 1 ),
	'xml_parser_create' => array( 'status' => 1 ),
	'bcmul' => array( 'status' => 1 ),
);

$filesockItems = array(
	'fsockopen' => array( 'status' => 1 ),
	'pfsockopen' => array( 'status' => 1 ),
	'stream_socket_client' => array( 'status' => 1 ),
	'curl_init' => array( 'status' => 1 )
);
// 要检测的扩展
$extLoadedItems = array(
	'mysql' => array( 'status' => 1 ),
	'pdo_mysql' => array( 'status' => 1 ),
	'mbstring' => array( 'status' => 1 ),
);
// 要检测的环境（r为所需，b为推荐）
$envItems = array(
	'os' => array( 'c' => 'PHP_OS', 'r' => 'notset', 'b' => 'unix' ),
	'php' => array( 'c' => 'PHP_VERSION', 'r' => '5.3.0', 'b' => '5.3.0' ),
	'attachmentupload' => array( 'r' => '2M', 'b' => '20M' ),
	'gdversion' => array( 'r' => '1.0', 'b' => '2.0' ),
	'diskspace' => array( 'r' => '100M', 'b' => 'notset' ),
	'Zend Guard Loader' => array( 'r' => 'install', 'b' => 'install' ),
);
// 要检测的文件、文件夹权限
$dirfileItems = array(
	'config' => array( 'type' => 'file', 'path' => './system/config/configDefault.php' ),
	'org' => array( 'type' => 'file', 'path' => './data/org.js' ),
	'config_dir' => array( 'type' => 'dir', 'path' => './system/config' ),
	'data' => array( 'type' => 'dir', 'path' => './data' ),
	'attachment' => array( 'type' => 'dir', 'path' => './data/attachment' ),
	'avatar' => array( 'type' => 'dir', 'path' => './data/avatar' ),
	'backup' => array( 'type' => 'dir', 'path' => './data/backup' ),
	'font' => array( 'type' => 'dir', 'path' => './data/font' ),
	'home' => array( 'type' => 'dir', 'path' => './data/home' ),
	'ipdata' => array( 'type' => 'dir', 'path' => './data/ipdata' ),
	'login' => array( 'type' => 'dir', 'path' => './data/login' ),
	'runtime' => array( 'type' => 'dir', 'path' => './data/runtime' ),
	'stamp' => array( 'type' => 'dir', 'path' => './data/stamp' ),
	'temp' => array( 'type' => 'dir', 'path' => './data/temp' ),
	'static' => array( 'type' => 'dir', 'path' => './static' )
);

$moduleSql = "CREATE TABLE IF NOT EXISTS `{dbpre}module` (
  `module` varchar(30) NOT NULL COMMENT '模块',
  `name` varchar(20) NOT NULL COMMENT '模块名',
  `url` varchar(100) NOT NULL COMMENT '链接地址',
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否核心模块',
  `version` varchar(50) NOT NULL DEFAULT '' COMMENT '版本号',
  `icon` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '图标文件存在与否',
  `category` varchar(30) NOT NULL COMMENT '模块所属分类',
  `description` varchar(255) NOT NULL COMMENT '模块描述',
  `config` mediumtext NOT NULL COMMENT '模块配置，数组形式',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序 ',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已禁用',
  `installdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '安装日期',
  `updatedate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新日期',
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$adminInfo = "<?php
	\$admin = array(
		'username' => '{username}',
		'isadministrator' => '{isadministrator}',
		'password' => '{password}',
		'createtime' => '{createtime}',
		'salt' => '{salt}',
		'realname' => '{realname}',
		'mobile' => '{mobile}',
		'email' => '{email}',
);
	\$adminco =array(
		'accesstoken'=> '{accesstoken}',
		'corptoken' => '{corptoken}',
		'fullname' => '{fullname}',
		'shortname' => '{shortname}',
		'guid' => '{guid}',
		'corpcode' => '{corpcode}',
		'aeskey' => '{aeskey}',
);";
