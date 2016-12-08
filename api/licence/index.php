<?php

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\main\model\Setting;

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../../');
$defines = PATH_ROOT . '/system/defines.php';
defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('TIMESTAMP') || define('TIMESTAMP', time());
defined('CALLBACK') || define('CALLBACK', true);
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
if (ENGINE == 'SAAS') {
    Env::iExit('Forbidden for saas');
}
$mainConfig = require PATH_ROOT . '/system/config/common.php';
require_once($yii);
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);
// 接收的参数
$signature = rawurldecode(Env::getRequest('signature'));
$timestamp = Env::getRequest('timestamp');
$url = $_SERVER["HTTP_HOST"];

if (strcmp($signature, sha1($url . $timestamp)) != 0) {
    $msg = "-" . $url . '|' . $timestamp . '|' . sha1($url . $timestamp);
    Env::iExit('error:sign error ' . $msg);
}

// 接收信息处理
$result = trim(file_get_contents("php://input"), " \t\n\r");
// 解析
if (!empty($result)) {
    $msg = json_decode($result, true);
    switch ($msg['op']) {
        case 'save':
            $res = saveLicence($msg);
            break;
        default:
            $res = array('isSuccess' => false, 'msg' => '未知操作');
            break;
    }
    Env::iExit(json_encode($res));
}

/**
 * 授权码写入文件
 */
function saveLicence($msg)
{
    $return = array(
        'isSuccess' => false
    );
    $licensekey = urldecode($msg['licensekey']);
    $filename = PATH_ROOT . '/data/licence.key';
    //读出原来的授权
    if (file_exists($filename) && is_readable($filename)) {
        $oldlicencekey = file_get_contents($filename);
    } else {
        $oldlicencekey = "";
    }
    //写入授权
    @file_put_contents($filename, $licensekey);
    $license = Ibos::app()->licence;
//  $license->init();
    $licenseInfo = $license->getLicence();
    if (!empty($licenseInfo)) {
        $iboscloud = Ibos::app()->setting->get('setting/iboscloud');
        $iboscloud['appid'] = isset($licenseInfo['appid']) ? $licenseInfo['appid'] : '';
        $iboscloud['secret'] = isset($licenseInfo['secret']) ? $licenseInfo['secret'] : '';
        Setting::model()->updateSettingValueByKey('iboscloud', serialize($iboscloud));
        Cache::update('setting');
        $return['isSuccess'] = true;
    } else {
        $return['msg'] = $licensekey;
        if (empty($oldlicencekey)) {
            @unlink($filename);
        } else {
            @file_put_contents($filename, $oldlicencekey);
        }
    }
    return $return;
}
