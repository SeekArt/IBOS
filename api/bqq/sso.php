<?php

use application\core\utils\Env;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\message\core\BQQApi;
use application\modules\user\model\UserBinding;

// 根目录
define('PATH_ROOT', dirname(__FILE__) . '/../../');
define('TIMESTAMP', time());
define('YII_DEBUG', true);
$defines = PATH_ROOT . '/system/defines.php';
$yii = PATH_ROOT . '/library/yii.php';

require_once($defines);
$config = PATH_ROOT . '/system/config/common.php';

require_once($yii);
require_once '../login.php';
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
Yii::createApplication('application\core\components\Application', $config);
$value = Setting::model()->fetchSettingValueByKey('im');
$im = StringUtil::utf8Unserialize($value);
$imCfg = $im['qq'];
$cid = Env::getRequest('company_id');
$openId = Env::getRequest('open_id');
$hashskey = Env::getRequest('hashskey');
$hashkey = Env::getRequest('hashkey');
$returnurl = Env::getRequest('returnurl');

if (empty($openId) || empty($hashskey) || empty($cid)) {
    exit('参数错误');
}

$uid = UserBinding::model()->fetchUidByValue(StringUtil::filterCleanHtml($openId), 'bqq');
if ($uid) {
    $properties = array(
        'company_id' => $cid,
        'company_token' => $imCfg['token'],
        'app_id' => $imCfg['appid'],
        'client_ip' => Env::getClientIp()
    );
    $api = new BQQApi($properties);
    $res = $api->getVerifyStatus(array('open_id' => $openId, 'hashskey' => $hashskey));
    /**
     * 原先代码里上面的res直接是status，并且没有下面的is_array的判断和json解码，
     * 这里涉及到php的一个奇怪的特点：php不喜欢字符串。这里不做讨论
     */
    /**
     * 这里返回的是一个json！
     */
    if (is_array($res)) {
        Env::iExit($res['error']);
    }
    /**
     * 这里还是加了第二个参数，因为这样更清晰
     */
    $status = CJSON::decode($res, true);
    if ($status['ret'] == '0') {
        doLogin($uid, 'bqqsso');
        if ($returnurl == 'index') {
            header('Location: ../../index.php', true);
        } else {
            $url = parse_url($returnurl);
            if (isset($url['scheme'])) {
                header('Location:' . $returnurl, true);
            } else {
                header('Location:../../' . $returnurl, true);
            }
        }
    } else {
        Env::iExit($status['msg']);
    }
} else {
    Env::iExit('该用户未绑定企业QQ');
}
