<?php

/**
 * 文件柜模块------ file_share表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------  共享文件数据表
 * @package application.modules.file.model
 * @version $Id: FileShare.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\user\model\User;
use CDbCriteria;
use CPagination;

class FileShare extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file_share}}';
    }

    /**
     * 根据条件获取符合条件的fid集合
     * @param string $condition
     * @return array
     */
    public function fetchFidsByCondition($condition)
    {
        $record = Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file_share}} fs")
            ->leftJoin("{{file}} f", "f.`fid`=fs.`fid`")
            ->leftJoin("{{file_detail}} fd", "fd.`fid`=fs.`fid`")
            ->where($condition)
            ->queryAll();
        return Convert::getSubByKey($record, 'fid');
    }

    /**
     * 获取我收到的共享首页列表
     * @param string $condition 条件
     * @param integer $pageSize 分页大小
     * @return array
     */
    public function getIndexList($condition = '', $pageSize = null)
    {
        $count = Ibos::app()->db->createCommand()
            ->select("count(fs.fromuid)")
            ->from("{{file_share}} fs")
            ->leftJoin("{{file}} f", "f.`fid`=fs.`fid`")
            ->leftJoin("{{file_detail}} fd", "fd.`fid`=fs.`fid`")
            ->where($condition)
            ->group('fs.fromuid')
            ->queryScalar();
        $pages = new CPagination($count);
        $limit = is_null($pageSize) ? File::PAGESIZE : $pageSize;
        $pages->setPageSize(intval($limit));
        $datas = Ibos::app()->db->createCommand()
            ->select("fs.fromuid, MAX(fs.uptime) AS uptime, fr.viewtime")
            ->from("{{file_share}} fs")
            ->leftJoin("{{file}} f", "f.`fid`=fs.`fid`")
            ->leftJoin("{{file_detail}} fd", "fd.`fid`=fs.`fid`")
            ->leftJoin("{{file_reader}} fr", "fs.`fromuid`=fr.`fromuid`")
            ->where($condition)
            ->group('fs.fromuid')
            ->order("fs.uptime DESC")
            ->limit($limit)
            ->offset($pages->getOffset())
            ->queryAll();
        return array('pages' => $pages, 'datas' => $datas);
    }

    /**
     * 获取收到的共享的列表（非首页）
     * @param string $condition 条件
     * @param string $order 排序
     * @param integer $pageSize 分页大小
     * @return type
     */
    public function getList($condition = '', $order = null, $pageSize = null)
    {
        $order = "f.type DESC, " . (is_null($order) ? "f.addtime DESC" : $order);
        $count = Ibos::app()->db->createCommand()
            ->select("count(fs.fid)")
            ->from("{{file_share}} fs")
            ->leftJoin("{{file}} f", "f.`fid` = fs.`fid`")
            ->leftJoin("{{file_detail}} fd", "fd.`fid`=fs.`fid`")
            ->where($condition)
            ->queryScalar();
        $pages = new CPagination($count);
        $limit = is_null($pageSize) ? File::PAGESIZE : $pageSize;
        $criteria = new CDbCriteria();
        $criteria->order = $order;
        $pages->applyLimit($criteria);
        $pages->setPageSize(intval($limit));
        $datas = Ibos::app()->db->createCommand()
            ->select("*,f.fid AS fid")
            ->from("{{file}} f")
            ->leftJoin("{{file_detail}} fd", "f.`fid` = fd.`fid`")
            ->leftJoin("{{file_share}} fs", "f.`fid` = fs.`fid`")
            ->where($condition)
            ->order($order)
            ->limit($limit)
            ->offset($pages->getOffset())
            ->queryAll();
        return array('pages' => $pages, 'datas' => $datas);
    }

    /**
     * 判断是否有新收到的共享
     * @return type
     */
    public function chkHasNewShare($uid)
    {
        // 最后一次查看时间
        $last = FileReader::model()->fetch(array('condition' => "uid={$uid}", 'order' => '`viewtime` DESC'));
        $viewtime = !empty($last) ? intval($last['viewtime']) : 0;
        $user = User::model()->fetchByUid($uid);
        $depts = explode(',', $user['alldeptid'] . ',alldept');
        $deptCon = '';
        foreach ($depts as $d) {
            $deptCon .= " OR FIND_IN_SET('{$d}',`todeptids`) ";
        }
        $shareCon = "( FIND_IN_SET({$uid},`touids`) OR FIND_IN_SET({$user['positionid']}, `toposids`) {$deptCon} ) AND `uptime` > {$viewtime}";
        $record = $this->fetch($shareCon);
        return !empty($record);
    }

}
