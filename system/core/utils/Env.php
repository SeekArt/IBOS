<?php

/**
 * 系统环境与变量处理工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 系统环境与变量处理工具类库
 * @package application.core.utils
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: env.php -1   $
 */

namespace application\core\utils;

use CException;

class Env
{

    /**
     * 手机浏览器列表
     * @staticvar array
     */
    private static $mobileBrowserList = array(
        'iphone', 'android', 'phone', 'mobile',
        'wap', 'netfront', 'java', 'opera mobi',
        'opera mini', 'ucweb', 'windows ce', 'symbian',
        'series', 'webos', 'sony', 'blackberry',
        'dopod', 'nokia', 'samsung', 'palmsource',
        'xda', 'pieplus', 'meizu', 'midp',
        'cldc', 'motorola', 'foma', 'docomo',
        'up.browser', 'up.link', 'blazer', 'helio',
        'hosin', 'huawei', 'novarra', 'coolpad',
        'webos', 'techfaith', 'palmsource', 'alcatel',
        'amoi', 'ktouch', 'nexian', 'ericsson',
        'philips', 'sagem', 'wellcom', 'bunjalloo',
        'maui', 'smartphone', 'iemobile', 'spice',
        'bird', 'zte-', 'longcos', 'pantech',
        'gionee', 'portalmmm', 'jig browser', 'hiptop',
        'benq', 'haier', '^lct', '320x320',
        '240x320', '176x220'
    );

    /**
     * 平板标识列表
     * @staticvar array
     */
    private static $padList = array(
        'pad', 'gt-p1000'
    );

    /**
     * 获取当前用户的IP
     * @return string
     */
    public static function getClientIp()
    {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return 'unknow';
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        if (getenv('HTTP_CLIENT_IP')) {
            $clientIp = getenv('HTTP_CLIENT_IP');
            $matcheClientIp = preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $clientIp);
            if ($matcheClientIp) {
                $ip = $clientIp;
            }
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        $ip = $ip == '::1' ? '127.0.0.1' : $ip;
        return $ip;
    }

    /**
     * ip限制访问
     * @param string $ip 要检查的ip地址
     * @return boolean 返回结果
     */
    public static function ipBanned($onlineip)
    {
        Cache::load('ipbanned');
        $ipBanned = Ibos::app()->setting->get('cache/ipbanned');
        if (empty($ipBanned)) {
            return false;
        } else {
            if ($ipBanned['expiration'] < TIMESTAMP) {
                Cache::update('ipbanned');
                Cache::load('ipbanned', true);
                $ipBanned = Ibos::app()->setting->get('cache/ipbanned');
            }
            return preg_match("/^(" . $ipBanned['regexp'] . ")$/", $onlineip);
        }
        return preg_match("/^(" . $ipBanned['regexp'] . ")$/", $onlineip);
    }

    /**
     * 检查是否以手机进入
     * @staticvar array $mobileBrowserList 手机所用的浏览器列表
     * @return boolean 是否以手机进入
     */
    public static function checkInMobile()
    {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
        // 先检查是否Pad
        if (StringUtil::istrpos($userAgent, self::$padList)) {
            return false;
        }
        $value = StringUtil::istrpos($userAgent, self::$mobileBrowserList, true);
        if ($value) {
            Ibos::app()->setting->set('mobile', $value);
            return true;
        }
        return false;
    }

    /**
     * 检查是否以手机APP进入
     * @return boolean 是否以手机APP进入
     */
    public static function checkInApp()
    {
        return defined('MODULE_NAME') && MODULE_NAME == 'mobile' ? true : false;
    }

    /**
     * 检查当前路由是否在后台
     * @return boolean 当前路由是否在后台
     * @author gzwwb
     */
    public static function checkInDashboard()
    {
        return defined('MODULE_NAME') && MODULE_NAME == 'dashboard' ? true : false;
    }

    /**
     * 刷新重定向
     * @param string $default 默认返回值
     * @return 处理后的重定向
     */
    public static function referer($default = '')
    {
        $referer = Ibos::app()->setting->get('referer');
        $default = empty($default) ? Ibos::app()->urlManager->createUrl('main/default/index') : $default;
        //前一页地址，如果存在$_GET参数，否则使用$_SERVER
        $referer = !empty($_GET['referer']) ? $_GET['referer'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $default);
        //请求的页面是登陆页,返回主页
        $loginPage = Ibos::app()->urlManager->createUrl('user/default/login');
        if (strpos($referer, $loginPage)) {
            $referer = $default;
        }
        // 安全过滤
        $referer = StringUtil::ihtmlSpecialChars($referer, ENT_QUOTES);
        $referer = strip_tags(str_replace('&amp;', '&', $referer));
        // 写入全局组件
        Ibos::app()->setting->set('referer', $referer);
        return $referer;
    }

