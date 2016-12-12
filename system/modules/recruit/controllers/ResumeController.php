<?php

/**
 * 招聘模块------招聘默认控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------招聘默认控制器，继承RecruitBaseController
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\controllers;

use application\core\model\Regular;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\recruit\core\RecruitBgchecks as ICRecruitBgchecks;
use application\modules\recruit\core\RecruitInterview as ICRecruitInterview;
use application\modules\recruit\core\ResumeContact as ICResumeContact;
use application\modules\recruit\core\ResumeDetail as ICResumeDetail;
use application\modules\recruit\model\Resume;
use application\modules\recruit\model\ResumeBgchecks;
use application\modules\recruit\model\ResumeContact;
use application\modules\recruit\model\ResumeDetail;
use application\modules\recruit\model\ResumeInterview;
use application\modules\recruit\model\ResumeStats;
use application\modules\recruit\utils\AnalysisConfig;
use application\modules\recruit\utils\Recruit as RecruitUtil;
use application\modules\recruit\utils\ResumeAnalysis;
use application\modules\user\utils\User as UserUtil;
use CJSON;

class ResumeController extends BaseController
{

    /**
     * 简历状态对应 resume_statistics 表字段名
     * @var array
     */
    protected $stateList = array(
        1 => 'interview',
        2 => 'employ',
        4 => 'pending',
        5 => 'eliminate',
    );

    /**
     * 模块首页
     */
    public function actionIndex()
    {
        $type = Env::getRequest('type');
        $this->condition = RecruitUtil::joinTypeCondition($type, $this->condition);
        $data = Resume::model()->fetchAllByPage($this->condition);
        $resumeList = ICResumeDetail::processListData($data['datas']);
        $params = array(
            'sidebar' => $this->getSidebar(),
            'resumeList' => $resumeList,
            'pages' => $data['pages'],
            'isInstallEmail' => $this->checkIsInstallEmail(),
            'countArramge' => Resume::model()->countArramge(),
            'countAudition' => Resume::model()->countAudition(),
            'countFlag' => Resume::model()->countFlag(),
        );
        $this->setPageTitle(Ibos::lang('Talent management'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Talent management')),
            array('name' => Ibos::lang('Resume list'))
        ));
        $this->render('index', $params);
    }

    /**
     * 进入新建简历页面
     */
    public function actionAdd()
    {
        $op = Env::getRequest('op');
        if (!in_array($op, array('new', 'save', 'analysis'))) {
            $op = 'new';
        }
        if ($op == 'new') {
            $params = array(
                'sidebar' => $this->getSidebar(),
                'dashboardConfig' => $this->getDashboardConfig(),
                'uploadConfig' => Attach::getUploadConfig()
            );
            $params['dashboardConfigToJson'] = CJSON::encode($params['dashboardConfig']);
            $regulars = Regular::model()->fetchAll();
            $params['regulars'] = CJSON::encode($regulars);
            $this->setPageTitle(Ibos::lang('Add resume'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
                array('name' => Ibos::lang('Talent management'), 'url' => $this->createUrl('resume/index')),
                array('name' => Ibos::lang('Add resume'))
            ));
            $this->render('add', $params);
        } else {
            $this->$op();
        }
    }

    /**
     * 保存简历信息
     */
    private function save()
    {
        $data = ICResumeDetail::processAddRequestData();
        $resume = array(
            'input' => Ibos::app()->user->uid,
            'positionid' => $data['positionid'],
            'entrytime' => TIMESTAMP,
            'uptime' => TIMESTAMP,
            'status' => $data['status'],
            'statustime' => strtotime(date('Y-m-d'))
        );
        $resumeId = Resume::model()->add($resume, true);
        if ($resumeId) {
            $data['resumeid'] = $resumeId;
            $data['birthday'] = strtotime($data['birthday']);
            ResumeDetail::model()->add($data);
            if (!empty($data['avatarid'])) {
                Attach::updateAttach($data['avatarid']);
            }
            if (!empty($data['attachmentid'])) {
                Attach::updateAttach($data['attachmentid']);
            }
            //更新积分
            $uid = Ibos::app()->user->uid;
            UserUtil::updateCreditByAction('addresume', $uid);
            ResumeStats::model()->updateState('new');
            ResumeStats::model()->updateState($this->stateList[$data['status']]);

            $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('resume/index'));
        }
    }

    /**
     * 简历显示
     */
    public function actionShow()
    {
        $resumeid = Env::getRequest('resumeid');
        if (empty($resumeid)) {
            $this->error(Ibos::lang('Parameters error', 'error'));
        }
        $resumeDetail = ResumeDetail::model()->fetch('resumeid=' . $resumeid);
        //取得上一个和下一个Id
        $prevAndNextPK = Resume::model()->fetchPrevAndNextPKByPK($resumeid);
        //取得联系记录
        $contactList = ResumeContact::model()->fetchAll('resumeid=:resumeid', array(':resumeid' => $resumeid));
        //取得面试记录
        $interviewList = ResumeInterview::model()->fetchAll('resumeid=:resumeid', array(':resumeid' => $resumeid));
        //取得背景调查记录
        $bgcheckList = ResumeBgchecks::model()->fetchAll('resumeid=:resumeid', array(':resumeid' => $resumeid));
        //取得头像路径
        $avatarid = $resumeDetail['avatarid'];
        if (empty($avatarid)) {
            $resumeDetail['avatarUrl'] = '';
        } else {
            $avatar = Attach::getAttachData($avatarid);
            $resumeDetail['avatarUrl'] = File::fileName(File::getAttachUrl() . '/' . $avatar[$avatarid]['attachment']);
        }
        //取出附件
        if (!empty($resumeDetail['attachmentid'])) {
            $resumeDetail['attach'] = Attach::getAttach($resumeDetail['attachmentid']);
        }
        $data = array(
            'sidebar' => $this->getSidebar(),
            'resumeDetail' => ICResumeDetail::processShowData($resumeDetail),
            'prevAndNextPK' => $prevAndNextPK,
            'contactList' => ICResumeContact::processListData($contactList),
            'interviewList' => ICRecruitInterview::processListData($interviewList),
            'bgcheckList' => ICRecruitBgchecks::processListData($bgcheckList),
            'resumeid' => $resumeid
        );
        $this->setPageTitle(Ibos::lang('Show resume'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Talent management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Show resume'))
        ));
        $this->render('show', $data);
    }

    /**
     * 转入到编辑页面
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $resumeid = Env::getRequest('resumeid');
        if (empty($op)) {
            $op = 'default';
        }
        if (!in_array($op, array('default', 'update', 'mark', 'status')) || empty($resumeid)) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('resume/index'));
        }
        if ($op == 'default') {
            $detail = ResumeDetail::model()->fetch('resumeid=:resumeid', array(':resumeid' => $resumeid));
            $detail['birthday'] = date('Y-m-d', $detail['birthday']);
            $detail['status'] = Resume::model()->fetchStatusByResumeid($detail['resumeid']);
            //取得头像路径
            $avatarid = $detail['avatarid'];
            if (empty($avatarid)) {
                $detail['avatarUrl'] = '';
            } else {
                $avatar = Attach::getAttachData($avatarid);
                $detail['avatarUrl'] = File::fileName(File::getAttachUrl() . '/' . $avatar[$avatarid]['attachment']);
            }
            //取出附件
            if (!empty($detail['attachmentid'])) {
                $detail['attach'] = Attach::getAttach($detail['attachmentid']);
            }
            $data = array(
                'sidebar' => $this->getSidebar(),
                'resumeDetail' => $detail,
                'dashboardConfig' => $this->getDashboardConfig(),
                'uploadConfig' => Attach::getUploadConfig()
            );
            $data['dashboardConfigToJson'] = CJSON::encode($data['dashboardConfig']);
            $regulars = Regular::model()->fetchAll();
            $data['regulars'] = CJSON::encode($regulars);
            $this->setPageTitle(Ibos::lang('Edit resume'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
                array('name' => Ibos::lang('Talent management'), 'url' => $this->createUrl('resume/index')),
                array('name' => Ibos::lang('Edit resume'))
            ));
            $this->render('edit', $data);
        } else {
            $this->$op();
        }
    }

    /**
     * 修改
     */
    private function update()
    {
        $resumeDetail = ICResumeDetail::processAddRequestData();
        $resumeid = Env::getRequest('resumeid');
        $detailid = Env::getRequest('detailid');
        $resume = Resume::model()->fetchByPk($resumeid);
        // 如果有改变状态，把改变状态的时期改为当前日期时间戳，否则不改动
        $statustime = $resume['status'] == $resumeDetail['status'] ? $resume['statustime'] : strtotime(date('Y-m-d'));
        $data = array(
            'input' => Ibos::app()->user->uid,
            'positionid' => $resumeDetail['positionid'],
            'uptime' => TIMESTAMP,
            'status' => $resumeDetail['status'],
            'statustime' => $statustime
        );
        $flag = Resume::model()->modify($resumeid, $data);
        // resume_statistics 表更新
        if ($resume['status'] != $resumeDetail['status']) {
            // 如果简历上次更新时间是当天，则需要同时将简历原状态的统计数更新
            $oldStatus = (in_array($resume['status'], array(1, 2, 4, 5)) && $resume['statustime'] == strtotime(date("Y-m-d", time()))) ? $this->stateList[$resume['status']] : '';
            ResumeStats::model()->updateState($this->stateList[$resumeDetail['status']], $oldStatus);
        }
        if ($flag) {
            unset($resumeDetail['status']);
            $resumeDetail['birthday'] = strtotime($resumeDetail['birthday']);
            $orgDetail = ResumeDetail::model()->fetchByPk($detailid);
            if ($resumeDetail['avatarid'] != $orgDetail['avatarid']) {
                Attach::updateAttach($resumeDetail['avatarid']);
            }
            if ($resumeDetail['attachmentid'] != $orgDetail['attachmentid']) {
                Attach::updateAttach($resumeDetail['attachmentid']);
            }
            ResumeDetail::model()->modify($detailid, $resumeDetail);
            $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('resume/show', array('resumeid' => $resumeid)));
        } else {
            $this->error(Ibos::lang('Update failed', 'message'), $this->createUrl('resume/show', array('resumeid' => $resumeid)));
        }
    }

    /**
     * 删除简历信息
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $resumeids = Env::getRequest('resumeids');
            if (empty($resumeids)) {
                $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('resume/index'));
            }
            $pk = '';
            if (strpos($resumeids, ',')) {
                $pk = explode(',', trim($resumeids, ','));
            } else {
                $pk = $resumeids;
            }
            $delSuccess = Resume::model()->deleteByPk($pk);
            if ($delSuccess) {
                //删除联系记录，面试记录，背景调查记录、详细信息
                ResumeContact::model()->deleteAll("FIND_IN_SET(resumeid,'{$resumeids}') ");
                ResumeInterview::model()->deleteAll("FIND_IN_SET(resumeid,'{$resumeids}') ");
                ResumeBgchecks::model()->deleteAll("FIND_IN_SET(resumeid,'{$resumeids}') ");
                // 删除头像、附件
                $detail = ResumeDetail::model()->fetchAll("FIND_IN_SET(resumeid,'{$resumeids}') ");
                $avataridArr = Convert::getSubByKey($detail, 'avatarid');
                $attachmentidArr = Convert::getSubByKey($detail, 'attachmentid');
                if (!empty($avataridArr)) {
                    foreach ($avataridArr as $avatarid) {
                        Attach::delAttach($avatarid);
                    }
                }
                if (!empty($attachmentidArr)) {
                    foreach ($attachmentidArr as $attachmentid) {
                        Attach::delAttach($attachmentid);
                    }
                }
                ResumeDetail::model()->deleteAll("FIND_IN_SET(resumeid,'{$resumeids}') ");
                $this->ajaxReturn(array('isSuccess' => 1, 'msg' => Ibos::lang('Del succeed', 'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => 0, 'msg' => Ibos::lang('Del failed', 'message')));
            }
        }
    }

    /**
     * 标记简历
     */
    private function mark()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $resumeid = intval(Env::getRequest('resumeid'));
            $flag = intval(Env::getRequest('flag'));
            $modifySuccess = Resume::model()->modify($resumeid, array('flag' => $flag));
            if ($modifySuccess) {
                $this->ajaxReturn(array('isSuccess' => 1, 'msg' => Ibos::lang('Operation succeed', 'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => 0, 'msg' => Ibos::lang('Operation failure', 'message')));
            }
        }
    }

    /**
     * 更改简历状态
     */
    private function status()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $resumeid = Env::getRequest('resumeid');
            $status = Env::getRequest('status');
            $resume = Resume::model()->findByPk($resumeid);
            // 如果简历上次更新时间是当天，则需要同时将简历原状态的统计数更新
            $oldStatus = (in_array($resume->status, array(1, 2, 4, 5)) && $resume->statustime == strtotime(date("Y-m-d", time()))) ? $this->stateList[$resume->status] : '';
            ResumeStats::model()->updateState($this->stateList[$status], $oldStatus);
            Resume::model()->updateAll(array('status' => $status, 'uptime' => TIMESTAMP, 'statustime' => strtotime(date('Y-m-d'))), "FIND_IN_SET(resumeid,'{$resumeid}')");
            $showStatus = ICResumeDetail::handleResumeStatus($status);
            $this->ajaxReturn(array('showStatus' => $showStatus, 'isSuccess' => 1, 'msg' => Ibos::lang('Operation succeed', 'message')));
        }
    }

    /**
     * 一建分析
     */
    private function analysis()
    {
        $importType = intval(Env::getRequest('importType'));
        if ($importType == 1) { // 导入文件
            $file = $_FILES['importFile'];
            if ($file['error'] > 0) {
                $this->error("上传失败，失败类型：" . $file['error'], $this->createUrl('resume/index'));
            }
            if (!preg_match("/.(txt)$/i", $file['name'], $match)) {
                $this->error("不支持的文件类型", $this->createUrl('resume/index'));
            }
            if ($match[1] == 'txt') { // 导入txt文件直接读取txt文件内容
                header("Content-Type:text/html;charset=utf-8");
                $importContent = file_get_contents($file['tmp_name']);
            }
//			elseif ( $match[1] == 'doc' ) { // 导入word文件要利用office（要装office）组件COM来读取doc类型文件，目前只测试过03版
//				$word = new COM( "word.application" ) or die( "无法定位WORD安装路径！" );
//				// 读取word内容
//				$word->Documents->Open( realpath( $file['tmp_name'] ) );
//				$importContent = (string) $word->ActiveDocument->Content;
//				//关闭 word
//				$word->Quit();
//			}
        } elseif ($importType == 2) { // 粘贴内容
            $importContent = Env::getRequest('importContent');
        }
        $code = strtolower(mb_detect_encoding($importContent, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5')));
        if (($code == 'gb2312' || $code == 'GBK' || $code == 'euc-cn') && $code != CHARSET) {
            $importContent = iconv($code, CHARSET, $importContent);
        }
        $config = AnalysisConfig::getAnalconf();
        $analysis = new ResumeAnalysis(isset($importContent) ? $importContent : '', $config);
        $result = $analysis->parse_content();
        $result['gender'] = preg_match('/女/', $result['gender']) ? 2 : 1;
        $result['maritalstatus'] = preg_match('/是|已/', $result['maritalstatus']) ? 1 : 0;
        $result['workyears'] = $result['workyears'] ? intval($result['workyears']) + 0 : '';
        $result['mobile'] = $result['mobile'] ? $result['mobile'] + 0 : '';
        $result['height'] = $result['height'] ? $result['height'] + 0 : '';
        $result['weight'] = $result['weight'] ? $result['weight'] + 0 : '';
        $result['zipcode'] = $result['zipcode'] ? $result['zipcode'] + 0 : '';
        $result['qq'] = $result['qq'] ? intval($result['qq']) + 0 : '';
        if ($result['birthday']) {
            $result['birthday'] = date('Y-m-d', strtotime($result['birthday']));
        } elseif (!empty($result['age'])) {
            $result['birthday'] = (date('Y') - ($result['age'] + 0)) . '-00-00';
        }
        $regulars = Regular::model()->fetchAll();
        $params = array(
            'importInfo' => CJSON::encode($result),
            'sidebar' => $this->getSidebar(),
            'dashboardConfig' => $this->getDashboardConfig(),
            'uploadConfig' => Attach::getUploadConfig(),
            'regulars' => CJSON::encode($regulars)
        );
        $params['dashboardConfigToJson'] = CJSON::encode($params['dashboardConfig']);
        $this->setPageTitle(Ibos::lang('Add resume'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Recruitment management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Talent management'), 'url' => $this->createUrl('resume/index')),
            array('name' => Ibos::lang('Add resume'))
        ));
        $this->render('add', $params);
    }

    /**
     * 发送邮件
     */
    public function actionSendEmail()
    {
        $resumeids = Env::getRequest('resumeids');
        $resumeidsStr = trim($resumeids, ',');
        if (empty($resumeidsStr)) {
            $this->error(Ibos::lang('Parameters error', 'error'));
        }
        $details = ResumeDetail::model()->fetchAll(array('select' => 'email', 'condition' => "resumeid IN ($resumeidsStr)"));
        $emails = Convert::getSubByKey($details, 'email');
        $this->redirect(Ibos::app()->urlManager->createUrl('email/content/add', array('webid' => $emails)));
    }

}
