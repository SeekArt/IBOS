<?php

namespace application\modules\file\core;

use CApplicationComponent;

abstract class CloudOSS extends CApplicationComponent
{

    /**
     * 获取列表
     */
    abstract public function listObject($config);

    /**
     * 上传文件、创建文件或文件夹
     */
    abstract public function putObject($config);

    /**
     * 复制文件
     */
    abstract public function copyObject($config);

    /**
     * 删除单个文件
     */
    abstract public function deleteObject($config);

    /**
     * 获取某个文件信息
     */
    abstract public function getObject($config);

    /**
     * 是否已存在
     */
    abstract public function getObjectContent($config);

    /**
     * 获取储存文件的地址
     */
    abstract public function getSiteUrl();
}