    /**
     * 取出 get, post, cookie 当中的某个变量
     * @param string $k key 值
     * @param string $type 类型 默认是GP 即为 GET/POST
     * @return mixed
     */
    public static function getRequest($key, $type = 'GP', $defaultValue = null)
    {
        $type = strtoupper($type);
        $request = Ibos::app()->request;
        switch ($type) {
            case 'G':
                $var = $request->getQuery($key, $defaultValue);
                break;
            case 'P':
                $var = $request->getPost($key, $defaultValue);
                break;
            case 'C':
                $var = isset($_COOKIE[$key]) ? $_COOKIE[$key] : $defaultValue;
                break;
            default :
                $var = $request->getParam($key, $defaultValue);
                break;
        }
        return $var;
    }

    /**
     * 获取用户加密的8位身份标识字符串
     * @param string $specialadd
     * @return string
     */
    public static function formHash()
    {
        $global = Ibos::app()->setting->toArray();
        $hashAdd = defined('IN_DASHBOARD') ? 'Only For IBOS Admin DASHBOARD' : '';
        return substr(md5(substr($global['timestamp'], 0, -7) . Ibos::app()->user->uid . $global['authkey'] . $hashAdd), 8, 8);
    }

    /**
     * 检查提交来路
     * @param string $var 提交的变量
     * @param integer $allowGet 允许$_GET方式 默认为0
     * @return mixed 返回true变量正确提交，返回false没有这个变量,不是提交状态,错误提示
     * 即提交来源非法
     */
    public static function submitCheck($var, $allowGet = 0)
    {
        if (self::getRequest($var) === null) {
            return false;
        } else {
            // ---- 表单提交变量检查 ----
            // Ibos::app()->request->isPostRequest是否是post请求类型
            // 直接访问属性应该比调用方法快
            $isPostRequest = Ibos::app()->request->getIsPostRequest();
            $emptyFlashProtected = empty($_SERVER['HTTP_X_FLASH_VERSION']);
            $emptyReferer = isset($_SERVER['HTTP_REFERER']) ? empty($_SERVER['HTTP_REFERER']) : true;
            $formHash = Ibos::app()->request->getParam('formhash');
            $formHashCorrect = !empty($formHash) && $formHash == self::formHash();
            // -------------------------
            //$_SERVER['HTTP_REFERER']直接访问页面时没有
            //TODO 直接访问r=assignment/default/add&addsubmit=1会报错：Undefined index: HTTP_REFERER
            $formPostCorrect = ($isPostRequest && $formHashCorrect && $emptyFlashProtected && $emptyReferer);
            $refererEqualsHost = true;
//			if ( !$emptyReferer ) {
//				$refererEqualsHost = preg_replace( "/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER'] ) == preg_replace( "/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'] );
//			}
            // ----------------------
            if ($allowGet or ($formPostCorrect or $refererEqualsHost)) {
                return true;
            } else {
                throw new CException(Ibos::lang('Data type invalid', 'error'));
            }
        }
    }

    /**
     * 系统信息获取
     * @return array
     */
    public static function getSystemInfo()
    {
        $sizeArr = array(
            ini_get('upload_max_filesize'), ini_get('post_max_size'), ini_get('memory_limit'));
        $sizeArray = StringUtil::ConvertBytes($sizeArr);
        $minKey = 0;
        foreach ($sizeArray as $key => $row) {
            if ($row == min($sizeArray)) {
                $minKey = $key;
            }
        }
        $size = $sizeArr[$minKey];
        $info = array(
//            $str = '1024k';
// function abc($str) {
// return eval("return ".
//    preg_replace( '/b?/i', '',
//    preg_replace( '/gb?/i', ' * 1024 * 1024 * 1024',
//    preg_replace( '/mb?/i', ' * 1024 * 1024',
//    preg_replace( '/kb?/i', ' * 1024 ',$str ) ) ) )
// .";");
// }
// $a = abc($str);
// echo $a;
            'operating_system' => php_uname('s'),
            'runtime_environment' => $_SERVER["SERVER_SOFTWARE"],
            'php_runtime' => php_sapi_name(),
            'upload_size' => $size,
            'execution_time' => ini_get('max_execution_time'),
            'server_time' => date("Y-n-j H:i:s"),
            'beijing_time' => gmdate("Y-n-j- H:i:s", time() + 8 * 3600),
            'server_domain' => $_SERVER['SERVER_NAME'],
            'server_ip' => gethostbyname($_SERVER['SERVER_NAME']),
            'disk_space' => function_exists('disk_free_space') ? round((disk_free_space(".") / (1024 * 1024)), 2) . 'M' : "",
            'register_globals' => get_cfg_var("register_globals") == "1" ? "open" : "closed",
            'magic_quotes_gpc' => (1 === get_magic_quotes_gpc()) ? true : false,
            'magic_quotes_runtime' => (1 === get_magic_quotes_runtime()) ? true : false,
        );
        return $info;
    }

