<?php

/**
 * curl请求错误代码工具类
 */

namespace application\core\utils;

class ApiCode extends System
{
    /**
     * curl错误的时候我也不知道写什么，所以只要curl错误就用CURL_ERROR统称，反正使用的时候用这个类常量就行了
     * 具体的curl错误代码以后慢慢加，可以参照curl的errno，
     * ps：这个目前在酷办公绑定的那里用上了
     */
    /**
     * 有生之年系列：下面的错误代码有待完善，注释掉的暂时缺失，等遇到了再加上去也不迟
     */

    /**
     * libcurl error codes
     * @link http://curl.haxx.se/libcurl/c/libcurl-errors.html
     */
    const CURL_ERROR = 'error';
    const CURLE_OK = '0';
    const CURLE_UNSUPPORTED_PROTOCOL = '1';
    const CURLE_FAILED_INIT = '2';
    const CURLE_URL_MALFORMAT = '3';
    const CURLE_NOT_BUILT_IN = '4';
    const CURLE_COULDNT_RESOLVE_PROXY = '5';
    const CURLE_COULDNT_RESOLVE_HOST = '6';
    const CURLE_COULDNT_CONNECT = '7';
    const CURLE_FTP_WEIRD_SERVER_REPLY = '8';
    const CURLE_REMOTE_ACCESS_DENIED = '9';
    const CURLE_FTP_ACCEPT_FAILED = '10';
    const CURLE_FTP_WEIRD_PASS_REPLY = '11';
    const CURLE_FTP_ACCEPT_TIMEOUT = '12';
    const CURLE_FTP_WEIRD_PASV_REPLY = '13';
    const CURLE_FTP_WEIRD_227_FORMAT = '14';
    const CURLE_FTP_CANT_GET_HOST = '15';
    const CURLE_HTTP2 = '16';
    const CURLE_FTP_COULDNT_SET_TYPE = '17';
    const CURLE_PARTIAL_FILE = '18';
    const CURLE_FTP_COULDNT_RETR_FILE = '19';
//	const CURLE_？？？？？ = '20';待查找
    const CURLE_QUOTE_ERROR = '21';
    const CURLE_HTTP_RETURNED_ERROR = '22';
    const CURLE_WRITE_ERROR = '23';
//	const CURLE_？？？？？ = '24';待查找
    const CURLE_UPLOAD_FAILED = '25';
    const CURLE_READ_ERROR = '26';
    const CURLE_OUT_OF_MEMORY = '27';
    const CURLE_OPERATION_TIMEDOUT = '28';
//	const CURLE_FTP_COULDNT_SET_ASCII = '29';
    const CURLE_FTP_PORT_FAILED = '30';
    const CURLE_FTP_COULDNT_USE_REST = '31';
//	const CURLE_FTP_COULDNT_GET_SIZE = '32';
    const CURLE_RANGE_ERROR = '33';
    const CURLE_HTTP_POST_ERROR = '34';
    const CURLE_SSL_CONNECT_ERROR = '35';
    const CURLE_BAD_DOWNLOAD_RESUME = '36';
    const CURLE_FILE_COULDNT_READ_FILE = '37';
    const CURLE_LDAP_CANNOT_BIND = '38';
    const CURLE_LDAP_SEARCH_FAILED = '39';
//	const CURLE_LIBRARY_NOT_FOUND = '40';
    const CURLE_FUNCTION_NOT_FOUND = '41';
    const CURLE_ABORTED_BY_CALLBACK = '42';
    const CURLE_BAD_FUNCTION_ARGUMENT = '43';
//	const CURLE_BAD_CALLING_ORDER = '44';
    const CURLE_INTERFACE_FAILED = '45';
//	const CURLE_BAD_PASSWORD_ENTERED = '46';
    const CURLE_TOO_MANY_REDIRECTS = '47';
    const CURLE_UNKNOWN_OPTION = '48';
    const CURLE_TELNET_OPTION_SYNTAX = '49';
//	const CURLE_OBSOLETE = '50';
    const CURLE_PEER_FAILED_VERIFICATION = '51';
    const CURLE_GOT_NOTHING = '52';
    const CURLE_SSL_ENGINE_NOTFOUND = '53';
    const CURLE_SSL_ENGINE_SETFAILED = '54';
    const CURLE_SEND_ERROR = '55';
    const CURLE_RECV_ERROR = '56';
//	const CURLE_SHARE_IN_USE = '57';
    const CURLE_SSL_CERTPROBLEM = '58';
    const CURLE_SSL_CIPHER = '59';
    const CURLE_SSL_CACERT = '60';
    const CURLE_BAD_CONTENT_ENCODING = '61';
    const CURLE_LDAP_INVALID_URL = '62';
    const CURLE_FILESIZE_EXCEEDED = '63';
    const CURLE_USE_SSL_FAILED = '64';

    private $curlMsg = array(
        self::CURL_ERROR => '请求失败',
        self::CURLE_OK => '请求成功',
        self::CURLE_OPERATION_TIMEDOUT => '请求超时',
        self::CURLE_COULDNT_RESOLVE_HOST => '无法解析主机',
    );

    /**
     * 单例化api
     * @return object
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 获取curl请求结果信息
     * @param type $curlNum
     * @param type $curlMsg
     * @param type $useLang
     * @return type
     */
    public function getCurlMsg($curlNum, $curlMsg, $useLang = true)
    {
        $contentA = explode(':', $curlMsg);
        array_shift($contentA);
        $content = !empty($contentA) ? '：' . trim(implode(',', $contentA)) : '';
        if (false === $useLang) {
            return $curlMsg;
        } else {
            if (isset($this->curlMsg[$curlNum])) {
                return $this->curlMsg[$curlNum] . $content;
            } else {
                return $curlMsg;
            }
        }
    }

}
