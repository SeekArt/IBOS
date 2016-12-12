<?php

namespace application\modules\file\core;

use CApplicationComponent;

abstract class FileCore extends CApplicationComponent
{

    /**
     * 获取office文件的阅读地址
     */
    abstract public function getOfficeReadUrl($attachment);

    /**
     * 移动附件操作
     */
    abstract public function moveAttach($attachids);

    /**
     * 创建office文件操作
     */
    abstract public function mkOffice($file);

    /**
     * 获取文件内容
     */
    abstract public function getContent($file);

    /**
     * 删除附件
     */
    abstract public function delAttach($attachids);

}
