<?php

/**
 * 工作总结与计划模块------评阅控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------评阅控制器，继承ReportBaseController
 * @package application.modules.report.components
 * @version $Id: ReviewController.php 1951 2013-12-17 03:47:48Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Stamp;
use application\modules\department\model\Department;
use application\modules\report\core\Report as ICReport;
use application\modules\report\model\ReportRecord;
use application\modules\report\model\ReportStats;
use application\modules\report\model\ReportType;
use application\modules\report\model\Report;
use application\modules\report\utils\Report as ReportUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class ReviewController extends BaseController
{

    /**
     * 侧栏视图
     */
    public function getSidebar($getUid, $getUser)
    {
        $uid = Ibos::app()->user->uid;
        if (!empty($getUid)) {
            $subUids = $getUid;
        } elseif (!empty($getUser)) {
            $subUids = Convert::getSubByKey($getUser, 'uid');
        } else {
            $subUids = UserUtil::getAllSubs($uid, '', true);
        }
        $deptArr = UserUtil::getManagerDeptSubUserByUid($uid);
        $sidebarAlias = 'application.modules.report.views.review.sidebar';
        $params = array(
            'statModule' => Ibos::app()->setting->get('setting/statmodules'),
            'lang' => Ibos::getLangSource('report.default'),
            'deptArr' => $deptArr,
            'dashboardConfig' => $this->getReportConfig(),
            'reportTypes' => ReportType::model()->fetchAllTypeByUid($subUids)
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, false);
        return $sidebarView;
    }

    /**
     * 列表页显示,取得当前uid所有下属的总结计划
     */
    public function actionIndex()
    {
        $op = Env::getRequest('op');
        if (!in_array($op, array('default', 'showDetail', 'personal', 'getsubordinates'))) {
            $op = 'default';
        }
        if ($op == 'default') {
            //是否搜索
            if (Env::getRequest('param') == 'search') {
                $this->search();
            }
            $typeid = intval(Env::getRequest('typeid'));
            $uid = Ibos::app()->user->uid;
            $getSubUids = Env::getRequest('subUids');  // 点击某个部门
            // 汇报类型条件
            $typeCondition = empty($typeid) ? 1 : "typeid = {$typeid}";
            // 点击某个部门
            if (empty($getSubUids)) {
                $subUidArr = User::model()->fetchSubUidByUid($uid);
                $getSubUids = implode(',', $subUidArr);
            } else {
                // 权限判断
                $subUidArr = explode(',', $getSubUids);
                foreach ($subUidArr as $subUid) {
                    if (!UserUtil::checkIsSub($uid, $subUid)) {
                        $this->error(Ibos::lang('Have not permission'), $this->createUrl('default/index'));
                    }
                }
            }
            $userCondition = "FIND_IN_SET(uid, '{$getSubUids}')";
            $condition = "( " . $typeCondition . " AND (" . $userCondition . " OR FIND_IN_SET({$uid}, `toid`) ) )";
            $this->_condition = ReportUtil::joinCondition($this->_condition, $condition);
            $paginationData = Report::model()->fetchAllByPage($this->_condition);
            $params = array(
                'typeid' => $typeid,
                'pagination' => $paginationData['pagination'],
                'reportList' => ICReport::handelListData($paginationData['data']),
                'dashboardConfig' => $this->getReportConfig()
            );
            $this->setPageTitle(Ibos::lang('Review subordinate report'));
            $this->setPageState('breadCrumbs', array(
                array('name' => Ibos::lang('Personal Office')),
                array('name' => Ibos::lang('Work report'), 'url' => $this->createUrl('review/index')),
                array('name' => Ibos::lang('Subordinate report'))
            ));
            $this->render('index', $params);
        } else {
            $this->$op();
        }
    }

    /**
     * 取得某个uid的所有总结计划
     * @return void
     */
    private function personal()
    {
        $uid = Ibos::app()->user->uid;
        $typeid = Env::getRequest('typeid');
        $getUid = intval(Env::getRequest('uid'));
        $condition = "uid = '{$getUid}'";
        if (!UserUtil::checkIsSub($uid, $getUid)) {
            $condition .= " AND FIND_IN_SET('{$uid}', toid )";
        }
        if (!empty($typeid)) {
            $condition .= " AND typeid = '{$typeid}'";
        }
        //是否搜索
        if (Env::getRequest('param') == 'search') {
            $this->search();
        }
        $this->_condition = ReportUtil::joinCondition($this->_condition, $condition);
        $paginationData = Report::model()->fetchAllByPage($this->_condition);
        $params = array(
            'dashboardConfig' => Ibos::app()->setting->get('setting/reportconfig'),
            'typeid' => $typeid,
            'pagination' => $paginationData['pagination'],
            'reportList' => ICReport::handelListData($paginationData['data']),
            'reportCount' => Report::model()->count("uid = '{$getUid}'"),
            'commentCount' => Report::model()->count("uid='{$getUid}' AND isreview=1"),
            'user' => User::model()->fetchByUid($getUid),
            'supUid' => UserUtil::getSupUid($getUid) //获取上司uid
        );
        $this->setPageTitle(Ibos::lang('Review subordinate report'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Work report'), 'url' => $this->createUrl('default/index')),
            array('name' => Ibos::lang('Subordinate personal report'))
        ));
        $this->render('personal', $params);
    }

    /**
     * 工作总结与计划详细页
     */
    public function actionShow()
    {
        $repid = intval(Env::getRequest('repid'));
        $uid = Ibos::app()->user->uid;
        if (empty($repid)) {
            $this->error(Ibos::lang('Parameters error', 'error'), $this->createUrl('review/index'));
        }
        $report = Report::model()->fetchByPk($repid);
        if (empty($report)) {
            $this->error(Ibos::lang('No data found', 'error'), $this->createUrl('review/index'));
        }
        // 增加阅读记录
        Report::model()->addReaderuid($report, $uid);
        if ($report['uid'] == $uid) {
            $this->redirect($this->createUrl('default/show', array('repid' => $repid)));
        }
        // 检查是否有权限
        $permission = ICReport::checkPermission($report, $uid);
        if (!$permission) {
            $this->error(Ibos::lang('You do not have permission to view the report'), $this->createUrl('review/index'));
        }
        // 取得原计划、计划外、下次计划
        $record = ReportRecord::model()->fetchAllRecordByRep($report);

        $attachs = $readers = array();
        // 附件
        if (!empty($report['attachmentid'])) {
            $attachments = Attach::getAttach($report['attachmentid'], true, true, false, false, true);
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
            'departmentName' => Department::model()->fetchDeptNameByUid($report['uid'])
        );
        // 处理下次计划的标题
        if (!empty($params['nextPlanList'])) {
            $reportType = ReportType::model()->fetchByPk($report['typeid']);
            $firstPlan = $params['nextPlanList'][0];
            $params['nextSubject'] = ICReport::handleShowSubject($reportType, $firstPlan['begindate'], $firstPlan['enddate'], 1);
        }
        //判断后台是否开启自动评阅，若是，把该总结改成已评阅
        $dashboardConfig = $this->getReportConfig();
        if ($dashboardConfig['stampenable'] && $dashboardConfig['autoreview']) {
            $this->changeIsreview($repid);
        }
        $this->setPageTitle(Ibos::lang('Show subordinate report'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Work report'), 'url' => $this->createUrl('default/index')),
            array('name' => Ibos::lang('Show subordinate report'))
        ));
        $this->render('show', $params);
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $op = Env::getRequest('op');
            $routes = array('changeIsreview');
            if (!in_array($op, $routes)) {
                $this->error(Ibos::lang('Can not find the path'), $this->createUrl('default/index'));
            }
            if ($op == 'changeIsreview') {
                $repid = Env::getRequest('repid');
                $this->changeIsreview($repid);
            } else {
                $this->$op();
            }
        }
    }

    /**
     * 把某篇总结改成已评阅
     */
    private function changeIsreview($repid)
    {
        $report = Report::model()->fetchByPk($repid);
        // 判断是否是直属上司，只给直属上司自动评阅
        if (!empty($report) && UserUtil::checkIsUpUid($report['uid'], Ibos::app()->user->uid)) {
            if ($report['stamp'] == 0) {
                $stamp = $this->getAutoReviewStamp();
                Report::model()->modify($repid, array('isreview' => 1, 'stamp' => $stamp));
                ReportStats::model()->scoreReport($report['repid'], $report['uid'], $stamp);
            } else {
                Report::model()->modify($repid, array('isreview' => 1));
            }
        }
    }

    /**
     * 得到某个用户的下属，取5条
     * @return void
     */
    private function getsubordinates()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = $_GET['uid'];
            $getItem = Env::getRequest('item');
            $item = empty($getItem) ? 5 : $getItem;
            $users = UserUtil::getAllSubs($uid);
            if (Env::getRequest('act') == 'stats') {
                $theUrl = 'report/stats/review';
            } else {
                $theUrl = 'report/review/index';
            }
            $htmlStr = '<ul class="mng-trd-list">';
            $num = 0;
            foreach ($users as $user) {
                if ($num < $item) {
                    $htmlStr .= '<li class="mng-item">
                                            <a href="' . Ibos::app()->urlManager->createUrl($theUrl, array('op' => 'personal', 'uid' => $user['uid'])) . '">
                                                <img src="' . $user['avatar_middle'] . '" alt="">
                                                ' . $user['realname'] . '
                                            </a>
                                        </li>';
                    $num++;
                }
            }
            $subNums = count($users);
            if ($subNums > $item) {
                $htmlStr .= '<li class="mng-item view-all" data-uid="' . $uid . '">
                                                <a href="javascript:;">
                                                    <i class="o-da-allsub"></i>
                                                    ' . Ibos::lang('View all subordinate') . '
                                                </a>
                                            </li>';
            }
            $htmlStr .= '</ul>';
            echo $htmlStr;
        }
    }

    /**
     * 获取图章icon
     */
    private function getStampIcon()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $repid = $_GET['repid'];
            $report = Report::model()->fetchByPk($repid);
            if ($report['stamp'] != 0) {
                $icon = Stamp::model()->fetchIconById($report['stamp']);
                $this->ajaxReturn(array('isSuccess' => true, 'icon' => $icon));
            }
        }
    }

}
