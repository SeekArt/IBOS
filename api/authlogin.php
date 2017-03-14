<?php

use application\core\utils\Env;
use application\modules\main\model\Setting;

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../');
$defines = PATH_ROOT . '/system/defines.php';
defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('TIMESTAMP') || define('TIMESTAMP', time());
defined('CALLBACK') || define('CALLBACK', true);
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$mainConfig = require PATH_ROOT . '/system/config/common.php';
require_once($yii);
require_once('login.php');
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);
// 签名验证
$signature = Env::getRequest('signature');
$timestamp = Env::getRequest('timestamp');
$aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
if (strcmp($signature, sha1($aeskey . $timestamp)) != 0) {
    die(CJSON::encode(array('code' => false, 'msg' => '签名错误')));
}

$uid = Env::getRequest('uid');
if (!empty($uid)) {
    $result = doLogin($uid);
    echo CJSON::encode($result);
}