<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\department\model\Department;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\main\components\CommonAttach;
use application\modules\main\utils\Main;
use application\modules\main\utils\Main as MainUtil;
use application\modules\message\model\Notify;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;
use application\modules\workflow\core\FlowConst;
use application\modules\workflow\core\FlowForm as ICFlowForm;
use application\modules\workflow\core\FlowFormViewer as ICFlowFormViewer;
use application\modules\workflow\core\FlowProcess as ICFlowProcess;
use application\modules\workflow\core\FlowRun as ICFlowRun;
use application\modules\workflow\core\FlowRunProcess as ICFlowRunProcess;
use application\modules\workflow\core\FlowType as ICFlowType;
use application\modules\workflow\model\FlowCategory;
use application\modules\workflow\model\FlowDataN;
use application\modules\workflow\model\FlowProcess;
use application\modules\workflow\model\FlowProcessTurn;
use application\modules\workflow\model\FlowRun;
use application\modules\workflow\model\FlowRunfeedback;
use application\modules\workflow\model\FlowRunProcess;
use application\modules\workflow\model\FlowType;
use application\modules\workflow\utils\Common;
use application\modules\workflow\utils\Common as WfCommonUtil;
use application\modules\workflow\utils\Handle;
use application\modules\workflow\utils\Handle as WfHandleUtil;
use application\modules\workflow\utils\Preview as WfPreviewUtil;
use application\modules\workflow\utils\WfNew as WfNewUtil;

class WorkController extends BaseController
{

    const TODO = '1,2';   // 待办标记
    const FORCE = 1;
    const UN_RECEIVE = 1; // 未接收
    const HANDLE = 2;   // 办理中
    const TRANS = '3,4';   // 已转交
    const DONE = 4; // 已办结
    const PRESET = 5;   // 自由流程预设步骤
    const DELAY = 6;   // 已延期
    const DEFAULT_PAGE_SIZE = 10; // 默认页面条数

    /**
     * 列表页专用属性
     * @var array
     */

    protected $_extraAttributes = array(
        'uid' => 0,
        'op' => '',
        'sort' => '',
        'type' => '',
        'runid' => '',
        'flowid' => '',
        'processid' => '',
        'flowprocess' => '',
        'sortText' => '',
        'key' => ''
    );

    /**
     * 检索类型 - 数据库标识 映射数组
     * @var array
     */
    protected $typeMapping = array(
        'todo' => self::TODO,
        'trans' => self::TRANS,
        'done' => self::DONE,
        'delay' => self::DELAY
    );

    public function actionIndex()
    {
        $param = array(
            'op' => $this->op,
            'type' => $this->type,
            'sort' => $this->sort,
        );
        $data = array_merge($param, $this->getListData());
        $this->ajaxReturn($data, Mobile::dataType());
    }

    /**
     * 处理列表数据
     * @return array
     */
    protected function getListData()
    {
        $fields = array(
            'frp.runid', 'frp.processid', 'frp.flowprocess',
            'frp.flag', 'frp.opflag', 'frp.processtime', 'ft.freeother',
            'ft.flowid', 'ft.name as typeName', 'ft.type', 'ft.listfieldstr',
            'fr.name as runName', 'fr.beginuser', 'fr.begintime', 'fr.endtime',
            'fr.focususer'
        );
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        // 如果是分类视图并且检索类型是待办，要把未接收的工作也找出来作为气泡显示
        $flag = $this->typeMapping[$this->type];
        $condition = array(
            'and', 'fr.delflag = 0', 'frp.childrun = 0', sprintf('frp.uid = %d', $this->uid)
        );
        if ($flag == self::DONE) {
            $condition[] = "fr.endtime != '0'";
        } else {
            $condition[] = array('in', 'frp.flag', explode(',', $flag));
        }
        if ($flag == self::TRANS) {
            $condition[] = 'fr.endtime = 0';
        }
        $sort = 'frp.runid DESC';
        $group = '';
        if ($this->getIsOver()) {
            if ($this->type == 'trans') {
                $sort = 'frp.processtime DESC';
            } else {
                $sort = 'fr.endtime DESC';
            }
            $group = 'frp.runid';
        } elseif ($this->getIsTodo()) {
            $sort = 'frp.createtime DESC';
        } elseif ($this->getIsDelay()) {
            $sort = 'frp.flag DESC';
        }
        if ($this->sort == 'host') {
            $condition[] = 'frp.opflag = 1';
        } else if ($this->sort == 'sign') {
            $condition[] = 'frp.opflag = 0';
        } else if ($this->sort == 'rollback') {
            $condition[] = 'frp.processid != frp.flowprocess';
        }
        if ($this->flowid !== '') {
            $condition[] = 'fr.flowid = ' . $this->flowid;
        }
        $key = StringUtil::filterCleanHtml(Env::getRequest('keyword'));
        if ($key) {
            $condition[] = array('or', "fr.runid LIKE '%{$key}%'", "fr.name LIKE '%{$key}%'",);
        }
        $runProcess = Ibos::app()->db->createCommand()
            ->select($fields)
            ->from('{{flow_run_process}} frp')
            ->leftJoin('{{flow_run}} fr', 'frp.runid = fr.runid')
            ->leftJoin('{{flow_type}} ft', 'fr.flowid = ft.flowid')
            ->where($condition)
            ->order($sort)
            ->group($group)
            ->limit(self::DEFAULT_PAGE_SIZE)
            ->offset($offset)
            ->queryAll();
        if (count($runProcess) < self::DEFAULT_PAGE_SIZE) {
            $hasMore = false;
        } else {
            $hasMore = true;
        }
        if ($this->op == 'list') {
            return array_merge(array(
                'datas' => $runProcess,
                'hasMore' => $hasMore
                ), $this->handleList($runProcess, $flag));
        } else if ($this->op == 'category') {
            return $this->handleCategory($runProcess);
        }
    }

    /**
     * 当前页面是否已转交或已办结类型
     * @return boolean
     */
    protected function getIsOver()
    {
        return in_array($this->type, array('trans', 'done'));
    }

    /**
     * 当前页面是否待办类型
     * @return boolean
     */
    protected function getIsTodo()
    {
        return $this->type == 'todo';
    }

// -------------------以下来自formController-----------------------------

    /**
     * 当前页面是否延期类型
     * @return boolean
     */
    protected function getIsDelay()
    {
        return $this->type == 'delay';
    }

    // ---------------------以下主办所用到的方法--------------------

    /**
     * 处理列表视图显示
     * @param array $runProcess
     * @param string $flag
     * @return array
     */
    protected function handleList($runProcess, $flag)
    {
        $allProcess = FlowProcess::model()->fetchAllProcessSortByFlowId();
        foreach ($runProcess as &$run) {
            // 发起人
            //$run['user'] = User::model()->fetchByUid( $run['beginuser'] );
            // 如果查询类型为已办结及已完成，查找该运行实例实际运行的步骤信息
            if ($this->getIsOver()) {
                //-- 获取当前工作流最新一步骤的主办信息
                $rp = FlowRunProcess::model()->fetchCurrentNextRun($run['runid'], $this->uid, $flag);
                if (!empty($rp)) {
                    $run['processid'] = $rp['processid'];
                    $run['flowprocess'] = $rp['flowprocess'];
                    $run['opflag'] = $rp['opflag'];
                    $run['flag'] = $rp['flag'];
                }
            }

            if ($run['type'] == 1) {
                // 如果是固定流程，显示实际步骤的名字
                if (isset($allProcess[$run['flowid']][$run['flowprocess']]['name'])) {
                    $run['stepname'] = $allProcess[$run['flowid']][$run['flowprocess']]['name'];
                } else {
                    $run['stepname'] = Ibos::lang('Process steps already deleted');
                }
            } else {
                //如果是自由流程则显示当前是第几步骤
                $run['stepname'] = Ibos::lang('Stepth', '', array('{step}' => $run['processid']));
            }
            if ($this->type !== 'done') {
                $run['focus'] = StringUtil::findIn($this->uid, $run['focususer']);
            } else {
                if (!empty($run['endtime'])) {
                    $usedTime = $run['endtime'] - $run['begintime'];
                    $run['usedtime'] = WfCommonUtil::getTime($usedTime);
                }
            }

            // 页面可操作项判断
            $handleOpt = $this->getHandleOpt($run);
            $rollbackOpt = $this->getRollbackOpt($run);
            $turnOpt = $this->getTurnOpt($run);
            $endOpt = $this->getEndOpt($run);
            $delOpt = $this->getDelOpt($run);
            // 通用传递参数，url编码使之不明文可见
            $param = array(
                'runid' => $run['runid'],
                'flowid' => $run['flowid'],
                'processid' => $run['processid'],
                'flowprocess' => $run['flowprocess']
            );
            $run['key'] = WfCommonUtil::param($param);
            // foreach($runProcess as $k => $rp){
            // 	$runProcess[$k]['handleopt'] = $handleOpt;
            // 	$runProcess[$k]['rollbackopt'] = $rollbackOpt;
            // 	$runProcess[$k]['turnopt'] = $turnOpt;
            // 	$runProcess[$k]['endopt'] = $endOpt;
            // 	$runProcess[$k]['delopt'] = $delOpt;
            // }
        }
        return array('datas' => $runProcess);
    }

    /**
     * 获取办理类型操作权限 (主办or会签)
     * @param array $run 当前运行实例
     */
    public function getHandleOpt(&$run)
    {
        // 必须在待办页面才可进行下一判断
        if ($this->getIsTodo()) {
            if ($run['opflag'] == '1') {
                $run['host'] = true;
                return 'host';
            } else {
                $run['sign'] = true;
                return 'sign';
            }
        }
    }

