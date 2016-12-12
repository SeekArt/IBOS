<?php

/**
 * 用户浏览器信息检测类
 *
 * @package application.core.components
 * @version $Id: Browser.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\components;

use CApplicationComponent;

class Browser extends CApplicationComponent
{

    /**
     * 浏览器名称
     * @var string
     * @access private
     */
    private $name;

    /**
     * 浏览器版本
     * @var string
     * @access private
     */
    private $version;

    /**
     * 用户所在系统平台
     * @var string
     * @access private
     */
    private $platform;

    /**
     * 用户接口识别的字符串，通过$_SERVER['HTTP_USER_AGENT']变量获得
     * @var string
     * @access private
     */
    private $userAgent;

    /**
     * 调用父类的初始化方法，然后检测用户的浏览信息
     */
    public function init()
    {
        parent::init();
        $this->detect();
    }

    /**
     * 通过$_SERVER['HTTP_USER_AGENT']检测用户浏览信息，分别赋值于四个私有变量
     * @access protected
     */
    protected function detect()
    {
        $userAgent = null;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        }
        if (preg_match('/opera/', $userAgent)) {
            $name = 'opera';
        } elseif (preg_match('/chrome/', $userAgent)) {
            $name = 'chrome';
        } elseif (preg_match('/apple/', $userAgent)) {
            $name = 'safari';
        } elseif (preg_match('/msie/', $userAgent)) {
            $name = 'msie';
        } elseif (preg_match('/mozilla/', $userAgent) && !preg_match('/compatible/', $userAgent)) {
            $name = 'mozilla';
        } else {
            $name = 'unrecognized';
        }
        if (preg_match('/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/', $userAgent, $matches)) { // Not Coding Standard
            $version = $matches[1];
        } else {
            $version = 'unknown';
        }
        if (preg_match('/linux/', $userAgent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/', $userAgent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/', $userAgent)) {
            $platform = 'windows';
        } else {
            $platform = 'unrecognized';
        }
        $this->name = $name;
        $this->version = $version;
        $this->platform = $platform;
        $this->userAgent = $userAgent;
    }

    /**
     * 获取浏览器名称
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取浏览器版本
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 获取用户系统
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * 获取用户代理接口信息
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

}
