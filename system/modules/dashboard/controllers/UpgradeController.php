<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Log;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\core\utils\Upgrade;
use application\extensions\SimpleUnzip;
use application\modules\dashboard\model\Cache;
use application\modules\main\model\Setting;

class UpgradeController extends BaseController
{

    /**
     * 必须是本地引擎才能进行此操作
     */
    public function init()
    {
        parent::init();
        if (!LOCAL) {
            die(Ibos::lang('Not compatible service', 'message'));
        }

        if (function_exists('ini_set') === true) {
            // 不做 PHP 最大内存限制
            ini_set('memory_limit', '-1');
        }

        if (function_exists('set_time_limit') === true) {
            set_time_limit(0);
        }
    }

    public function actionIndex()
    {
        if (Env::getRequest('op')) {
            $operation = Env::getRequest('op');
            $operations = array('checking', 'patch', 'showupgrade');
            if (!in_array($operation, $operations)) {
                exit();
            }

            $opFunction = 'handle' . ucfirst($operation);
            if (method_exists($this, $opFunction)) {
                return $this->$opFunction();
            }

        } else {
            $this->render('upgradeCheckVersion');
        }
    }

    /**
     * 输出在线更新中错误信息
     */
    public function actionShowUpgradeErrorMsg()
    {
        $msg = Env::getRequest('msg');
        $this->render('upgradeError', array('msg' => $msg));
    }

    public function actionUpdateCache()
    {
        $op = Env::getRequest('op') ? Env::getRequest('op') : "cache";
        switch ($op) {
            case "cache":
                CacheUtil::update();
                $op = "org";
                $msg = "正在更新静态文件缓存";
                $isSuccess = true;
                $isContinue = true;
                break;
            case "org":
                Org::update();
                $op = "module";
                $msg = "正在更新模块配置缓存";
                $isSuccess = true;
                $isContinue = true;
                break;
            case "module":
                Module::updateConfig();
                $op = "end";
                $msg = "缓存更新完成,记得要定期检查更新哦~";
                $isSuccess = true;
                $isContinue = false;
                break;
            default:
                $msg = '';
                $isSuccess = true;
                $isContinue = false;
                break;
        }
        $param = array(
            'op' => $op,
            'msg' => $msg,
            'isSuccess' => $isSuccess,
            'isContinue' => $isContinue,
        );
        $this->ajaxReturn($param, "json");
    }


    /**
     * 第一步：检查更新
     */
    protected function handleChecking()
    {
        $upgradeStep = Cache::model()->fetchByPk('upgrade_step');
        $upgradeStep['cachevalue'] = StringUtil::utf8Unserialize($upgradeStep['cachevalue']);
        $isExistStep = !empty($upgradeStep['cachevalue']) && !empty($upgradeStep['cachevalue']['step']);
        // 查找步骤缓存
        if (!Env::getRequest('rechecking') && $isExistStep) {
            // 步骤缓存的URL参数
            $param = array(
                'op' => $upgradeStep['cachevalue']['operation'],
                'step' => $upgradeStep['cachevalue']['step']
            );
            $data = array(
                'url' => $this->createUrl('upgrade/index', $param),
                'stepName' => Upgrade::getStepName($upgradeStep['cachevalue']['step'])
            );
            $this->render('upgradeContinue', array('data' => $data));
        } else {
            // 如果是重新请求更新或者步骤缓存为空，都做重新检查
            $this->upgradeReset();
            Upgrade::checkUpgrade();
            $url = $this->createUrl('upgrade/index', array('op' => 'showupgrade'));
            $this->redirect($url);
        }
    }

    /**
     * 第二步：显示更新列表
     */
    protected function handleShowUpgrade()
    {
        $this->upgradeStep(array('op' => 'showupgrade'));

        $result = $this->processingUpgradeList();
        if ($result['isHaveUpgrade']) {
            $this->render('upgradeShow', $result);
        } else {
            $this->render('upgradeNewest');
        }
    }

