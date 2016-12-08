<?php

/**
 * 文件柜模块------ file表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------  文件、文件夹通用信息表
 * @package application.modules.file.model
 * @version $Id: File.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\File as FileUtil;
use application\core\utils\Ibos;
use application\core\utils\Image;
use application\core\utils\StringUtil;
use CDbCriteria;
use CPagination;

class File extends Model
{

    /**
     * 文件类型
     */
    const FILE = 0;

    /**
     * 文件夹类型
     */
    const FOLDER = 1;

    /**
     * 所属个人
     */
    const BELONG_PERSONAL = 0;

    /**
     * 所属公司
     */
    const BELONG_COMPANY = 1;

    /**
     * 每页显示文件个数
     */
    const PAGESIZE = 21;

    /**
     * 顶级文件夹的idpath
     */
    const TOP_IDPATH = '/0/';

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file}}';
    }

    /**
     * 获取分页和数据(个人网盘)
     * @param string $conditions 条件
     * @param string $order 排序
     * @param integer $pageSize 每页显示个数
     * @return array
     */
    public function fetchList($condition = '', $order = null, $pageSize = null)
    {
        $order = "f.type DESC, " . (is_null($order) ? "f.addtime DESC" : $order);
        $count = Ibos::app()->db->createCommand()
            ->select("count(f.fid)")
            ->from("{{file}} f")
            ->leftJoin("{{file_detail}} fd", "f.`fid` = fd.`fid`")
            ->where($condition)
            ->queryScalar();
        $pages = new CPagination($count);
        $limit = is_null($pageSize) ? self::PAGESIZE : $pageSize;
        $criteria = new CDbCriteria();
        $criteria->order = $order;
        $pages->applyLimit($criteria);
        $pages->setPageSize(intval($limit));
        $datas = Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file}} f")
            ->leftJoin("{{file_detail}} fd", "f.`fid` = fd.`fid`")
            ->where($condition)
            ->order($order)
            ->limit($limit)
            ->offset($pages->getOffset())
            ->queryAll();
        $p = array('count' => $count, 'limit' => $limit, 'curPage' => $pages->getCurrentPage());
        return array('pages' => $p, 'datas' => $datas);
    }

    /**
     * 根据条件获取符合条件的fids数组
     * @param string $condition 条件
     * @return array
     */
    public function fetchFidsByCondition($condition)
    {
        $record = Ibos::app()->db->createCommand()
            ->select("f.fid")
            ->from("{{file}} f")
            ->leftJoin("{{file_detail}} fd", "f.fid=fd.fid")
            ->where($condition)
            ->queryAll();
        return Convert::getSubByKey($record, 'fid');
    }

    /**
     * 添加文件夹
     * @param integer $pid 上级文件夹id
     * @param string $name 文件夹名
     * @param integer $uid 用户id
     * @param integer $belong 所属（0为个人，1为公司）
     * @return integer 返回添加的fid
     * @param integer $cloudid 云盘（0为本地，其他为云盘id）
     */
    public function addDir($pid, $name, $uid, $belong, $cloudid)
    {
        $data = array(
            'pid' => intval($pid),
            'uid' => intval($uid),
            'name' => htmlspecialchars(strtolower($name)),
            'type' => 1,
            'remark' => '',
            'size' => 0,
            'addtime' => TIMESTAMP,
            'idpath' => self::TOP_IDPATH, //默认在根文件夹，开头必需有斜杠
            'belong' => $belong,
            'cloudid' => $cloudid
        );
        if ($pid > 0) {
            $dir = $this->fetchByPk($pid);
            if (!empty($dir)) {
                $data['idpath'] = $dir['idpath'] . $dir['fid'] . '/';
            }
        }
        $fid = $this->add($data, true);
        return $fid;
    }

    /**
     * 生成缩略图
     * @param array $attach 附件数据
     * @param integer $thumbWidth 缩略图宽
     * @param integer $thumbHeight 缩略图高
     * @return string 返回缩略图真实路径
     */
    public function createThumb($attach, $thumbWidth = 96, $thumbHeight = 96)
    {
        $imagePath = FileUtil::getAttachUrl() . '/' . $attach['attachment'];
        $imageType = StringUtil::getFileExt($attach['filename']);
        $thumbName = 'thumb_' . date('His') . strtolower(StringUtil::random(16)) . '.' . $imageType;
        $sourceFileName = explode('/', $imagePath);
        $sourceFileName[count($sourceFileName) - 1] = $thumbName;
        $thumb = implode('/', $sourceFileName);
        $imgUrl = Ibos::engine()->io()->file()->thumbnail($imagePath, $thumb, $thumbWidth, $thumbHeight);
        return $imgUrl;
    }

    /**
     * 添加一个文件
     * @param integer $pid 所在文件夹id
     * @param mix $attachids 附件id
     * @param integer $uid 用户id
     * @param integer $belongType 所属（0为个人，1为公司）
     * @param integer $cloudid 云盘（0为本地，其他为云盘id）
     * @return integer 返回最后一个添加的fid
     */
    public function addObject($pid, $attachids, $uid, $belong, $cloudid)
    {
        $attachments = Attach::getAttachData($attachids);
        foreach ($attachments as $attach) {
            $data = array(
                'pid' => intval($pid),
                'uid' => intval($uid),
                'name' => $attach['filename'],
                'type' => 0,
                'remark' => '',
                'size' => $attach['filesize'],
                'addtime' => TIMESTAMP,
                'idpath' => self::TOP_IDPATH, //默认在根文件夹，开头必需有斜杠
                'belong' => $belong,
                'cloudid' => $cloudid
            );
            if ($pid > 0) {
                $dir = $this->findByPk($pid);
                if (!empty($dir)) {
                    $data['idpath'] = $dir['idpath'] . $dir['fid'] . '/';
                }
            }
            $fid = $this->add($data, true);
            $fileType = StringUtil::getFileExt($attach['filename']);
            if ($fid && in_array($fileType, array('jpg', 'png',))) { // 生成缩略图
                $thumb = $this->createThumb($attach);
            }
            $detail = array(
                'fid' => $fid,
                'attachmentid' => isset($attach['aid']) ? $attach['aid'] : 0,
                'filetype' => $fileType,
                'mark' => 0,
                'thumb' => isset($thumb) ? $thumb : ''
            );
            FileDetail::model()->add($detail);
        }
        return $fid;
    }

    /**
     * 复制一个文件
     * @param integer $pid 所在文件夹id
     * @param array $sourceFile 源文件数据
     * @param integer $uid 用户id
     * @param integer $belongType 所属（0为个人，1为公司）
     * @param integer $cloudid 云盘（0为本地，其他为云盘id）
     * @return integer 返回添加的fid
     */
    public function copy($pid, $sourceFile, $uid, $belong, $cloudid)
    {
        $data = array(
            'pid' => intval($pid),
            'uid' => intval($uid),
            'name' => $sourceFile['name'],
            'type' => 0,
            'remark' => '',
            'size' => $sourceFile['size'],
            'addtime' => TIMESTAMP,
            'idpath' => self::TOP_IDPATH, //默认在根文件夹，开头必需有斜杠
            'belong' => $belong,
            'cloudid' => $cloudid
        );
        if ($pid > 0) {
            $dir = $this->fetchByPk($pid);
            if (!empty($dir)) {
                $data['idpath'] = $dir['idpath'] . $dir['fid'] . '/';
            }
        }
        $fid = $this->add($data, true);
        $attachments = Attach::getAttachData($sourceFile['attachmentid']);
        $attach = array_shift($attachments);
        if ($fid && !empty($attach) && $attach['isimage']) { // 生成缩略图
            $thumb = $this->createThumb($attach);
        }
        $detail = array(
            'fid' => $fid,
            'attachmentid' => isset($sourceFile['attachmentid']) ? $sourceFile['attachmentid'] : 0,
            'filetype' => StringUtil::getFileExt($sourceFile['name']),
            'mark' => 0,
            'thumb' => isset($thumb) ? $thumb : ''
        );
        FileDetail::model()->add($detail);
        return $fid;
    }

    /**
     * 获取某个文件夹下的所有文件或文件夹（包括深层文件和文件夹）
     * @param integer $fid 文件夹id
     * @return array
     */
    public function fetchAllSubByIdpath($fid)
    {
        $records = array();
        $fid = intval($fid);
        if ($fid) {
            $condition = "f.`idpath` LIKE '%\/{$fid}\/%'";
            $records = Ibos::app()->db->createCommand()
                ->select("*,f.fid AS fid")
                ->from("{{file}} f")
                ->leftJoin("{{file_detail}} fdt", "f.`fid` = fdt.`fid`")
                ->where($condition)
                ->queryAll();
        }
        return $records;
    }

    /*
     * 获取某个文件夹下的没有被删除的所有文件和文件夹（包括深层文件和文件夹）
     */
    public function fetchNoDelSubByIdpath($fid)
    {
        $records = array();
        $fid = intval($fid);
        if ($fid) {
            $condition = "f.`idpath` LIKE '%\/{$fid}\/%' AND f.`isdel`=0";
            $records = Ibos::app()->db->createCommand()
                ->select("*,f.fid AS fid")
                ->from("{{file}} f")
                ->leftJoin("{{file_detail}} fdt", "f.`fid` = fdt.`fid`")
                ->where($condition)
                ->queryAll();
        }
        return $records;
    }

    /**
     * 获取一个文件夹的总大小
     * @param integer $fid 文件夹id（必须是文件夹，若果传的是文件，会返回0）
     * @return integer
     */
    public function countSizeByFid($fid)
    {
        $fid = intval($fid);
        $condition = "f.`idpath` LIKE '%\/{$fid}\/%'";
        $size = Ibos::app()->db->createCommand()
            ->select("sum(f.size)")
            ->from("{{file}} f")
            ->where($condition)
            ->queryScalar();
        return intval($size);
    }

    /**
     * 查找某个文件夹下的首级文件和文件夹，以主键形式数组返回
     * @param integer $pid 要查找的父级id
     * @param integer $uid 用户id(因为pid=0的情况必须指定uid)
     * @return array
     */
    public function fetchFirstSubByPid($pid, $uid)
    {
        $records = Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file}} f")
            ->leftJoin("{{file_detail}} fdt", "f.`fid` = fdt.`fid`")
            ->where(sprintf("f.`pid`=%d AND f.`uid`=%d", intval($pid), intval($uid)))
            ->queryAll();
        $res = array();
        foreach ($records as $file) {
            $res[$file['fid']] = $file;
        }
        return $res;
    }

    /**
     * 在某个目录下查找名为$name的数据
     * @param string $name 文件名（全名）
     * @param int $pid 上级目录
     * @return array
     */
    public function fetchByNameWidthPid($name, $pid, $uid)
    {
        return $this->fetchAll("`name` = '{$name}' AND `pid` = {$pid} AND `uid` = {$uid} AND `isdel` = 0");
    }

    /**
     * 根据fid关联查询一条文件数据
     * @param integer $fid
     * @return array
     */
    public function fetchByFid($fid)
    {
        return Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file}} f")
            ->leftJoin("{{file_detail}} fdt", "f.`fid` = fdt.`fid`")
            ->where(sprintf("f.`fid` = %d", intval($fid)))
            ->queryRow();
    }

    /**
     * 根据fids获取所有关联数据，返回已主键做键值的数组
     * @param mix $fids fid数组或逗号隔开字符串
     * @return array
     */
    public function fetchAllByFids($fids)
    {
        $fids = is_array($fids) ? implode(',', $fids) : $fids;
        $records = Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file}} f")
            ->leftJoin("{{file_detail}} fdt", "f.`fid` = fdt.`fid`")
            ->where("FIND_IN_SET(f.`fid`, '{$fids}')")
            ->queryAll();
        $res = array();
        foreach ($records as $file) {
            $res[$file['fid']] = $file;
        }
        return $res;
    }

    /**
     * 获取一条组合共享表的数据
     * @param integer $fid 文件id
     * @return array
     */
    public function fetchWithShare($fid)
    {
        return Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file}} f")
            ->leftJoin("{{file_share}} fs", "f.`fid` = fs.`fid`")
            ->where(sprintf("f.`fid` = %d", intval($fid)))
            ->queryRow();
    }

    /**
     * 根据fid获取附件id
     * @param integer $fid
     * @return integer
     */
    public function fetchAttachmentidByFid($fid)
    {
        $record = $this->fetchByFid($fid);
        return intval($record['attachmentid']);
    }

    /**
     * 获取用户已用容量
     * @param integer $uid 用户uid
     * @return integer
     */
    public function getUsedSize($uid, $cloudid)
    {
        $where = array('and', "uid={$uid}", 'isdel=0', 'belong=0', "cloudid={$cloudid}");
        $count = Ibos::app()->db->createCommand()
            ->select('SUM(size) AS sum')
            ->from("{{file}}")
            ->where($where)
            ->queryScalar();
        return intval($count);
    }

    /**
     * 根据fid获取文件/文件夹名
     * @param integer $fid 文件/文件夹id
     * @return string
     */
    public function fetchNameByFid($fid)
    {
        $name = '';
        $record = $this->fetchByFid($fid);
        if (!empty($record)) {
            $name = $record['name'];
        }
        return $name;
    }

}
