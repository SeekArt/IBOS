<?php

/**
 * BQQApi class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * IM组件QQ类的API实现方法，实现并提供调用各种企业QQAPI
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core
 * @version $Id$
 */

namespace application\modules\message\core;

use application\core\utils\Api;

class BQQApi
{

    // 获取企业基本信息
    const CORPORATION_BASE_URL = 'https://openapi.b.qq.com/api/corporation/get';
    // 刷新企业TOKEN
    const REFRESH_TOKEN_URL = 'https://openapi.b.qq.com/oauth2/companyRefresh';
    // 增加用户
    const ADD_ACCOUNT_URL = 'https://openapi.b.qq.com/api/dept/adduser';
    // 设置用户状态
    const SET_USERSTATUS_URL = 'https://openapi.b.qq.com/api/dept/setuserstatus';
    // 发送提醒
    const SEND_TIPS_URL = 'https://openapi.b.qq.com/api/tips/send';
    // 获取员工基本资料列表
    const USER_LIST_URL = 'https://openapi.b.qq.com/api/user/list';
    // 登录状态校验
    const VERIFY_HASH_URL = 'https://openapi.b.qq.com/api/login/verifyhashskey';

    /**
     * 公共参数
     * @var array
     */
    private $_publicParam = array(
        'company_id' => '{id}',
        'company_token' => '{token}',
        'app_id' => '{appid}',
        'client_ip' => '{ip}',
        'oauth_version' => '2',
    );

    /**
     * 构造函数，初始化公共参数里的可变变量
     * @param array $param
     */
    public function __construct($param = array())
    {
        $publicParam = &$this->_publicParam;
        foreach ($param as $key => $value) {
            if (isset($publicParam[$key])) {
                $publicParam[$key] = $value;
            }
        }
    }

    /**
     * 增加一个企业QQ账户
     * @link http://wiki.open.b.qq.com/api:api_dept_adduser 该api介绍
     * @param array $acountData 要增加的用户信息
     * @return array
     */
    public function addAccount($acountData)
    {
        return $this->fetchResult(self::ADD_ACCOUNT_URL, array_merge($this->_publicParam, $acountData), 'post');
    }

    /**
     * 设置用户账户状态
     * @link https://openapi.b.qq.com/api/dept/setuserstatus 该api介绍
     * @param string $openId 每个绑定用户的openId
     * @param string $flag 1为启用，2为禁用
     */
    public function setStatus($openId, $flag)
    {
        $param = array_merge(array('open_id' => $openId, 'status' => intval($flag)), $this->_publicParam);
        return $this->fetchResult(self::SET_USERSTATUS_URL, $param, 'post');
    }

    /**
     * 发送提醒
     * @link http://wiki.open.b.qq.com/api:tips_send 该api介绍
     * @param array $param 要配置的参数
     * @return array
     */
    public function sendNotify($param)
    {
        $param = array_merge($param, $this->_publicParam);
        return $this->fetchResult(self::SEND_TIPS_URL, $param, 'post');
    }

    /**
     * 获取企业基本信息
     * @link http://wiki.open.b.qq.com/api:corporation_get 该api介绍
     * @return array
     */
    public function getCorBase()
    {
        $url = $this->buildUrl(self::CORPORATION_BASE_URL);
        return $this->fetchResult($url);
    }

    /**
     * 获取验证状态
     * @link http://wiki.open.b.qq.com/api:login_verifyhashskey 该api介绍
     * @param array $param
     * @return array
     */
    public function getVerifyStatus($param)
    {
        $url = $this->buildUrl(self::VERIFY_HASH_URL, $param);
        return $this->fetchResult($url);
    }

    /**
     * 获取员工基本资料列表
     * @link http://wiki.open.b.qq.com/api:user_list 该api介绍
     * @param array $param
     * @return array
     */
    public function getUserList($param)
    {
        $url = $this->buildUrl(self::USER_LIST_URL, $param);
        return $this->fetchResult($url);
    }

    /**
     * 刷新企业QQ token
     * @link http://wiki.open.b.qq.com/api:oauth2_refresh 该api介绍
     * @param array $param
     * @return array
     */
    public function getRefreshToken($param)
    {
        $url = $this->buildUrl(self::REFRESH_TOKEN_URL, $param, false);
        return $this->fetchResult($url);
    }

    /**
     * 获取结果集
     * @param string $url 链接地址
     * @param array $param 附加参数
     * @param string $type 发送类型,get或者post
     * @return array 结果集
     */
    protected function fetchResult($url, $param = array(), $type = 'get')
    {
        return Api::getInstance()->fetchResult($url, $param, $type);
    }

    /**
     * 创建发送请求前的URL
     * @param string $url 基础URL，这个应该是常量里定义好的
     * @param array $param 附件在url后面的参数
     * @param boolean $includePublic 是否包含公共参数
     * @return string 创建后的URL
     */
    protected function buildUrl($url, $param = array(), $includePublic = true)
    {
        if ($includePublic) {
            $param = array_merge($this->_publicParam, $param);
        }
        return Api::getInstance()->buildUrl($url, $param);
    }

}
