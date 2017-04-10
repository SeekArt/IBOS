<?php

use application\core\utils\Attach;
use application\core\utils\Ibos;
use application\modules\main\utils\Main;
use application\modules\user\utils\User;
use application\modules\main\model\Setting;

$gUploadConfig = Attach::getUploadConfig();
$gAccount = User::getAccountSetting();
$mods = Main::getModuleParamsForJs();
$qr = Ibos::app()->setting->get('setting/qrcode');
$qrcode = isset($qr) ? $qr : '';
$uid = Ibos::app()->user->isGuest ? 0 : Ibos::app()->user->uid;
$unitSerialize = Setting::model()->fetchSettingValueByKey('unit');
$unit = unserialize($unitSerialize);
$conf = array(
    'VERSION' => VERSION . ',' . VERSION_TYPE,
    'modules' => Ibos::app()->getEnabledModule(),
    'VERHASH' => VERHASH,
    'PLATFORM' => strtolower(ENGINE),
    'SITE_URL' => Ibos::app()->setting->get('siteurl'),
    'STATIC_URL' => STATICURL,
    'uid' => $uid,
    'shortname' => $unit['shortname'],
    'cookiePre' => Ibos::app()->setting->get('config/cookie/cookiepre'),
    'cookiePath' => Ibos::app()->setting->get('config/cookie/cookiepath'),
    'cookieDomain' => Ibos::app()->setting->get('config/cookie/cookiedomain'),
    'creditRemind' => Ibos::app()->setting->get('setting/creditnames'),
    'formHash' => FORMHASH,
    'settings' => array('notifyInterval' => 320),
    'contact' => User::getJsConstantUids($uid),
    'loginTimeout' => $gAccount['timeout'],
    'upload' => array(
        'attachexts' => array(
            'depict' => $gUploadConfig['attachexts']['depict'],
            'ext' => $gUploadConfig['attachexts']['ext']
        ),
        'imageexts' => array(
            'depict' => $gUploadConfig['imageexts']['depict'],
            'ext' => $gUploadConfig['imageexts']['ext']
        ),
        'hash' => $gUploadConfig['hash'],
        'limit' => $gUploadConfig['limit'],
        'max' => $gUploadConfig['max']
    ),
    'password' => array(
        'minLength' => $gAccount['minlength'],
        'maxLength' => 32,
        'regex' => $gAccount['preg']
    ),
    'mods' => $mods,
    'appDownloadPageUrl' => 'http://www.ibos.com.cn/home/download/mobile',
    'followWxUrl' => 'http://www.ibos.com.cn/api/show/qrcode?qrcode=' . $qrcode,
);

echo "var G = " . json_encode($conf);
