<?php

/**
 * 文件柜模块------ file_trash表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------  回收站
 * @package application.modules.file.model
 * @version $Id: FileTrash.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\file\utils\FileOffice;
use CDbCriteria;
use CPagination;

class FileTrash extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file_trash}}';
    }

    /**
     * 获取回收站分页列表
     * @return array
     */
    public function fetchList($condition)
    {
        $count = Ibos::app()->db->createCommand()
            ->select("count(ft.fid)")
            ->from("{{file_trash}} ft")
            ->leftJoin("{{file}} f", "f.`fid` = ft.`fid`")
            ->where($condition)
            ->queryScalar();
        $pages = new CPagination($count);
        $order = "ft.deltime DESC";
        $criteria = new CDbCriteria();
        $criteria->order = $order;
        $pages->applyLimit($criteria);
        $limit = Ibos::app()->params['basePerPage'];
        $pages->setPageSize(intval($limit));
        $datas = Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file_trash}} ft")
            ->leftJoin("{{file}} f", "f.`fid` = ft.`fid`")
            ->where($condition)
            ->order($order)
            ->limit($limit)
            ->offset($pages->getOffset())
            ->queryAll();
        return array('pages' => $pages, 'datas' => $datas, 'count' => $count);
    }

    /**
     * 从回收站还原
     * @param mix $fids 要还原的文件夹数组或逗号隔开字符串
     * @return boolean
     */
    public function restore($fids)
    {
        if (empty($fids)) {
            return false;
        }
        $fids = is_array($fids) ? $fids : explode(',', $fids);
        $restore = array();
        foreach ($fids as $fid) {
            $sub = File::model()->fetchAllSubByIdpath($fid);
            $subFids = Convert::getSubByKey($sub, 'fid');
            $restore = array_merge($restore, $subFids, array($fid)); // file表要还原的fid
        }
        $fidStr = implode(',', $fids);
        $this->deleteAll("FIND_IN_SET(`fid`, '{$fidStr}')");
        if (!empty($restore)) {
            $res = File::model()->updateByPk($restore, array('isdel' => 0, 'deltime' => 0));
            return $res;
        }
        return false;
    }

    /**
     * 彻底删除
     * @param mix $fids 要删除的文件夹数组或逗号隔开字符串
     * @return boolean
     */
    public function fully($fids)
    {
        $files = File::model()->fetchAllByFids($fids);
        $deletes = array();
        foreach ($files as $f) {
            $sub = File::model()->fetchAllSubByIdpath($f['fid']);
            $deletes = array_merge($deletes, $sub, array($f));
        }
        FileOffice::delAttach($deletes);
        $delFids = Convert::getSubByKey($deletes, 'fid');
        $delFidStr = implode(',', $delFids);
        $this->deleteAll("FIND_IN_SET(`fid`, '{$delFidStr}')"); // 删除回收站
        FileShare::model()->deleteAll("FIND_IN_SET(`fid`, '{$delFidStr}')"); // 删除共享数据
        FileDetail::model()->deleteAll("FIND_IN_SET(`fid`, '{$delFidStr}')"); // 删除详细信息
        $res = File::model()->deleteByPk($delFids, "`isdel`=1"); // 删除文件数据
        return $res;
    }

}