    /**
     * 打开一个socket连接，返回该对象以供后续处理
     * @param string $hostName 主机名
     * @param string $errno 错误编号
     * @param string $errstr 错误描述字符串
     * @param integer $port 端口
     * @param integer $timeout 超时连接
     * @return mixed 打开的对象
     */
    public static function getSocketOpen($hostName, &$errno, &$errstr, $port = 80, $timeout = 15)
    {
        $fp = '';
        if (function_exists('fsockopen')) {
            $fp = @fsockopen($hostName, $port, $errno, $errstr, $timeout);
        } elseif (function_exists('pfsockopen')) {
            $fp = @pfsockopen($hostName, $port, $errno, $errstr, $timeout);
        } elseif (function_exists('stream_socket_client')) {
            $fp = @stream_socket_client($hostName . ':' . $port, $errno, $errstr, $timeout);
        }
        return $fp;
    }

    /**
     * 获取当前访问者的客户端类型
     * @return string
     */
    public static function getVisitorClient()
    {
        //客户端类型，0：网站；1：手机版；2：Android；3：iPhone；3：iPad；3：win.Phone
        return '0';
    }

    //获取一条微博的来源信息
    public static function getFromClient($type = 0, $module = 'weibo')
    {
        if ($module != 'weibo') {
            $modules = Ibos::app()->getEnabledModule();
            if (isset($modules[$module])) {
                return '来自' . $modules[$module]['name'];
            } else {
                return '来自未知客户端';
            }
        }
        $type = intval($type);
        $clientType = array(
            0 => '来自网页',
            1 => '来自手机版',
            2 => '来自Android客户端',
            3 => '来自iPhone客户端',
            4 => '来自iPad客户端',
            5 => '来自win.Phone客户端',
            6 => '来自微信企业号',
        );

        //在列表中的
        if (in_array($type, array_keys($clientType))) {
            return $clientType[$type];
        } else {
            return $clientType[0];
        }
    }

    /**
     * 格式化header输出退出信息
     * @param string $msg
     */
    public static function iExit($msg = 0)
    {
        header('Content-Type:text/html; charset=' . CHARSET);
        exit($msg);
    }

    /**
     * 是否https链接
     * @return boolean
     */
    public static function isHttps()
    {
        return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;
    }

    /**
     * 获取站点url
     * @param boolean $isHttps
     * @param string $sitePath
     * @return string
     */
    public static function getSiteUrl($isHttps = false, $sitePath = '')
    {
        if (empty($sitePath)) {
            $phpself = self::getScriptUrl();
            $sitePath = substr($phpself, 0, strrpos($phpself, '/'));
        }
        if (ENGINE === 'SAAS') {
            $setting = Ibos::app()->setting->toArray();
            $return = $setting['setting']['unit']['systemurl'] . $sitePath . '/';
        } else {
            $return = StringUtil::ihtmlSpecialChars('http' . ($isHttps ? 's' : '') . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $sitePath . '/');
        }
        return $return;
    }

    /**
     * 获取当前脚本url
     * @return string
     * @throws CException
     */
    public static function getScriptUrl()
    {
        $phpSelf = '';
        $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
        if (basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
            $phpSelf = $_SERVER['SCRIPT_NAME'];
        } else if (basename($_SERVER['PHP_SELF']) === $scriptName) {
            $phpSelf = $_SERVER['PHP_SELF'];
        } else if (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
            $phpSelf = $_SERVER['ORIG_SCRIPT_NAME'];
        } else if (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
            $phpSelf = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
        } else if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
            $phpSelf = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            $phpSelf[0] != '/' && $phpSelf = '/' . $phpSelf;
        } else {
            throw new CException(Ibos::lang('Request tainting', 'error'));
        }
        return $phpSelf;
    }

}
