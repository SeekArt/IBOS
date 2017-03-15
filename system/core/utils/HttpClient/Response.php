<?php
/**
 * @namespace application\core\utils\HttpClient
 * @filename Response.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/12/21 11:39
 */

namespace application\core\utils\HttpClient;

use application\core\model\Log;
use application\core\utils\HttpClient\exception\ConnectFailedException;


/**
 * HTTP 响应类
 *
 * @package application\core\utils\HttpClient
 */
class Response
{
    protected $headers;
    protected $body;
    protected $reasonPhrase;
    protected $statusCode;

    private static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    );

    public function __construct($statusCode, array $headers, $body = null)
    {
        $this->setStatus($statusCode);

        $this->headers = $headers;

        if ($this->hasHeader('Content-Encoding') && strtolower($this->getHeader('Content-Encoding')) == 'gzip') {
            $body = gzdecode($body);
        }
        $this->body = $body;
    }

    /**
     * @param $headerName
     * @return false|string
     */
    public function getHeader($headerName)
    {
        if ($this->hasHeader($headerName)) {
            return $this->headers[$headerName];
        }

        return false;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $headerName
     * @return bool
     */
    public function hasHeader($headerName)
    {
        return isset($this->headers[$headerName]);
    }

    /**
     * @param integer $statusCode
     * @param string $reasonPhrase
     * @return $this
     */
    public function setStatus($statusCode, $reasonPhrase = '')
    {
        $this->statusCode = (int)$statusCode;

        if (!$reasonPhrase && isset(self::$statusTexts[$this->statusCode])) {
            $this->reasonPhrase = self::$statusTexts[$this->statusCode];
        } else {
            $this->reasonPhrase = $reasonPhrase;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}