<?php

namespace application\core\engines;

/**
 * 文件存储操作公共接口
 */
interface FileOperationInterface
{

    public function fileExists($filename);

    public function createFile($filename, $content);

    public function deleteFile($filename);

    public function fileName($filename);

    public function readFile($filename);

    public function copyFile($source, $savepath);

    public function fileSize($filename);

    public function uploadFile($destFileName, $srcFileName);
}
