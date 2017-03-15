<?php

/**
 * 文件柜模块------ 我共享的
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------ 继承FileBaseController
 * @package application.modules.file.controllers
 * @version $Id: MyShareController.php 3297 2014-06-19 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\file\model\File;
use application\modules\file\model\FileDynamic;
use application\modules\file\model\FileShare;
use application\modules\file\utils\FileCheck;
use application\modules\file\utils\FileData;
use application\modules\file\utils\FileOffice;

class MyShareController extends BaseController
{

    public function init()
    {
        parent::init();
        $this->belongType = File::BELONG_PERSONAL;
    }

    /**
     * 渲染模板
     */
    public function actionIndex()
    {
        $params = array(
            'pid' => 0,
            'idpath' => File::TOP_IDPATH
        );
        $this->setPageTitle(Ibos::lang('My share folder'));
        $this->render('index', $params);
    }

    /**
     * 获取数据
     */
    public function actionGetCate()
    {
        $this->search();
        $pid = intval(Env::getRequest('pid'));
        $order = $this->getOrder();
        if ($pid == 0) {
            $condition = $this->getCondition();
        } else {
            $condition = $this->getConditionWithPid($pid);
        }
        $list = File::model()->fetchList($condition, $order);
        $params = array(
            'pid' => $pid,
            'data' => $this->handleList($list['datas']),
            'page' => $list['pages'],
            'breadCrumbs' => $this->getBreadCrumbs($pid),
            'pDir' => array_merge(FileData::getDirInfo($pid), array('access' => FileCheck::WRITEABLED))
        );
        $this->ajaxReturn($params);
    }

    /**
     * 共享、取消共享
     */
    public function actionShare()
    {
        $op = Env::getRequest('op');
        if (in_array($op, array('share', 'cancelShare', 'getShareData'))) {
            $this->$op();
        }
    }

    /**
     * 共享
     */
    protected function share()
    {
        $fids = StringUtil::filterStr(Env::getRequest('fids'));
        $shares = StringUtil::handleSelectBoxData(Env::getRequest('shares'));
        $shareFids = FileShare::model()->fetchFidsByCondition("FIND_IN_SET(fs.`fid`, '{$fids}')"); // 已共享的fid
        $fidArr = explode(',', $fids);
        foreach ($fidArr as $fid) {
            $file = File::model()->fetchByFid($fid);
            if ($file['uid'] == $this->uid) {
                $data = array(
                    'fid' => $fid,
                    'fromuid' => $this->uid,
                    'touids' => $shares['uid'],
                    'todeptids' => $shares['deptid'],
                    'toposids' => $shares['positionid'],
                    'toroleids' => $shares['roleid'],
                    'uptime' => TIMESTAMP
                );
                if (empty($data['touids']) && empty($data['todeptids']) && empty($data['toposids']) && empty($data['toroleids'])) {
                    FileShare::model()->deleteAll("`fid` = {$fid}");
                } elseif (in_array($fid, $shareFids)) {
                    FileShare::model()->updateAll($data, "`fid` = {$fid}");
                } else {
                    FileShare::model()->add($data);
                }
                $content = Ibos::lang('Feed content', '', array(
                    '{filename}' => html_entity_decode($file['name']),
                    '{shortname}' => StringUtil::cutStr($file['name'], 20),
                    '{placeUrl}' => Ibos::app()->urlManager->createUrl('file/fromshare/index#from/' . $this->uid),
                    '{downloadUrl}' => Ibos::app()->urlManager->createUrl('file/personal/ajaxEnt', array('op' => 'download', 'fids' => $fid)),
                ));
                FileDynamic::model()->record($fid, $this->uid, $content, $shares['uid'], $shares['deptid'], $shares['positionid']);
            }
        }

        $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Operation succeed', 'message')));
    }

    /**
     * 获取共享设置弹框视图
     */
    protected function getShareData()
    {
        $fids = Env::getRequest('fids');
        $alias = "application.modules.file.views.myshare.setup";
        $shares = '';
        if (!empty($fids) && count(explode(',', $fids)) == 1) {
            $record = FileShare::model()->fetchByAttributes(array('fid' => $fids));
            if (!empty($record)) {
                $shares = StringUtil::joinSelectBoxValue($record['todeptids'], $record['toposids'], $record['touids'], $record['toroleids']);
            }
        }
        $html = $this->renderPartial($alias, array('shares' => $shares), true);
        $this->ajaxReturn(array('isSuccess' => true, 'html' => $html));
    }

    /**
     * 取消共享
     */
    protected function cancelShare()
    {
        $fids = Env::getRequest('fids');
        $deletes = array();
        foreach ($fids as $fid) {
            if (FileCheck::getInstance()->isOwn($fid, $this->uid)) {
                $deletes[] = $fid;
            }
        }
        $delFids = implode(',', $deletes);
        $res = FileShare::model()->deleteAll("FIND_IN_SET(`fid`, '{$delFids}')");
        $msg = $res ? Ibos::lang('Operation succeed', 'message') : Ibos::lang('Operation failure', 'message');
        $this->ajaxReturn(array('isSuccess' => !!$res, 'msg' => $msg));
    }

    /**
     * 获取查询条件
     * @return string
     */
    protected function getCondition()
    {
        $con = array(
            'uidCon' => "f.`uid` = {$this->uid}",
            'personalCon' => "f.`belong` = {$this->belongType}",
            'cloudCon' => "f.`cloudid` = {$this->cloudid}",
            'delCon' => "f.`isdel` = 0",
            'shareCon' => "fs.`fromuid` = {$this->uid}",
            'typeCon' => $this->getTypeCondition()
        );
        $fids = FileShare::model()->fetchFidsByCondition(implode(' AND ', $con));
        $condition = sprintf("FIND_IN_SET(f.`fid`, '%s')", implode(',', $fids));
        return FileData::joinCondition($this->condition, $condition);
    }

    /**
     * 根据pid获取查询条件
     * @param integer $pid 所在文件夹id
     * @return string
     */
    protected function getConditionWithPid($pid)
    {
        $con = array(
            'pidCon' => "f.`pid` = {$pid}",
            'uidCon' => "f.`uid` = {$this->uid}",
            'personalCon' => "f.`belong` = {$this->belongType}",
            'cloudCon' => "f.`cloudid` = {$this->cloudid}",
            'delCon' => "f.`isdel` = 0",
            'typeCon' => $this->getTypeCondition()
        );
        $condition = implode(' AND ', $con);
        return $condition;
    }

    /**
     * 获得面包屑
     * @param integer $pid 文件/文件夹id
     * @param integer $fromuid 共享人uid
     * @return array 面包屑数组
     */
    private function getBreadCrumbs($pid)
    {
        $breadCrumbs = FileOffice::getBreadCrumb($pid);
        foreach ($breadCrumbs as $k => $bread) {
            if (!FileCheck::getInstance()->isShare($bread['fid'])) {
                unset($breadCrumbs[$k]);
            } else {
                break;
            }
        }
        return $breadCrumbs;
    }

}
