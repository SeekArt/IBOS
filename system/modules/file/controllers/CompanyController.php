<?php

/**
 * 文件柜模块------ 公司网盘控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------ 继承FileBaseController
 * @package application.modules.file.controllers
 * @version $Id: CompanyController.php 3297 2014-06-19 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\file\core\FileOperationApi;
use application\modules\file\model\File;
use application\modules\file\model\FileDirAccess;
use application\modules\file\utils\FileCheck;
use application\modules\file\utils\FileData;
use application\modules\file\utils\FileOffice;

class CompanyController extends BaseController
{

    public function init()
    {
        parent::init();
        $this->belongType = File::BELONG_COMPANY;
    }

    /**
     * 渲染模板
     */
    public function actionIndex()
    {
        $params = array(
            'pid' => 0,
            'idpath' => File::TOP_IDPATH,
            'uploadConfig' => Attach::getUploadConfig(),
            'isManager' => FileCheck::getInstance()->isManager($this->uid)
        );
        $this->setPageTitle(Ibos::lang('Company folder'));
        $this->render('index', $params);
    }

    /**
     * 获取数据
     */
    public function actionGetCate()
    {
        $this->search();
        $pid = intval(Env::getRequest('pid'));
        $condition = $this->getCondition($pid);
        $order = $this->getOrder();
        $list = File::model()->fetchList($condition, $order);
        $breadCrumbs = FileOffice::getBreadCrumb($pid);
        $params = array(
            'pid' => $pid,
            'breadCrumbs' => $breadCrumbs,
            'data' => $this->handleCompanyList($this->handleList($list['datas']), $pid),
            'page' => $list['pages'],
            'pDir' => $this->mergeCurDirAccess(FileData::getDirInfo($pid), $this->uid)
        );
        $this->ajaxReturn($params);
    }

    /**
     * 处理公司网盘输出数据
     * @param array $list 文件数组
     * @param type $uid 用户uid
     */
    protected function handleCompanyList($list, $pid)
    {
        $uid = $this->uid;
        $isManager = FileCheck::getInstance()->isManager($uid);
        $fids = Convert::getSubByKey($list, 'fid');
        if ($pid != 0) {
            $parent = File::model()->fetchByFid($pid);
            $fids = array_merge($fids, array($pid), FileOffice::getPidsByIdPath($parent['idpath']));
        }
        $accessArr = FileDirAccess::model()->fetchAllSortByFid($fids);
        foreach ($list as $k => $f) {
            // 权限赋值
            $list[$k]['access'] = $isManager ? FileCheck::WRITEABLED : $this->getAccess($accessArr, $f, $uid);
        }
        return $list;
    }

    /**
     * 组合当前文件夹的权限
     * @param array $file 文件夹数据
     * @param integer $uid 登陆用户id
     * @return array
     */
    protected function mergeCurDirAccess($file, $uid)
    {
        if (FileCheck::getInstance()->isManager($uid)) {
            $file['access'] = FileCheck::WRITEABLED;
        } elseif (!empty($file['fid'])) {
            $fids = array_merge(array($file['fid']), FileOffice::getPidsByIdPath($file['idpath']));
            $accessArr = FileDirAccess::model()->fetchAllSortByFid($fids);
            $file['access'] = $this->getAccess($accessArr, $file, $uid);
        } else {
            $file['access'] = FileCheck::READABLED;
        }
        return $file;
    }

    /**
     * 获取实际权限
     * @param array $accessArr 权限数据
     * @param array $file 文件/文件夹数据
     * @param integer $uid 用户id
     * @return integer
     */
    protected function getAccess($accessArr, $file, $uid)
    {
        // 权限赋值
        if (isset($accessArr[$file['fid']])) {
            $access = FileCheck::getInstance()->getAccess($accessArr[$file['fid']], $uid);
        } else if ($file['pid'] != 0) { // 找父级权限
            $parentF = File::model()->fetchByFid($file['pid']);
            $access = $this->getAccess($accessArr, $parentF, $uid);
        } else {
            $access = FileCheck::READABLED;
        }

        return $access;
    }

    /**
     * 添加文件或文件夹
     */
    public function actionAdd()
    {
        $op = Env::getRequest('op');
        $allowOps = array('upload', 'mkDir', 'mkOffice');
        if (!in_array($op, $allowOps)) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Request tainting', 'error')));
        }
        $pid = intval(Env::getRequest('pid'));
        $access = FileDirAccess::model()->fetchByAttributes(array('fid' => $pid));
        if (FileCheck::getInstance()->getAccess($access, $this->uid) != FileCheck::WRITEABLED) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No write permission')));
        }
        $this->$op();
    }

    /**
     * 删除（删除到回收站/彻底删除）
     */
    public function actionDel()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $fids = StringUtil::filterStr(Env::getRequest('fids'));
            $files = File::model()->fetchAllByFids($fids);
            foreach ($files as $f) {
                $access = FileDirAccess::model()->fetchByAttributes(array('fid' => $f['fid']));
                if (FileCheck::getInstance()->getAccess($access, $this->uid) != FileCheck::WRITEABLED) {
                    $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No permission to delete files', '', array('{file}' => $f['name']))));
                }
            }
            FileOperationApi::getInstance()->recycle($fids);
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Del succeed', 'message')));
        }
    }

    /**
     * 复制、剪切、重命名、下载、权限入口
     */
    public function actionAjaxEnt()
    {
        $op = Env::getRequest('op');
        $allowOps = array('copy', 'cut', 'rename', 'download', 'setAccess', 'getAccessView');
        if (!in_array($op, $allowOps)) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Request tainting', 'error')));
        }
        $this->$op();
    }

    /**
     * 获取权限
     */
    protected function getAccessView()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $fid = intval(Env::getRequest('fid'));
            $access = FileDirAccess::model()->fetchByAttributes(array('fid' => $fid));
            $params = array('rScope' => '', 'wScope' => '', 'lang' => Ibos::getLangSource('file.default'));
            if (!empty($access)) {
                $params['rScope'] = StringUtil::joinSelectBoxValue($access['rdeptids'], $access['rposids'], $access['ruids'], $access['rroleids']);
                $params['wScope'] = StringUtil::joinSelectBoxValue($access['wdeptids'], $access['wposids'], $access['wuids'], $access['wroleids']);
            }
            $alias = 'application.modules.file.views.company.access';
            $view = $this->renderPartial($alias, $params, true);
            echo $view;
        }
    }

    /**
     * 设置权限
     */
    protected function setAccess()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $fid = intval(Env::getRequest('fid'));
            $rScope = StringUtil::handleSelectBoxData($_POST['rScope']);
            $wScope = StringUtil::handleSelectBoxData($_POST['wScope']);
            $data = array(
                'fid' => $fid,
                'rdeptids' => $rScope['deptid'],
                'rposids' => $rScope['positionid'],
                'rroleids' => $rScope['roleid'],
                'ruids' => $rScope['uid'],
                'wdeptids' => $wScope['deptid'],
                'wposids' => $wScope['positionid'],
                'wuids' => $wScope['uid'],
                'wroleids' => $wScope['roleid'],
            );
            $record = FileDirAccess::model()->fetchByAttributes(array('fid' => $fid));
            if (empty($record)) {
                FileDirAccess::model()->add($data);
            } else {
                FileDirAccess::model()->updateByPk($record['id'], $data);
            }
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Operation succeed', 'message')));
        }
    }

    /**
     * 获取查询条件
     * @return string
     */
    protected function getCondition($pid)
    {
        $con = array(
            'dirCon' => "f.`pid` = {$pid}",
            'personalCon' => "f.`belong` = {$this->belongType}",
            'cloudCon' => "f.`cloudid` = {$this->cloudid}",
            'delCon' => "f.`isdel` = 0",
            'typeCon' => $this->getTypeCondition()
        );
        if (!FileCheck::getInstance()->isManager($this->uid)) { // 如果不是网盘管理员，查找出有阅读权限的fid
            $fids = File::model()->fetchFidsByCondition(implode(' AND ', $con));
            $fidStr = implode(',', $fids);
            $accessArr = FileDirAccess::model()->fetchAll("FIND_IN_SET(`fid`, '{$fidStr}')");
            foreach ($accessArr as $access) {
                if (FileCheck::getInstance()->getAccess($access, $this->uid) == FileCheck::NONE_ACCESS) { // 去掉没有权限的fid
                    $key = array_search($access['fid'], $fids);
                    if (isset($fids[$key])) {
                        unset($fids[$key]);
                    }
                }
            }
            $con = array(
                'fidCon' => sprintf("FIND_IN_SET(f.`fid`, '%s')", implode(',', $fids))
            );
        }
        $this->condition = FileData::joinCondition($this->condition, implode(' AND ', $con));
        return $this->condition;
    }

}
