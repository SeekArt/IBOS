<?php

/**
 *
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\event;

use application\core\utils\Ibos;
use application\modules\message\core\wx\WxApi;

class HelperScan extends ScanEvent
{

    /**
     * 插入记录
     */
    public function handle()
    {
        $result = $this->getScanResult();
        $file = PATH_ROOT . './data/temp/login_' . $result . '.txt';
        if (!file_exists($file)) {
            fopen($file, 'a+');
        } else {
            touch($file);
        }
        $url = WxApi::getInstance()->createOauthUrl(WxApi::getInstance()->getHostInfo() . '/api/wxqy/callback.php?type=quicklogin&param=' . $result, $this->getAppId());
        return $this->resText('HI,' . Ibos::app()->user->realname . ",请<a href='{$url}'>点击确认登录</a>");
    }

}
