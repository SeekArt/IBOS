<?php

/**
 * 招聘模块------面试记录控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------面试记录控制器，继承RecruitBaseController
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\recruit\core\RecruitInterview as ICRecruitInterview;
use application\modules\recruit\model\ResumeDetail;
use application\modules\recruit\model\ResumeInterview;
use application\modules\user\model\User;
use CJSON;

class InterviewController extends BaseController
{

    /**
     * 面试管理页面
     */
    public function actionIndex()
    {
        $paginationData = ResumeInterview::model()->fetchAllByPage($this->condition);
        $resumes = ResumeDetail::model()->fetchAllRealnamesAndDetailids();
        $params = array(
            'sidebar' => $this->getSidebar(),
            'resumeInterviewList' => ICRecruitInterview::processListData($paginationData['data']),
            'pagination' => $paginationData['pagination'],
            'exportData' => json_encode($paginationData['data']),
            'resumes' => $resumes
        );
        $this->setPageTitle(Ibos::lang('Interview management'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Interview management'), 'url' => $this->createUrl('interview/index')),
            array('name' => Ibos::lang('Interview list'))
        ));
        $this->render('index', $params);
    }

    /**
     * 添加面试记录
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
            $data = ICRecruitInterview::processAddOrEditData($_POST);
            $data['resumeid'] = $resumeid;
            $interviewid = ResumeInterview::model()->add($data, true);
            if ($interviewid) {
                // 返回刚添加的面试记录
                $interview = ResumeInterview::model()->fetchByPk($interviewid);
                $interview['interviewtime'] = date('Y-m-d', $interview['interviewtime']);
                $interview['process'] = StringUtil::cutStr($interview['process'], 12);
                $interview['interviewer'] = User::model()->fetchRealnameByUid($interview['interviewer']);
                $interview['fullname'] = ResumeDetail::model()->fetchRealnameByDetailid($detailid);
                $this->ajaxReturn($interview);
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Add fail')));
            }
        }
    }

    /**
     * 编辑页面
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $interviewid = Env::getRequest('interviewid');
        if (!in_array($op, array('update', 'getEditData')) || empty($interviewid)) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('interview/index'));
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
            $interviewid = Env::getRequest('interviewid');
            $interview = ResumeInterview::model()->fetchByPk($interviewid);
            $interview['interviewtime'] = date('Y-m-d', $interview['interviewtime']);
            $interview['interviewer'] = StringUtil::wrapId($interview['interviewer']);
            $this->ajaxReturn($interview);
        }
    }

    /**
     * 修改面试记录
     */
    private function update()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $interviewid = Env::getRequest('interviewid');
            $data = ICRecruitInterview::processAddOrEditData($_POST);
            $modifySuccess = ResumeInterview::model()->modify($interviewid, $data);
            if ($modifySuccess) {
                $interview = ResumeInterview::model()->fetchByPk($interviewid);
                $interview['fullname'] = ResumeDetail::model()->fetchRealnameByResumeid($interview['resumeid']);
                $interview['interviewtime'] = date('Y-m-d', $interview['interviewtime']);
                $interview['interviewer'] = User::model()->fetchRealnameByUid($interview['interviewer']);
                $interview['process'] = StringUtil::cutStr($interview['process'], 12);
                $this->ajaxReturn($interview);
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
            $interviewids = Env::getRequest('interviewids');
            if (empty($interviewids)) {
                $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('interview/index'));
            }
            $pk = '';
            if (strpos($interviewids, ',')) {
                $pk = explode(',', trim($interviewids, ','));
            } else {
                $pk = $interviewids;
            }
            $delSuccess = ResumeInterview::model()->deleteByPk($pk);
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
        $interviews = Env::getRequest('interviews');
        $interviewArr = ResumeInterview::model()->fetchAll("FIND_IN_SET(interviewid, '{$interviews}')");
        $fieldArr = array(
            Ibos::lang('Name'),
            Ibos::lang('Interview time'),
            Ibos::lang('Interview people'),
            Ibos::lang('Interview types'),
            Ibos::lang('Interview process')
        );
        $str = implode(',', $fieldArr) . "\n";
        foreach ($interviewArr as $interview) {
            $realname = ResumeDetail::model()->fetchRealnameByResumeid($interview['resumeid']);
            $time = empty($interview['interviewtime']) ? '' : date('Y-m-d', $interview['interviewtime']);
            $interviewer = User::model()->fetchRealnameByUid($interview['interviewer']);
            $type = $interview['type'];
            $process = $interview['process'];
            $str .= $realname . ',' . $time . ',' . $interviewer . ',' . $type . ',' . $process . "\n"; //用引文逗号分开 
        }
        $outputStr = iconv('utf-8', 'gbk//ignore', $str);
        $filename = date('Y-m-d') . mt_rand(100, 999) . '.csv';
        File::exportCsv($filename, $outputStr);
    }

}
