<?php

use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;

function getScriptUrl()
{
    $phpSelf = '';
    $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
    if (basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
        $phpSelf = $_SERVER['SCRIPT_NAME'];
    } else if (basename($_SERVER['PHP_SELF']) === $scriptName) {
        $phpSelf = $_SERVER['PHP_SELF'];
    } else if (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
        $phpSelf = $_SERVER['ORIG_SCRIPT_NAME'];
    } else if (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
        $phpSelf = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
    } else if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
        $phpSelf = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
        $phpSelf[0] != '/' && $phpSelf = '/' . $phpSelf;
    } else {
        throw new Exception(Ibos::lang('Request tainting', 'error'));
    }
    return $phpSelf;
}

function geturl()
{
    $phpself = getScriptUrl();
    $isHTTPS = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;
    $url = StringUtil::ihtmlSpecialChars('http' . ($isHTTPS ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $phpself);
    return $url;
}

// 根目录
define('PATH_ROOT', dirname(__FILE__) . '/../../');
define('TIMESTAMP', time());
define('YII_DEBUG', true);
$defines = PATH_ROOT . '/system/defines.php';
$yii = PATH_ROOT . '/library/yii.php';
require_once($defines);
$config = PATH_ROOT . '/system/config/common.php';

require_once($yii);
Yii::setPathOfAlias('application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system');
$ibos = Yii::createApplication('application\core\components\Application', $config);
$value = Setting::model()->fetchSettingValueByKey('im');
$im = StringUtil::utf8Unserialize($value);
$imCfg = $im['qq'];
define('OAUTH2_TOKEN', 'https://openapi.b.qq.com/oauth2/token');
define('OPEN_CALLBACKURL', geturl()); //此URL需要登记到企业QQ
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $query = array(
        'grant_type' => 'authorization_code',
        'app_id' => $imCfg['appid'],
        'app_secret' => $imCfg['appsecret'],
        'code' => $code,
        'state' => md5(rand()),
        'redirect_uri' => OPEN_CALLBACKURL,
    );

    $options = array(
        CURLOPT_RETURNTRANSFER => true, // 返回页面内容
        CURLOPT_HEADER => false, // 不返回头部
        CURLOPT_ENCODING => "", // 处理所有编码
        CURLOPT_USERAGENT => "spider", //
        CURLOPT_AUTOREFERER => true, // 自定重定向
        CURLOPT_CONNECTTIMEOUT => 15, // 链接超时时间
        CURLOPT_TIMEOUT => 120, // 超时时间
        CURLOPT_MAXREDIRS => 10, // 超过十次重定向后停止
        CURLOPT_POST => 0, // 是否post提交数据
        CURLOPT_POSTFIELDS => "", // post的值
        CURLOPT_SSL_VERIFYHOST => 0, // 不检查ssl链接
        CURLOPT_SSL_VERIFYPEER => false, //
        CURLOPT_VERBOSE => 1 //
    );

    $url = OAUTH2_TOKEN . '?' . http_build_query($query);
    $curl = curl_init($url);
    if (curl_setopt_array($curl, $options)) {
        $result = curl_exec($curl);
    }
    curl_close($curl);

    if (false !== $result) {
        $company_info = CJSON::decode($result, true);
        if ($company_info['ret'] == '0') {
            $data = $company_info['data'];
            $imCfg['id'] = $data['open_id'];
            $imCfg['token'] = $data['access_token'];
            $imCfg['refresh_token'] = $data['refresh_token'];
            $imCfg['expires_in'] = $data['expires_in'];
            $imCfg['time'] = time();
            $im['qq'] = $imCfg;
            Setting::model()->updateSettingValueByKey('im', $im);
            Cache::update(array('setting'));
            echo json_encode(array('ret' => 0));
            die();
        }
    }
}

echo json_encode(array('ret' => -1));
die();
