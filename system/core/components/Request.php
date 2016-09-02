<?php

/**
 * 重写 getIsAjaxRequest、sendFile
 *
 * @package application.core.components
 * @version $Id$
 * @author Aeolus <Aeolus@ibos.com.cn>
 */

namespace application\core\components;

use application\core\utils\IBOS;
use CHttpException;
use CHttpRequest;

class Request extends CHttpRequest {
    /**
     * Returns whether this is an AJAX (XMLHttpRequest) request.
     *
     * @return boolean whether this is an AJAX (XMLHttpRequest) request.
     */
    public function getIsAjaxRequest() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || isset($_SERVER['HTTP_ISCORS']);
    }

    /**
     * Sends a file to user.
     *
     * @param string $fileName
     * @param string $content
     * @param null $mimeType
     * @param bool $terminate
     * @throws CHttpException
     */
    public function sendFile($fileName, $content, $mimeType = null, $terminate = true) {
        if ($mimeType === null) {
            if (($mimeType = \CFileHelper::getMimeTypeByExtension($fileName)) === null)
                $mimeType = 'text/plain';
        }

        $fileSize = (function_exists('mb_strlen') ? mb_strlen($content, '8bit') : strlen($content));
        $contentStart = 0;
        $contentEnd = $fileSize - 1;

        $httpVersion = $this->getHttpVersion();
        if (isset($_SERVER['HTTP_RANGE'])) {
            header('Accept-Ranges: bytes');

            //client sent us a multibyte range, can not hold this one for now
            if (strpos($_SERVER['HTTP_RANGE'], ',') !== false) {
                header("Content-Range: bytes $contentStart-$contentEnd/$fileSize");
                throw new \CHttpException(416, 'Requested Range Not Satisfiable');
            }

            $range = str_replace('bytes=', '', $_SERVER['HTTP_RANGE']);

            //range requests starts from "-", so it means that data must be dumped the end point.
            if ($range[0] === '-')
                $contentStart = $fileSize - substr($range, 1);
            else {
                $range = explode('-', $range);
                $contentStart = $range[0];

                // check if the last-byte-pos presents in header
                if ((isset($range[1]) && is_numeric($range[1])))
                    $contentEnd = $range[1];
            }

            /* Check the range and make sure it's treated according to the specs.
             * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
             */
            // End bytes can not be larger than $end.
            $contentEnd = ($contentEnd > $fileSize) ? $fileSize - 1 : $contentEnd;

            // Validate the requested range and return an error if it's not correct.
            $wrongContentStart = ($contentStart > $contentEnd || $contentStart > $fileSize - 1 || $contentStart < 0);

            if ($wrongContentStart) {
                header("Content-Range: bytes $contentStart-$contentEnd/$fileSize");
                throw new \CHttpException(416, 'Requested Range Not Satisfiable');
            }

            header("HTTP/$httpVersion 206 Partial Content");
            header("Content-Range: bytes $contentStart-$contentEnd/$fileSize");
        } else
            header("HTTP/$httpVersion 200 OK");

        $length = $contentEnd - $contentStart + 1; // Calculate new content length

        // 文件名乱码解决
        // 示例使用的文件名为：IBOS工作流(2016-08-27).zip
        $contentDisposition = 'Content-Disposition: attachment; ';
        $browserName = strtolower(Ibos::app()->browser->name);
        $browserVersion = Ibos::app()->browser->version;
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $pathParts = pathinfo($fileName);
        if ($browserName == 'msie' && in_array($browserVersion, array('7.0', '8.0'))) {
            // Content-Disposition: attachment; filename="IBOS%E5%B7%A5%E4%BD%9C%E6%B5%81%282016-08-27%29zip"
            $fileName = sprintf('%s.%s', rawurlencode($pathParts['filename']), $pathParts['extension']);
            $contentDisposition .= sprintf('filename="%s"', $fileName);
        } elseif ($browserName == 'safari') {
            // Content-Disposition: attachment; filename="IBOS工作流(2016-08-27).zip"
            $contentDisposition .= sprintf('filename="%s"', $fileName);
        } elseif (stripos($userAgent, 'android') !== false) {
            // Content-Disposition: attachment; filename="IBOS工作流(2016-08-27).zip
            $contentDisposition .= sprintf('filename="%s"', $fileName);
        } else {
            // Content-Disposition: attachment; filename*=UTF-8''IBOS%E5%B7%A5%E4%BD%9C%E6%B5%81%282016-08-27%29zip
            $fileName = sprintf('%s.%s', rawurlencode($pathParts['filename']), $pathParts['extension']);
            $contentDisposition .= sprintf("filename*=UTF-8''%s", $fileName);
        }

        // ie hack
        if (stripos($userAgent, 'Trident') !== false) {
            $contentDisposition = str_replace('%20', ' ', $contentDisposition);
        }
        header($contentDisposition);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Content-Type: $mimeType");
        header('Content-Length: ' . $length);
        header('Content-Transfer-Encoding: binary');
        $content = function_exists('mb_substr') ? mb_substr($content, $contentStart, $length, '8bit') : substr($content, $contentStart, $length);

        if ($terminate) {
            // clean up the application first because the file downloading could take long time
            // which may cause timeout of some resources (such as DB connection)
            ob_start();
            Ibos::app()->end(0, false);
            ob_end_clean();
            echo $content;
            exit(0);
        } else
            echo $content;
    }
}
