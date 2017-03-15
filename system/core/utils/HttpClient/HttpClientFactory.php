<?php
/**
 * @namespace application\core\utils\HttpClient
 * @filename CactusFactory.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/12/22 17:19
 */

namespace application\core\utils\HttpClient;


class HttpClientFactory
{
    public static function create()
    {
        return new HttpClient();
    }
}