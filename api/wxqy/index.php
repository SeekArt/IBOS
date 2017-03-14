<?php

use application\core\utils\Env;
use application\modules\message\core\wx\Factory;
use application\modules\message\core\wx\WxApi;
use application\modules\user\model\UserBinding;

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../../');
$defines = PATH_ROOT . '/system/defines.php';
defined('TIMESTAMP') || define('TIMESTAMP', time());
defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('CALLBACK') || define('CALLBACK', true);
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$mainConfig = require PATH_ROOT . '/system/config/common.php';
require_once($yii);
require_once('../login.php');
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);
// 接收的参数
$msgSignature = rawurldecode(Env::getRequest('msgSignature'));
$timestamp = rawurldecode(Env::getRequest('timestamp'));
$aeskey = WxApi::getInstance()->getAeskey();
$echoStr = rawurldecode(Env::getRequest('echoStr'));
if (strcmp($msgSignature, md5($aeskey . $timestamp)) != 0) {
    Env::iExit('access denied');
}
if (!empty($echoStr)) {
    WxApi::getInstance()->resetCorp();
    Env::iExit($echoStr);
}
// 接收信息处理
$result = trim(file_get_contents("php://input"), " \t\n\r");
// 解析
if (!empty($result)) {
    $msg = CJSON::decode($result, true);
    if (!empty($msg)) {
        $uid = UserBinding::model()->fetchUidByValue($msg['properties']['userId'], 'wxqy');
        if ($uid) {
            doLogin($uid);
            $factory = new Factory();
            $res = $factory->createHandle($msg['class'], $msg['properties'])->handle();
        } else {
            $res = resByText($userId, $corpId, $newTime, '您的账号尚未绑定，无法进行任何操作');
        }
        Env::iExit($res);
    } else {
        Env::iExit('');
    }
}

/**
 * 以文本格式回复
 * @param string $userId
 * @param string $corpId
 * @param integer $newTime
 * @param string $text
 * @return string
 */
function resByText($userId, $corpId, $newTime, $text = '')
{
    return "<xml>
	   <ToUserName><![CDATA[{$userId}]]></ToUserName>
	   <FromUserName><![CDATA[{$corpId}]]></FromUserName> 
	   <CreateTime>{$newTime}</CreateTime>
	   <MsgType><![CDATA[text]]></MsgType>
	   <Content><![CDATA[{$text}]]></Content>
	</xml>";
}
