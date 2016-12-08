<?php

/**
 * 文件柜模块
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------  文件操作api
 * @package application.modules.file.core
 * @version $Id: FileOperationApi.php 3297 2014-06-19 09:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\core;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\File as FileUtil;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\System;
use application\extensions\Zip;
use application\modules\file\model\File;
use application\modules\file\model\FileDetail;
use application\modules\file\model\FileTrash;
use application\modules\file\utils\FileCheck;
use application\modules\file\utils\FileData;
use application\modules\file\utils\FileOffice;
use application\modules\main\model\Attachment;
use application\modules\main\model\AttachmentUnused;
use CException;

Class FileOperationApi extends System
{

    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 上传
     * @param FileAttr $fileAttr 文件属性对象
     * @param FileCore $core 附件处理对象
     * @param mix $attachids 附件ids
     * @return boolean
     */
    public function upload(FileAttr $fileAttr, FileCore $core, $attachids)
    {
        if (empty($attachids)) {
            return false;
        }
        Attach::updateAttach($attachids);
        //$core->moveAttach( $attachids );
        $res = File::model()->addObject($fileAttr->pid, $attachids, $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
        return $res;
    }

    /**
     * 创建文件夹
     * @param FileAttr $fileAttr 文件属性对象
     * @param string $dirName 文件夹名
     * @return integer
     */
    public function mkDir(FileAttr $fileAttr, $dirName)
    {
        $fid = File::model()->addDir($fileAttr->pid, $dirName, $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
        return $fid;
    }

    /**
     * 新建office文件
     * @param FileAttr $fileAttr 文件属性对象
     * @param FileCore $core 附件处理对象
     * @param string $name 文件名
     * @param string $type 文件类型('doc', 'ppt', 'xls')
     * @param string $moduleName 模块名
     * @return boolean
     */
    public function mkOffice(FileAttr $fileAttr, FileCore $core, $name, $type, $moduleName = 'file')
    {
        $attachDir = FileUtil::getAttachUrl() . '/';
        $moduleDir = $moduleName . '/';
        $ymDir = date('Ym') . '/';
        $dDir = date('d') . '/';
        $random = date('His') . strtolower(StringUtil::random(16));
        $saveName = $random . '.' . $type;
        $attachment = $moduleDir . $ymDir . $dDir . $saveName;
        $dir = $attachDir . $moduleDir . $ymDir . $dDir;
        if ($fileAttr->cloudid == 0 && !is_dir(FileUtil::fileName($dir))) {
            FileUtil::makeDirs($dir);
        }
        $core->mkOffice($attachDir . $attachment);
        $aid = Attachment::model()->add(array('uid' => $fileAttr->uid, 'tableid' => 127), true);
        if ($aid) {
            $data = array(
                'aid' => $aid,
                'uid' => $fileAttr->uid,
                'dateline' => TIMESTAMP,
                'filename' => $name,
                'filesize' => 0,
                'attachment' => $attachment,
                'isimage' => 0,
                'description' => ''
            );
            AttachmentUnused::model()->add($data);
            Attach::updateAttach($aid);
            $fid = File::model()->addObject($fileAttr->pid, $aid, $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
            return $fid;
        } else {
            return false;
        }
    }

    /**
     * 重命名
     * @param integer $fid 文件id
     * @param string $newName 新名称
     * @return boolean
     */
    public function rename($fid, $newName)
    {
        $res = File::model()->modify($fid, array('name' => $newName));
        return $res;
    }

    /**
     * 下载
     * @param FileCore $core 附件处理对象
     * @param mix $fids 文件ids
     * @param type $downloadName 下载名
     * @throws CException
     */
    public function download(FileCore $core, $fids, $downloadName = '')
    {
        $uid = Ibos::app()->user->uid;
        $fids = is_array($fids) ? $fids : explode(',', $fids);
        $attachDir = FileUtil::getAttachUrl() . '/';
        $output = '';
        $downloadZip = 1; // 1表示压缩包下载
        if (count($fids) == 1) {
            $file = File::model()->fetchByFid($fids[0]);
            if ($file['isdel'] == 1) {
                return false;
            }
            if ($file && $file['type'] == 0) { // 如果是单文件，不打包
                $downloadZip = 0; // 1表示单文件下载
            }
        }
        if ($downloadZip == 1) {
            $attachname = (empty($downloadName) ? TIMESTAMP : $downloadName) . '.zip';
            $zip = new Zip();
            $fileList = File::model()->fetchAllByFids($fids);
            foreach ($fileList as $file) {
                if (!FileCheck::getInstance()->isReadable($file['fid'], $uid)) {
                    continue;
                }
                if ($file['type'] == '1') { // 文件夹
                    $sourcePath = FileOffice::getSourcePath($file['idpath']);
                    $subfiles = File::model()->fetchAllSubByIdpath($file['fid']);
                    foreach ($subfiles as $subfile) {
                        if ($subfile['type'] == '0') { // 文件
                            $attach = $this->getAttachByAid($subfile['attachmentid']);
                            if (!empty($attach)) {
                                if ($file['pid'] != 0) {
                                    $name = str_replace($sourcePath, $file['name'] . '/', FileOffice::getSourcePath($subfile['idpath'])) . $subfile['name'];
                                } else {
                                    $name = FileOffice::getSourcePath($subfile['idpath']) . $subfile['name'];
                                }
                                $name = trim($name, '/');
                                $content = $core->getContent($attachDir . $attach['attachment']);
                                $zip->addFile($content, Convert::iIconv($name, CHARSET, 'gbk'));
                            }
                        }
                    }
                } else {
                    $attach = $this->getAttachByAid($file['attachmentid']);
                    if (!empty($attach)) {
                        $content = $core->getContent($attachDir . $attach['attachment']);
                        $zip->addFile($content, Convert::iIconv($file['name'], CHARSET, 'gbk'));
                    }
                }
            }
            $output = $zip->file();
        } else {
            $attachname = $file['name'];
            $attach = $this->getAttachByAid($file['attachmentid']);
            if (!FileCheck::getInstance()->isReadable($fids[0], $uid)) {
                return false;
            }
            $output = $core->getContent($attachDir . $attach['attachment']);
        }
        if (ob_get_length()) {
            ob_end_clean();
        }
        header("Content-type: text/html; charset=" . CHARSET);
        header("Cache-control: private");
        header("Content-type: application/x-zip");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($output));
        header("Content-Length: " . strlen($output));
        header("Content-Disposition: attachment; filename= " . urlencode($attachname));
        echo $output;
    }

    /**
     * 获取单个附件
     * @param integer $attachid 附件id
     * @return array
     */
    private function getAttachByAid($attachid)
    {
        $attachs = Attach::getAttachData($attachid);
        $attach = array_shift($attachs);
        return $attach;
    }

    /**
     * 复制
     * @param FileAttr $fileAttr 文件属性对象
     * @param mix $sourceFids 操作的文件fids
     * @param integer $targetFid 目标文件夹id
     * @return boolean
     */
    public function copy(FileAttr $fileAttr, $sourceFids, $targetFid)
    {
        // 处理同名文件/文件夹
        $firstSub = File::model()->fetchFirstSubByPid($targetFid, $fileAttr->uid);
        $names = Convert::getSubByKey($firstSub, 'name');
        $sources = File::model()->fetchAllByFids($sourceFids);
        $sourceRes = FileOffice::handleSameName($sources, $names);
        foreach ($sourceRes as $s) {
            // 如果是文件夹，找深层文件
            if ($s['type'] == 1) {
                $fid = File::model()->addDir($targetFid, $s['name'], $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
                $allSubs = File::model()->fetchNoDelSubByIdpath($s['fid']);
                $subs = FileData::hanldleLevelChild($allSubs, $s['fid']);
                $this->insertSingleCopyData($fileAttr, $subs, $fid);
            } else {
                File::model()->copy($targetFid, $s, $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
            }
        }
        return true;
    }

    /**
     * 递归添加复制数据
     * @param FileAttr $fileAttr 文件属性对象
     * @param array $files 用来copy的数据
     * @param integer $targetFid 目标文件夹
     */
    private function insertCopyData(FileAttr $fileAttr, $files, $targetFid)
    {
        foreach ($files as $f) {
            // 如果是文件夹，找深层文件
            if ($f['type'] == 1) {
                $fid = File::model()->addDir($targetFid, $f['name'], $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
                $this->insertCopyData($fileAttr, $f['child'], $fid);
            } else {
                File::model()->addObject($targetFid, $f, $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
            }
        }
        return true;
    }

    /*
     * 文件柜递归添加复制数据
     */
    private function insertSingleCopyData(FileAttr $fileAttr, $files, $targetFid)
    {
        foreach ($files as $f) {
            // 如果是文件夹，找深层文件
            if ($f['type'] == 1) {
                $fid = File::model()->addDir($targetFid, $f['name'], $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
                $this->insertCopyData($fileAttr, $f['child'], $fid);
            } else {
                File::model()->copy($targetFid, $f, $fileAttr->uid, $fileAttr->belongType, $fileAttr->cloudid);
            }
        }
        return true;
    }

    /**
     * 剪切
     * @param FileAttr $fileAttr 文件属性对象
     * @param mix $sourceFids 操作的文件fids
     * @param integer $targetFid 目标文件夹id
     * @return boolean
     */
    public function cut(FileAttr $fileAttr, $sourceFids, $targetFid)
    {
        $sourceFids = is_array($sourceFids) ? $sourceFids : explode(',', $sourceFids);
        $target = File::model()->fetchByFid($targetFid);
        if (!empty($target)) { // 子文件夹
            $targetIdpath = $target['idpath'] . $targetFid . '/';
        } else { // 用户顶级目录
            $targetIdpath = '/0/';
        }
        // 处理同名文件/文件夹
        $firstSub = File::model()->fetchFirstSubByPid($targetFid, $fileAttr->uid);
        $names = Convert::getSubByKey($firstSub, 'name');
        $sources = File::model()->fetchAllByFids($sourceFids);
        $sourceRes = FileOffice::handleSameName($sources, $names);
        foreach ($sourceRes as $s) {
            // 先更新深层文件和文件夹
            if ($s['type'] == 1) {
                $oldIdpath = $s['idpath'];
                $subs = File::model()->fetchAllSubByIdpath($s['fid']);
                foreach ($subs as $sub) {
                    $newIdpath = str_replace($oldIdpath, $targetIdpath, $sub['idpath']);
                    File::model()->updateByPk($sub['fid'], array('idpath' => $newIdpath, 'belong' => $fileAttr->belongType, 'cloudid' => $fileAttr->cloudid));
                }
            }
            // 更新操作文件
            File::model()->updateByPk($s['fid'], array('name' => $s['name'], 'pid' => $targetFid, 'idpath' => $targetIdpath, 'belong' => $fileAttr->belongType, 'cloudid' => $fileAttr->cloudid));
        }
        return true;
    }

    /**
     * 删除到回收站
     * @param mix $fids 要删除的文件fid
     * @return integer
     */
    public function recycle($fids)
    {
        $fids = is_array($fids) ? $fids : explode(',', $fids);
        $delFids = array();
        foreach ($fids as $fid) {
            FileTrash::model()->add(array('fid' => $fid, 'deltime' => TIMESTAMP));
            $sub = File::model()->fetchAllSubByIdpath($fid); // 子文件/文件夹
            $subFids = Convert::getSubByKey($sub, 'fid');
            $delFids = array_merge($delFids, $subFids, array($fid));
        }
        return File::model()->updateByPk($delFids, array('isdel' => 1));
    }

    /**
     * 标记
     * @param integer $fid 文件fid
     * @param integer $mark 标记(1为标记，0为取消标记)
     */
    public function mark($fid, $mark)
    {
        return FileDetail::model()->updateAll(array('mark' => $mark), "fid={$fid}");
    }

}
