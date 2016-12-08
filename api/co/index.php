<?php

use application\core\utils\Env;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\user\model\User;

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../../');
$defines = PATH_ROOT . '/system/defines.php';
defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('TIMESTAMP') || define('TIMESTAMP', time());
defined('CALLBACK') || define('CALLBACK', true);
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$mainConfig = require PATH_ROOT . '/system/config/common.php';
require_once($yii);
require_once('../login.php');
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);
// 接收信息处理
$result = trim(file_get_contents("php://input"), " \t\n\r");
// 解析
if (!empty($result)) {
    $msg = CJSON::decode($result, true);
    if (isset($msg['op'])) {
        switch ($msg['op']) {
            case 'verify':
                $res = doverify($msg['username'], $msg['password']);
                if ($res['isSuccess'] == true) {
                    $aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
                    $res['aeskey'] = $aeskey;
                    Setting::model()->updateSettingValueByKey('cobinding', 1);
                }
                break;

            default:
                $res = array('isSuccess' => false, 'msg' => '未知操作');
                break;
        }
    } else {
        $res = array('isSuccess' => false, 'msg' => '数据请求不合法');
    }
    Env::iExit(json_encode($res));
}

/**
 *
 * @param string $userName 用户名
 * @param string $password 密码
 * @return array
 */
function doverify($userName, $password)
{
    if (StringUtil::isMobile($userName)) {
        $loginField = 'mobile';
    } else if (StringUtil::isEmail($userName)) {
        $loginField = 'email';
    } else {
        $loginField = 'username';
    }
    $user = User::model()->fetch($loginField . ' = :name', array(':name' => $userName));
    if (!empty($user)) {
        $password = md5($password . $user['salt']);
        if (strcmp($user['password'], $password) != 0) {
            return array('isSuccess' => false, 'msg' => '身份验证失败，密码错误');
        }
        if (!$user['isadministrator']) {
            return array('isSuccess' => false, 'msg' => '非管理员身份不能进行此操作');
        }
        return array('isSuccess' => true);
    }
    return array('isSuccess' => false, 'msg' => '身份验证失败，不存在该用户');
}
