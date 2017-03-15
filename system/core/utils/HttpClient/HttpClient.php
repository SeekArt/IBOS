<?php
/**
 * @namespace application\core\utils\HttpClient
 * @filename Client.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/12/21 11:38
 */

namespace application\core\utils\HttpClient;

use application\core\model\Log;
use application\core\utils\HttpClient\exception\ConnectFailedException;
use application\core\utils\Ibos;

/**
 * HTTP 客户端
 *
 * @package application\core\utils\HttpClient
 */
class HttpClient
{
    const GET = 'GET';
    const PUT = 'PUT';
    const POST = 'POST';
    const DELETE = 'DELETE';
    const HEAD = 'HEAD';
    const CONNECT = 'CONNECT';
    const OPTIONS = 'OPTIONS';
    const TRACE = 'TRACE';
    const PATCH = 'PATCH';

    /**
     * 请求超时时间，单位：秒
     *
     * @var int
     */
    protected $timeout = 50;

    /**
     * 默认 User-Agent
     *
     * @var string
     */
    protected $defaultUserAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36';

    /**
     * 默认 headers
     *
     * @var array
     */
    protected $defaultHeaders = array();

    /**
     * 是否开启 gzip
     *
     * @var bool
     */
    protected $gzip = true;

    /**
     * 发送 HTTP 请求
     *
     * @param string $method 请求方法
     * @param string $uri 请求地址
     * @param array $headers http header
     * @param null|string|array $body http body
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function request($method, $uri, array $headers = array(), $body = null, array $options = array())
    {
        $this->beforeRequest();

        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $resp = $this->requestWithCurl($method, $uri, $headers, $body, $options);
        } else {
            $resp = $this->requestWithSocket($method, $uri, $headers, $body);
        }

        $this->afterRequest();

        return $resp;
    }

    /**
     * 发送 HTTP 请求前执行的操作
     */
    protected function beforeRequest()
    {
        $this->initHeaders();
    }

    /**
     * 发送 HTTP 请求后执行的操作
     */
    protected function afterRequest()
    {
        $this->resetHeaders();
    }


    /**
     * 发送 HTTP GET 请求
     *
     * @param string $uri
     * @param array|null $headers
     * @param array $options
     * @return Response
     */
    public function get($uri, $headers = array(), $options = array())
    {
        return $this->request(static::GET, $uri, $headers, null, $options);
    }

    /**
     * 发送 HTTP HEAD 请求
     *
     * @param string $uri
     * @param null|array $headers
     * @param array $options
     * @return Response
     */
    public function head($uri, $headers = array(), array $options = array())
    {
        return $this->request(static::HEAD, $uri, $headers, null, $options);
    }

    /**
     * 发送 HTTP DELETE 请求
     *
     * @param string $uri
     * @param null|array $headers
     * @param null|string $body
     * @param array $options
     * @return Response
     */
    public function delete($uri, $headers = array(), $body = null, array $options = array())
    {
        return $this->request(static::DELETE, $uri, $headers, $body, $options);
    }

    /**
     * 发送 HTTP PUT 请求
     *
     * @param string $uri
     * @param null|array $headers
     * @param null $body
     * @param array $options
     * @return Response
     */
    public function put($uri, $headers = array(), $body = null, array $options = array())
    {
        return $this->request(static::PUT, $uri, $headers, $body, $options);
    }

    /**
     * 发送 HTTP PATCH 请求
     *
     * @param string $uri
     * @param null|array $headers
     * @param null|array $body
     * @param array $options
     * @return Response
     */
    public function patch($uri, $headers = array(), $body = null, array $options = array())
    {
        return $this->request(static::PATCH, $uri, $headers, $body, $options);
    }

    /**
     * 发送 HTTP POST 请求
     *
     * @param string $uri
     * @param null|array $headers
     * @param null|array $body
     * @param array $options
     * @return Response
     */
    public function post($uri, $headers = array(), $body = null, array $options = array())
    {
        return $this->request(static::POST, $uri, $headers, $body, $options);
    }

