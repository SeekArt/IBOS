<?php

/**
 * 云盘网盘工具类
 *
 * @author  gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */

/**
 * 云盘网盘工具类，用于处理附件
 *
 * @package application.module.file.core
 * @version $Id: FileCloud.php 3564 2014-06-05 01:56:12Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\core;

use application\core\utils\Attach;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\file\model\FileCloudSet;
use CException;

class FileCloud extends FileCore
{

    private $_server = 'AliOSS'; // 网盘服务，默认阿里云
    private $_instance; // 网盘实例

    public function __construct($cloudid)
    {
        $cloudSet = FileCloudSet::model()->fetchByAttributes(array('id' => $cloudid));
        if (empty($cloudSet)) {
            throw new CException(Ibos::t('file.default', 'Ibos cloud did not open succeed'));
        }
        if (!empty($cloudSet['server'])) {
            $this->_server = $cloudSet['server'];
        }
        $cloudOSSFactory = new CloudOSSFactory();
        $this->_instance = $cloudOSSFactory->createDisk($this->_server, $cloudSet);
    }

    /**
     * 获取office文件的阅读地址
     *
     * @param $idString
     * @return string
     */
    public function getOfficeReadUrl($idString)
    {
        // return "http://o.ibos.cn/op/view.aspx?src=" . urlencode( $this->getRealUrl( $attachUrl ) );
        $urlManager = Ibos::app()->urlManager;
        return $urlManager->createUrl('main/attach/office', array('id' => $idString, 'op' => 'read'));
    }

    /**
     * 取得存储文件的网站地址
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->_instance->getSiteUrl();
    }

    /**
     * 获取文件真实路径（远程路径）
     * @param string $file 文件相对路径
     * @return string
     */
    public function getRealUrl($file)
    {
        return $this->getSiteUrl() . $file;
    }

    /**
     * 处理附件(将文件移动到云盘)
     * @param mix $attachids 附件id
     * @return array
     */
    public function moveAttach($attachids)
    {
        $attachDir = File::getAttachUrl() . '/';
        $attachs = Attach::getAttachData($attachids);
        foreach ($attachs as $attach) {
            $key = $attachDir . $attach['attachment'];
            $res = false;
            if (File::fileExists($key)) {
                $res = $this->_instance->putObject(array('key' => $key, 'content' => File::readFile($key)));
            }
            if ($res) {
                File::deleteFile($key);
            }
        }
        return $res;
    }

    /**
     * 创建空白office文件
     * @param string $attachment 文件名（带路径）
     * @return boolean
     */
    public function mkOffice($file)
    {
        return $this->_instance->putObject(array('key' => $file, 'content' => ''));
    }

    /**
     * 获取一个文件内容
     * @param string $file 带路径文件
     * @return string
     */
    public function getContent($file)
    {
        return $this->_instance->getObjectContent(array('key' => $file));
    }

    /**
     * 删除附件
     * @param mix $aids 附件id
     * @return integer
     */
    public function delAttach($aids)
    {
        $attachs = Attach::getAttachData($aids);
        $attachDir = File::getAttachUrl() . '/';
        $count = 0;
        foreach ($attachs as $attach) {
            $key = $attachDir . $attach['attachment'];
            if ($this->_instance->deleteObject(array('key' => $key))) {
                $count++;
            }
        }
        return $count;
    }

}
