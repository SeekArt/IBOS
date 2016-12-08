<?php

/**
 * 本地网盘工具类
 *
 * @author  gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */

/**
 * 本地网盘工具类,用于处理附件
 *
 * @package application.module.file.core
 * @version $Id: FileLocal.php 3564 2014-06-05 01:56:12Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\core;

use application\core\utils\Attach;
use application\core\utils\File;
use application\core\utils\Ibos;

class FileLocal extends FileCore
{

    /**
     * 获取office文件的阅读地址
     * @param string $attachUrl 附件地址
     * @return string
     */
    public function getOfficeReadUrl($idString)
    {
        // return "http://o.ibos.cn/op/view.aspx?src=" . urlencode( $this->getRealUrl( $attachUrl ) );
        $urlManager = Ibos::app()->urlManager;
        return $urlManager->createUrl('main/attach/office', array('id' => $idString, 'op' => 'read'));
    }

    /**
     * 获取office文件的编辑地址
     * @param string $idString 附件id
     * @return string
     */
    public function getOfficeEditUrl($idString)
    {
        $urlManager = Ibos::app()->urlManager;
        return $urlManager->createUrl('main/attach/office', array('id' => $idString, 'op' => 'edit'));
    }

    /**
     * 取得存储文件的网站地址
     * @return string
     */
    public function getSiteUrl()
    {
        return Ibos::app()->setting->get('siteurl');
    }

    /**
     * 获取文件真实路径
     * @param string $file 文件相对路径
     * @return string
     */
    public function getRealUrl($file)
    {
        return $this->getSiteUrl() . $file;
    }

    /**
     * 处理附件(本地不做任务操作)
     * @param array $attachs 附件
     * @return array
     */
    public function moveAttach($attachids)
    {
        return true;
    }

    /**
     * 创建空白office文件
     * @param string $attachment 带路径文件
     * @return boolean
     */
    public function mkOffice($file)
    {
        return File::createFile(File::fileName($file), '');
    }

    /**
     * 获取一个文件内容
     * @param string $file 带路径文件
     * @return string
     */
    public function getContent($file)
    {
        return File::readFile($file);
    }

    /**
     * 删除附件
     * @param mix $aids 附件id
     * @return integer
     */
    public function delAttach($aids)
    {
        return Attach::delAttach($aids);
    }

}
