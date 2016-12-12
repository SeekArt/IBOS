<?php

/**
 * 文件柜模块------ 本地我收到的共享
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------ 继承FileBaseController
 * @package application.modules.file.controllers
 * @version $Id: FromShareController.php 3297 2014-06-19 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\controllers;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\file\model\File;
use application\modules\file\model\FileReader;
use application\modules\file\model\FileShare;
use application\modules\file\utils\FileCheck;
use application\modules\file\utils\FileData;
use application\modules\file\utils\FileOffice;
use application\modules\user\model\User;

class FromShareController extends BaseController
{

    public function init()
    {
        parent::init();
        $this->belongType = File::BELONG_PERSONAL;
    }

    /**
     * 列表
     */
    public function actionIndex()
    {
        $params = array(
            'fromuid' => 0,
            'pid' => 0,
            'idpath' => File::TOP_IDPATH
        );
        $this->setPageTitle(Ibos::lang('From share'));
        $this->render('index', $params);
    }

    /**
     * 获取数据
     */
    public function actionGetCate()
    {
        $fromuid = intval(Env::getRequest('fromuid'));
        $pid = intval(Env::getRequest('pid'));
        $shareCon = $this->getShareCondition($this->uid);
        $fileCon = $this->getFileCondition($pid);
        if ($pid) { // 某个文件夹
            if (!FileCheck::getInstance()->isReadable($pid, $this->uid)) {
                $this->error(Ibos::lang('No read permission'));
            }
        } else if ($fromuid) { // 某个用户
            FileReader::model()->record($fromuid, $this->uid);
            $fileCon = $shareCon . ' AND ' . $fileCon . " AND fs.`fromuid`={$fromuid}";
        } else { // 首页
            $con = $shareCon . ' AND ' . $this->getUserSearch();
            $list = FileShare::model()->getIndexList($con);
            $list['datas'] = $this->handleIndexList($list['datas']);
        }
        if ($pid || $fromuid) {
            $this->search();
            $con = FileData::joinCondition($this->condition, $fileCon);
            $list = FileShare::model()->getList($con, $this->getOrder());
            $list['datas'] = $this->handleList($list['datas']);
        }
        $params = array(
            'pid' => $pid,
            'data' => $list['datas'],
            'page' => $list['pages'],
            'breadCrumbs' => $this->getBreadCrumbs($pid, $fromuid, $this->uid),
            'pDir' => array_merge(FileData::getDirInfo($pid), array('access' => FileCheck::READABLED))
        );
        $this->ajaxReturn($params);
    }

    /**
     * 获得面包屑
     * @param integer $pid 文件/文件夹id
     * @param integer $fromuid 共享人uid
     * @return array 面包屑数组
     */
    private function getBreadCrumbs($pid, $fromuid, $touid)
    {
        $breadCrumbs = FileOffice::getBreadCrumb($pid);
        $shareCon = $this->getShareCondition($touid);
        foreach ($breadCrumbs as $k => $bread) {
            $record = Ibos::app()->db->createCommand()
                ->from("{{file_share fs}}")
                ->where("fs.`fid`={$bread['fid']} AND " . $shareCon)
                ->queryRow();
            if (empty($record)) {
                unset($breadCrumbs[$k]);
            } else {
                break;
            }
        }
        if (!$fromuid) {
            if ($pid) {
                $file = File::model()->fetchByFid($pid);
                $fromuid = $file['uid'];
            }
        }
        if ($fromuid) {
            $fromuser = User::model()->fetchByUid($fromuid);
            array_unshift($breadCrumbs, $fromuser);
        }
        return $breadCrumbs;
    }

    /**
     * 处理显示数据
     * @param array $list
     * @return array
     */
    private function handleIndexList($list)
    {
        foreach ($list as $k => $li) {
            $list[$k]['isnew'] = intval($li['uptime']) - intval($li['viewtime']) > 0;
            $list[$k]['formatuptime'] = date('Y/m/d', $li['uptime']);
            $list[$k]['user'] = User::model()->fetchByUid($li['fromuid']);
        }
        return $list;
    }

    /**
     * 获得某个用户收到共享的条件
     * @param integer $uid 收到共享的用户id
     * @return string
     */
    protected function getShareCondition($uid)
    {
        $user = User::model()->fetchByUid($uid);
        $depts = explode(',', $user['alldeptid'] . ',alldept');
        $deptCon = $posCon = $roleCon = $uidCon = array();
        foreach ($depts as $d) {
            $deptCon[] = " FIND_IN_SET('{$d}', fs.`todeptids`) ";
        }
        $con = "(" . implode(' OR ', array_filter($deptCon)) . ' OR ';
        if (!empty($user['allposid'])) {
            foreach (explode(',', $user['allposid']) as $p) {
                $posCon[] = " FIND_IN_SET( '{$p}', fs.`toposids` )";
            }
            $con .= implode(' OR ', array_filter($posCon)) . ' OR ';
        }
        if (!empty($user['allroleid'])) {
            foreach (explode(',', $user['allroleid']) as $r) {
                $roleCon[] = " FIND_IN_SET( '{$r}', fs.`toroleids` )";
            }
            $con .= implode(' OR ', array_filter($roleCon)) . ' OR ';
        }
        $con .= " FIND_IN_SET({$uid}, fs.`touids`) )";
        return $con;
    }

    /**
     * 获取查询条件
     * @return string
     */
    protected function getFileCondition($pid)
    {
        $con = array(
            'personalCon' => "f.`belong` = {$this->belongType}",
            'cloudCon' => "f.`cloudid` = {$this->cloudid}",
            'delCon' => "f.`isdel` = 0",
            'typeCon' => $this->getTypeCondition()
        );
        if (!empty($pid)) {
            $con['pidCon'] = "f.`pid` = {$pid}";
        }
        return implode(' AND ', $con);
    }

    /**
     * 我收到的首页搜索条件
     * @param integer $pid 文件夹id
     * @param integer $fromuid 分享用户id
     * @return string
     */
    protected function getUserSearch()
    {
        $con = 1;
        if (Env::getRequest('search') == '1') {
            $keyword = \CHtml::encode(Env::getRequest('keyword'));
            $users = User::model()->fetchAll("`realname` LIKE '%{$keyword}%'");
            $uids = implode(',', Convert::getSubByKey($users, 'uid'));
            $con = " FIND_IN_SET(fs.fromuid, '{$uids}') ";
        }
        return $con;
    }

}
