<?php

define('DS', DIRECTORY_SEPARATOR);
// IBOS 根目录
define('PATH_ROOT', dirname(dirname(__DIR__)));
define('PATH_UPGRADE', PATH_ROOT . DS . 'upgrade');
define('UPGRADE_FILE', PATH_ROOT . DS . 'upgrade.php');

require_once PATH_UPGRADE . '/vendor/autoload.php';

// 加载关键文件（版本及数据库配置文件）
require_once PATH_ROOT . DS . 'system/defines.php';
$config = require PATH_ROOT . DS . 'system' . DS . 'config' . DS . 'config.php';
$versions = require PATH_UPGRADE . DS . 'config' . DS . 'versions.php';

session_start();
error_reporting(E_ALL | E_STRICT);

if (function_exists('ini_set')) {
    ini_set('session.bug_compat_warn', 0);
    ini_set('session.bug_compat_42', 0);
}