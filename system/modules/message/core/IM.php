<?php

/**
 * IM class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 抽象IM组件类，全局IM组件的基类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core
 * @version $Id$
 */

namespace application\modules\message\core;

use CApplicationComponent;

abstract class IM extends CApplicationComponent
{

    const ERROR_INIT = 0;
    const ERROR_PUSH = 1;
    const ERROR_SYNC = 2;
    const ERROR_UNKNOWN = 3;

    /**
     * 每个适配器的配置数组
     * @var array
     */
    protected $config = array();

    /**
     * 可能出现的错误信息，按const定义的类型推进该error数组
     * @var string
     */
    protected $error = array();

    /**
     * 处理的用户
     * @var type
     */
    protected $uid = array();

    /**
     * 推送类型
     * @var string
     */
    protected $pushType = '';

    /**
     * 推送的内容
     * @var string
     */
    protected $message = '';

    /**
     * 点击跳转的url
     * @var string
     */
    protected $url = '';

    /**
     * 抽象检查函数，检查该适配器是否可用
     */
    abstract function check();

    /**
     * 抽象推送函数
     */
    abstract function push();

    /**
     * 抽象同步用户函数
     */
    abstract function syncUser();

    /**
     * 同步组织架构
     */
    abstract function syncOrg();

    /**
     * 构建函数，设置适配器的配置数组
     * @param array $config
     */
    public function __construct($config)
    {
        $this->setConfig($config);
    }

    /**
     * 设置同步标识
     * @param integer $flag
     */
    public function setSyncFlag($flag)
    {
        $this->syncFlag = intval($flag);
    }

    /**
     * 返回同步标识
     * @return integer
     */
    public function getSyncFlag()
    {
        return $this->syncFlag;
    }

    /**
     * error setter方法
     * @param string $msg
     * @param integer $errorLevel 错误等级
     */
    public function setError($msg, $errorLevel = self::ERROR_INIT)
    {
        $this->error[$errorLevel][] = $msg;
    }

    /**
     * 返回错误消息数组
     * @param integer $level 错误等级
     * @return array
     */
    public function getError($level = null)
    {
        return empty($level) ? $this->error : (isset($this->error[$level]) ? $this->error[$level] : array('Unknow error'));
    }

    /**
     * 设置配置数组
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * 返回配置数组
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 设置推送类型
     * @param string $type
     */
    public function setPushType($type)
    {
        $this->pushType = $type;
    }

    /**
     * 返回推送类型
     * @return string
     */
    public function getPushType()
    {
        return $this->pushType;
    }

    /**
     * 设置推送的用户ID
     * @param array $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * 获取推送的用户ID
     * @return array
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * 设置要推送的内容
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * 返回推送内容
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 设置URL
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * 获取URL
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * 检查某个值是否开启
     * @param string $key
     * @return boolean
     */
    protected function isEnabled($key)
    {
        $cfg = $this->getConfig();
        $key = explode('/', $key);
        $v = &$cfg;
        foreach ($key as $k) {
            if (!isset($v[$k])) {
                return false;
            }
            $v = &$v[$k];
        }
        return $v ? true : false;
    }

}
