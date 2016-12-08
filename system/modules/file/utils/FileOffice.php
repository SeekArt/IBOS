<?php

/**
 * 文件柜模块
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------  工具类
 * @package application.modules.assignment.util
 * @version $Id: FileOffice.php 3297 2014-06-19 09:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\utils;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\System;
use application\modules\file\core\FileCloud;
use application\modules\file\core\FileLocal;
use application\modules\file\model\File;
use application\modules\file\model\FileDetail;

class FileOffice extends System
{

    /**
     * 获取面包屑
     * @param integer $fid 文件夹id
     * @param array $firstFolder 开始文件夹
     * @return type
     */
    public static function getBreadCrumb($fid)
    {
        $breadCrumbs = array();
        if (!empty($fid)) {
            $dir = File::model()->fetchByFid($fid);
            $breadCrumbs = self::getParentsByIdPath($dir['idpath']);
            array_push($breadCrumbs, $dir);
        }
        return array_values($breadCrumbs);
    }

    /**
     * 获取类型对应所有后缀
     * @return array
     */
    public static function getAllType()
    {
        $type = array(
            'excel' => 'xlsx,xlsm,xlsb,xltx,xltm,xlt,xls,xml,xlam,xla,xlw,csv',
            'word' => 'doc,docm,docx,dot,dotm,dotx',
            'ppt' => 'pptx,pptm,ppt,potx,potm,pot,pps,ppsx,ppsm,ppam,ppa',
            'text' => 'txt',
            'image' => 'bmp,pcx,tiff,gif,jpeg,jpg,tga,exif,fpx,svg,psd,cdr,pcd,dxf,ufo,eps,png,cdr,wmf,emf,hdri,ai,raw,ic,fli,flc',
            'package' => 'zip,rar,7z,tar,gz,bz2',
            'audio' => 'aac,ac3,acc,aiff,amr,ape,au,cda,dts,flac,m1a,m2a,m4a,mka,mp2,mp3,mpa,mpc,ra,tta,wav,wma,wv,mid,midi,ogg,oga',
            'video' => 'asf,avi,wm,wmp,wmv,ram,rm,rmvb,rp,rpm,rt,smil,scm,dat,m1v,m2v,m2p,m2ts,mp2v,mpe,mpeg,mpeg1,mpeg2,mpg,mpv2,pss,pva,tp,tpr,ts,m4b,m4p,mp4,mpeg4,3g2,3gp,3gp2,3gpp,mov,qt,mov,qt,flv,f4v,swf,hlv,ifo,vob,amv,csf,divx,evo,mkv,mod,pmp,vp6,bik,mts,xv,xlmv,ogm,ogv,ogx,dvd',
            'program' => 'exe,bat,dll,sh,rpm,srpm,deb'
        );
        return $type;
    }

    /**
     * 通过idpath获得所有父级id
     * @param string $idPath 文件idpath
     * @return array
     */
    public static function getPidsByIdPath($idPath)
    {
        $pids = array();
        if (preg_match('/^\/(\d+\/)+$/', $idPath)) {
            $idPath = str_replace('/0/', '', $idPath);
            $pids = explode('/', trim($idPath, '/'));
        }
        return $pids;
    }

    /**
     * 通过idpath获得所有父级,已fid为键值返回父级文件数组
     * @param string $idPath 文件idpath
     * @return array
     */
    public static function getParentsByIdPath($idPath)
    {
        $pids = self::getPidsByIdPath($idPath);
        $parents = File::model()->fetchAllByFids($pids);
        return $parents;
    }

    /**
     * 获取文件名称路径，如果找不到将返回空的路径
     * @param string $idPath
     * @return string $sourcePath
     */
    public static function getSourcePath($idPath, $flag = '/')
    {
        $sourcePath = '';
        if (preg_match('/^\/(\d+\/)+$/', $idPath)) {
            $sourcePath = '/';
            $fids = explode('/', trim($idPath, '/'));
            $fileList = File::model()->fetchAllByFids($fids);
            foreach ($fids as $fid) {
                if ($fid === '0') {
                    continue;
                }
                if (isset($fileList[$fid])) {
                    $sourcePath .= $fileList[$fid]['name'] . $flag;
                }
            }
        }
        return $sourcePath;
    }

    /**
     * 处理同名文件/文件夹，并更改同名文件名（结尾加(1)等等）
     * @param array $files 要处理的文件数组
     * @param type $names
     * @return type
     */
    public static function handleSameName($files, $names)
    {
        foreach ($files as $k => $f) {
            if (in_array($f['name'], $names)) {
                $files[$k] = self::changeName($f, $names);
            }
        }
        return $files;
    }

    /**
     * 更改同名文件/文件夹名（结尾加(1)等等）
     * @param string $file 原文件
     * @param array $names 对比文件数组
     * @param integer $k 结尾数字
     * @return string
     */
    public static function changeName($file, $names, $k = 2)
    {
        if ($file['type'] == 0) { // 文件
            $info = pathinfo($file['name']);
            $file['name'] = $info['filename'] . '(' . $k . ')' . '.' . $info['extension'];
        } else { // 文件夹
            $file['name'] = $file['name'] . '(' . $k . ')';
        }
        if (in_array($file['name'], $names)) {
            $k++;
            self::changeName($file, $names, $k);
        }
        return $file;
    }

    /**
     * 删除附件
     * @param array $deletes 要删除的文件数据
     */
    public static function delAttach($deletes)
    {
        if (is_array($deletes)) {
            $details = FileDetail::model()->fetchAll();
            $aids = Convert::getSubByKey($details, 'attachmentid');
            $counts = array_count_values($aids);
            $delAttachIds = array();
            foreach ($deletes as $d) {
                $curAid = $d['attachmentid'];
                if (isset($counts[$curAid]) && $counts[$curAid] <= 1) { // 只取有仅只有1次的附件，即没有copy过
                    $clId = $d['cloudid'];
                    $delAttachIds[$clId][] = $d['attachmentid'];
                }
            }
            // 可同时删除本地盘及不同云盘之间附件
            foreach ($delAttachIds as $clid => $attachid) {
                if (!empty($attachid)) {
                    if ($clid == 0) { // 本地
                        $core = new FileLocal();
                    } else {
                        $core = new FileCloud($clid);
                    }
                    $core->delAttach($attachid);
                }
            }
            return true;
        }
    }

    /**
     * 获取未知类型文件图标
     * @return string
     */
    public static function getUnknownIcon()
    {
        return Attach::ICON_PATH . 'unknown.png';
    }

}
