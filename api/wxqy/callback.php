<?php

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Tasks;
use application\modules\file\core\FileCloud;

define('PATH_ROOT', dirname(__FILE__) . '/../../');
$defines = PATH_ROOT . '/system/defines.php';
defined('TIMESTAMP') || define('TIMESTAMP', time());
defined('YII_DEBUG') || define('YII_DEBUG', true);
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$mainConfig = require PATH_ROOT . '/system/config/common.php';
require_once($yii);
require_once '../login.php';
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $mainConfig);
// callback类型
$type = Env::getRequest('type');
// callback参数
$param = Env::getRequest('param');
$config = @include PATH_ROOT . '/system/config/config.php';
if (empty($config)) {
    close(Ibos::Lang('Config not found', 'error'));
} else {
    define('IN_MOBILE', Env::checkInMobile());
    $global = array(
        'clientip' => Env::getClientIp(),
        'config' => $config,
        'timestamp' => time()
    );
    Ibos::app()->setting->copyFrom($global);
// 加载系统缓存以初始化用户组件
    LoadSysCache();
    if (!Ibos::app()->user->isGuest) {
        switch ($type) {
            case 'attach':
                $userId = Env::getRequest('userid');
                $appId = Env::getRequest('appid');
                doAttachDownload($userId, $appId, $param);
                break;
            case 'todo':
                completeTodo($param);
                break;
            case 'quicklogin':
                doquicklogin($param);
                break;
            default:
                break;
        }
    } else {
        close('身份信息已经过期，请重新请求');
    }
}

function close($msg)
{
    $exit = <<<EOT
<script>
document.addEventListener("WeixinJSBridgeReady", function(){
    if(window.confirm('{$msg}')){
        WeixinJSBridge.invoke('closeWindow',{},function(res){
        });  
    }
    }, false);
            
</script>
EOT;
    Env::iExit($exit);
}

/**
 * 完成一个待办
 * @param mixed $id
 * @return void
 */
function completeTodo($id)
{
    Tasks::model()->modifyTasksComplete($id, 1);
    Tasks::model()->updateCalendar($id, 1);
    return close('已经完成该任务，需要关闭页面吗？');
}

/**
 * 执行快速登录触发操作：写入内容给监听的txt文件，然后前端可触发事件
 * @param string $code
 * @return void
 */
function doquicklogin($code)
{
    $file = PATH_ROOT . './data/temp/login_' . $code . '.txt';
    $uid = Ibos::app()->user->uid;
    file_put_contents($file, StringUtil::authCode($uid, 'ENCODE', $code));
    return close('登录成功，请关闭窗口');
}

/**
 * 处理附件下载
 * @param string $userId 微信用户ID
 * @param integer $appId 应用ID
 * @param string $aid 带附件类型的附件ID字符串
 * @return mixed
 */
function doAttachDownload($userId, $appId, $aid)
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $isIphone = (strpos($agent, 'iphone')) ? true : false;
    list($type, $id) = explode('/', $aid);
    if ($type == 'cloud') {
        list($id, $cloud) = explode('-', $id);
    } else {
        $cloud = 0;
    }

    $attachs = Attach::getAttachData($id);
    $attach = array_shift($attachs);
    if ($attach['uid'] != Ibos::app()->user->uid) {
        return close('您没有权限下载此文件');
    }
    $filepath = File::fileName(File::getAttachUrl() . '/' . $attach['attachment']);
    if ($cloud) {
        $core = new FileCloud($cloud);
        $url = $core->getRealUrl($filepath);
    } else {
        $url = Ibos::app()->request->getHostInfo() . '/' . $filepath;
    }
    if ($isIphone) {
        header('Location:' . $url, true);
        exit();
    } else {
        Env::iExit("<h1>微信现只支持IOS系统在微信内打开下载，请长按链接选择打开或者复制下载链接到手机浏览器下载<br/>$url</h1>");
    }
}
