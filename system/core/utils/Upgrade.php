<?php

/**
 * IBOS升级处理文件类,提供所有升级相关方法
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2017 IBOS Inc
 */

namespace application\core\utils;

use application\core\utils\Cache as CacheUtil;
use application\core\utils\HttpClient\exception\ConnectFailedException;
use application\core\utils\HttpClient\HttpClientFactory;
use application\modules\dashboard\model\Cache as CacheModel;
use application\modules\main\model\Setting;

class Upgrade
{
    const TYPE_CONTINUE_DOWNLOAD = 1;

    const TYPE_DOWNLOAD_COMPLETE = 2;

    const TYPE_DOWNLOAD_RETRY = 3;

    const TYPE_DOWNLOAD_ERROR = 4;

    // 更新检测地址
    const UPGRADE_CHECK_URL = 'http://api.ibos.cn/';

    /**
     * 检查更新信息，这些信息将被记录在setting中的upgrade中
     *
     * @return boolean
     */
    public static function checkUpgrade()
    {

        $versionNum = VERSION;
        $version = strtolower(VERSION_TYPE);
        $upgradeCheckUrl = self::UPGRADE_CHECK_URL . 'v3/version/check?version=' . $versionNum . '&platform=ibos' . $version;
        $httpClient = HttpClientFactory::create();
        $remoteResponse = $httpClient->get($upgradeCheckUrl);
        $response = json_decode($remoteResponse->getBody(), true);
        if (isset($response['data']['update']) && $response['data']['update'] != 'no') {
            Setting::model()->updateSettingValueByKey('upgrade', $response['data']);
            CacheUtil::update('setting');
            $return = true;
        } else {
            Setting::model()->updateSettingValueByKey('upgrade', '');
            $return = false;
        }
        return $return;
    }

    /**
     * 下载文件
     *
     * @param string $downloadFileUrl 下载文件 url
     * @param string $savePath 保存路径
     * @param integer $position 文件指针，指定文件指针后，下载器将从这个指针开始下载
     * @param integer $offset 指定本文内容长度，仅仅检验下载文件的长度是否和$offset相等
     * @param int $downloadFileSize 下载文件大小，单位：字节。如果不提供该值，则通过 HEAD 请求获取文件大小
     * @return bool|int 1 断点文件下载成功 2文件完全下载完成
     */
    public static function downloadFile($downloadFileUrl, $savePath, $position = 0, $offset = 0, $downloadFileSize = 0)
    {
        $downloadCompleteFlag = true;
        if (!$position) {
            $mode = 'wb';
        } else {
            $mode = 'ab';
        }
        @mkdir($savePath, '0777', true);
        $destFile = rtrim($savePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . static::getFileNameFromUrl($downloadFileUrl);
        $fp = fopen($destFile, $mode);
        if (!$fp) {
            return false;
        }

        try {
            $httpClient = HttpClientFactory::create();
            $httpClient->setTimeout(30);

            $headers = array();
            if ($position > 0 || $offset > 0) {
                // 断点续传
                if ($offset > 0) {
                    if (empty($downloadFileSize)) {
                        // 未设置文件大小，通过 HEAD 请求获取下载文件大小
                        $headResp = $httpClient->head($downloadFileUrl);
                        $downloadFileSize = $headResp->getHeader('Content-Length');
                        if ($downloadFileSize === false) {
                            throw new \Exception('File server need return Content-Length header.');
                        }
                    }

                    $endPosition = $position + $offset;
                    if ($endPosition > $downloadFileSize) {
                        $endPosition = $downloadFileSize;
                    }
                    $endPosition -= 1;

                    $downloadRange = sprintf('bytes=%d-%d', $position, $endPosition);
                } else {
                    $downloadRange = sprintf('bytes=%d-', $position);
                }
                $headers['Range'] = $downloadRange;
            }

            $downloadResp = $httpClient->get($downloadFileUrl, $headers);
            $statusCode = $downloadResp->getStatusCode();

            if ($statusCode === 404) {
                throw new ConnectFailedException('Download file error. Status code: ' . $downloadResp->getStatusCode());
            }

            if ($statusCode === 302) {
                return static::downloadFile($downloadResp->getHeader('Location'), $savePath, $position, $offset, $downloadFileSize);
            }

            if (!in_array($statusCode, array(200, 206))) {
                throw new \Exception('Download file error. Status code: ' . $downloadResp->getStatusCode());
            }
            $response = $downloadResp->getBody();
        } catch (ConnectFailedException $e) {
            // 下载出错
            return self::TYPE_DOWNLOAD_ERROR;
        } catch (\Exception $e) {
            // 重新下载文件（如果是断点续传，从失败的 position 处重新下载）
            return self::TYPE_DOWNLOAD_RETRY;
        }

        $responseLength = strlen($response);
        if ($responseLength > 0 && $offset > 0 && strlen($response) == $offset) {
            // 下载还未完成
            $downloadCompleteFlag = false;
        }

        //写入数据
        if ($responseLength > 0) {
            fwrite($fp, $response);
        }

        fclose($fp);

        if ($downloadCompleteFlag === true) {
            // 下载文件完成
            return self::TYPE_DOWNLOAD_COMPLETE;
        } else {
            // 下载文件的部分内容成功，继续下载
            return self::TYPE_CONTINUE_DOWNLOAD;
        }
    }

    /**
     * 获得更新步骤名称，用于断点更新
     *
     * @param integer $step 步骤 1：获取更新文件 2：下载更新 3：本地文件对比 4：正在更新
     * @return string 返回步骤名
     */
    public static function getStepName($step)
    {
        $stepNameArr = array(
            '1' => Ibos::lang('Upgrade get file'),
            '2' => Ibos::lang('Upgrade download'),
            '3' => Ibos::lang('Upgradeing'),
            '4' => Ibos::lang('Upgrade db'),
            'dbupdate' => Ibos::lang('Upgrade db'),
        );
        return $stepNameArr[$step];
    }

    /**
     * 从一个下载URL中获取文件名
     *
     * @param string $url 下载链接
     * @return string
     */
    public static function getFileNameFromUrl($url)
    {
        return pathinfo($url, PATHINFO_FILENAME) . '.' . StringUtil::getFileExt($url);
    }

    /**
     * 记录更新步骤
     *
     * @param integer $step 第几步
     */
    public static function recordStep($step)
    {
        $upgradeStep = CacheModel::model()->fetchByPk('upgrade_step');
        if (!empty($upgradeStep['cachevalue']) && !empty($upgradeStep['cachevalue']['step'])) {
            $upgradeStep['cachevalue'] = StringUtil::utf8Unserialize($upgradeStep['cachevalue']);
            $upgradeStep['cachevalue']['step'] = $step;
            CacheModel::model()->add(array(
                'cachekey' => 'upgrade_step',
                'cachevalue' => serialize($upgradeStep['cachevalue']),
                'dateline' => TIMESTAMP,
            ), false, true);
        }
    }

}
