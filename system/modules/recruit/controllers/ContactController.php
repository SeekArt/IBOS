<?php

/**
 * 招聘模块------联系记录控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------联系记录控制器类，RecruitBaseController
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\controllers;

use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\recruit\core\ResumeContact as ICResumeContact;
use application\modules\recruit\model\Resume;
use application\modules\recruit\model\ResumeContact;
use application\modules\recruit\model\ResumeDetail;
use application\modules\user\model\User;
use CJSON;

class ContactController extends BaseController
{

    /**
     * 去首页
     * @return void
     */
    public function actionIndex()
    {
        $paginationData = ResumeContact::model()->fetchAllByPage($this->condition);
        $resumes = ResumeDetail::model()->fetchAllRealnamesAndDetailids();
        $params = array(
            'sidebar' => $this->getSidebar(),
            'resumeContactList' => ICResumeContact::processListData($paginationData['data']),
            'pagination' => $paginationData['pagination'],
            'exportData' => json_encode($paginationData['data']),
            'resumes' => $resumes
        );
        $this->setPageTitle(Ibos::lang('Contact record'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Contact record'), 'url' => $this->createUrl('contact/index')),
            array('name' => Ibos::lang('Contact list'))
        ));
        $this->render('index', $params);
    }

    /**
     * 去增加联系记录页面
     * @return void
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
            $data = ICResumeContact::processAddOrEditData($_POST);
            $data['resumeid'] = $resumeid;
            $contactid = ResumeContact::model()->add($data, true);
            if ($contactid) {
                // 返回刚添加的联系记录
                $contact = ResumeContact::model()->fetchByPk($contactid);
                $contact['inputtime'] = date('Y-m-d', $contact['inputtime']);
                $contact['input'] = User::model()->fetchRealnameByUid($contact['input']);
                $contact['fullname'] = ResumeDetail::model()->fetchRealnameByDetailid($detailid);
                //取得简历状态，如果状态为待安排，改为面试
                $status = Resume::model()->fetchStatusByResumeid($resumeid);
                if ($status == 4) {
                    Resume::model()->modify($resumeid, array('status' => 1));
                }
                $this->ajaxReturn($contact);
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Add fail')));
            }
        }
    }

    /**
     * 删除联系信息
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $contactids = Env::getRequest('contactids');
            if (empty($contactids)) {
                $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('contact/index'));
            }
            if (strpos($contactids, ',')) {
                $pk = explode(',', trim($contactids, ','));
            } else {
                $pk = $contactids;
            }
            $delSuccess = ResumeContact::model()->deleteByPk($pk);
            if ($delSuccess) {
                $this->ajaxReturn(array('isSuccess' => 1, 'msg' => Ibos::lang('Del succeed', 'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => 0, 'msg' => Ibos::lang('Del failed', 'message')));
            }
        }
    }

    /**
     * 取得要编辑的数据
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $contactid = Env::getRequest('contactid');
        if (!in_array($op, array('update', 'getEditData')) || empty($contactid)) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('contact/index'));
        } else {
            $this->$op();
        }
    }

    /**
     * 修改联系记录
     */
    private function update()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $contactid = Env::getRequest('contactid');
            $data = ICResumeContact::processAddOrEditData($_POST);
            $modifySuccess = ResumeContact::model()->modify($contactid, $data);
            if ($modifySuccess) {
                // 返回刚修改的数据
                $contact = ResumeContact::model()->fetchByPk($contactid);
                $contact['inputtime'] = date('Y-m-d', $contact['inputtime']);
                $contact['input'] = User::model()->fetchRealnameByUid($contact['input']);
                $contact['fullname'] = ResumeDetail::model()->fetchRealnameByResumeid($contact['resumeid']);
                $contact['detail'] = StringUtil::cutStr($contact['detail'], 12);
                $this->ajaxReturn($contact);
            } else {
                $this->ajaxReturn(array('isSuccess' => 0));
            }
        }
    }

    /**
     * 取得要编辑的数据,ajax返回
     */
    private function getEditData()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $contactid = Env::getRequest('contactid');
            $contact = ResumeContact::model()->fetchByPk($contactid);
            $contact['inputtime'] = date('Y-m-d', $contact['inputtime']);
            $contact['upuid'] = StringUtil::wrapId($contact['input']);
            $this->ajaxReturn($contact);
        }
    }

    /**
     * 导出CSV
     */
    public function actionExport()
    {
        $contactids = Env::getRequest('contactids');
        $contactArr = ResumeContact::model()->fetchAll("FIND_IN_SET(contactid, '{$contactids}')");
        $fieldArr = array(
            Ibos::lang('Name'),
            Ibos::lang('Contact date'),
            Ibos::lang('Contact staff'),
            Ibos::lang('Contact method'),
            Ibos::lang('Contact purpose'),
            Ibos::lang('Content')
        );
        $str = implode(',', $fieldArr) . "\n";
        foreach ($contactArr as $contact) {
            $realname = ResumeDetail::model()->fetchRealnameByResumeid($contact['resumeid']);
            $input = User::model()->fetchRealnameByUid($contact['input']);
            $inputtime = empty($contact['inputtime']) ? '' : date('Y-m-d', $contact['inputtime']);
            $method = $contact['contact'];
            $purpose = $contact['purpose'];
            $detail = $contact['detail'];
            $str .= $realname . ',' . $inputtime . ',' . $input . ',' . $method . ',' . $purpose . ',' . $detail . "\n"; //用引文逗号分开 
        }
        $outputStr = iconv('utf-8', 'gbk//ignore', $str);
        $filename = date('Y-m-d') . mt_rand(100, 999) . '.csv';
        File::exportCsv($filename, $outputStr);
    }

}
