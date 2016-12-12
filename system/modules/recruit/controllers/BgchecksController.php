<?php

/**
 * 招聘模块------背景调查控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------背景调查控制器类，RecruitBaseController
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\recruit\core\RecruitBgchecks as ICRecruitBgchecks;
use application\modules\recruit\model\ResumeBgchecks;
use application\modules\recruit\model\ResumeDetail;
use CJSON;

class BgchecksController extends BaseController
{

    /**
     * 背景调查首页-人才管理
     */
    public function actionIndex()
    {
        $paginationData = ResumeBgchecks::model()->fetchAllByPage($this->condition);
        $resumes = ResumeDetail::model()->fetchAllRealnamesAndDetailids();
        $params = array(
            'sidebar' => $this->getSidebar(),
            'resumeBgchecksList' => ICRecruitBgchecks::processListData($paginationData['data']),
            'pagination' => $paginationData['pagination'],
            'exportData' => json_encode($paginationData['data']),
            'resumes' => $resumes
        );
        $this->setPageTitle(Ibos::lang('Background investigation'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Background investigation'), 'url' => $this->createUrl('bgchecks/index')),
            array('name' => Ibos::lang('Bgchecks list'))
        ));
        $this->render('index', $params);
    }

    /**
     * 添加背景记录
     */
    public function actionAdd()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $detailid = Env::getRequest('detailid');
            // 根据 detailid 获取简历 id
            $resumeid = ResumeDetail::model()->fetchResumeidByDetailid($detailid);
            if (empty($resumeid)) {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('This name does not exist resume')));
            }
            $data = ICRecruitBgchecks::processAddOrEditData($_POST);
            $data['resumeid'] = $resumeid;
            $bgcheckid = ResumeBgchecks::model()->add($data, true);
            if ($bgcheckid) {
                // 返回刚添加的背景调查
                $bgcheck = ResumeBgchecks::model()->fetchByPk($bgcheckid);
                $bgcheck['entrytime'] = $bgcheck['entrytime'] == 0 ? '-' : date('Y-m-d', $bgcheck['entrytime']);
                $bgcheck['quittime'] = $bgcheck['quittime'] == 0 ? '-' : date('Y-m-d', $bgcheck['quittime']);
                $bgcheck['fullname'] = ResumeDetail::model()->fetchRealnameByDetailid($detailid);
                $this->ajaxReturn($bgcheck);
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Add fail')));
            }
        }
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $checkid = Env::getRequest('checkid');
        if (!in_array($op, array('update', 'getEditData')) || empty($checkid)) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('bgchecks/index'));
        } else {
            $this->$op();
        }
    }

    /**
     * 取得要编辑的记录
     */
    private function getEditData()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $checkid = Env::getRequest('checkid');
            $bgcheck = ResumeBgchecks::model()->fetchByPk($checkid);
            $bgcheck['entrytime'] = $bgcheck['entrytime'] == 0 ? '' : date('Y-d-d', $bgcheck['entrytime']);
            $bgcheck['quittime'] = $bgcheck['quittime'] == 0 ? '' : date('Y-d-d', $bgcheck['quittime']);
            $bgcheck['fullname'] = ResumeDetail::model()->fetchRealnameByResumeid($bgcheck['resumeid']);
            $this->ajaxReturn($bgcheck);
        }
    }

    /**
     * 修改面试记录
     */
    private function update()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $checkid = Env::getRequest('checkid');
            $data = ICRecruitBgchecks::processAddOrEditData($_POST);
            $modifySuccess = ResumeBgchecks::model()->modify($checkid, $data);
            if ($modifySuccess) {
                // 返回刚修改的数据
                $bgcheck = ResumeBgchecks::model()->fetchByPk($checkid);
                $bgcheck['entrytime'] = $bgcheck['entrytime'] == 0 ? '-' : date('Y-m-d', $bgcheck['entrytime']);
                $bgcheck['quittime'] = $bgcheck['entrytime'] == 0 ? '-' : date('Y-m-d', $bgcheck['quittime']);
                $bgcheck['fullname'] = ResumeDetail::model()->fetchRealnameByResumeid($bgcheck['resumeid']);
                $this->ajaxReturn($bgcheck);
            } else {
                $this->ajaxReturn(array('isSuccess' => 0));
            }
        }
    }

    /**
     * 删除面试信息
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $checkids = Env::getRequest('checkids');
            if (empty($checkids)) {
                $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('bgchecks/index'));
            }
            $pk = '';
            if (strpos($checkids, ',')) {
                $pk = explode(',', trim($checkids, ','));
            } else {
                $pk = $checkids;
            }
            $delSuccess = ResumeBgchecks::model()->deleteByPk($pk);
            if ($delSuccess) {
                $this->ajaxReturn(array('isSuccess' => 1, 'msg' => Ibos::lang('Del succeed', 'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => 0, 'msg' => Ibos::lang('Del failed', 'message')));
            }
        }
    }

    /**
     * 导出CSV
     */
    public function actionExport()
    {
        $checkids = Env::getRequest('checkids');
        $bgcheckArr = ResumeBgchecks::model()->fetchAll("FIND_IN_SET(checkid, '{$checkids}')");
        $fieldArr = array(
            Ibos::lang('Name'),
            Ibos::lang('Company name'),
            Ibos::lang('Position'),
            Ibos::lang('Entry time'),
            Ibos::lang('Departure time'),
            Ibos::lang('Details')
        );
        $str = implode(',', $fieldArr) . "\n";
        foreach ($bgcheckArr as $bgcheck) {
            $realname = ResumeDetail::model()->fetchRealnameByResumeid($bgcheck['resumeid']);
            $company = $bgcheck['company'];
            $position = $bgcheck['position'];
            $entryTime = empty($bgcheck['entrytime']) ? '' : date('Y-m-d', $bgcheck['entrytime']);
            $quitTime = empty($bgcheck['quittime']) ? '' : date('Y-m-d', $bgcheck['quittime']);
            $detail = $bgcheck['detail'];
            $str .= $realname . ',' . $company . ',' . $position . ',' . $entryTime . ',' . $quitTime . ',' . $detail . "\n"; //用引文逗号分开 
        }
        $outputStr = iconv('utf-8', 'gbk//ignore', $str);
        $filename = date('Y-m-d') . mt_rand(100, 999) . '.csv';
        File::exportCsv($filename, $outputStr);
    }

}
