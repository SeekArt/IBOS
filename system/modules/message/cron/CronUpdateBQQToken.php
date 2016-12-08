<?php

use application\core\utils\Cache;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\message\core\IMFactory;

$value = Setting::model()->fetchSettingValueByKey('im');
$im = StringUtil::utf8Unserialize($value);
$neededUpgrade = false;
if (isset($im['qq'])) {
    $cfg = $im['qq'];
    // 如果已经通过检查
    if (isset($cfg['checkpass']) && $cfg['checkpass'] == '1') {
        // 必须确定是通过系统自动获取
        if (!empty($cfg['refresh_token']) && !empty($cfg['time'])) {
            $secs = TIMESTAMP - $im['time'];
            if (!empty($cfg['expires_in'])) {
                $leftsecs = $cfg['expires_in'] - $secs;
                if ($leftsecs / 86400 < 7) {
                    $neededUpgrade = true;
                }
            } else {
                $neededUpgrade = true;
            }
        }
    }
    if ($neededUpgrade) {
        $factory = new IMFactory();
        $adapter = $factory->createAdapter('application\modules\message\core\IMQq', $cfg);
        $api = $adapter->getApi();
        $infoJson = $api->getRefreshToken(array('app_id' => $cfg['appid'], 'app_secret' => $cfg['appsecret'], 'refresh_token' => $cfg['refresh_token']));
        if (is_array($infoJson)) {
            $info = null;
        } else {
            $info = CJSON::decode($infoJson, true);
        }
        /**
         * todo：这里没有做请求失败的情况，如果需要的话……
         */
        if (isset($info['ret']) && $info['ret'] == 0) {
            $cfg['token'] = $info['data']['company_token'];
            $cfg['refresh_token'] = $info['data']['refresh_token'];
            $cfg['expires_in'] = $info['data']['expires_in'];
            $cfg['time'] = time();
            $im['qq'] = $cfg;
            Setting::model()->updateSettingValueByKey('im', $im);
            Cache::update(array('setting'));
        }
    }
}
