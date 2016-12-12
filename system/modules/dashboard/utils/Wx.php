<?php

/**
 * Weixin.class.file
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 后台模块函数库类，提供全局静态方法调用
 * @package application.modules.dashboard.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\utils;

use application\core\utils\Cache;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\System;
use application\core\utils\WebSite;
use application\modules\dashboard\model\Nav;
use application\modules\dashboard\model\Page;
use application\modules\main\model\Setting;
use Yii;

class Wx extends System
{

    const BINDING_ROUTE = 'Api/WxCorp/loginView';

    private $_aeskey;
    private $_websiteUid;

    /**
     * 单例调用方法
     * @return object
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * aeskey设置
     */
    public function __construct()
    {
        $this->_aeskey = Yii::app()->setting->get('setting/aeskey');
        $this->_websiteUid = Yii::app()->setting->get('setting/websiteuid');
    }

    /**
     * 设置aeskey
     */
    public function setAeskey($aeskey)
    {
        $this->_aeskey = $aeskey;
    }

    /**
     * 获取aeskey
     * @return string
     */
    public function getAeskey()
    {
        return $this->_aeskey;
    }

    /**
     * 获取项目地址
     * @return type
     */
    public function getSiteUrl()
    {
        return rtrim(Ibos::app()->setting->get('siteurl'), '/');
    }

    /**
     * 获取官网绑定视图地址
     * @param string $aeskey客户端aeskey ，初次安装的话需要传此值来获取绑定路由
     * @param string $domain OA地址
     * @param boolean $isInstall 是否是安装时的请求
     * @return string
     */
    public function getBindingSrc($aeskey = null, $domain = null, $isInstall = false)
    {
        $aeskey = array('aeskey' => is_null($aeskey) ? $this->getAeskey() : $aeskey);
        $domain = array('domain' => is_null($domain) ? $this->getSiteUrl() : $domain);
        $siteroot = array('siteroot' => $isInstall ? '' : Ibos::app()->setting->get('siteroot'));
        $param = array_merge($aeskey, $siteroot, $domain);
        return WebSite::getInstance()->build(self::BINDING_ROUTE, $param);
    }
}