    /**
     * 处理升级内容列表
     *
     * @return array 结果数组 e.g : array('isHaveUpgrade' => true, list => array(...));
     */
    protected function processingUpgradeList()
    {
        $upgrade = Ibos::app()->setting->get('setting/upgrade');
        if (!$upgrade) {
            return array('isHaveUpgrade' => false, 'msg' => Ibos::lang('Upgrade latest version'));
        } else {
            $linkUrl = $this->createUrl('upgrade/index', array('op' => 'patch'));
            $verList = $upgrade['desc'];
            foreach ((array)$verList as $key => $ver) {
                $verList[$key]['version'] = 'IBOS ' . VERSION_TYPE . ' [' . $ver['version'] . ']';
            }
            $data = array(
                'upgrade' => true,
                'link' => $linkUrl,
                'upgradeDesc' => $verList,
            );
            return array('isHaveUpgrade' => true, 'data' => $data);
        }
    }

    /**
     * 第三步：升级补丁
     */
    protected function handlePatch()
    {
        $operation = 'patch';
        $step = Env::getRequest('step');
        $step = intval($step) ? $step : 1;
        $upgradeStepRecord = Cache::model()->fetchByPk('upgrade_step');
        $upgradeStep = StringUtil::utf8Unserialize($upgradeStepRecord['cachevalue']);

        // 下一步所需URL参数
        $param = array('op' => $operation);
        // 开始步骤处理
        switch ($step) {
            case 1:
                // 第一步：显示更新文件
                $this->processingShowUpgrade($param);
                break;
            case 2:
                // 第二步：下载文件
                $this->processingDownloadFile($param);
                break;
            case 3:
                // 第三步：应用更新文件
                $this->processingUpdateFile($upgradeStep, $param);
                break;
            case 4:
                // 第四步：删除临时下载文件，更新完成
                $this->processingTempFile();
                break;
            default:
                $msg = Ibos::lang('Upgrade error, has not step', array('{step}' => $step));
                Log::write($msg);
                throw new \Exception($msg);
                break;
        }

        // 更新步骤（除了最后一步）
        if ($step != 4) {
            $this->upgradeStep(array(
                'step' => $step,
                'operation' => $operation,
            ));
        }

    }

    /**
     * 更新第一步：显示更新列表
     *
     * @param array $urlParam url参数数组，用于生成并返回下一步链接
     * @param string $savePath 显示更新文件保存路径
     */
    protected function processingShowUpgrade($urlParam = array(), $savePath = '/data/patch')
    {
        $upgrade = Ibos::app()->setting->get('setting/upgrade');
        $urlParam['step'] = 2;
        $url = $this->createUrl('upgrade/index', $urlParam);
        $sizeCount = 0;
        foreach ($upgrade['filesize'] as $value) {
            $filesizeList[] = Convert::sizeCount($value);
            $sizeCount += $value;
        }

        $sizeCount = Convert::sizeCount($sizeCount);
        $data = array_merge(
            array('actionUrl' => $url),
            array('list' => $upgrade['download_url'], 'filesize' => $filesizeList, 'count' => $sizeCount),
            array('savePath' => $savePath)
        );
        $this->render('upgradeDownloadList', array('step' => 1, 'data' => $data));
    }

