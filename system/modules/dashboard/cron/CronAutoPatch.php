<?php

/**
 * 自动打补丁计划任务
 */
use application\core\utils\File;
use application\core\utils\Upgrade;
use application\extensions\SimpleUnzip;

if (!LOCAL) {
    return true;
}
$url = 'http://ibosupgrade.oss-cn-hangzhou.aliyuncs.com/patch/';
$path = strtolower(VERSION_TYPE) . '/' . VERSION . '/' . VERSION_DATE . '.zip';
$file = $url . $path;
$savePath = PATH_ROOT . '/data/update/patch';
$fileName = $savePath . DIRECTORY_SEPARATOR . Upgrade::getFileNameFromUrl($file);
try {
    $remoteResponse = File::fileSockOpen($file);
    if (!empty($remoteResponse)) {
        $offset = 600 * 1024;
        $downStatus = Upgrade::downloadFile($file, $savePath, 0, $offset);
        if ($downStatus == 2) {
            $unzip = new SimpleUnzip();
            $unzip->ReadFile($fileName);
            if ($unzip->Count() == 0 || $unzip->GetError(0) != 0) {
                return true;
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
            $execFile = PATH_ROOT . DIRECTORY_SEPARATOR . 'execute.php';
            if (file_exists($execFile)) {
                @include $execFile;
                @unlink($execFile);
            }
            @unlink($fileName);
        }
    }
} catch (Exception $exc) {
    @unlink($fileName);
}