    /**
     * 获取撤回操作权限
     * @param array $run 当前运行实例
     */
    public function getRollbackOpt(&$run)
    {
        // 必须：主办人 及 转交下一步状态 及 未结束 及 在结束类型页面 才可撤回
        if ($run['opflag'] && $run['flag'] == FlowConst::PRCS_TRANS && $run['endtime'] == 0 && $this->getIsOver()) {
            $run['rollback'] = true;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取转交下一步操作权限
     * @param array $run 当前运行实例
     */
    public function getTurnOpt(&$run)
    {
        // 非办结类型及办理中状态才可进行下一判断
        if ($run['flag'] == FlowConst::PRCS_HANDLE && !$this->getIsOver()) {
            // 主办人才可转交
            if ($run['opflag'] == '1') {
                $run['turn'] = true;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取结束流程操作权限
     * @param array $run 当前运行实例
     */
    public function getEndOpt(&$run)
    {
        // 非办结类型及办理中状态才可进行下一判断
        if ($run['flag'] == FlowConst::PRCS_HANDLE && !$this->getIsOver()) {
            // 非固定流程与主办人才可结束
            if ($run['type'] != 1 && $run['opflag'] == '1') {
                $run['end'] = true;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取删除权限
     * @param array $run 当前运行实例
     */
    public function getDelOpt(&$run)
    {
        // 必须在流程第一步 及 未转交之前 或 拥有管理员权限的人才可删除
        if (($run['processid'] == '1' && $run['flag'] < FlowConst::PRCS_TRANS) || Ibos::app()->user->isadministrator) {
            $run['del'] = true;
            return true;
        } else {
            return false;
        }
    }

    public function actionFollow()
    {
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $fields = array(
            'frp.runid', 'frp.processid', 'frp.flowprocess',
            'frp.flag', 'frp.opflag', 'frp.processtime', 'ft.freeother',
            'ft.flowid', 'ft.name as typeName', 'ft.type', 'ft.listfieldstr',
            'fr.name as runName', 'fr.beginuser', 'fr.begintime', 'fr.endtime',
            'fr.focususer'
        );
        $flag = $this->typeMapping[$this->type];
        $sort = 'frp.runid DESC';
        $group = 'frp.runid';
        $condition = array(
            'and', 'fr.delflag = 0', 'frp.childrun = 0', sprintf('frp.uid = %d', $this->uid),
            sprintf("FIND_IN_SET(fr.focususer,'%s')", $this->uid)
        );
        $list = Ibos::app()->db->createCommand()
            ->select($fields)
            ->from('{{flow_run_process}} frp')
            ->leftJoin('{{flow_run}} fr', 'frp.runid = fr.runid')
            ->leftJoin('{{flow_type}} ft', 'fr.flowid = ft.flowid')
            ->where($condition)
            ->order($sort)
            ->group($group)
            ->limit(self::DEFAULT_PAGE_SIZE)
            ->offset($offset)
            ->queryAll();
        if (count($list) < self::DEFAULT_PAGE_SIZE) {
            $hasMore = false;
        } else {
            $hasMore = true;
        }
        $data = array_merge(
            array(
            'datas' => $list,
            'hasMore' => $hasMore
            ), $this->handleList($list, $flag)
        );
        $this->ajaxReturn($data, Mobile::dataType());
    }

    public function actionNew()
    {
        $data = array();
        $this->handleStartFlowList($data);
        $this->ajaxReturn($data, Mobile::dataType());
    }

    /**
     * 处理发起工作的列表数据
     * @param array $data
     */
    protected function handleStartFlowList(&$data)
    {
        $flowList = $commonlyFlowList = $sort = array();
        // 获取当前用户可用的工作流ID
        $enabledFlowIds = WfNewUtil::getEnabledFlowIdByUid($this->uid);
        $commonlyFlowIds = FlowRun::model()->fetchCommonlyUsedFlowId($this->uid);
        foreach (FlowType::model()->fetchAll(array('order' => 'sort,flowid')) as $flow) {
            $catId = $flow['catid'];
            $flowId = $flow['flowid'];
            if (!isset($flowList[$catId])) {
                $sort[$catId] = array();
                $cat = FlowCategory::model()->fetchByPk($catId);
                if ($cat) {
                    $sort[$catId] = $cat;
                }
            }
            // 使用状态过滤：无论有无权限，都不可见
            if ($flow['usestatus'] == 3) {
                continue;
            }
            // 使用状态过滤：有权限才可见
            $enabled = in_array($flowId, $enabledFlowIds);
            if (!$enabled && $flow['usestatus'] == 2) {
                continue;
            }
            // 使用状态过滤：可见但无权限者不可点击，赋予一个变量标识，交由前台控制交互
            $flow['enabled'] = $enabled;
            // 常用流程
            if (in_array($flowId, $commonlyFlowIds)) {
                $commonlyFlowList[] = $flow;
            }
            $flowList[$catId][] = $flow; //和网页端不一样，只需要数组
        }
        // 根据后台分类的排序对数组重新排序
        ksort($flowList, SORT_NUMERIC);
//		$data['flows'] = $flowList;
        $data['common'] = $commonlyFlowList;
        foreach ($sort as $key => &$cate) {
            if (isset($flowList[$key])) {
                $cate["flows"] = $flowList[$key];
                $cate["flowcount"] = count($flowList[$key]);
                $data['cate'][] = $cate;
            }
        }
    }

    /**
     * 表单办理
     *
     */
    public function actionForm()
    {
        $key = Env::getRequest('key');
        $type = Env::getRequest('type');
        if ($key) {
            $this->key = $key;
            $param = WfCommonUtil::param($key, 'DECODE');
            $this->_extraAttributes = $param;
            $this->runid = $param['runid'];
            $this->flowid = $param['flowid'];
            $this->processid = $param['processid'];
            $this->flowprocess = $param['flowprocess'];
        } else {
            $this->ajaxReturn("<script>alert('工作流数据错误，可能已转交或被回退')</script>", "EVAL");
        }
        // 流程实例
        $flow = new ICFlowType(intval($this->flowid));
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = array();
            // 只读控件
            $readOnly = $_POST['readonly'];
            // 隐藏控件
            $hidden = $_POST['hidden'];
            // 保存标志
            $saveflag = $_POST['saveflag']; //保存跳转标志
            // 会签意见附件
            $fbAttachmentId = $_POST['fbattachmentid'];
            // 公共附件
            $attachmentID = $_POST['attachmentid'];
            // 会签意见
            $content = isset($_POST['content']) ? StringUtil::filterCleanHtml($_POST['content']) : '';
            // 经办人标记
            $topflag = $_POST['topflag'];
            // 检查权限
            $this->checkRunAccess($this->runid, $this->processid, $this->createUrl('list/index'));
            // 如果是主办人
            if (FlowRunProcess::model()->getIsOp($this->uid, $this->runid, $this->processid)) {
                //手机端改写：只需要关注可写字段
                $enablefiledArr = explode(",", $_POST['enablefiled']);

                $formData = array();
                $structure = $flow->form->parser->structure;
                foreach ($structure as $index => $item) {
                    if (!in_array("data_" . $item['itemid'], $enablefiledArr)) {
                        continue;
                    }
                    $value = isset($_POST[$index]) ? $_POST[$index] : '';
                    $formData[$index] = $value;
                }
                $formData && $this->handleImgComponent($formData);
                $formData && FlowDataN::model()->update($this->flowid, $this->runid, $formData);
            }
            // 会签意见处理部分，会签意见，手写签章，或上传了会签附件都视为提交了一条会签记录
            if (!empty($content) || !empty($fbAttachmentId)) {
                $fbData = array(
                    'runid' => $this->runid,
                    'processid' => $this->processid,
                    'flowprocess' => $this->flowprocess,
                    'uid' => $this->uid,
                    'content' => $content,
                    'attachmentid' => $fbAttachmentId,
                    'edittime' => TIMESTAMP,
                );
                FlowRunfeedback::model()->add($fbData);
                // 更新会签附件ID
                Attach::updateAttach($fbAttachmentId, $this->runid);
            }
            FlowRun::model()->modify($this->runid, array('attachmentid' => $attachmentID));
            Attach::updateAttach($attachmentID, $this->runid);
            // 执行保存插件
            $plugin = FlowProcess::model()->fetchSavePlugin($this->flowid, $this->flowprocess);
            if (!empty($plugin)) {
                $pluginFile = './system/modules/workflow/plugins/save/' . $plugin;
                if (file_exists($pluginFile)) {
                    include_once($pluginFile);
                }
            }
            switch ($saveflag) {
                case 'save':
                    MainUtil::setCookie('save_flag', 1, 300);
                    $this->redirect($this->createUrl('form/index', array('key' => $this->key)));
                    $this->ajaxReturn("<script>alert('保存成功')</script>", "EVAL");
                    break;
                case 'turn':
                    MainUtil::setCookie('turn_flag', 1, 300);
                    $this->redirect($this->createUrl('form/index', array('key' => $this->key)));
                    break;
                case 'end':
                case 'finish':
                    if ($saveflag == 'end') {
                        $param = array('opflag' => 1);
                    } else {
                        $param = array('topflag' => $topflag);
                    }
                    $this->redirect($this->createUrl('handle/complete', array_merge($param, array('key' => $this->key))));
                    break;
                default:
                    break;
            }
        } else {
            $this->checkRunDel();
            // 查看工作流时不检查权限
            if ($type != 'view') {
                $this->checkIllegal();
            }
            $len = strlen($flow->autonum);
            for ($i = 0; $i < $flow->autolen - $len; $i++) {
                $flow->autonum = "0" . $flow->autonum;
            }
            // 运行步骤实例
            $runProcess = new ICFlowRunProcess($this->runid, $this->processid, $this->flowprocess, $this->uid);
            // 如果是固定流程，取步骤信息
            $checkitem = '';
            $attr = array();
            if ($flow->isFixed()) {
                // 步骤实例
                $process = new ICFlowProcess($this->flowid, $this->flowprocess);
                // 会签人不需要添加表单填写验证
                $attr = $process->toArray();
                if (!empty($attr) && $runProcess->opflag != 0) {
                    $checkitem = $process->checkitem;
                }
                if (!empty($attr) && $process->allowback > 0) {
                    $isAllowBack = true; //$this->isAllowBack( $runProcess->parent );
                }
            } else {
                $process = array();
            }
            // 运行实例
            $run = new ICFlowRun($this->runid);
            $hasOtherOPUser = FlowRunProcess::model()->getHasOtherOPUser(
                $this->runid, $this->processid, $this->flowprocess, $this->uid);
            // 如果当前步骤是子流程，查找出父流程的流程ID
            // if ( $run->pid !== 0 ) {
            // 	$parentFlowID = FlowRun::model()->fetchFlowIdByRunId( $run->pid );
            // }
            // 如果当前步骤状态为未接收，设置该步骤状态为办理中
            if ($runProcess->flag == self::UN_RECEIVE) {
                $this->setSelfToHandle($runProcess->id);
            }
            // 如果当前是主办人并且设置了先接收者为主办，把后续步骤更新为从办人
            if ($runProcess->topflag == 1 && $runProcess->opflag == 1) {
                FlowRunProcess::model()->updateTop(
                    $this->uid, $this->runid, $this->processid, $this->flowprocess
                );
            }
            // 如果设置了无主办人会签，要检查是否还有别的人没有会签，如果只剩下自己，那么就可以转交下一步
            if ($runProcess->topflag == 2) {
                if (!$hasOtherOPUser) {
                    $runProcess->opflag = 1;
                }
            }
            // 如果是流程第一步
            if ($this->processid == 1) {
                // 设置工作流的发起人为自己
                FlowRun::model()->modify(
                    $this->runid, array('beginuser' => $this->uid, 'begintime' => TIMESTAMP)
                );
                // 如果当前步骤是子流程且是第一步，更新工作流运行步骤表该步骤的办理状态为办理中
                if (!empty($run->parentrun)) {
                    $this->setParentToHandle($run->parentrun, $this->runid);
                }
            }
            // 修改上一步骤状态为已经办理完毕
            // $preProcess = $this->processid - 1;
            // if ( $preProcess ) {
            // 	if ( $flow->isFree() ||
            // 			$flow->isFixed() && !empty( $attr ) && $process->gathernode != self::FORCE
            // 	) {
            // 		$this->setProcessDone( $preProcess );
            // 	}
            // }
            // 如果是固定流程并设置了超时间隔的
            if ($flow->isFixed() && !empty($attr) && $process->timeout != 0) {
                // 如果该步骤未接收并且不是第一步，流程开始的时间为上一步的办结完的时间
                if ($runProcess->flag == self::UN_RECEIVE && $this->processid !== 1) {
                    $processBegin = FlowRunProcess::model()->fetchDeliverTime($this->runid, $preProcess);
                } else {
                    // 否则，为该步骤开始办理的时间
                    $processBegin = $runProcess->processtime ? $runProcess->processtime : TIMESTAMP;
                }
                $timeUsed = TIMESTAMP - $processBegin;
            }
            // 处理表单
            $viewer = new ICFlowFormViewer(
                array(
                'flow' => $flow,
                'form' => $flow->getForm(),
                'run' => $run,
                'process' => $process,
                'rp' => $runProcess
                )
            );
            $data = array_merge(
                array(
                'flow' => $flow->toArray(),
                'run' => $run->toArray(),
                'process' => !empty($process) ? $attr : $process,
                'checkItem' => $checkitem,
                'prcscache' => WfCommonUtil::loadProcessCache($this->flowid),
                'rp' => $runProcess->toArray(),
                'rpcache' => WfPreviewUtil::getViewFlowData($this->runid, $this->flowid, $this->uid, $remindUid),
                'fbSigned' => $this->isFeedBackSigned(),
                'allowBack' => isset($isAllowBack) ? $isAllowBack : false,
                'timeUsed' => isset($timeUsed) ? $timeUsed : 0,
                'uploadConfig' => Attach::getUploadConfig()
                ), $viewer->render(false, false, true) //手机端渲染数据
            );

            // 按可写,已写,空值 三种状态来划分控件. 手机端：
            $formdata = array(
                'run' => $data['run'],
                'flow' => $data['flow'],
                'enableArr' => '',
                'valueArr' => '',
                'emptyArr' => '',
            );
            $data['enablefiled'] = array();

            if (isset($data['model']['itemData']) && is_array($data['model']['itemData'])) {
                if ($flow->isFixed()) {
                    if (isset($data['prcscache'][$data['rp']['flowprocess']]['processitem'])) {
                        $enableFiled = explode(",", $data['prcscache'][$data['rp']['flowprocess']]['processitem']);
                    } else {
                        $enableFiled = array();
                    }
                } elseif ($flow->isFree()) {  // 自由流程
                    if (isset($data['rp']['freeitem'])) {
                        $enableFiled = explode(",", $data['rp']['freeitem']);
                    } else {
                        $enableFiled = array();
                    }
                }
                foreach ($data['model']['itemData'] as $k => $v) {
                    if (substr($k, 0, 5) != "data_") {
                        continue;
                    }
                    if (!empty($data['model']['structure'][$k]['data-title'])) {
                        $data['model']['structure'][$k]['origin-value'] = $v;
                        $data['model']['structure'][$k]['value'] = $v;

                        if (in_array($data['model']['structure'][$k]['data-title'], $enableFiled)) {
                            //记下可修改的页面
                            $data['enablefiled'][] = $k;
                            $data['model']['structure'][$k]['value'] = empty($data['model']['eleout'][$k]) ? '' : $data['model']['eleout'][$k];
                            $formdata['enableArr'][] = $data['model']['structure'][$k];
                            continue;
                        }
                        if ($v != "") {
                            $formdata['valueArr'][] = $data['model']['structure'][$k];
                            continue;
                        }
                        // 自由流程为可写字段未设置时，默认都可写
                        if ($flow->isFree() && empty($data['rp']['freeitem'])) {
                            $data['enablefiled'][] = $k;
                            $data['model']['structure'][$k]['value'] = $data['model']['eleout'][$k];
                            $formdata['enableArr'][] = $data['model']['structure'][$k];
                        }
                        if ($flow->isFixed()) {
                            $formdata['emptyArr'][] = $data['model']['structure'][$k];
                        }
                    }
                }
            }
            //记下可修改的页面
            $data['model'] = $this->renderPartial('application.modules.mobile.views.work.form', $formdata, true);
            $data['model'] .= '<input type="hidden" name="key" value="' . $this->key . '">';
            $data['model'] .= '<input type="hidden" name="hidden" value="' . $data['hidden'] . '">';
            $data['model'] .= '<input type="hidden" name="readonly" value="' . $data['readonly'] . '">';
            $data['model'] .= '<input type="hidden" name="attachmentid" id="attachmentid" value="' . $data['run']['attachmentid'] . '">';
            $data['model'] .= '<input type="hidden" name="fbattachmentid" id="fbattachmentid" value="">';
            $data['model'] .= '<input type="hidden" name="topflag" value="' . $data['rp']['opflag'] . '">';
            $data['model'] .= '<input type="hidden" name="saveflag">';
            $data['model'] .= '<input type="hidden" name="formhash" value="' . FORMHASH . '">';
            $data['model'] .= '<input type="hidden" name="enablefiled" value="' . implode(",", $data['enablefiled']) . '">';

            $data['enableArr'] = $formdata['enableArr'];
            $data['valueArr'] = $formdata['valueArr'];
            $data['emptyArr'] = $formdata['emptyArr'];

            // exit($data['form']);
            // 处理公共附件
            if ($this->isEnabledAttachment($flow, $run, $process, $runProcess)) {
                $data['allowAttach'] = true;
                if (!empty($run->attachmentid)) {
                    $attachPurv = $this->getAttachPriv($flow, $process, $runProcess);
                    $down = $attachPurv['down'];
                    $edit = $attachPurv['edit'];
                    $del = $attachPurv['del'];
                    // 暂时没用到
                    // $read = $attachPurv['read'];
                    // $print = $attachPurv['print'];
                    $data['attachData'] = Attach::getAttach($run->attachmentid, $down, $down, $edit, $del);
                }
            } else {
                $data['allowAttach'] = false;
            }

            // 是否允许会签及读取会签意见信息
            if ($flow->isFixed() && !empty($attr) && $process->feedback != 1) {
                $data['allowFeedback'] = true;
                $data['feedback'] = WfHandleUtil::loadFeedback($flow->getID(), $run->getID(), $flow->type, $this->uid);
            } else {
                $data['allowFeedback'] = false;
            }

            // 如果回退选项为“允许回退到之前步骤”
            // 则将之前步骤列表输出
            if (!empty($process) && $process->allowback == '2') {
                $data['backlist'] = $this->getBackList($runProcess->flowprocess);
            }

            // 自由流程判断是否有预设步骤，如果没有，则可以结束当前流程
            if ($flow->isFree() && $runProcess->opflag == '1') {
                $hasDefault = FlowRunProcess::model()->getHasDefaultStep($this->runid, $this->processid);
                if (!$hasDefault) {
                    $data['defaultEnd'] = true;
                }
            }
            // 如果是自由流程及流程主办人选项设置为无主办人会签
            if ($flow->isFree() && $runProcess->topflag == '2') {
                // 检查是否还有其他经办人
                if (!$hasOtherOPUser) {
                    $data['otherEnd'] = true;
                }
            }
            $this->ajaxReturn($data, Mobile::dataType());
            /////////////////////////////////////////////////////////////
        }
    }

    /**
     * 检查运行实例权限
     * @param integer $runId
     * @param string $jump
     */
    public function checkRunAccess($runId, $processId = 0, $jump = '')
    {
        $per = WfCommonUtil::getRunPermission($runId, $this->uid, $processId);
        if (empty($per)) {
            $errMsg = Ibos::lang('Permission denied');
            if (!empty($jump)) {
                $this->error($errMsg, $jump);
            } else {
                exit($errMsg);
            }
        }
    }

// -------------------来自formController结束-----------------------------
// --------------------来自newController -------------------------------

    /**
     * 表单处理提交时对于图片上传控件的特别处理
     * @param array $formData
     */
    protected function handleImgComponent(&$formData)
    {
        // 处理图片上传控件
        foreach ($GLOBALS['_FILES'] as $key => $value) {
            if (strtolower(substr($key, 0, 5)) == "data_") {
                $formData["{$key}"] = "";
                $old = $_POST["imgid_" . substr($key, 5)];
                if ($value['name'] != "") {
                    if (!empty($old)) {
                        Attach::delAttach($old);
                    }
                    $upload = new CommonAttach($key, 'workflow');
                    $upload->upload();
                    $info = $upload->getUpload()->getAttach();
                    $upload->updateAttach($info['aid'], $this->runid);
                    $formData["{$key}"] = $info['aid'];
                } else {
                    $formData["{$key}"] = $old;
                }
            }
        }
    }

    /**
     * 检查处理实例是否已删除
     */
    protected function checkRunDel()
    {
        $isDel = FlowRun::model()->countByAttributes(
            array('delflag' => 1, 'runid' => $this->runid)
        );
        if ($isDel) {
            $this->error(Ibos::lang('Form run has been deleted'), $this->createUrl('list/index'));
        }
    }

    /**
     * 检查运行实例是否合法
     */
    protected function checkIllegal()
    {
        $illegal = FlowRunProcess::model()->getIsIllegal(
            $this->runid, $this->processid, $this->flowprocess, $this->uid
        );
        if ($illegal) {
            $this->error(Ibos::lang('Form run has been processed'), $this->createUrl('list/index'));
        }
    }

    /**
     * 设置当前步骤为办理状态
     * @param integer $id
     */
    protected function setSelfToHandle($id)
    {
        FlowRunProcess::model()->modify(
            $id, array('flag' => self::HANDLE, 'processtime' => TIMESTAMP)
        );
    }

// -------------------来自listController-----------------------------

    /**
     * 子流程设置父流程的步骤为办理中
     * @param integer $id
     * @param integer $child
     */
    protected function setParentToHandle($id, $child)
    {
        $criteria = array(
            // 'condition' => array(
            //     array(
            //         'and',
            //         sprintf( 'runid = %d', $id ),
            //         sprintf( 'uid = %d', $this->uid ),
            //         sprintf( 'childrun = %d', $child ),
            //     )
            // )
            'condition' => sprintf("runid = %d AND uid = %d AND childrun = %d", $id, $this->uid, $child)
        );
        FlowRunProcess::model()->updateAll(
            array('flag' => self::HANDLE, 'processtime' => TIMESTAMP), $criteria
        );
    }

    /**
     * 设置流程为已办结
     * @param integer $processID
     */
    protected function setProcessDone($processID)
    {
        $condition = sprintf('processid = %d AND runid = %d', $processID, $this->runid);
        FlowRunProcess::model()->updateAll(array('flag' => self::DONE), $condition);
    }

    /**
     * 是否已有主办人会签
     * @return boolean
     */
    protected function isFeedBackSigned()
    {
        return FlowRunfeedback::model()->getHasSignAccess($this->runid, $this->processid, $this->uid);
    }

    /**
     * 附件是否可用
     * @param ICFlowType $flow
     * @param ICFlowRun $run
     * @param mixed $process
     * @param ICFlowRunProcess $rp
     * @return boolean
     */
    protected function isEnabledAttachment(ICFlowType $flow, ICFlowRun $run, $process, ICFlowRunProcess $rp)
    {
        $enabled = false;
        if ($flow->allowattachment) {
            //$alreadyHaveAttach = $run->attachmentid !== '';
            $enabledInFreeItem = $this->isEnabledInFreeItem($flow, $rp);
            $isHost = $rp->opflag == '1';
            $inProcessItem = $flow->isFixed() && StringUtil::findIn($process->processitem, '[A@]');
            if ($enabledInFreeItem || ($inProcessItem && $isHost)) {
                $enabled = true;
            }
        }
        return $enabled;
    }

    /**
     * 自由流程中的可写字段判断
     * @param ICFlowType $flow
     * @param ICFlowRunProcess $rp
     * @return boolean
     */
    protected function isEnabledInFreeItem(ICFlowType $flow, ICFlowRunProcess $rp)
    {
        return $flow->isFree() && $rp->freeitem == '' || StringUtil::findIn($rp->freeitem, '[A@]');
    }

    /**
     * 获取附件权限
     * @param ICFlowType $flow
     * @param mixed $process
     * @param ICFlowRunProcess $rp
     */
    protected function getAttachPriv(ICFlowType $flow, $process, ICFlowRunProcess $rp)
    {
        $down = $edit = $del = $read = $print = false;
        // 附件权限判定开始 --
        if ($flow->isFree()) {
            // 自由流程不限制
            $down = true;
        } else {
            if (StringUtil::findIn($process->attachpriv, 4)) {
                $down = true;
            }
        }
        if ($flow->isFixed() && empty($process->attachpriv)) {
            $down = $edit = $del = true;
        }
        $isHost = $rp->opflag == '1';
        $inProcessItem = $flow->isFixed() && StringUtil::findIn($process->processitem, '[A@]');
        $enabledInFreeItem = $this->isEnabledInFreeItem($flow, $rp);
        if ($isHost && ($inProcessItem || $enabledInFreeItem)) {
            if (StringUtil::findIn($process->attachpriv, 2)) {
                $edit = true;
            }
            if (StringUtil::findIn($process->attachpriv, 3)) {
                $del = true;
            }
            if ($flow->isFixed()) {
                $edit = $del = true;
            }
        }
        if ($flow->isFixed() && StringUtil::findIn($process->processitem, 5)) {
            $print = true;
        }
        return array(
            'down' => $down,
            'edit' => $edit,
            'del' => $del,
            'read' => $read,
            'print' => $print,
        );
    }

    /**
     * 新建操作
     */
    public function actionAdd()
    {
        $flowId = intval(Env::getRequest('flowid'));
        $flowname = Env::getRequest('name');
        $flow = new ICFlowType($flowId);
        if (!empty($flow->autoname)) {
            $flowname = WfNewUtil::replaceAutoName($flow, $this->uid);
        } else {
            $flowname = sprintf('%s (%s)', $flow->name, date('Y-m-d H:i:s'));
        }
        // if ( Env::submitCheck( 'formhash' ) ) {
        $this->checkFlowAccess($flowId, 1, $this->createUrl('new/add'));
        // $this->beforeAdd( $_POST, $flow );
        // 运行实例记录
        $run = array(
            'name' => $flowname,
            'flowid' => $flowId,
            'beginuser' => $this->uid,
            'begintime' => TIMESTAMP
        );
        $runId = FlowRun::model()->add($run, true);
        // 运行实例步骤记录
        $runProcess = array(
            'runid' => $runId,
            'processid' => 1,
            'uid' => $this->uid,
            'flag' => FlowConst::PRCS_UN_RECEIVE,
            'flowprocess' => 1,
            'createtime' => TIMESTAMP
        );
        FlowRunProcess::model()->add($runProcess);
        // 检查是否有自动编号规则，有的话加1，下次新建时就会递增
        if (strstr($flow->autoname, '{N}')) {
            FlowType::model()->updateCounters(array('autonum' => 1), sprintf('flowid = %d', $flowId));
        }
        // 运行实例表单数据
        $runData = array(
            'runid' => $runId,
            'name' => $flowname,
            'beginuser' => $this->uid,
            'begin' => TIMESTAMP
        );
        $this->handleRunData($flow, $runData);
        $param = array(
            'flowid' => $flowId,
            'runid' => $runId,
            'processid' => 1,
            'flowprocess' => 1,
            'fromnew' => 1
        );
        // if ( Ibos::app()->request->getIsAjaxRequest() ) {
        $this->ajaxReturn(array('isSuccess' => true, 'key' => WfCommonUtil::param($param)), Mobile::dataType());
        // } else {
        //     $url = Ibos::app()->urlManager->createUrl( 'workflow/form/index', array( 'key' => WfCommonUtil::param( $param ) ) );
        //     header( "Location: $url" );
        // }
        // } else {
        // 	$this->checkFlowAccess( $flowId, 1 );
        // 	// 有自动文号表达式的，替换之
        // 	if ( !empty( $flow->autoname ) ) {
        // 		$runName = WfNewUtil::replaceAutoName( $flow, $this->uid );
        // 	} else {
        // 		$runName = sprintf( '%s (%s)', $flow->name, date( 'Y-m-d H:i:s' ) );
        // 	}
        // 	$data = array(
        // 		'flow' => $flow->toArray(),
        // 		'runName' => $runName
        // 		// ,'lang' => Ibos::getLangSources()
        // 	);
        // 	$this->ajaxReturn( $data, Mobile::dataType() );
        // }
    }

    /**
     * 检查流程步骤权限
     * @param integer $flowId 流程类型ID
     * @param integer $processId 步骤ID
     * @param string $jump 出错后跳转的URL
     */
    public function checkFlowAccess($flowId, $processId, $jump = '')
    {
        $per = WfNewUtil::checkProcessPermission($flowId, $processId, $this->uid);
        if (!$per) {
            $errMsg = Ibos::lang('Permission denied');
            if (!empty($jump)) {
                $this->error($errMsg, $jump);
            } else {
                exit($errMsg);
            }
        }
    }

    /**
     * 处理运行实例数据
     * @param ICFlowType $type 工作流类型实例
     * @param array $runData
     */
    protected function handleRunData(ICFlowType $type, &$runData)
    {
        $structure = $type->form->structure;
        foreach ($structure as $k => $v) {
            if ($v['data-type'] == "checkbox" && stristr($v['content'], "checkbox")) {
                if (stristr($v['content'], "checked") || stristr($v['content'], " checked=\"checked\"")) {
                    $itemData = "on";
                } else {
                    $itemData = "";
                }
            } else if (!in_array($v['data-type'], array('select', 'listview'))) {
                if (isset($v['data-value'])) {
                    $itemData = str_replace("\"", "", $v['data-value']);
                    if ($itemData == "{macro}") {
                        $itemData = "";
                    }
                } else {
                    $itemData = '';
                }
            } else {
                $itemData = '';
            }
            $runData[strtolower($k)] = $itemData;
        }
        WfCommonUtil::addRunData($type->getID(), $runData, $structure);
    }

    /**
     * 初始化检索条件[动作，类型，排序三个维度]
     */
    public function init()
    {
        parent::init();
        $op = Env::getRequest('op');
        if (!in_array($op, array('category', 'list'))) {
            $op = 'list';
        }
        $sort = Env::getRequest('sort');
        $sortMap = array(
            'all' => Ibos::lang('All of it'),
            'host' => Ibos::lang('Host'),
            'sign' => Ibos::lang('Sign'),
            'rollback' => Ibos::lang('Rollback'),
        );
        if (!isset($sortMap[$sort])) {
            $sort = 'all';
        }
        $type = Env::getRequest('type');
        if (!isset($this->typeMapping[$type])) {
            $type = 'todo';
        }
        $flowId = Env::getRequest('flowid');
        if ($flowId) {
            $this->flowid = intval($flowId);
        }
        $this->op = $op;
        $this->sort = $sort;
        $this->type = $type;
        $this->sortText = $sortMap[$sort];
    }

    /**
     * 主办页面回退操作
     */
    public function actionFallback()
    {
        $key = Env::getRequest('key');
        $param = WfCommonUtil::param($key, 'DECODE');
        $flowId = $param['flowid'];
        $processId = $param['processid'];
        $flowProcess = $param['flowprocess'];
        $runId = $param['runid'];
        $last = intval(Env::getRequest('id'));
        $msg = StringUtil::filterCleanHtml(Env::getRequest('remind'));
        $per = WfCommonUtil::getRunPermission($runId, $this->uid, $processId);
        if (!StringUtil::findIn($per, 1) && !StringUtil::findIn($per, 2) && !StringUtil::findIn($per, 3)) {
            $this->ajaxReturn(array('isSuccess' => false), Mobile::dataType());
        }
        $process = new ICFlowProcess($flowId, $flowProcess);
        $currentStep = Ibos::app()->db->createCommand()
            ->select()
            ->from('{{flow_run_process}}')
            ->where(" processid = '{$processId}' ")
            ->andWhere(" runid = '{$runId}' ")
            ->andWhere(" flowprocess = '{$flowProcess}' ")
            ->queryRow();
        if ($process->allowback > 0 && $processId != 1) {
            $prcsIDNew = $processId + 1;
            //------------直接返回上一步骤-----------------
            if (empty($last)) {
                $temp = Ibos::app()->db->createCommand()
                    ->select('frp.id,frp.flowprocess,frp.uid,fp.name,frp.parent')
                    ->from('{{flow_run}} fr')
                    ->leftJoin('{{flow_process}} fp', 'fr.flowid = fp.flowid')
                    ->leftJoin('{{flow_run_process}} frp', 'fr.runid = frp.runid AND frp.flowprocess = fp.processid')
                    ->where(array(
                        'and',
                        "fr.runid = {$runId}",
                        "frp.flowprocess = '{$currentStep['parent']}' "
                    ))
                    ->order('frp.id DESC')
                    ->limit(1)
                    ->queryRow();
                if ($temp) {
                    $flowProcessNew = $temp['flowprocess'];
                    $lastUID = $temp['uid'];
                    $parent = $temp['parent'];
                }
                $log = Ibos::lang('Return to prev step') . "【{$temp['name']}】";
            } else {
                $flowProcessNew = $last;
                $temp = FlowRunProcess::model()->fetch(array(
                    'select' => 'uid,flowprocess',
                    'condition' => "runid = {$runId} AND flowprocess = '{$last}' AND opflag = 1",
                    'order' => 'processid',
                    'limit' => 1
                ));
                if ($temp) {
                    $lastUID = $temp['uid'];
                }
                $log = Ibos::lang('Return to step', '', array('{step}' => FlowProcess::model()->fetchName($flowId, $flowProcessNew)));
            }
            //新建下一步
            $data = array(
                'runid' => $runId,
                'processid' => $prcsIDNew,
                'uid' => $lastUID,
                'flag' => '1',
                'flowprocess' => $flowProcessNew,
                'opflag' => '1',
                'topflag' => '0',
                'parent' => $parent,
                'isfallback' => 1
            );
            FlowRunProcess::model()->add($data);
            //更新本步骤状态
            FlowRunProcess::model()->updateAll(array(
                'delivertime' => TIMESTAMP,
                'flag' => FlowConst::PRCS_DONE
                ), "runid = {$runId} AND processid = {$processId} AND flowprocess = '{$flowProcess}' AND flag IN('1','2')");
            $key = WfCommonUtil::param(array(
                    'runid' => $runId,
                    'flowid' => $flowId,
                    'processid' => $prcsIDNew,
                    'flowprocess' => $flowProcessNew
            ));
            $url = Ibos::app()->urlManager->createUrl('workflow/form/index', array('key' => $key));
            $config = array(
                '{url}' => $url,
                '{msg}' => $msg
            );
            Notify::model()->sendNotify($lastUID, 'workflow_goback_notice', $config);
            WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $log);
            $this->ajaxReturn(array('isSuccess' => true), Mobile::dataType());
        } else {
            $this->ajaxReturn(array('isSuccess' => false), Mobile::dataType());
        }
    }

    /**
     * 固定流程转交下一步提交处理
     */
    public function actionTurnNextPost()
    {
        //-----------参数初始化-----------
        // $runId = filter_input( INPUT_POST, 'runid', FILTER_SANITIZE_NUMBER_INT );
        // $flowId = filter_input( INPUT_POST, 'flowid', FILTER_SANITIZE_NUMBER_INT );
        // $processId = filter_input( INPUT_POST, 'processid', FILTER_SANITIZE_NUMBER_INT );
        // $flowProcess = filter_input( INPUT_POST, 'flowprocess', FILTER_SANITIZE_NUMBER_INT );
        // $topflag = filter_input( INPUT_POST, 'topflag', FILTER_SANITIZE_NUMBER_INT );
        $runId = Env::getRequest('runid');
        $flowId = Env::getRequest('flowid');
        $processId = Env::getRequest('processid');
        $flowProcess = Env::getRequest('flowprocess');
        $topflag = Env::getRequest('topflag');
        $topflag = isset($topflag) ? $topflag : null;
        $this->nextAccessCheck($topflag, $runId, $processId);
        //----------  执行流程插件 ----------------------
        $plugin = FlowProcess::model()->fetchTurnPlugin($flowId, $flowProcess);
        if ($plugin) {
            $pluginFile = './system/modules/workflow/plugins/turn/' . $plugin;
            if (file_exists($pluginFile)) {
                include_once($pluginFile);
            }
        }
        //----------------------------------------------
        //------------------- 开始流程转交或结束的处理 ----------------------
        // $prcsTo = filter_input( INPUT_POST, 'processto', FILTER_SANITIZE_STRING );
        // $prcsChoose = filter_input( INPUT_POST, 'prcs_choose', FILTER_SANITIZE_STRING );
        $prcsTo = Env::getRequest('processto');
        // $prcsChoose = Env::getRequest( 'prcs_choose' );

        $prcsToArr = explode(",", trim($prcsTo, ','));
        // $prcsChooseArr = explode( ",", trim( $prcsChoose, ',' ) );
        $prcsChooseArr = Env::getRequest('prcs_choose');
        if (!isset($prcsChooseArr)) {
            $prcsChooseArr = array();
        }
        $prcsChoose = implode($prcsChooseArr, ",");
        //----------  执行事务提醒 ----------------------
        // $message = filter_input( INPUT_POST, 'message', FILTER_SANITIZE_STRING );
        $message = Env::getRequest('message');
        $toId = $nextId = $beginUserId = $toallId = '';
        $ext = array(
            '{url}' => Ibos::app()->urlManager->createUrl('workflow/list/index', array('op' => 'category')),
            '{message}' => $message
        );
        //下一步骤主办人
        $remind = Env::getRequest('remind');
        $prcs_user_op = Env::getRequest('prcs_user_op');
        $prcs_user = Env::getRequest('prcs_user');

        if (isset($remind[1])) {
            $nextId = '';
            if (isset($prcs_user_op)) {
                $nextId = intval($prcs_user_op);
            } else {
                foreach ($prcsChooseArr as $k => $v) {
                    $prcs_user_op_k = Env::getRequest('prcs_user_op' . $k);
                    if (isset($prcs_user_op_k)) {
                        // $nextId .= filter_input( INPUT_POST, 'prcs_user_op' . $k, FILTER_SANITIZE_STRING ) . ',';
                        $nextId .= $prcs_user_op_k . ',';
                    }
                }
                $nextId = trim($nextId, ',');
            }
        }
        //流程发起人
        if (isset($remind[2])) {
            $beginuser = FlowRunProcess::model()->fetchAllOPUid($runId, 1, true);
            if ($beginuser) {
                $beginUserId = StringUtil::wrapId($beginuser[0]['uid']);
            }
        }
        //所有经办人
        if (isset($remind['3'])) {
            $toallId = '';
            if (isset($prcs_user)) {
                $toallId = filter_input(INPUT_POST, 'prcs_user', FILTER_SANITIZE_STRING);
            } else {
                foreach ($prcsChooseArr as $k => $v) {
                    $prcs_user_k = Env::getRequest('prcs_user' . $k);
                    if ($prcs_user_k) {
                        $toallId .= filter_input(INPUT_POST, 'prcs_user' . $k, FILTER_SANITIZE_STRING);
                    }
                }
            }
        }
        $idstr = $nextId . ',' . $beginUserId . ',' . $toallId;
        $toId = StringUtil::filterStr($idstr);
        if ($toId) {
            Notify::model()->sendNotify($toId, 'workflow_turn_notice', $ext);
        }
        //-----  结束流程 -----
        if ($prcsChoose == "") {
            $prcsUserOp = isset($prcs_user_op) ? intval($prcs_user_op) : ''; //主办人
            $prcsUser = isset($prcs_user) ? $prcs_user : ''; //经办人
            $run = FlowRun::model()->fetchByPk($runId);
            if ($run) {
                $pId = $run['parentrun'];
                $runName = $run["name"];
            }
            //更新当前步骤状态为办结
            FlowRunProcess::model()->updateAll(array('flag' => FlowConst::PRCS_DONE), sprintf("runid = %d AND processid = %d AND flowprocess = %d", $runId, $processId, $flowProcess));
            // 更新所有状态为“转交下一步”的步骤的状态为已办结,用来解决并发流程中，第一个转交并发步骤的运行实例状态总是为转交下一步的情况 @banyan
            FlowRunProcess::model()->updateAll(array('flag' => FlowConst::PRCS_DONE), sprintf("runid = %d AND flag = 3", $runId));
            //更新当前步骤办结时间
            FlowRunProcess::model()->updateAll(array('delivertime' => TIMESTAMP), sprintf("runid = %d AND processid = %d AND flowprocess = %d AND uid = %d", $runId, $processId, $flowProcess, $this->uid));
            //判断是否唯一执行中步骤,如果不是，则强制结束该工作
            $isUnique = FlowRunProcess::model()->getIsUnique($runId);
            if (!$isUnique) {
                FlowRun::model()->modify($runId, array('endtime' => TIMESTAMP));
            }
            // 子流程结束后跳转回父流程处理
            if ($pId != 0) {
                $parentflowId = FlowRun::model()->fetchFlowIdByRunId($pId); //子流程id
                $parentFormId = FlowType::model()->fetchFormIdByFlowId($parentflowId); //子流程表单id
                $parentPrcs = FlowRunProcess::model()->fetchIDByChild($pId, $runId); //子流程步骤id
                if ($parentPrcs) {
                    $parentPrcsId = $parentPrcs['processid'];
                    $parentFlowProcess = $parentPrcs['flowprocess'];
                }
                $parentProcess = FlowProcess::model()->fetchProcess($parentflowId, $parentPrcsId);
                //更新拷贝表单字段到父流程
                if ($parentProcess['relationout'] !== '') {
                    $relationArr = explode(',', trim($parentProcess['relationout'], ','));
                    $src = $des = $set = array();
                    foreach ($relationArr as $field) {
                        $src[] = substr($field, 0, strpos($field, "=>"));
                        $des[] = substr($field, strpos($field, "=>") + strlen("=>"));
                    }
                    $runData = WfHandleUtil::getRunData($runId);
                    $form = new ICFlowForm($parentFormId);
                    $structure = $form->parser->structure;
                    foreach ($structure as $k => $v) {
                        if ($v['data-type'] !== 'label' && in_array($v['data-title'], $des)) {
                            $i = array_search($v['data-title'], $des);
                            $ptitle = $src[$i];
                            $itemData = $runData[$ptitle];
                            if (is_array($itemData) && $v['data-type'] == "listview") {
                                $itemDataStr = "";
                                $newDataStr = "";
                                $j = 1;
                                for (; $j < count($itemData); ++$j) {
                                    foreach ($itemData[$i] as $val) {
                                        $newDataStr .= $val . "`";
                                    }
                                    $itemDataStr .= $newDataStr . "\r\n";
                                    $newDataStr = "";
                                }
                                $itemData = $itemDataStr;
                            }
                            $field = "data_" . $v['itemid'];
                            $set[$field] = $itemData;
                        }
                    }
                    if (!empty($set)) {
                        FlowDataN::model()->update($parentflowId, $pId, $set);
                    }
                }
                //更新父流程节点为办结
                WfHandleUtil::updateParentOver($runId, $pId);
                //返回父流程节点，设置了返回步骤才返回
                $prcsBack = Env::getRequest('prcsback') . '';
                if ($prcsBack != "") {
                    $parentPrcsIdNew = $parentPrcsId + 1;
                    $data = array(
                        'runid' => $pId,
                        'processid' => $parentPrcsIdNew,
                        'uid' => $prcsUserOp,
                        'flag' => '1',
                        'flowprocess' => $prcsBack,
                        'opflag' => 1,
                        'topflag' => 0,
                        'parent' => $parentFlowProcess,
                    );
                    FlowRunProcess::model()->add($data);
                    foreach (explode(",", trim($prcsUser, ',')) as $k => $v) {
                        if ($v != $prcsUserOp && !empty($v)) {
                            $data = array(
                                'runid' => $pId,
                                'processid' => $parentPrcsIdNew,
                                'uid' => $v,
                                'flag' => '1',
                                'flowprocess' => $prcsBack,
                                'opflag' => 0,
                                'topflag' => 0,
                                'parent' => $parentFlowProcess,
                            );
                            FlowRunProcess::model()->add($data);
                        }
                    }
                    //工作流日志 - 返回父流程
                    $parentRunName = FlowRun::model()->fetchNameByRunId($pId);
                    $content = "[{$runName}]" . Ibos::lang('Log return the parent process') . ":[{$parentRunName}]";
                    WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
                    FlowRun::model()->modify($pId, array('endtime' => null));
                }
            }
            //工作流日志
            $content = Ibos::lang('Form endflow');
            WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
        } else {
            //---------------------是否允许按转交规则转交-----------------------
            $freeother = FlowType::model()->fetchFreeOtherByFlowId($flowId);
            $prcsChooseArrCount = count($prcsChooseArr);
            for ($i = 0; $i < $prcsChooseArrCount; $i++) {
                $flowPrcsNext = $prcsToArr[$prcsChooseArr[$i]]; //下一步骤序号
                $prcsIdNew = $processId + 1; //下一步骤运行编号
                $str = "prcs_user_op" . $prcsChooseArr[$i];
                $prcsUserOp = Env::getRequest($str); //传过来的是直接的用户ID
                //手机端判断转交主办人
                // if ( empty( $prcsUserOp ) ) {
                //     $this->ajaxReturn( array( "isSuccess" => false, "msg" => "必须选择主办人" ), Mobile::dataType() );
                //     exit();
                // }

                if ($freeother == 2) {
                    $prcsUserOp = WfHandleUtil::turnOther($prcsUserOp, $flowId, $runId, $processId, $flowProcess);
                }
                $str = "prcs_user" . $prcsChooseArr[$i];
                $prcsUser = explode(',', Env::getRequest($str));
                array_push($prcsUser, $prcsUserOp); //把主办人添加到经办人中，省去前台判断
                $prcsUser = implode(',', array_unique($prcsUser));
                if ($freeother == 2) {
                    $prcsUser = WfHandleUtil::turnOther($prcsUser, $flowId, $runId, $processId, $flowProcess, $prcsUserOp);
                }

                $str = "topflag" . $prcsChooseArr[$i];
                $topflag = intval(Env::getRequest($str));
                //-- 处理合并规则 --
                //强制合并节点与子流程
                $fp = FlowProcess::model()->fetchProcess($flowId, $flowPrcsNext);
                //非子流程
                if ($fp['childflow'] == 0) {
                    //如果检测到曾经按先到先得转交或者无主办人会签，则此次转交也按先到先得规则
                    $_topflag = FlowRunProcess::model()->fetchTopflag($runId, $prcsIdNew, $flowPrcsNext);
                    if ($_topflag) {
                        $topflag = $_topflag;
                    }
                    //如果检测到有主办人正在办理中，则此次转交设定的主办人作废，主办人依据现存的
                    $isOpHandle = FlowRunProcess::model()->getIsOpOnTurn($runId, $prcsIdNew, $flowPrcsNext);
                    if ($isOpHandle) {
                        $prcsUserOp = "";
                        $t_flag = 1;
                    } else {
                        $t_flag = 0;
                    }
                    foreach (explode(',', trim($prcsUser)) as $k => $v) {
                        if ($v == $prcsUserOp || $topflag == 1) {
                            $opflag = 1;
                        } else {
                            $opflag = 0;
                        }
                        //无主办人会签
                        if ($topflag == 2) {
                            $opflag = 0;
                        }
                        //-- 如果检测到同名用户正在办理，则不再转发给他，但如果曾经办理过，则会再次转发给他 --
                        $workedId = FlowRunProcess::model()->fetchProcessIDOnTurn($runId, $prcsIdNew, $flowPrcsNext, $v, $fp['gathernode']);
                        if (!$workedId) {
                            $wrp = FlowRunProcess::model()->fetchRunProcess($runId, $processId, $flowProcess, $this->uid);
                            if ($wrp) {
                                $otherUser = $wrp['otheruser'] != '' ? $wrp['otheruser'] : '';
                            } else {
                                $otherUser = '';
                            }
                            $data = array(
                                'runid' => $runId,
                                'processid' => $prcsIdNew,
                                'uid' => $v,
                                'flag' => 1,
                                'flowprocess' => $flowPrcsNext,
                                'opflag' => $opflag,
                                'topflag' => $topflag,
                                'parent' => $flowProcess,
                                'createtime' => TIMESTAMP,
                                'otheruser' => $otherUser
                            );
                            FlowRunProcess::model()->add($data);
                        } else {
                            if ($prcsIdNew < $workedId) {
                                $prcsIdNew = $workedId;
                            }
                            $lastPrcsId = $workedId;
                            FlowRunProcess::model()->updateTurn($flowProcess, $prcsIdNew, $runId, $lastPrcsId, $flowPrcsNext, $v);
                        }
                    }
                    //主办人依照原来，则不能收回
                    if ($t_flag == 1) {
                        FlowRunProcess::model()->updateToOver($runId, $processId, $flowProcess);
                    } else {
                        FlowRunProcess::model()->updateToTrans($runId, $processId, $flowProcess);
                    }
                    //工作流日志
                    $userNameStr = User::model()->fetchRealnamesByUids($prcsUser);
                    $content = Ibos::lang('To the steps') . $prcsIdNew . "," . Ibos::lang('Transactor') . ":" . $userNameStr;
                    WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
                } else {
                    //新建子流程
                    $runidNew = WfNewUtil::createNewRun($fp['childflow'], $prcsUserOp, $prcsUser, $runId);
                    $data = array(
                        'runid' => $runId,
                        'processid' => $prcsIdNew,
                        'uid' => $prcsUserOp,
                        'flag' => 1,
                        'flowprocess' => $flowPrcsNext,
                        'opflag' => 1,
                        'topflag' => 0,
                        'parent' => $flowProcess,
                        'childrun' => $runidNew,
                        'createtime' => TIMESTAMP
                    );
                    FlowRunProcess::model()->add($data);
                    //直接更新状态为办结
                    FlowRunProcess::model()->updateToOver($runId, $processId, $flowProcess);
                    //工作流日志
                    $content = Ibos::lang('Log new subflow') . $runidNew;
                    WfCommonUtil::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
                }
            }//for
        } // end else
        //------------------- 流程监控的硬性转交，要模拟接收办理过程------手机端不需要 -----------------
        // if ( $op == "manage" ) {
        // 	$parent = Ibos::app()->db->createCommand()
        // 			->select( 'parent' )
        // 			->from( '{{flow_run_process}}' )
        // 			->where( sprintf( "runid = %d AND processid = %d AND flowprocess = %d", $runId, $processId, $flowProcess ) )
        // 			->queryScalar();
        // 	$prcsIdpre = $processId - 1;
        // 	$sql = "UPDATE {{flow_run_process}} SET flag='4' WHERE runid='{$runId}' AND processid='{$prcsIdpre}'";
        // 	if ( $parent && $parent != "0" ) {
        // 		$sql.=" AND flowprocess IN ('$parent')";
        // 	}
        // 	Ibos::app()->db->createCommand()->setText( $sql )->execute();
        // }
        // MainUtil::setCookie( 'flow_turn_flag', 1, 30 );
        // $url = Ibos::app()->urlManager->createUrl( 'workflow/list/index', array( 'op' => 'list', 'type' => 'trans', 'sort' => 'all' ) );
        // $this->redirect( $url );
        //此时检测当前结束的流程是否是最后一个流程，如果是，那么增加一个结束的记录
        $lastRecord = FlowRunProcess::model()->findLastProcessRecord($runId);
        // 最后流程的标识符
        $lastProcessFlag = false;
        if ($lastRecord['flag'] == '4') {
            $lastProcess = FlowProcess::model()->findLastProcess($flowId, $lastRecord['flowprocess']);
            if (!empty($lastProcess)) {
                //如果设计的最后一个步骤和结束的最后一个步骤相等，则表示结束了
                $lastProcessFlag = true;
                Ibos::app()->db->createCommand()
                    ->insert('{{flow_run_process}}', array(
                        'runid' => $runId,
                        'processid' => $processId + 1,
                        'uid' => $this->uid,
                        'processtime' => TIMESTAMP,
                        'delivertime' => TIMESTAMP + 1,
                        'flag' => 4,
                        'flowprocess' => 0, //结束的特殊表示为0
                        'freeitem' => '',
                        'otheruser' => '',
                        'comment' => '',
                        'opflag' => 1,
                        'topflag' => 0,
                        'parent' => $lastRecord['flowprocess'],
                        'createtime' => TIMESTAMP,
                ));
                //如果已经结束，强制结束所有的步骤
                Ibos::app()->db->createCommand()
                    ->update('{{flow_run_process}}', array('flag' => 4), "runid = '{$runId}' ");
            }
        }
        $this->ajaxReturn(array("isSuccess" => true), Mobile::dataType());
    }

// -------------------来自listController结束-----------------------------
// -------------------来自handleController-----------------------------

    /**
     * 检查下一步的权限
     * @param type $topflag
     * @param type $runID
     * @param type $processID
     */
    protected function nextAccessCheck($topflag, $runId, $processId)
    {
        $per = WfCommonUtil::getRunPermission($runId, $this->uid, $processId);
        if ($topflag != 2) {
            // 如果不是系统管理员，主办人，管理与监控人，退出
            if (!StringUtil::findIn($per, 1) && !StringUtil::findIn($per, 2) && !StringUtil::findIn($per, 3)) {
                Env::iExit('必须是系统管理员，主办人，管理或监控人才能进行操作');
            }
        } else {
            //如果不是经办人
            if (!StringUtil::findIn($per, 4)) {
                Env::iExit('您不是经办人，没有权限进行操作。');
            }
        }
    }

    /**
     * 转交显示下一步
     */
    public function actionShowNext()
    {
        $key = Env::getRequest('key');
        if ($key) {
            $param = WfCommonUtil::param($key, 'DECODE');
            $flowId = $param['flowid'];
            $runId = $param['runid'];
            $processId = $param['processid'];
            $flowProcess = $param['flowprocess'];
            $op = isset($param['op']) ? $param['op'] : '';
            $topflag = Env::getRequest('topflag');
            $lang = Ibos::getLangSources();
            $this->nextAccessCheck($topflag, $runId, $processId);
            $run = new ICFlowRun($runId);
            $process = new ICFlowProcess($flowId, $flowProcess);
            $notAllFinished = array();
            $done = FlowConst::PRCS_DONE;
            $canCombile = true;
            $flag = Ibos::app()->db->createCommand()
                ->select('flag')
                ->from(FlowRunProcess::model()->tableName())
                ->where(" `runid` = '{$runId}' ")
                ->andWhere(" `processid` = '{$processId}' ")
                ->andWhere(" `flowprocess` = '{$flowProcess}' ")
                ->andWhere(" `uid` = '{$this->uid}' ")
                ->queryScalar();
            if (in_array($flag, array(FlowConst::PRCS_DONE, FlowConst::PRCS_TRANS))) {
                // 自己已经转交并且是当前步骤的话不能重复转交
                Env::iExit(Ibos::lang('Already trans'));
            }
            $flowProcessArray = array($flowProcess);
            $all = $flowProcessArray;
            $find = array();
            while (1) {
                foreach ($flowProcessArray as $flowProcessid) {
                    $fromFlowProcess = Ibos::app()->db->createCommand()
                        ->select('processid')
                        ->from(FlowProcess::model()->tableName())
                        ->where(" `flowid` = '{$flowId}' ")
                        ->andWhere(" FIND_IN_SET( '{$flowProcessid}', `processto` ) ")
                        ->queryColumn();
                    foreach ($fromFlowProcess as $row) {
                        $list2 = Ibos::app()->db->createCommand()
                            ->select('uid,flowprocess')
                            ->from(FlowRunProcess::model()->tableName())
                            ->where(" `runid` = '{$runId}' ")
                            ->andWhere(" FIND_IN_SET( `flowprocess`, '{$row}' ) ")
                            ->andWhere(" `flag` != '{$done}' ")
                            ->queryAll();
                        foreach ($list2 as $row2) {
                            $notAllFinished[] = $row2['uid'];
                            $find[] = $row2['flowprocess'];
                            $canCombile = false;
                        }
                    }
                }
                $diff = array_diff($find, $all);
                if (empty($diff)) {
                    break;
                } else {
                    $flowProcessArray = array_unique($diff);
                    $all = array_unique(array_merge($all, $find));
                }
            }
            if (!empty($notAllFinished)) {
                $notAllFinished = User::model()->fetchRealnamesbyUids($notAllFinished);
            } else {
                $notAllFinished = '';
            }
            // 检查强制合并
            if ($process->gathernode == 1) {
                if (false === $canCombile) {
                    //此步骤为强制合并步骤，尚有步骤未转交至此步骤，不能继续转交下一步
                    Env::iExit(Ibos::lang('Gathernode trans error'));
                }
            }
            // $processNext = $processid + 1;
            //未定义下一步骤,自动判断
            if ($process->processto == "") {
                $prcsMax = FlowProcess::model()->fetchMaxProcessIDByFlowID($flowId);
                if ($flowProcess !== $prcsMax) {
                    $process->processto = $flowProcess + 1;
                } else {
                    $process->processto = '0';
                }
            }
            $prcsArr = explode(',', trim($process->processto, ',')); // 有无多个步骤
            $prcsArrCount = count($prcsArr);
            $prcsEnableCount = 0;
            $prcsStop = "S";
            $prcsback = '';
            $prcsEnableFirst = null;
            $list = array();
            // 获取表单值
            $formData = WfHandleUtil::getFormData($flowId, $runId);
            foreach ($prcsArr as $key => $to) {
                $param = array(
                    'checked' => false
                );
                // 结束流程 走你~
                if ($to == '0') {
                    $param['isover'] = true;
                    // 步骤名称显示结束流程还是结束子流程
                    $param['prcsname'] = $run->parentrun !== '0' ? Ibos::lang('End subflow') : Ibos::lang('Form endflow');
                    $prcsStop = $key;
                    $prcsEnableCount++;
                    if ($prcsEnableCount == 1) {
                        $param['checked'] = true;
                        $prcsEnableFirst = $key;
                    }
                    // 如果是子流程，查询回退流程信息
                    if ($run->parentrun !== '0') {
                        $parentFlowId = FlowRun::model()->fetchFlowIdByRunId($run->parentrun);
                        $parentProcess = FlowRunProcess::model()->fetchIDByChild($run->parentrun, $runId);
                        $parentFlowProcess = $parentProcess['flowprocess'];
                        if ($parentFlowId && $parentFlowProcess) {
                            $temp = FlowProcess::model()->fetchProcess($parentFlowId, $parentFlowProcess);
                            if ($temp) {
                                $prcsback = $temp['processto'];
                                $backUserOP = $temp['autouserop'];
                                $param['backuser'] = $temp['autouser'];
                            }
                        }
                        if ($prcsback != '') {
                            $param['prcsEnabledUsers'] = WfHandleUtil::getPrcsUser($flowId, $prcsback);
                            $param['display'] = $prcsEnableFirst !== $prcsStop ? false : true;
                            if (isset($backUserOP)) {
                                $param['prcsopuser'] = $backUserOP;
                            }
                        }
                    }
                } else {
                    $param['isover'] = false;
                    $curProcess = FlowProcess::model()->fetchProcess($flowId, $to);
                    $param['prcsname'] = $curProcess['name'];
                    $processOut = FlowProcessTurn::model()->fetchByUnique($flowId, $flowProcess, $to);
                    if (!$processOut) {
                        $processOut = array('processout' => '', 'conditiondesc' => '');
                    }
                    //检查转入条件
                    $notpass = WfHandleUtil::checkCondition($formData, $processOut['processout'], $processOut['conditiondesc']);
                    if ($curProcess['childflow'] !== '0') {
                        $param['prcsname'] .= "(" . $lang['Subflow'] . ")";
                    }
                    if (substr($notpass, 0, 5) == 'setok') {
                        $notpass = "";
                    }
                    if ($notpass == "") {//符合条件的
                        $prcsEnableCount++;
                        if ($prcsEnableCount == 1 || ($process->syncdeal > 0 && !is_numeric($prcsStop))) {
                            $param['checked'] = true;
                            if ($prcsEnableCount == 1) {
                                $prcsEnableFirst = $key;
                            }
                        }
                        unset($param['notpass']);
                        //获取默认选择人员数组
                        $param['process'] = $curProcess;
                        $userSelect = $this->makeUserSelect($runId, $key, $curProcess, $param['prcsname'], $flowId, $processId);
                        $param = array_merge($param, $userSelect);
                    } else {
                        $param['notpass'] = $notpass;
                    }
                }
                $list[$key] = $param;
            }

            //----------------- 异常处理 ----------------------
            if ($prcsEnableCount == 0) {
                if ($notpass == "") {
                    Env::iExit($lang['Process define error']);
                } else {
                    Env::iExit($notpass);
                }
            }
            $data = array(
                'lang' => $lang,
                'notAllFinished' => $notAllFinished,
                'enableCount' => $prcsEnableCount,
                'prcsto' => $prcsArr,
                'prcsback' => $prcsback,
                'notpass' => isset($notpass) ? $notpass : '',
                'process' => $process->toArray(),
                'run' => $run->toArray(),
                'runid' => $runId,
                'flowid' => $flowId,
                'processid' => $processId,
                'flowprocess' => $flowProcess,
                'count' => $prcsArrCount,
                'prcsStop' => $prcsStop,
                'topflag' => $topflag,
                'list' => $list,
                'op' => $op
            );
            $this->ajaxReturn($data, Mobile::dataType());
        }
    }

    /**
     * 生成转交下一步经办用户选择框组件
     * @param type $runID
     * @param type $index
     * @param string $process
     * @param type $name
     * @param type $flowID
     * @param type $processID
     * @return type
     */
    protected function makeUserSelect($runId, $index, $process, $name, $flowId, $processId)
    {
        $lang = Ibos::getLangSource('workflow.default');
        $nopriv = '';

        //子流程
        if ($process['childflow'] != 0) {
            $flow = FlowType::model()->fetchByPk($process['childflow']);
            if ($flow) {
                $type = $flow['type'];
            }
            if ($type == FlowType::FLOW_TYPE_FREE) { //自由流程
                $process['prcs_id_next'] = '';
            }
            //获取子流程第一步的信息
            $subfp = FlowProcess::model()->fetchProcess($process['childflow'], 1);
            if ($subfp) {
                $prcsuser = WfHandleUtil::getPrcsUser($process['childflow'], $processId);
            } else {
                $prcsuser = '';
            }
            // $prcsuser = sprintf( '[%s]', !empty( $prcsuser ) ? StringUtil::iImplode( $prcsuser ) : ''  );
            if (empty($subfp['uid']) && empty($subfp['deptid']) && empty($subfp['positionid'])) {
                $nopriv = $lang['Not set step permissions']; //没有经办权限
            }

            $userSelect = array(
                'prcsEnabledUsers' => $prcsuser,
                'nopriv' => $nopriv
            );
        } else {
            if (empty($process['uid']) && empty($process['deptid']) && empty($process['positionid']) && empty($process['roleid'])) {
                $nopriv = $lang['Not set step permissions']; //没有经办权限
            }
            $prcsOpUser = $prcsUserAuto = '';
            $deptArr = DepartmentUtil::loadDepartment();
            //自动选择流程发起人
            if ($process['autotype'] == 1) {
                //发起人信息
                $uid = FlowRun::model()->fetchBeginUserByRunId($runId);
                $prcsuser = User::model()->fetchByUid($uid);
                //检查该发起人是否有经办权限
                if ($process['deptid'] == "alldept" ||
                    StringUtil::findIn($process['uid'], $prcsuser['uid']) ||
                    StringUtil::findIn($process['deptid'], $prcsuser['alldeptid']) ||
                    StringUtil::findIn($process['positionid'], $prcsuser['allposid'] ||
                        StringUtil::findIn($process['roleid'], $prcsuser['allroleid']))
                ) {
                    $prcsOpUser = $prcsuser['uid'];
                    $prcsUserAuto = $prcsuser['uid'] . ",";
                }
            } //自动选择本部门主管(2) or 上级主管领导(4) or 上级分管领导(6) or 一级部门主管(5)
            elseif (in_array($process['autotype'], array(2, 4, 5, 6))) {
                if ($process['autobaseuser'] != 0) {  //部门针对对象,0为当前步骤
                    // 基准对象
                    $baseUid = FlowRunProcess::model()->fetchBaseUid($runId, $process['autobaseuser']);
                    $baseuser = User::model()->fetchByUid($baseUid);
                    $autodept = $baseuser['deptid'];
                } else {
                    $autodept = Ibos::app()->user->deptid;
                }
                if (intval($autodept) > 0) {
                    if ($process['autotype'] == 2) { //本部门id
                        $tmpdept = $autodept;
                    } elseif ($process['autotype'] == 4 || $process['autotype'] == 6) { //上级部门id
                        $tmpdept = $deptArr[$autodept]['pid'] == 0 ? $autodept : $deptArr[$autodept]['pid'];
                    } elseif ($process['autotype'] == 5) { //一级部门id
                        $deptStr = Department::model()->queryDept($autodept, true);
                        $temp = explode(',', $deptStr);
                        $count = count($temp);
                        $dept = isset($temp[$count - 2]) ? $temp[$count - 2] : $autodept;
                        if ($deptArr[$dept]['pid'] != 0) {
                            $tmpdept = $deptArr[$dept]['deptid'];
                        } else {
                            $tmpdept = $autodept;
                        }
                    }

                    $manager = $deptArr[$tmpdept]['manager']; //部门主管

                    if ($process['autotype'] == 4 || $process['autotype'] == 6) {
                        $leader = $deptArr[$autodept]['leader']; //上级主管领导
                        $subleader = $deptArr[$autodept]['subleader']; //上级分管领导
                        if ($leader != '0' && $process['autotype'] == 4) {
                            $manager = $leader;
                        }
                        if ($subleader != '0' && $process['autotype'] == 6) {
                            $manager = $subleader;
                        }
                    }

                    if (!empty($manager)) {
                        $muser = User::model()->fetchByUid($manager);
                        if (!empty($muser)) {
                            if ($process['deptid'] == "alldept" ||
                                StringUtil::findIn($process['uid'], $muser['uid']) ||
                                StringUtil::findIn($process['deptid'], $muser['alldeptid']) ||
                                StringUtil::findIn($process['positionid'], $muser['allposid'] ||
                                    StringUtil::findIn($process['roleid'], $muser['allroleid']))) {
                                $prcsUserAuto = $muser['uid'] . ",";
                            }
                            if ($prcsUserAuto != "") {
                                $prcsOpUser = strtok($prcsUserAuto, ",");
                            }
                        }
                    } else { //如果没设本部门主管
                        $userPerMax = "";
                        foreach (User::model()->fetchAllOtherManager($tmpdept) as $user) {
                            $user = User::model()->fetchByUid($user['uid']);
                            $uid = $user['uid'];
                            if ($process['deptid'] == "alldept" ||
                                StringUtil::findIn($process['uid'], $uid) ||
                                StringUtil::findIn($process['deptid'], $user['alldeptid']) ||
                                StringUtil::findIn($process['positionid'], $user['allposid'] ||
                                    StringUtil::findIn($process['roleid'], $user['allroleid']))
                            ) {
                                if ($userPerMax == "") {
                                    $prcsOpUser = $uid;
                                    $prcsUserAuto .= $uid . ",";
                                    $userPerMax = $user['allposid'];
                                } elseif ($user['allposid'] == $userPerMax) {
                                    $prcsUserAuto .= $uid . ",";
                                }
                            }
                        }
                    }
                }
            } elseif ($process['autotype'] == 3) { //指定自动选择默认人员
                //默认主办人
                $autouserop = User::model()->fetchByUid($process['autouserop']);
                if (!empty($autouserop)) {
                    if ($process['deptid'] == "alldept" ||
                        StringUtil::findIn($process['uid'], $autouserop['uid']) ||
                        StringUtil::findIn($process['deptid'], $autouserop['alldeptid']) ||
                        StringUtil::findIn($process['positionid'], $autouserop['allposid']) ||
                        StringUtil::findIn($process['roleid'], $autouserop['allroleid'])) {
                        $prcsOpUser = $autouserop['uid'];
                    }
                }
                //默认经办人
                if (!empty($process['autouser'])) {
                    foreach (User::model()->fetchAllByUids(explode(',', trim($process['autouser'], ','))) as $user) {
                        if ($process['deptid'] == "alldept" ||
                            StringUtil::findIn($process['uid'], $user['uid']) ||
                            StringUtil::findIn($process['deptid'], $user['alldeptid']) ||
                            StringUtil::findIn($process['positionid'], $user['allposid']) ||
                            StringUtil::findIn($process['roleid'], $user['allroleid'])) {
                            $prcsUserAuto .= $user['uid'] . ',';
                        }
                    }
                }
            } elseif ($process['autotype'] == 7) { //从表单选择
                if (is_numeric($process['autouser'])) {
                    $itemData = FlowDataN::model()->fetchItem($process['autouser'], $process['flowid'], $runId);
                    $tmp = strtok($itemData, ",");
                    $userarr = array();
                    while ($tmp) {
                        $userarr[$tmp] = array();
                        $tmp = strtok(",");
                    }
                    $tempArray = explode(',', trim($itemData, ','));
                    //把uid转为realname，迎合接下来的查询语句。因为输入的可能是输入的姓名
                    foreach ($tempArray as $key => $value) {
                        if (is_numeric($value)) {
                            // 手机端用户选择框直接使用用户ID，和网页端处理U_不同
                            $value = User::model()->fetchRealnameByUid($value, '');
                            $tempArray[$key] = $value;
                        }
                    }
                    $uidArray = User::model()->findUidByRealnameX($tempArray);
                    $temp = array();
                    foreach ($uidArray as $u) {
                        $temp[] = array(
                            'uid' => $u,
                            'alldeptid' => User::model()->findAllDeptidByUid($u),
                            'allposid' => User::model()->findAllPositionidByUid($u),
                            'allroleid' => User::model()->findAllRoleidByUid($u),
                        );
                    }

                    foreach ($temp as $k => $v) {
                        $dept = Department::model()->queryDept($v['alldeptid']);
                        if ($process['deptid'] == "alldept" ||
                            StringUtil::findIn($process['uid'], $v['uid']) ||
                            StringUtil::findIn($process['deptid'], $dept) ||
                            StringUtil::findIn($process['positionid'], $v["allposid"]) ||
                            StringUtil::findIn($process['roleid'], $v['allroleid'])
                        ) {
                            $prcsUserAuto .= $v["uid"] . ",";
                        }
                    }
                    if ($prcsUserAuto != "") {
                        $prcsOpUser = strtok($prcsUserAuto, ",");
                    }
                }
            } elseif ($process['autotype'] == 8 && is_numeric($process['autouser'])) { //自动选择指定步骤主办人
                $uid = FlowRunProcess::model()->fetchBaseUid($runId, $process['autouser']);
                if ($uid) {
                    $temp = array(
                        'uid' => $uid,
                        'alldeptid' => User::model()->findAllDeptidByUid($uid),
                        'allposid' => User::model()->findAllPositionidByUid($uid),
                        'allroleid' => User::model()->findAllRoleidByUid($uid),
                    );
                    if ($temp) {
                        if ($process['deptid'] == 'alldept' ||
                            StringUtil::findIn($process['uid'], $temp['uid']) ||
                            StringUtil::findIn($process['deptid'], $temp["alldeptid"]) ||
                            StringUtil::findIn($process['positionid'], $temp["allposid"]) ||
                            StringUtil::findIn($process['roleid'], $temp['allroleid'])) {
                            $prcsOpUser = $prcsUserAuto = $temp['uid'];
                            $prcsUserAuto .= ",";
                        }
                    }
                }
            } elseif ($process['autotype'] == 9) { //自动选择本部门内符合条件所有人员
                $main = Ibos::app()->user->deptid;
                foreach (User::model()->fetchAllFitDeptUser($main) as $k => $v) {
                    if ($process['deptid'] == 'alldept' ||
                        StringUtil::findIn($process['uid'], $v['uid']) ||
                        StringUtil::findIn($process['deptid'], $v['alldeptid']) ||
                        StringUtil::findIn($process['positionid'], $v['allposid']) ||
                        StringUtil::findIn($process['roleid'], $v['allroleid'])) {
                        $prcsUserAuto .= $v['uid'] . ",";
                    }
                }
                if (!empty($prcsUserAuto)) {
                    $prcsOpUser = strtok($prcsUserAuto, ',');
                }
            } elseif ($process['autotype'] == 10) { //自动选择本一级部门内符合条件所有人员
                $main = Ibos::app()->user->deptid;
                $deptStr = Department::model()->queryDept($main, true);
                $temp = explode(',', $deptStr);
                $count = count($temp);
                $dept = isset($temp[$count - 2]) ? $temp[$count - 2] : $main;
                if ($deptArr[$dept]['pid'] != 0) {
                    $tmpdept = $deptArr[$dept]['deptid'];
                } else {
                    $tmpdept = $main;
                }
                foreach (User::model()->fetchAllFitDeptUser($tmpdept) as $k => $v) {
                    if ($process['deptid'] == "alldept" ||
                        StringUtil::findIn($process['uid'], $v['uid']) ||
                        StringUtil::findIn($process['deptid'], $v["alldeptid"]) ||
                        StringUtil::findIn($process['positionid'], $v["allposid"]) ||
                        StringUtil::findIn($process['roleid'], $v['allroleid'])) {
                        $prcsUserAuto .= $v['uid'] . ",";
                    }
                }
                if (!empty($prcsUserAuto)) {
                    $prcsOpUser = strtok($prcsUserAuto, ',');
                }
            } elseif ($process['uid'] != "" && $process['deptid'] == "" && $process['positionid'] == "") {//非自动选择时，如只有一个经办人
                $prcsUserArr = explode(",", $process['uid']);
                $prcsUserCount = count($prcsUserArr) - 1;
                if ($prcsUserCount == 1) {
                    $prcsUserAuto = $process['uid'];
                    $prcsOpUser = $prcsUserAuto;
                }
            }
            $prcsuser = WfHandleUtil::getPrcsUser($flowId, $process['processid']);
            // $prcsuser = sprintf( '[%s]', !empty( $prcsuser ) ? StringUtil::iImplode( $prcsuser ) : ''  );

            $userSelect = array(
                'topdefault' => $process['topdefault'],
                'topmodify' => $process['userlock'],
                'prcsOpUser' => $prcsOpUser,
                'prcsUser' => $prcsUserAuto,
                'prcsEnabledUsers' => $prcsuser,
                'nopriv' => $nopriv
            );
        }
        return $userSelect;
    }

    /**
     * 自由流程下一步流程或视图
     */
    public function actionFreeNext()
    {
        $key = Env::getRequest('key');
        $op = Env::getRequest('op');
        $topflag = Env::getRequest('topflag');
        $widget = $this->createWidget('application\modules\mobile\utils\FreeNext', array('key' => $key, 'op' => $op, 'topflag' => $topflag));
        if (Ibos::app()->request->getIsPostRequest()) {
            $topflag = Env::getRequest('topflagOld');
        }
        $this->nextAccessCheck($topflag, $widget->getKey('runid'), $widget->getKey('processid'));
        if (Ibos::app()->request->getIsPostRequest()) {
            $widget->nextPost();
        } else {
            $widget->run();
        }
    }

    /**
     * 经办人办理完毕
     */
    public function actionComplete()
    {
        $key = Env::getRequest('key');
        if ($key) {
            $param = Common::param($key, 'DECODE');
            $processId = $param['processid'];
            $flowProcess = $param['flowprocess'];
            $runId = $param['runid'];
            $topflag = Env::getRequest('topflag');
            $opflag = Env::getRequest('opflag');
            $inajax = Env::getRequest('inajax');
            $op = Env::getRequest('op');
            $this->complete($runId, $processId, $opflag, $topflag, $inajax, $flowProcess, $op);
        }
    }

// -------------------来自handleController结束-----------------------------

    /**
     * 经办人办理完毕操作
     * @param integer $runId
     * @param integer $processId
     * @param integer $opflag
     * @param integer $topflag
     * @param type $inajax
     * @param integer $flowProcess
     * @param string $op
     */
    protected function complete($runId, $processId, $opflag = 1, $topflag = 0, $inajax = 0, $flowProcess = '', $op = '')
    {
        $flowType = FlowRun::model()->fetchFlowTypeByRunId($runId);
        //----------- 自由流程主办人结束流程 或监控人结束流程 -----------
        if ($opflag || $op == 'manage') {
            //--- 检查有无后续预设步骤 ---
            $pidNext = $processId + 1;
            //-- 如果有 --
            // 存在后续预设步骤，不能结束
            if (FlowRunProcess::model()->getHasDefaultStep($runId, $pidNext)) {
                // 如果不是在管理模式进入，提示并退出函数
                if ($op != "manage") {
                    if ($inajax) {
                        $this->ajaxReturn(
                            array(
                                'isSuccess' => false,
                                'msg' => Ibos::lang('Subsequent default steps in the process')
                            )
                        );
                    } else {
                        $this->error(Ibos::lang('Subsequent default steps in the process'), $this->createUrl('list/index'));
                    }
                } else {
                    //删除后续步骤
                    FlowRunProcess::model()->deleteByIDScope($runId, $pidNext);
                }
            }
            //--- 写自己的办理完毕状态 ---
            if ($op != 'manage') {
                FlowRunProcess::model()->updateAll(array('delivertime' => TIMESTAMP), sprintf("runid = %d AND processid = %d AND uid = %d", $runId, $processId, $this->uid));
            } else {
                //-- 监控人结束补全步骤开始和结束时间为当前时间 --
                FlowRunProcess::model()->updateAll(array('delivertime' => TIMESTAMP), sprintf("runid = %d AND delivertime = 0", $runId));
                FlowRunProcess::model()->updateAll(array('processtime' => TIMESTAMP), sprintf("runid = %d AND processtime = 0", $runId));
            }
            //--- 结束本流程 ---
            FlowRunProcess::model()->updateAll(array('flag' => FlowConst::PRCS_DONE), "runid = {$runId}");
            //--- 写入结束时间 ---
            FlowRun::model()->modify($runId, array('endtime' => TIMESTAMP));
            //--- 流程日志 ---
            $content = $op != 'manage' ? Ibos::lang('Form endflow') : Ibos::app()->user->realname . Ibos::lang('Forced end process');
            Common::runlog($runId, $processId, $flowProcess, $this->uid, 1, $content);
            //--- 子流程返回父流程 ---
            $parentRun = FlowRun::model()->fetchParentByRunId($runId);
            if ($parentRun != 0) {
                $parentFlowId = FlowRun::model()->fetchFlowIdByRunId($parentRun);
                $temp = FlowRunProcess::model()->fetchIDByChild($parentRun, $runId);
                if ($temp) {
                    $parentProcessId = $temp['processid'];
                    $parentFlowprocess = $temp['flowprocess'];
                }
                $parentProcess = FlowProcess::model()->fetchProcess($parentFlowId, $parentFlowprocess);
                if ($parentProcess) {
                    $prcsBack = $parentProcess["processto"];
                    $backUserOp = $parentProcess["autouserop"];
                    $backUser = $parentProcess["autouser"];
                }
                //更新父流程节点为办结
                FlowRunProcess::model()->updateToOver($parentRun, $parentProcessId, $parentFlowprocess);
                //--- 创建父流程返回步骤 ---
                if ($prcsBack != "") {
                    $parentProcessIdNew = $parentProcessId + 1;
                    $data = array(
                        'runid' => $parentRun,
                        'processid' => $parentProcessIdNew,
                        'uid' => $backUserOp,
                        'flag' => 1,
                        'flowprocess' => $prcsBack,
                        'opflag' => 1,
                        'topflag' => 0,
                        'parent' => $parentFlowprocess
                    );
                    FlowRunProcess::model()->add($data);
                    $backUserArr = explode(",", $backUser);
                    for ($k = 0; $k < count($backUserArr); $k++) {
                        if ($backUserArr[$k] != '' && $backUserArr[$k] != $backUserOp) {
                            $data = array(
                                'runid' => $parentRun,
                                'processid' => $parentProcessIdNew,
                                'uid' => $backUserArr[$k],
                                'flag' => 1,
                                'flowprocess' => $prcsBack,
                                'opflag' => 0,
                                'topflag' => 0,
                                'parent' => $parentFlowprocess
                            );
                            FlowRunProcess::model()->add($data);
                        }
                    }
                } else {  //父流程若所有节点均已办结 则更新流程结束时间字段
                    if (!FlowRunProcess::model()->getIsNotOver($parentRun)) {
                        FlowRun::model()->modify($parentRun, array('endtime' => TIMESTAMP));
                    }
                }
            }
            $datas = array(
                // 'runid' => $parentRun,
                // 'temp' => $temp,
                // 'processid' => $parentProcessIdNew,
                // 'flowprocess' => $prcsBack,
                // 'topflag' => $topflag,
                // 'opflag' => $opflag,
                // 'inajax' => $inajax,
                // 'op' => $op,
                // 'uid' => $backUserOp
            );
            $flag = Env::getRequest('flag');
            if ($flowType == 2 && $flag != 1) {
                $inajax && $this->ajaxReturn(array('isSuccess' => true, 'data' => $datas));
//                $this->redirect( $this->createUrl( 'list/index' ) );
            }
            $inajax && $this->ajaxReturn(array('isSuccess' => true, 'data' => $datas));
        } else {
            //----------- 从办人点击(办理完毕)按钮 -----------
            $flowId = FlowRun::model()->fetchFlowIDByRunID($runId);
            //无主办人会签，先检查是否最后一个经办人
            if ($topflag == 2) {
                if (!(FlowRunProcess::model()->getHasOtherOPUser($runId, $processId, $flowProcess, $this->uid))) {
                    if (is_null($flowProcess) || $flowProcess == "0") {
                        $turnpage = 'showNextFree';
                    } else {
                        $turnpage = 'showNext';
                    }
                    $param = array(
                        'flowid' => $flowId,
                        'processid' => $processId,
                        'flowprocess' => $flowProcess,
                        'runid' => $runId
                    );
                    $url = $this->createUrl('handle/' . $turnpage, array('key' => Common::param($param), 'topflag' => $topflag));
                    $this->ajaxReturn(array('status' => 2, 'url' => $url));
                }
            }
            //-- 写办理完毕状态 --
            $con = sprintf("runid = %d AND processid = %d AND uid = %d", $runId, $processId, $this->uid);
            if ($flowProcess !== "" && $flowProcess !== "0") {//如果是固定流程
                $con .= " AND flowprocess = " . $flowProcess;
            }
            FlowRunProcess::model()->updateAll(array('flag' => '4', 'delivertime' => TIMESTAMP), $con);
            //-- 经办人办理完毕后，如检查到所有经办人会签全部结束后，短信提醒主办人 --
            if (!FlowRunProcess::model()->getHasOtherAgentNotDone($runId, $processId)) {
                $run = FlowRun::model()->fetchByPk($runId);
                $uid = FlowRunProcess::model()->fetchNotDoneOpuser($runId, $processId);
                if ($uid) {
                    $param = array(
                        'runid' => $runId,
                        'flowid' => $flowId,
                        'processid' => $processId,
                        'flowprocess' => $flowProcess
                    );
                    $key = Common::param($param);
                    $config = array(
                        '{runname}' => $run['name'],
                        '{url}' => Ibos::app()->urlManager->createUrl('workflow/form/index', array('key' => $key)),
                        'id' => $key,
                    );
                    Notify::model()->sendNotify($uid, 'workflow_sign_notice', $config);
                }
            }
            Main::setCookie('flow_complete_flag', 1, 30);
            $data = array(
                // 'run' => $run,
                // 'uid' => $uid,
                // 'runid' => $runId,
                // 'flowid' => $flowId,
                // 'processid' => $processId,
                // 'flowprocess' => $flowProcess,
                // 'topflag' => $topflag,
                // 'opflag' => $opflag,
                // 'inajax' => $inajax,
                // 'op' => $op
            );
//            $url = Ibos::app()->urlManager->createUrl( 'workflow/list/index', array( 'op' => 'list', 'type' => 'trans', 'sort' => 'all' ) );
//            $this->redirect( $url );
            $this->ajaxReturn(array('isSuccess' => true, 'data' => $data), Mobile::dataType());
        }
    }

    /**
     * 结束流程
     */
    public function actionEnd()
    {
        if (Ibos::app()->request->getIsPostRequest()) {
            $id = Env::getRequest('id');
            if (Handle::endRun($id, $this->uid)) {
                $this->ajaxReturn(array('isSuccess' => true));
            } else {
                $this->ajaxReturn(array('isSuccess' => false));
            }
        }
    }

    /**
     * 下一步未接收之前的回收操作
     */
    public function actionTakeBack()
    {
        $key = Env::getRequest('key');
        if ($key) {
            $param = Common::param($key, 'DECODE');
            $status = Handle::takeBack($param['runid'], $param['flowprocess'], $param['processid'], $this->uid);
            if ($status == 0) {
                $this->ajaxReturn(array('isSuccess' => true));
            } else {
                $this->ajaxReturn(array('isSuccess' => false));
            }
        }
    }

    /**
     * 删除工作流
     */
    public function actionDel()
    {
        if (Ibos::app()->request->getIsPostRequest()) {
            $id = Env::getRequest('id');
            $runId = StringUtil::filterStr(StringUtil::filterCleanHtml($id));
            Handle::destroy($runId);
            $this->ajaxReturn(array('isSuccess' => true));
        }
    }

    /**
     * 是否允许回退
     * @param integer $parent
     * @return boolean
     */
    protected function isAllowBack($parent = 0)
    {
        return FlowRunProcess::model()->getIsAllowBack($this->runid, $this->processid, $this->flowprocess, $parent);
    }

    /**
     * 新增工作流运行实例前预处理
     * @param array $data 提交上来的数据
     * @param ICFlowType $type 工作流类型实例
     * @return void
     */
    protected function beforeAdd(&$data, ICFlowType $type)
    {
        $name = $data['name'];
        // 流程运行实例名称等于前缀+工作名称/文号+后缀
        if (isset($data['prefix'])) {
            $name = $data['prefix'] . $name;
        }
        if (isset($data['suffix'])) {
            $name = $name . $data['suffix'];
        }
        $runName = StringUtil::filterCleanHtml($name);
        // 检查流程运行实例名称是否存在
        $runNameExists = FlowRun::model()->checkExistRunName($type->getID(), $runName);
        if ($runNameExists) {
            $this->error(Ibos::lang('Duplicate run name'));
        }
        $data['name'] = $runName;
    }

    /**
     * 获取回退列表
     * @return array
     */
    protected function getBackList($parent)
    {
        $flowProcessIds = $this->getParent($parent);
        $list = array();
        foreach ($flowProcessIds as $flowprocess) {
            // 去掉空值项
            if (!empty($flowprocess)) {
                $list[] = array('id' => $flowprocess, 'name' => FlowProcess::model()->fetchName($this->flowid, $flowprocess));
            }
        }
        return $list;
    }

    /**
     * 获取当前实例步骤的父步骤
     * @staticvar array $ids
     * @param type $parent
     * @return type
     */
    private function getParent($parent, $id = 0)
    {
        static $ids = array();
        $row = Ibos::app()->db->createCommand()
            ->select('id,parent')
            ->from('{{flow_run_process}} frp')
            ->where(array(
                'and',
                "frp.runid = {$this->runid}",
                "frp.isfallback != 1",
                "frp.flowprocess = '{$parent}'",
                !empty($id) ? "frp.id < '{$id}'" : '',
            ))
            ->order('frp.processid DESC')
            ->limit(1)
            ->queryRow();
        if ($row) {
            $tmpParent = $row['parent'];
            $id = $row['id'];
            $ids[] = $tmpParent;
            return $this->getParent($tmpParent, $id);
        } else {
            return array_unique($ids);
        }
    }

}
