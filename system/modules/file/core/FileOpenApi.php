<?php

/**
 * 文件柜模块
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------  文件打开方式api
 * @package application.modules.file.core
 * @version $Id: FileOpenApi.php 3297 2014-06-19 09:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\core;

use application\core\utils\System;
use CException;

Class FileOpenApi extends System
{

    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 文本方式的打开
     * @param string $file 文件相对地址
     * @param FileCore $core 附件处理核心类
     * @return string 返回文件内容
     */
    public function text($file, FileCore $core)
    {
        $content = $core->getContent($file);
        return $content;
    }

    /**
     * google方式打开
     * @param string $file 文件相对地址
     * @param FileCore $core 附件处理核心类
     * @return void
     */
    public function google($file, FileCore $core)
    {
        $url = $core->getRealUrl($file);
        header("location: https://docs.google.com/viewer?url=" . urlencode($url));
    }

    /**
     * office方式打开
     * @param string $file 文件相对地址
     * @param FileCore $core 附件处理核心类
     * @return void
     */
    public function office($file, FileCore $core)
    {
        $url = $core->getOfficeReadUrl($file);
        header("location: " . $url);
    }

    /**
     * pdf方式打开
     * @param string $file 文件相对地址
     * @param FileCore $core 附件处理核心类
     * @return void
     */
    public function pdf($file, FileCore $core)
    {
        $url = $core->getRealUrl($file);
        $mime = 'application/pdf';
        $this->ouput($mime, $url);
    }

    /**
     * pdf方式打开
     * @param string $file 文件相对地址
     * @param FileCore $core 附件处理核心类
     * @return string 返回文件的绝对真实路径
     */
    public function xiuxiu($file, FileCore $core)
    {
        $url = $core->getRealUrl($file);
        return $url;
    }

    /**
     * 输出文件
     * @param string $mime 文件格式
     * @param string $url 文件路径
     */
    private function ouput($mime, $url)
    {
        try {
            header('Content-Type: ' . $mime);
            if (ob_get_length()) {
                ob_end_clean();
            }
            readfile($url);
            flush();
            ob_flush();
            exit();
        } catch (CException $e) {
            throw new CException($e->getMessage());
        }
    }

}
