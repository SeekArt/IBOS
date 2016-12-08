<?php

/**
 * 云服务api应用工具类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 提供云服务api curl的连接调用
 * @package application.core.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

class Cloud extends Api
{

    const IBOS_KEY = '3569c4ee701cb512fef319fc16ec88af';

    protected $setting = array();

    /**
     * 单例调用方法
     * @return object
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 缓存设置
     */
    public function __construct()
    {
        $setting = Ibos::app()->setting->get('setting/iboscloud');
        $this->setSetting($setting);
    }

    /**
     * 获取配置
     * @return array
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * 设置配置
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;
    }

    /**
     * 是否开启了云服务API
     * @return boolean
     */
    public function isOpen()
    {
        $setting = $this->getSetting();
        return $setting['isopen'] == '1';
    }

    /**
     * 创建URL并返回
     * @param string $route
     * @param array $param
     * @return string
     */
    public function build($route, $param = array())
    {
        $data = array_merge($this->getCloudAuthParam(), $param);
        return $this->buildUrl($this->getUrl() . $route, $data);
    }

    /**
     *
     * @param array $param
     * @return string
     */
    public function fetchPush($param)
    {
        $url = 'http://api.ibos.cn/v1/push/receive';
        $url = $this->buildUrl($url, $this->getApiAuthParam());
        return $this->fetchResult($url, json_encode($param), 'post');
    }

    /**
     * 获取API调用结果
     * @param string $route 路由
     * @param array $param 附加提交参数
     * @param string $method 提交方法
     * @return string
     */
    public function fetch($route, $param = array(), $method = 'get')
    {
        $data = array_merge($this->getCloudAuthParam(), $param);
        $url = $this->getUrl() . $route;
        return $this->fetchResult($url, $data, $method);
    }

    /**
     * 检测某个API是否存在
     * @staticvar array $setting
     * @param string $apiName Api名称
     * @return boolean 存在与否
     */
    public function exists($apiName)
    {
        $setting = $this->getSetting();
        if (!empty($setting['apilist'])) {
            foreach ($setting['apilist'] as $api) {
                if (strcmp($apiName, $api['name']) == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 返回setting里记录的云服务调用api地址
     * @return string
     */
    public function getUrl()
    {
        $setting = $this->getSetting();
        return $setting['url'];
    }

    /**
     * 返回认证签名参数
     * @param boolean $build 是否建立为url参数的字符串链接
     * @return mixed
     */
    public function getApiAuthParam($build = false)
    {
        $time = TIMESTAMP;
        $data = array(
            'platform' => 'ibos',
            'timestamp' => $time
        );
        $param['sign'] = $this->createApiSignature($data);
        $param['method'] = 'md5';
        $param = array_merge($data, $param);
        return $build ? http_build_query($param) : $param;
    }

    /**
     * 创建签名
     * @return string 生成签名后的字符
     */
    private function createApiSignature($data)
    {
        foreach ($data as $key => $value) {
            $arr[] = $key . '=' . $value;
        }
        return md5(implode('&', $arr) . self::IBOS_KEY);
    }

    /**
     * 返回认证签名参数
     * @param boolean $build 是否建立为url参数的字符串链接
     * @return mixed
     */
    public function getCloudAuthParam($build = false)
    {
        $setting = $this->getSetting();
        $time = TIMESTAMP;
        $param = array(
            'appid' => $setting['appid'],
            'signature' => $this->createCloudSignature($setting['appid'], $setting['secret'], $time),
            'timestamp' => $time
        );
        return $build ? http_build_query($param) : $param;
    }

    /**
     * 创建签名
     * @return string 生成签名后的字符
     */
    private function createCloudSignature($id, $secret, $time = null)
    {
        return strtoupper(md5($id . $secret . ($time == null ? TIMESTAMP : $time)));
    }

}