    /**
     * 更新第二步：下载文件
     *
     * @param array $urlParam url参数数组，用于生成并返回下一步链接
     */
    protected function processingDownloadFile($urlParam)
    {
        if (Env::getRequest('downloadStart')) {
            $upgrade = Ibos::app()->setting->get('setting/upgrade');
            $updateFileList = $upgrade['download_url'];
            // 文件列表索引
            $fileSeq = intval(Env::getRequest('fileseq'));
            // 默认第1个（实际数组从0开始，所以下载时需要减1）
            $fileSeq = $fileSeq ? $fileSeq : 1;
            // 文件指针，用于断点下载
            $position = intval(Env::getRequest('position'));
            $position = $position ? $position : 0;
            // 文件最大长度，如果下载的文件超过这个长度，则自动使用断点下载
            $offset = 600 * 1024;
            $data['step'] = 2;
            // 返回数据
            $data['data'] = array(
                'isSuccess' => true,
                'data' => array('percent' => '100%'),
                'msg' => '',
                'url' => '',
            );

            // 所有文件更新完成后，更新固定的特殊文件
            if ($fileSeq > count($updateFileList)) {
                $data['data']['msg'] = Ibos::lang('Upgrade download complete');
                $data['data']['url'] = $this->createUrl('upgrade/index', array_merge(array('step' => 3), $urlParam));
                $data['step'] = 3;
            } else {
                // 当前文件
                $curFile = $updateFileList[$fileSeq - 1];
                $baseCurName = rawurldecode(basename($curFile));
                // 当前文件大小（单位：字节）
                $curFileSize = $upgrade['filesize'][$fileSeq - 1];
                // 当前文件 md5 校验值
                $curFileMd5Sum = $upgrade['md5sum'][$fileSeq - 1];
                // 当前进度百分比
                $percent = sprintf('%2d', 100 * $position / $curFileSize) . '%';
                $percent2 = $fileSeq . "/" . count($updateFileList);

                $data['data']['percent'] = $percent;

                // 开始下载并返回下载状态
                // 保存路径
                $savePath = PATH_ROOT . '/data/update';
                $downloadStatus = Upgrade::downloadFile($curFile, $savePath, $position, $offset, $curFileSize);

                if ($downloadStatus == Upgrade::TYPE_CONTINUE_DOWNLOAD) {
                    // 断点下载，继续进行下载
                    $data['data']['msg'] = Ibos::lang('Upgrade downloading file', '',
                        array('{file}' => $baseCurName, '{percent}' => $percent, '{percent2}' => $percent2));
                    $data['data']['url'] = $this->createUrl('upgrade/index',
                        array_merge(array('step' => 2, 'fileseq' => $fileSeq, 'position' => ($position + $offset)),
                            $urlParam));
                } elseif ($downloadStatus == Upgrade::TYPE_DOWNLOAD_COMPLETE) {
                    // 下载完成，开始校验文件
                    $destFile = $savePath . DIRECTORY_SEPARATOR . Upgrade::getFileNameFromUrl($curFile);
                    $downloadFileMd5Sum = md5_file($destFile);
                    if (strcasecmp($curFileMd5Sum, $downloadFileMd5Sum) !== 0) {
                        // 文件损坏，重新下载整个文件
                        $data['data']['isSuccess'] = false;
                        $data['data']['msg'] = Ibos::lang('Upgrade redownload', '', array('{file}' => $baseCurName));
                        $data['data']['url'] = $this->createUrl('upgrade/index',
                            array_merge(array('step' => 2, 'fileseq' => $fileSeq), $urlParam));
                    } else {
                        // 文件下载成功并无损坏，下载下一个
                        $data['data']['msg'] = Ibos::lang('Upgrade downloading file', '',
                            array('{file}' => $baseCurName, '{percent}' => $percent, '{percent2}' => $percent2));
                        $data['data']['url'] = $this->createUrl('upgrade/index',
                            array_merge(array('step' => 2, 'fileseq' => ($fileSeq + 1)), $urlParam));
                    }

                } elseif ($downloadStatus == Upgrade::TYPE_DOWNLOAD_RETRY) {
                    // 尝试重新下载（断点续传）
                    $data['data']['msg'] = Ibos::lang('Upgrade downloading file', '',
                        array('{file}' => $baseCurName, '{percent}' => $percent, '{percent2}' => $percent2));
                    $data['data']['url'] = $this->createUrl('upgrade/index',
                        array_merge(array('step' => 2, 'fileseq' => $fileSeq, 'position' => ($position)),
                            $urlParam));
                } elseif ($downloadStatus == Upgrade::TYPE_DOWNLOAD_ERROR) {
                    // 下载出错（可能不存在该文件或网络故障），重新下载整个文件
                    $data['data']['isSuccess'] = false;
                    $data['data']['msg'] = Ibos::lang('Upgrade redownload', '', array('{file}' => $baseCurName));
                    $data['data']['url'] = $this->createUrl('upgrade/index',
                        array_merge(array('step' => 2, 'fileseq' => $fileSeq), $urlParam));
                }
            }
            return $this->ajaxReturn($data, 'json');
        } else {
            // 更新步骤缓存
            Upgrade::recordStep(2);
            $downloadUrl = $this->createUrl('upgrade/index', array_merge(array('step' => 2), $urlParam));
            $this->render('upgradeDownload', array('downloadUrl' => $downloadUrl));
        }
    }

