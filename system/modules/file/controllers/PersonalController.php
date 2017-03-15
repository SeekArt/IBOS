<?php

/**
 * 文件柜模块------ 个人网盘控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------ 继承FileBaseController
 * @package application.modules.file.controllers
 * @version $Id: PersonalController.php 3297 2014-06-19 06:40:54Z gzhzh $
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
use application\modules\file\utils\FileCheck;
use application\modules\file\utils\FileData;
use application\modules\file\utils\FileOffice;
use application\modules\main\components\CommonAttach;

class PersonalController extends BaseController
{

    public function init()
    {
        $this->belongType = File::BELONG_PERSONAL;
        parent::init();
    }

    /**
     * 渲染页面
     */
    public function actionIndex()
    {
        $params = array(
            'pid' => 0,
            'idpath' => File::TOP_IDPATH,
            'uploadConfig' => Attach::getUploadConfig()
        );
        $this->setPageTitle(Ibos::lang('Personal folder'));
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
        $limit = Env::getRequest('getfull') ? 99999 : File::PAGESIZE;
        $list = File::model()->fetchList($condition, $order, $limit);
        $breadCrumbs = FileOffice::getBreadCrumb($pid);
        $params = array(
            'pid' => $pid,
            'breadCrumbs' => $breadCrumbs,
            'data' => FileData::handleIsShared($this->handleList($list['datas'])),
            'page' => $list['pages'],
            'pDir' => array_merge(FileData::getDirInfo($pid), array('access' => FileCheck::WRITEABLED))
        );
        $this->ajaxReturn($params);
    }

    /**
     * 添加文件或文件夹
     */
    public function actionAdd()
    {
        $op = Env::getRequest('op');
        if ($op == 'upload') {
            $this->checkUserSize();
        }
        $allowOps = array('upload', 'mkDir', 'mkOffice');
        if (in_array($op, $allowOps)) {
            $this->$op();
        }
    }

    public function actionShow()
    {
        $this->open();
    }

    /**
     * 删除（删除到回收站/彻底删除）
     */
    public function actionDel()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $fids = StringUtil::filterStr(Env::getRequest('fids'));
            $files = File::model()->fetchAllByFids($fids);
            foreach ($files as $f) { // 安全判断
                if ($f['uid'] != $this->uid) {
                    $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No permission to delete files', '', array('{file}' => $f['name']))));
                }
            }
            FileOperationApi::getInstance()->recycle($fids);
            $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Operation succeed', 'message')));
        }
    }

    /**
     * 复制、剪切、重命名、下载入口
     */
    public function actionAjaxEnt()
    {
        $op = Env::getRequest('op');
        $allowOps = array('copy', 'cut', 'rename', 'download', 'mark');
        if (in_array($op, $allowOps)) {
            $this->$op();
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
            'uidCon' => "f.`uid` = " . Ibos::app()->user->uid,
            'personalCon' => "f.`belong` = {$this->belongType}",
            'cloudCon' => "f.`cloudid` = {$this->cloudid}",
            'delCon' => "f.`isdel` = 0",
            'typeCon' => $this->getTypeCondition()
        );
        $this->condition = FileData::joinCondition($this->condition, implode(' AND ', $con));
        return $this->condition;
    }

    /**
     * 检查用户容量使用情况
     */
    protected function checkUserSize()
    {
        $userSize = FileData::getUserSize(Ibos::app()->user->uid);
        $usedSize = File::model()->getUsedSize(Ibos::app()->user->uid, $this->cloudid);
        $attach = new CommonAttach('Filedata', 'file');
        $attachSize = $attach->getAttachSize();
        //如果是比较数字大小，只要让其中一边是数字即可，另一边是字符串无所谓
        if (($usedSize + $attachSize + 0) > implode('', StringUtil::ConvertBytes($userSize . 'm'))) {
            echo json_encode(array('isSuccess' => false, 'msg' => Ibos::lang('Capacity overflow', '', array('{size}' => $userSize))));
            exit(0);
        }
    }

}
