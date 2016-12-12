<?php

/**
 * 工作总结与计划模块------工作总结与计划默认控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------工作总结与计划默认控制器，继承ReportBaseController
 * @package application.modules.report.components
 * @version $Id: DefaultController.php 1897 2013-12-12 12:33:07Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Calendars;
use application\modules\dashboard\model\Stamp;
use application\modules\department\model\Department;
use application\modules\message\model\Notify;
use application\modules\report\core\Report as ICReport;
use application\modules\report\model\CalendarRepRecord;
use application\modules\report\model\Report;
use application\modules\report\model\ReportRecord;
use application\modules\report\model\ReportStats;
use application\modules\report\model\ReportType;
use application\modules\report\utils\Report as ReportUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;
use application\core\model\Log;
use CHtml;

class DefaultController extends BaseController
{

    /**
     * 取得侧栏视图
     * @return string
     */
    public function getSidebar()
    {
        $sidebarAlias = 'application.modules.report.views.sidebar';
        $uid = Ibos::app()->user->uid;
        $params = array(
            'statModule' => Ibos::app()->setting->get('setting/statmodules'),
            'lang' => Ibos::getLangSource('report.default'),
            'reportTypes' => ReportType::model()->fetchAllTypeByUid($uid)
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params);
        return $sidebarView;
    }

    /**
     * 个人总结与计划页面列表
     */
    public function actionIndex()
    {
        $typeid = Env::getRequest('typeid');
        $uid = Ibos::app()->user->uid;
        $op = Env::getRequest('op');
        if (!in_array($op,
            array('default', 'showDetail', 'getReaderList', 'getCommentList'))
        ) {
            $op = 'default';
        }
        if ($op == 'default') {
            //是否搜索
            //post类型的请求
            if (Env::getRequest('param') == 'search' && Ibos::app()->request->isPostRequest) {
                $this->search();
            }
            if (empty($typeid)) {
                $typeCondition = 1;
            } else {
                $typeCondition = "typeid = '{$typeid}'";
            }
            $this->_condition = ReportUtil::joinCondition($this->_condition,
                "uid = '{$uid}' AND {$typeCondition}");
            $paginationData = Report::model()->fetchAllByPage($this->_condition);
            $params = array(
                'typeid' => $typeid,
                'pagination' => $paginationData['pagination'],
                'reportList' => ICReport::handelListData($paginationData['data']),
                'reportCount' => Report::model()->count("uid='{$uid}'"),
                'commentCount' => Report::model()->count("uid='{$uid}' AND isreview=1"),
                'user' => User::model()->fetchByUid($uid),
            );
            $this->setPageTitle(Ibos::lang('My report'));
            $this->setPageState('breadCrumbs',
                array(
                    array('name' => Ibos::lang('Personal Office')),
                    array('name' => Ibos::lang('Work report'), 'url' => $this->createUrl('default/index')),
                    array('name' => Ibos::lang('My report list'))
                ));
            $this->render('index', $params);
        } else {
            $this->$op();
        }
    }

    /**
     * 填写工作总结与计划页面
     */
    public function actionAdd()
    {
        $op = Env::getRequest('op');
        if (!in_array($op, array('new', 'save'))) {
            $op = 'new';
        }
        if ($op == 'new') {
            $typeid = intval(Env::getRequest('typeid')); //接收汇报类型id
            if (!$typeid) {
                $typeid = 1;
            }
            $uid = Ibos::app()->user->uid;
            // 获取直属uid
            $upUid = UserUtil::getSupUid($uid);
            // 总结和计划日期
            $reportType = ReportType::model()->fetchByPk($typeid);
            $summaryAndPlanDate = ReportUtil::getDateByIntervalType($reportType['intervaltype'],
                $reportType['intervals']);
            $subject = ICReport::handleShowSubject($reportType,
                strtotime($summaryAndPlanDate['summaryBegin']),
                strtotime($summaryAndPlanDate['summaryEnd']));
            // 上一次typeid汇报类型的总结报告
            $lastRep = Report::model()->fetchLastRepByUidAndTypeid($uid, $typeid);
            // 获取原计划
            $orgPlanList = array();
            if (!empty($lastRep)) {
                $orgPlanList = ReportRecord::model()->fetchRecordByRepidAndPlanflag($lastRep['repid'],
                    2);
            }
            $params = array(
                'typeid' => $typeid,
                'summaryAndPlanDate' => $summaryAndPlanDate,
                'intervals' => $reportType['intervals'],
                'intervaltype' => $reportType['intervaltype'],
                'subject' => $subject,
                'upUid' => StringUtil::wrapId($upUid),
                'uploadConfig' => Attach::getUploadConfig(),
                'orgPlanList' => $orgPlanList,
                'isInstallCalendar' => Module::getIsEnabled('calendar')
            );
            $this->setPageTitle(Ibos::lang('Add report'));
            $this->setPageState('breadCrumbs',
                array(
                    array('name' => Ibos::lang('Personal Office')),
                    array('name' => Ibos::lang('Work report'), 'url' => $this->createUrl('default/index')),
                    array('name' => Ibos::lang('Add report'))
                ));
            $this->render('add', $params);
        } else {
            $this->$op();
        }
    }

    /**
     * 保存工作总结与计划
     */
    private function save()
    {
        if (Env::submitCheck('formhash')) {
            $postData = $_POST;
            $uid = Ibos::app()->user->uid;
            $postData['uid'] = $uid;
            $postData['subject'] = CHtml::encode($_POST['subject']);
            $toidArr = StringUtil::getId($postData['toid']);
            $postData['toid'] = implode(',', $toidArr);
            $postData['begindate'] = strtotime($postData['begindate']);
            $postData['enddate'] = strtotime($postData['enddate']);
            $reportData = ICReport::handleSaveData($postData);
            $repid = Report::model()->add($reportData, true);
            if ($repid) {
                // 更新附件
                if (!empty($postData['attachmentid'])) {
                    Attach::updateAttach($postData['attachmentid']);
                }
                // 更改原计划、添加计划外、下次计划
                $orgPlan = $outSidePlan = array();
                // 如果原计划存在，修改原计划完成度和执行情况
                if (array_key_exists('orgPlan', $_POST)) {
                    $orgPlan = $_POST['orgPlan'];
                }
                if (!empty($orgPlan)) {
                    foreach ($orgPlan as $recordid => $val) {
                        $updateData = array(
                            'process' => intval($val['process']),
                            'exedetail' => CHtml::encode($val['exedetail'])
                        );
                        if ($updateData['process'] == self::COMPLETE_FALG) {  // 如果进度条=10，改变完成状态
                            $updateData['flag'] = 1;
                        }
                        ReportRecord::model()->modify($recordid, $updateData);
                    }
                }
                // 去掉内容为空的计划外// 如果存在计划外，添加到该总结报告中
                if (array_key_exists('outSidePlan', $_POST)) {
                    $outSidePlan = array_filter($_POST['outSidePlan'],
                        create_function('$v',
                            'return !empty($v["content"]);'));
                }
                if (!empty($outSidePlan)) {
                    ReportRecord::model()->addPlans($outSidePlan, $repid,
                        $postData['begindate'], $postData['enddate'], $uid,
                        1);
                }
                // 下次计划
//                $nextPlan = array_filter($_POST['nextPlan'],
//                    create_function('$v', 'return !empty($v["content"]);'));
                $nextPlan = is_array($_POST['nextPlan']) ? $_POST['nextPlan'] : array();
                ReportRecord::model()->addPlans($nextPlan, $repid,
                    strtotime($_POST['planBegindate']),
                    strtotime($_POST['planEnddate']), $uid, 2);
                // 动态推送
                $wbconf = WbCommonUtil::getSetting(true);
                if (isset($wbconf['wbmovement']['report']) && $wbconf['wbmovement']['report']
                    == 1
                ) {
                    $userid = $postData['toid'];
                    $supUid = UserUtil::getSupUid($uid);
                    if (intval($supUid) > 0 && !in_array($supUid,
                            explode(',', $userid))
                    ) {
                        $userid = $userid . ',' . $supUid;
                    }
                    $data = array(
                        'title' => Ibos::lang('Feed title', '',
                            array(
                                '{subject}' => $postData['subject'],
                                '{url}' => Ibos::app()->urlManager->createUrl('report/review/show',
                                    array('repid' => $repid))
                            )),
                        'body' => StringUtil::cutStr($_POST['content'], 140),
                        'actdesc' => Ibos::lang('Post report'),
                        'userid' => trim($userid, ','),
                        'deptid' => '',
                        'positionid' => '',
                    );
                    WbfeedUtil::pushFeed($uid, 'report', 'report', $repid, $data);
                }
                // 更新积分
                UserUtil::updateCreditByAction('addreport', $uid);
                // 给汇报对象发提醒
                if (!empty($toidArr)) {
                    $config = array(
                        '{sender}' => User::model()->fetchRealnameByUid($uid),
                        '{subject}' => $reportData['subject'],
                        '{url}' => Ibos::app()->urlManager->createUrl('report/review/show',
                            array('repid' => $repid))
                    );
                    Notify::model()->sendNotify($toidArr, 'report_message',
                        $config, $uid);
                }
                /**
                 * 日志记录
                 */
                $log = array(
                    'user' => Ibos::app()->user->username,
                    'ip' => Ibos::app()->setting->get('clientip'),
                    'isSuccess' => 1
                );
                Log::write($log, 'action', 'module.report.default.save');
                $this->success(Ibos::lang('Save succeed', 'message'),
                    $this->createUrl('default/index'));
            } else {
                /**
                 * 日志记录
                 */
                $log = array(
                    'user' => Ibos::app()->user->username,
                    'ip' => Ibos::app()->setting->get('clientip'),
                    'isSuccess' => 0
                );
                Log::write($log, 'action', 'module.report.default.save');
                $this->error(Ibos::lang('Save faild', 'message'),
                    $this->createUrl('default/index'));
            }
        }
    }

    /**
     * 编辑页面
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        $repid = intval(Env::getRequest('repid'));
        $uid = Ibos::app()->user->uid;
        if (empty($op) || !in_array($op, array('getEditData', 'update'))) {
            $op = 'getEditData';
        }
        if ($op == 'getEditData') {
            if (empty($repid)) {
                $this->error(Ibos::lang('Parameters error', 'error'),
                    $this->createUrl('default/index'));
            }
            $report = Report::model()->fetchByPk($repid);
            $reportType = ReportType::model()->fetchByPk($report['typeid']);
            if (empty($report)) {
                $this->error(Ibos::lang('No data found', 'error'),
                    $this->createUrl('default/index'));
            }
            // 检查该总结是否属于该用户
            if ($report['uid'] != $uid) {
                $this->error(Ibos::lang('Request tainting', 'error'),
                    $this->createUrl('default/index'));
            }
            // 获取直属uid
            $upUid = UserUtil::getSupUid($uid);
            // 取得原计划、计划外、下次计划
            $record = ReportRecord::model()->fetchAllRecordByRep($report);
            // 取得附件
            $attachs = array();
            if (!empty($report['attachmentid'])) {
                $attachs = Attach::getAttach($report['attachmentid']);
            }
            $params = array(
                'report' => $report,
                'reportType' => $reportType,
                'upUid' => $upUid,
                'preAndNextRep' => Report::model()->fetchPreAndNextRep($report),
                'orgPlanList' => $record['orgPlanList'],
                'outSidePlanList' => $record['outSidePlanList'],
                'nextPlanList' => $record['nextPlanList'],
                'attachs' => $attachs,
                'uploadConfig' => Attach::getUploadConfig(),
                'isInstallCalendar' => Module::getIsEnabled('calendar')
            );
            // 取得下次计划时间
            if (!empty($params['nextPlanList'])) {  // 不为空就取其中一条的开始结束日期
                $firstPlan = $params['nextPlanList'][0];
                $params['nextPlanDate'] = array(
                    'planBegindate' => $firstPlan['begindate'],
                    'planEnddate' => $firstPlan['enddate']
                );
            } else {
                $params['nextPlanDate'] = array('planBegindate' => 0, 'planEnddate' => 0);
            }
            $this->setPageTitle(Ibos::lang('Edit report'));
            $this->setPageState('breadCrumbs',
                array(
                    array('name' => Ibos::lang('Personal Office')),
                    array('name' => Ibos::lang('Work report'), 'url' => $this->createUrl('default/index')),
                    array('name' => Ibos::lang('Edit report'))
                ));
            $this->render('edit', $params);
        } else {
            $this->$op();
        }
    }

    /**
     * 更新编辑提交数据
     */
    private function update()
    {
        if (Env::submitCheck('formhash')) {
            $repid = $_POST['repid'];
            $typeid = $_POST['typeid'];
            $uid = Ibos::app()->user->uid;
            // 获取要更新的数据
            $editRepData = array(
                'uid' => $uid,
                'begindate' => strtotime($_POST['begindate']),
                'enddate' => strtotime($_POST['enddate']),
                'typeid' => $typeid,
                'subject' => CHtml::encode($_POST['subject']),
                'content' => $_POST['content'],
                'attachmentid' => $_POST['attachmentid'],
                'toid' => implode(',', StringUtil::getId($_POST['toid']))
            );
            // 修改总结计划
            Report::model()->modify($repid, $editRepData);
            // 如果原计划存在，修改原计划完成度和执行情况
            if (isset($_POST['orgPlan'])) {
                foreach ($_POST['orgPlan'] as $recordid => $orgPlan) {
                    $updateData = array(
                        'process' => intval($orgPlan['process']),
                        'exedetail' => CHtml::encode($orgPlan['exedetail'])
                    );
                    if ($updateData['process'] == self::COMPLETE_FALG) {  // 如果进度条=10，改变完成状态
                        $updateData['flag'] = 1;
                    }
                    ReportRecord::model()->modify($recordid, $updateData);
                }
            }
            // 删除原来的计划外、下次计划
            ReportRecord::model()->deleteAll('repid=:repid AND planflag!=:planflag',
                array(':repid' => $repid, ':planflag' => 0));
            //若已安装日程，删除关联表数据和有提醒时间的日程,再重新插入新的
            $isInstallCalendar = Module::getIsEnabled('calendar');
            if ($isInstallCalendar) {
                Calendars::model()->deleteALL("`calendarid` IN(select `cid` from {{calendar_rep_record}} where `repid`={$repid})");
                CalendarRepRecord::model()->deleteAll("repid = {$repid}");
            }
            // 插入新的计划外
            if (isset($_POST['outSidePlan'])) {
                $outSidePlan = array_filter($_POST['outSidePlan'],
                    create_function('$v', 'return !empty($v["content"]);'));
                if (!empty($outSidePlan)) {
                    ReportRecord::model()->addPlans($outSidePlan, $repid,
                        $editRepData['begindate'], $editRepData['enddate'],
                        $uid, 1);
                }
            }
            // 插入新的下次计划
            if (isset($_POST['nextPlan'])) {
                $nextPlan = array_filter($_POST['nextPlan'],
                    create_function('$v', 'return !empty($v["content"]);'));
                if (!empty($nextPlan)) {
                    ReportRecord::model()->addPlans($nextPlan, $repid,
                        strtotime($_POST['planBegindate']),
                        strtotime($_POST['planEnddate']), $uid, 2);
                }
            }
            //更新附件
            $attachmentid = trim($_POST['attachmentid'], ',');
            Attach::updateAttach($attachmentid);

            $this->success(Ibos::lang('Update succeed', 'message'),
                $this->createUrl('default/index'));
        }
    }

    /**
     * 删除总结与计划
     */
    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $repids = Env::getRequest('repids');
            $uid = Ibos::app()->user->uid;
            if (empty($repids)) {
                $this->error(Ibos::lang('Parameters error', 'error'),
                    $this->createUrl('default/index'));
            }
            $pk = '';
            if (strpos($repids, ',')) {
                $repids = trim($repids, ',');
                $pk = explode(',', $repids);
            } else {
                $pk = array($repids);
            }
            $reports = Report::model()->fetchAllByPk($pk);
            foreach ($reports as $report) {
                // 权限判断
                if ($report['uid'] != $uid) {
                    $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('You do not have permission to delete the report')));
                }
            }
            //删除附件
            $aids = Report::model()->fetchAllAidByRepids($pk);
            if ($aids) {
                Attach::delAttach($aids);
            }
            //若已安装日程，删除关联表数据和有提醒时间的日程
            $isInstallCalendar = Module::getIsEnabled('calendar');
            if ($isInstallCalendar) {
                Calendars::model()->deleteALL("`calendarid` IN(select `cid` from {{calendar_rep_record}} where FIND_IN_SET(`repid`, '{$repids}')) ");
                CalendarRepRecord::model()->deleteAll("repid IN ({$repids})");
            }
            $delSuccess = Report::model()->deleteByPk($pk);
            if ($delSuccess) {
                ReportRecord::model()->deleteAll("repid IN('{$repids}')");
                // 删除评分
                ReportStats::model()->deleteAll("repid IN ({$repids})");
                $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Del succeed',
                    'message')));
            } else {
                $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Del failed',
                    'message')));
            }
        }
    }

    /**
     * 总结与计划详细页
     */
    public function actionShow()
    {
        $repid = Env::getRequest('repid');
        $uid = Ibos::app()->user->uid;
        if (empty($repid)) {
            $this->error(Ibos::lang('Parameters error', 'error'),
                $this->createUrl('default/index'));
        }
        $report = Report::model()->fetchByPk($repid);
        if (empty($report)) {
            $this->error(Ibos::lang('File does not exists', 'error'),
                $this->createUrl('default/index'));
        }
        // 检查该总结是否属于该用户
        if ($report['uid'] != $uid) {
            if (ICReport::checkPermission($report, $uid)) {
                $this->redirect($this->createUrl('review/show',
                    array('repid' => $repid)));
            } else {
                $this->error(Ibos::lang('Request tainting', 'error'),
                    $this->createUrl('default/index'));
            }
        }
        // 取得原计划、计划外、下次计划
        $record = ReportRecord::model()->fetchAllRecordByRep($report);

        $attachs = $readers = array();
        // 附件
        if (!empty($report['attachmentid'])) {
            $attachments = Attach::getAttach($report['attachmentid'], true,
                true, false, false, true);
            $attachs = array_values($attachments);  // 为了改成数字下标
        }
        // 阅读人
        if (!empty($report['readeruid'])) {
            $readerArr = explode(',', $report['readeruid']);
            $readers = User::model()->fetchAllByPk($readerArr);
        }
        // 图章
        $stampUrl = '';
        if (!empty($report['stamp'])) {
            $stampUrl = Stamp::model()->fetchStampById($report['stamp']);
        }
        $params = array(
            'report' => $report,
            'preAndNextRep' => Report::model()->fetchPreAndNextRep($report),
            'orgPlanList' => $record['orgPlanList'],
            'outSidePlanList' => $record['outSidePlanList'],
            'nextPlanList' => $record['nextPlanList'],
            'attachs' => $attachs,
            'readers' => $readers,
            'stampUrl' => $stampUrl,
            'realname' => User::model()->fetchRealnameByUid($report['uid']),
            'departmentName' => Department::model()->fetchDeptNameByUid($report['uid']),
            'isInstallCalendar' => Module::getIsEnabled('calendar')
        );
        // 处理下次计划的标题
        if (!empty($params['nextPlanList'])) {
            $reportType = ReportType::model()->fetchByPk($report['typeid']);
            $firstPlan = $params['nextPlanList'][0];
            $params['nextSubject'] = ICReport::handleShowSubject($reportType,
                $firstPlan['begindate'], $firstPlan['enddate'], 1);
        }
        $this->setPageTitle(Ibos::lang('Show report'));
        $this->setPageState('breadCrumbs',
            array(
                array('name' => Ibos::lang('Personal Office')),
                array('name' => Ibos::lang('Work report'), 'url' => $this->createUrl('default/index')),
                array('name' => Ibos::lang('Show report'))
            ));
        $this->render('show', $params);
    }

}