    /**
     * 发送 HTTP OPTIONS 请求
     *
     * @param string $uri
     * @param array $options
     * @return Response
     */
    public function options($uri, array $options = array())
    {
        return $this->request(static::OPTIONS, $uri, null, null, $options);
    }

    /**
     * 格式化 http header，便于处理
     *
     * Example: formatHeaders(array('Host: www.qq.com')) returns array('Host': 'www.qq.com')
     *
     * @param array $headers
     * @return array
     */
    protected function formatHeaders(array $headers)
    {
        $newHeaders = array();
        foreach ($headers as $header) {
            if (strpos($header, ':') === false || !is_string($header)) {
                continue;
            }
            list($headerName, $headerValue) = explode(':', $header, 2);
            $newHeaders[$headerName] = trim($headerValue);
        }

        return $newHeaders;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param boolean $gzip
     */
    public function setGzip($gzip)
    {
        $this->gzip = $gzip;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->defaultUserAgent = $userAgent;
    }

    /**
     * @param string $headerName
     * @param string $headerValue
     */
    public function saveHeader($headerName, $headerValue)
    {
        $this->defaultHeaders[$headerName] = $headerValue;
    }

    /**
     * @param string $headerName
     * @return bool
     */
    public function removeHeader($headerName)
    {
        if (isset($this->defaultHeaders[$headerName])) {
            unset($this->defaultHeaders[$headerName]);
            return true;
        }

        return false;
    }

    /**
     * 使用 CURL 扩展发送 HTTP 请求
     *
     * @param string $method 请求方法
     * @param string $uri 请求地址
     * @param array $headers http header
     * @param null|string|array $body http body
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    private function requestWithCurl($method, $uri, $headers = array(), $body = null, array $options = array())
    {
        $ch = curl_init();

        $uriInfoArr = $this->parseUri($uri);

        // header 处理
        $headers = array_merge($headers, array('Host' => $uriInfoArr['host']));
        $headers = array_merge($this->defaultHeaders, $headers);
        $headers = array_map(function ($headerName, $headerValue) {
            return sprintf('%s: %s', $headerName, $headerValue);
        }, array_keys($headers), $headers);


        // Array of default cURL options.
        $curlOptions = array(
            CURLOPT_URL => $uriInfoArr['requestUrl'],
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_PORT => $uriInfoArr['port'],
            CURLOPT_HTTPHEADER => $headers,

        );

        // Specify settings according to the HTTP method
        if ($method == static::GET) {
            $curlOptions[CURLOPT_HTTPGET] = true;
        } elseif ($method == static::HEAD) {
            $curlOptions[CURLOPT_NOBODY] = true;
            // HEAD requests do not use a write function
            unset($curlOptions[CURLOPT_WRITEFUNCTION]);
        } else {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
            $curlOptions[CURLOPT_POST] = 1;

            if (is_string($body)) {
                parse_str($body, $body);
            }
            $curlOptions[CURLOPT_POSTFIELDS] = $body;
        }

        if (!empty($options)) {
            $curlOptions = array_merge($curlOptions, $options);
        }

        curl_setopt_array($ch, $curlOptions);
        $response = curl_exec($ch);

        Log::write(array('uri' => $uri, 'raw_resp' => base64_encode($response)));

        // CURL 错误代码：https://curl.haxx.se/libcurl/c/libcurl-errors.html
        $errorNo = curl_errno($ch);
        if ($errorNo != CURLE_OK) {
            Log::write(array(
                'msg' => sprintf('Curl error no: %d, url: %s', $errorNo, $uri),
                'trace' => debug_backtrace(),
            ), 'action', 'application.core.utils.HttpClient.requestWithCurl');
            throw new ConnectFailedException(Ibos::lang('Network error', 'error', array('{code}' => $errorNo)));
        }

        $responseHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $responseHeader = explode("\r\n", substr($response, 0, $responseHeaderSize));

        // 忽略 HTTP 请求头和 empty header
        $responseHeader = array_filter($responseHeader, function ($item) {
            if (strcasecmp(substr($item, 0, 5), 'HTTP/') === 0 || empty($item)) {
                return false;
            }
            return true;
        });

        $responseBody = substr($response, $responseHeaderSize);

        return new Response($responseStatusCode, $this->formatHeaders($responseHeader), $responseBody);
    }

    /**
     * 使用 socket 发送 HTTP 请求
     *
     * @param string $method 请求方法
     * @param string $uri 请求地址
     * @param array $headers http header
     * @param null|string|array $body http body
     * @return Response
     * @throws \Exception
     */
    private function requestWithSocket($method, $uri, array $headers = array(), $body = null)
    {
        $uriInfoArr = $this->parseUri($uri);
        $eof = "\r\n";

        $httpRequestStr = sprintf("%s %s HTTP/1.1" . $eof, $method, $uriInfoArr['path']);


        if (is_array($body)) {
            $body = http_build_query($body);
        }

        // header 处理
        if (!in_array($method, array(static::GET, static::HEAD))) {
            $headers = array_merge($headers, array('Content-Type' => 'application/x-www-form-urlencoded'));
        }
        $headers = array_merge($headers, array(
            'Content-Length' => strlen($body),
            'Host' => $uriInfoArr['host'],
        ));
        $headers = array_merge($this->defaultHeaders, $headers);
        $headers = array_map(function ($headerName, $headerValue) {
            return sprintf('%s: %s', $headerName, $headerValue);
        }, array_keys($headers), $headers);


        $httpRequestStr .= (implode($eof, $headers) . $eof . $eof);
        $httpRequestStr .= $body;
        $fp = @fsockopen($uriInfoArr['host'], $uriInfoArr['port'], $errNo, $errStr, $this->timeout);

        @fwrite($fp, $httpRequestStr);
        $response = @stream_get_contents($fp);
        @fclose($fp);

        if (strpos($response, $eof . $eof) === false) {
            $msg = sprintf('http request error, url: %s', $uri);
            Log::write($msg);
            throw new ConnectFailedException($msg);
        }

        list($responseHeader, $responseBody) = explode($eof . $eof, $response);
        $responseHeader = explode($eof, $responseHeader);

        $responseStatusCode = 200;
        if (isset($responseHeader[0]) && strcasecmp(substr($responseHeader[0], 0, 5), 'HTTP/') === 0) {
            $requestLineArr = explode(' ', $responseHeader[0]);
            if (count($requestLineArr) >= 2) {
                $responseStatusCode = $requestLineArr[1];
            }
            unset($responseHeader[0]);
            return array($responseHeader, $responseBody, $responseStatusCode);
        }

        return new Response($responseStatusCode, $this->formatHeaders($responseHeader), $responseBody);
    }

    /**
     * 解析 URI
     *
     * @param string $uri
     * @return array
     */
    protected function parseUri($uri)
    {
        $matches = parse_url($uri);
        $scheme = isset($matches['scheme']) ? $matches['scheme'] : 'http';
        $host = $matches['host'];
        $path = isset($matches['path']) ? $matches['path'] . (isset($matches['query']) ? '?' . $matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : ($scheme == 'http' ? 80 : 443);
        $requestUrl = $scheme . '://' . $host . ':' . $port . $path;


        return array(
            'scheme' => $scheme,
            'host' => $host,
            'path' => $path,
            'port' => $port,
            'requestUrl' => $requestUrl,
        );
    }

    /**
     * 初始化 HTTP Headers
     */
    protected function initHeaders()
    {
        $this->saveHeader('User-Agent', $this->defaultUserAgent);
        $this->saveHeader('Accept-Language', 'zh-CN,zh;q=0.8,en;q=0.6');

        if ($this->gzip === true) {
            $this->saveHeader('Accept-Encoding', 'gzip');
        }
    }

    /**
     * 重置默认 headers
     */
    protected function resetHeaders()
    {
        $this->defaultHeaders = array();
    }
}