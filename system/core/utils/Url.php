<?php

/**
 * Url工具类
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 提供url处理
 *
 * @package application.core.utils
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @version $Id: Url.php 3501 2014-06-06 03:07:31Z gzhzh $
 */

namespace application\core\utils;

class Url
{

    /**
     * 根据路由获取站内或者站外地址
     * @param string $url 路由地址
     * @return string
     */
    public static function getUrl($url)
    {
        if (count(explode('/', $url)) == 3 && !preg_match("/^http/iUs", $url)) {
            $url = Ibos::app()->urlManager->createUrl($url);
        } else {
            $urlInfo = parse_url($url);
            $url = isset($urlInfo['scheme']) ? $url : 'http://' . $url;
        }
        return str_replace('"', "'", $url);
    }

}
