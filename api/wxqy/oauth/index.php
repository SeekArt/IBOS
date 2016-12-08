<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;

// 程序根目录路径
define('PATH_ROOT', dirname(__FILE__) . '/../../../');
define('YII_DEBUG', true);
$defines = PATH_ROOT . '/system/defines.php';
defined('TIMESTAMP') or define('TIMESTAMP', time());
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$mainConfig = require PATH_ROOT . '/system/config/common.php';
require_once($yii);
require_once '../../login.php';
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);

$signature = Ibos::app()->getRequest()->getQuery('signature');
$aeskey = Setting::model()->fetchSettingValueByKey('aeskey');
$userId = Ibos::app()->getRequest()->getQuery('userid');
$isPost = Ibos::app()->getRequest()->getIsPostRequest();
$text = '详见 <a href = "http://doc.ibos.com.cn/article/detail/id/329" target="_blank" >文档中心</a>';
if (!$isPost && strcmp($signature, md5($aeskey . $userId)) != 0) {
    Env::iExit("签名错误:" . $text);
}
$msg = '';
if ($isPost) {
    $cookies = Ibos::app()->getRequest()->cookies['userid'];
    $userId = $cookies->value;
    if (!empty($userId)) {
        new CHttpCookie('userid', null, array('httpOnly' => true));
        $username = Ibos::app()->getRequest()->getPost('username');
        $password = Ibos::app()->getRequest()->getPost('password');
        if (StringUtil::isMobile($username)) {
            $loginField = 'mobile';
        } else if (StringUtil::isEmail($username)) {
            $loginField = 'email';
        } else {
            $loginField = 'username';
        }
        $user = User::model()->fetch($loginField . ' = :name', array(':name' => $username));
        if (!empty($user)) {
            $password = md5(md5($password) . $user['salt']);
            if (strcmp($user['password'], $password) == 0) {
                $hasBind = UserBinding::model()->fetch('uid = :uid AND bindvalue = :bindvalue AND  app = :app', array(':uid' => $user['uid'], ':app' => 'wxqy',':bindvalue' => $userId));
                if(empty($hasBind)){
                    $userBind = UserBinding::model()->fetch('(uid = :uid OR bindvalue = :bindvalue ) AND  app = :app', array(':uid' => $user['uid'], ':app' => 'wxqy',':bindvalue' => $userId));
                    if (!empty($userBind)) {
                        UserBinding::model()->deleteAll('(uid = :uid OR bindvalue = :bindvalue ) AND app = :app', array(':uid' => $user['uid'], ':app' => 'wxqy',':bindvalue' => $userId));
                    }
                    UserBinding::model()->add(array('uid' => $user['uid'], 'bindvalue' => $userId, 'app' => 'wxqy'));
                }
            } else {
                $msg = 'IBOS账号或者密码错误';
            }
        }
    }
}
if (!empty($userId)) {
    $cookie = new CHttpCookie('userid', $userId);
    $cookie->httpOnly = true;
    Ibos::app()->getRequest()->cookies['userid'] = $cookie;
    $uid = UserBinding::model()->fetchUidByValue($userId, 'wxqy');
    if ($uid) {
        $resArr = dologin($uid);
        if (!Ibos::app()->user->isGuest && $resArr['code'] > '0') {
            $redirect = Env::getRequest('redirect');
            $url = base64_decode($redirect);
            $parse = parse_url($url);
            if (isset($parse['scheme'])) {
                header('Location:' . $url, true);
                exit();
            } else {
                header('Location:../../../' . $url, true);
                exit();
            }
        } else {
            Env::iExit($resArr['msg']);
        }
    } else {
        showBind($msg);
        die;
    }
}
Env::iExit('用户验证失败,尝试以下步骤的操作：<br/>'
    . '1、在“微信企业号->通讯录”，找到并删除该用户<br/>'
    . '2、在“IBOS后台->微信->部门及用户同步”，同步该用户<br/>'
    . '3、邀请该用户关注企业号<br/>'
    . '如果还存在此提示，请将问题反馈给我们的工作人员：' . $text);

function showBind($msg = '')
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
    $str .= '<div id="stylized" class="myform"><form name="form" method="post" ><h1>绑定企业号</h1><p>' . $msg . '</p>';
    $str .= '<label>用户名<span class="small">填写你的IBOS用户名</span></label><input type="text" name="username" />';
    $str .= '<label>密码<span class="small">输入你登录IBOS的密码</span></label><input type="password" name="password" />';
    $str .= '<div class="spacer"></div><input type="hidden" name="op" value="bind" /><button type="submit">验证并绑定</button></form></div>';
    $str .= '</body></html>';
    echo $str;
}
