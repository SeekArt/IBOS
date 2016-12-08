<?php

/**
 * 官网服务api应用工具类
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
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

class WebSite extends Api
{

    const SITE_URL = 'http://www.ibos.com.cn/'; // 官网url

    /**
     * 单例调用方法
     * @return object
     */

    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 创建URL并返回
     * @param string $route
     * @param array $param
     * @return string
     */
    public function build($route, $param = array())
    {
        return $this->buildUrl($this->getUrl() . $route, $param);
    }

    /**
     * 获取API调用结果
     * @param string $route 路由
     * @param array $param 附加提交参数
     * @param string $method 提交方法
     * @return type
     */
    public function fetch($route, $param = array(), $method = 'get')
    {
        $url = $this->getUrl() . $route;
        return $this->fetchResult($url, $param, $method);
    }

    /**
     * 返回官网服务调用地址
     * @return string
     */
    public function getUrl()
    {
        return self::SITE_URL;
    }

}