    /**
     * 更新第三步：更新覆盖文件
     *
     * @param string $upgradeStep
     * @param array $urlParam
     */
    protected function processingUpdateFile($upgradeStep, $urlParam)
    {
        $upgrade = Ibos::app()->setting->get('setting/upgrade');
        if (Env::getRequest('coverStart')) {
            $data['step'] = 3;
            // 开始升级
            // --- 覆盖文件 ---
            foreach ($upgrade['download_url'] as $url) {
                $destFile = PATH_ROOT . '/data/update/' . Upgrade::getFileNameFromUrl($url);
                if (!file_exists($destFile)) {
                    continue;
                }
                $unzip = new SimpleUnzip();
                $unzip->ReadFile($destFile);
                if ($unzip->Count() == 0 || $unzip->GetError(0) != 0) {
                    continue;
                }
                foreach ($unzip->Entries as $entry) {
                    if (!empty($entry->Path)) {
                        File::makeDirs($entry->Path);
                        $file = $entry->Path . '/' . $entry->Name;
                    } else {
                        $file = $entry->Name;
                    }
                    $fp = fopen($file, 'wb');
                    fwrite($fp, $entry->Data);
                    fclose($fp);
                }
                unset($unzip);
            }
            // --- 覆盖操作完成 ---
            // -- 是否有数据库升级 --
            if (file_exists(PATH_ROOT . DIRECTORY_SEPARATOR . 'upgrade.php')) {
                // 直接访问升级模块
                $dbReturnUrl = $this->createUrl('upgrade/index', array_merge(array('step' => 4), $urlParam));
                $param = array(
                    'from' => rawurlencode($dbReturnUrl),
                );
                $data['data']['status'] = 'upgrade_database';
                $data['data']['url'] = 'upgrade/index.php?' . http_build_query($param);
                $data['data']['msg'] = Ibos::lang('Upgrade file successful');
                $this->ajaxReturn($data, 'json');
            }
            $data['data']['status'] = 'upgrade_file_successful';
            $data['data']['url'] = $this->createUrl('upgrade/index', array_merge(array('step' => 4), $urlParam));
            $data['step'] = 4;
            $this->ajaxReturn($data, 'json');
        } else {
            // 更新步骤缓存
            Upgrade::recordStep(3);
            $data = array(
                'coverUrl' => $this->createUrl('upgrade/index', array_merge(array('step' => 3), $urlParam)),
                'to' => 'IBOS ' . VERSION_TYPE . ' [' . $upgrade['version'] . ']',
                'from' => VERSION . ' ' . VERSION_TYPE
            );
            $this->render('upgradeCover', $data);
        }
    }

    /**
     * 更新第四步：删除临时文件，返回成功信息
     */
    protected function processingTempFile()
    {
        @unlink(PATH_ROOT . '/upgrade.php');
        $this->upgradeReset();
        Setting::model()->updateSettingValueByKey('upgrade', '');
        Setting::model()->updateSettingValueByKey('version', VERSION . ' ' . strtolower(VERSION_TYPE));
        $data['step'] = 4;
        $data['data']['url'] = $this->createUrl('upgrade/updateCache', array_merge(array('op' => "cache")));
        $data['data']['msg'] = Ibos::lang('Upgrade successful', '', array(
                '{version}' => 'IBOS ' . VERSION_TYPE . ' [' . VERSION . ']'
            )
        );
        $this->render('upgradeSuccess', $data);
    }

    protected function upgradeStep($data)
    {
        $row = Cache::model()->fetchByPk('upgrade_step');
        if (empty($row)) {
            Cache::model()->add(array(
                'cachekey' => 'upgrade_step',
                'cachevalue' => serialize($data),
                'dateline' => TIMESTAMP,
            ));
        } else {
            Cache::model()->updateByPk('upgrade_step', array(
                'cachevalue' => serialize($data),
                'dateline' => TIMESTAMP,
            ));
        }
    }

    /**
     * 重置更新状态（删除缓存数据）
     */
    protected function upgradeReset()
    {
        Cache::model()->deleteByPk('upgrade_step');
    }
}
