<?php

use application\core\utils\Env;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;

// CORS 设置
$str = strtolower($_SERVER['SERVER_SOFTWARE']);
list($server) = explode('/', $str);

if ($server == "apache" || $server == "nginx" || $server == "lighttpd") {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    }
    header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, Authorization, ISCORS');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, DELETE');

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit();
    }
} else if ($server == "iis") {
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        // CORS 设置，有待讨论
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }
        header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, Authorization, ISCORS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, DELETE');
        exit();
    }
}

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../../../');
$defines = PATH_ROOT . '/system/defines.php';
define('YII_DEBUG', true);
define('TIMESTAMP', time());
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$mainConfig = require_once PATH_ROOT . '/system/config/common.php';
require_once($yii);
require_once '../../login.php';
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);

$userId = Env::getRequest('userid');
$timestamp = Env::getRequest('timestamp');
$redirect = Env::getRequest('redirect');
$signature = Env::getRequest('signature');
$deadline = Env::getRequest('deadline');
$op = Env::getRequest('op');

if (empty($deadline)) {
    $deadline = 60 * 60;
}
if (TIMESTAMP - $timestamp > $deadline) {
    Env::iExit('链接已经过期');
}

$aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
if (strcmp($signature, sha1($aeskey . $timestamp)) != 0) {
    Env::iExit('签名验证错误');
}
if ($op == "bind") {
    $from = "co";
    $userName = Env::getRequest('username');
    $password = Env::getRequest('password');
    $replace = Env::getRequest('replace');
    $check = checkAndBind($userName, $password, $userId, $from, $replace);
    if ($check == 1) {
        $msg = "用户已绑定，你可以输入新的账号绑定或者检查你的酷办公账号是否正确<br />";
        $url = "?userid={$userId}&timestamp={$timestamp}&redirect={$redirect}&signature={$signature}&username={$userName}&password={$password}&op=bind&replace=1";
        showBind($msg, $url);
        exit();
    }
    if ($check > 1) {
        $msg = "用户名或密码错误，请核对后重新输入<br />";
        showBind($msg);
        exit();
    }
}
checkBind($userId, $redirect);

function checkBind($userId, $redirect)
{
    $isSuccess = false;
    if (!empty($userId)) {
        $uid = UserBinding::model()->fetchUidByValue($userId, 'co');
        if (!empty($uid)) {
            $resArr = doLogin($uid);
            if ($resArr['code'] >= 0) {
                $isSuccess = true;
            }
        }
    }
    if (!empty($redirect)) {
        $url = rawurldecode($redirect);
        $parse = parse_url($url);
        if ($isSuccess) {
            if (isset($parse['scheme'])) {
                header('Location:' . $url, true);
                exit();
            } else {
                header('Location:../../../' . $url, true);
                exit();
            }
        } else {
            $msg = "你的企业账号可能还没有与酷办公绑定，请输入你的企业账号用户名密码来绑定你的账户<br />";
            showBind($msg);
            exit();
        }
    }
    Env::iExit(json_encode(array('isSuccess' => $isSuccess)));
}

function showBind($msg, $url = "")
{
    $str = '<!DOCTYPE html><html><head><meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width"><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>';
    $str .= '	<style>';
    $str .= '	body {background: #eee; line-height:1.4em;padding-top:20px;}';
    $str .= '	.spacer{clear:both; height:1px;}';
    $str .= '	.myform{margin:0 auto;width:400px;padding:14px;}';
    $str .= '	#stylized{border:solid 2px #cde;background:#f9fcff;}';
    $str .= '	h1 {font-size:18px;font-weight:bold;margin-bottom:8px;}';
    $str .= '	p{font-size:14px;color:#F95;margin-bottom:20px;border-bottom:solid 1px #b7ddf2;padding-bottom:10px;}';
    $str .= '	label{font-weight:bold;text-align:right;width:140px;float:left;clear:left;}';
    $str .= '	.small{display:block; text-align:right; color:#9bc;  font-size:11px; font-weight: 400;}';
    $str .= '	input{ float:left; width:200px; margin:2px 0 20px 10px; padding:8px; border:solid 1px #aacfe4; border-radius:5px; font-size:18px;}';
    $str .= '	button{ clear:both; display: block; margin-left:150px; width:125px; padding: 10px; border: 0 none; background:#6ac;color:#FFF;font-size:14px;font-weight:bold;border-radius:5px;}button:hover{background:#49c}';
    $str .= '	@media(max-width:430px){';
    $str .= '	body{padding-top: 0;  height: 800px;}';
    $str .= '	.myform{ width: auto }';
    $str .= '	label{ text-align: left; width: auto; float: none; }';
    $str .= '	.small{ display: inline; text-align: left; }';
    $str .= '	input{ box-sizing: border-box; width: 100%; float: none; display: block; margin-left: 0; }';
    $str .= '	button{ box-sizing: border-box; width: 100%; margin-left: 0; padding-top: 15px; padding-bottom: 15px; }   }';
    $str .= '	</style>';
    if ($url) {
        $str .= '	<script>  if(confirm("用户已被绑定其它酷办公账号，确定重新绑定新账号吗？")){location.href="' . $url . '";}';
        $str .= '	</script>';
    }
    $str .= '<div id="stylized" class="myform"><form name="form" method="post" ><h1>绑定企业账号</h1><p>' . $msg . '</p>';
    $str .= '<label>用户名<span class="small">填写你的企业账号用户名</span></label><input type="text" name="username" />';
    $str .= '<label>密码<span class="small">输入你的登录密码</span></label><input type="password" name="password" />';
    $str .= '<div class="spacer"></div><input type="hidden" name="op" value="bind" /><button type="submit">验证并绑定</button></form></div>';
    $str .= '</body></html>';
    echo $str;
}

/**
 *
 * @param string $userName 用户名
 * @param string $password 密码
 * @return 0 成功,1 用户已绑定,2 密码错误,3用户不存在
 */
function checkAndBind($userName, $password, $guid, $from, $replace = false)
{
    // 登录类型
    if (StringUtil::isMobile($userName)) {
        $loginField = 'mobile';
    } else if (StringUtil::isEmail($userName)) {
        $loginField = 'email';
    } else {
        $loginField = 'username';
    };
    $user = User::model()->fetch($loginField . ' = :name', array(':name' => $userName));
    if (!empty($user)) {
        $password = md5(md5($password) . $user['salt']);
        if (strcmp($user['password'], $password) == 0) {
            $userBind = UserBinding::model()->fetch('uid = :uid AND  app = :app', array(':uid' => $user['uid'], ':app' => $from));
            if (empty($userBind)) {
                //绑定与第三方对接，如酷办公
                UserBinding::model()->add(array('uid' => $user['uid'], 'bindvalue' => $guid, 'app' => $from));
                return 0;
            } elseif ($replace) {
                UserBinding::model()->modify($userBind['id'], array('uid' => $user['uid'], 'bindvalue' => $guid, 'app' => $from));
                return 0;
            } else {
                return 1;  //用户已绑定
            }
        }
        return 2;
        //密码错误
    }
    return 3; //用户不存在
}
