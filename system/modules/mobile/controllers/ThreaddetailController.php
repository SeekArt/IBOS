<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\mobile\utils\Mobile;
use application\modules\workflow\utils\Common;
use application\modules\thread\controllers\DetailController;
use application\modules\thread\core\ThreadSetting;
use application\modules\thread\model\Thread;
use application\modules\thread\model\ThreadAttention;
use application\modules\thread\model\ThreadReader;
use application\modules\thread\utils\Thread as ThreadUtil;


class ThreadDetailController extends DetailController
{

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     * @param string $routes
     * @return boolean
     */
    public function filterRoutes($routes)
    {
        return true;
    }

    /**
     * 详细页
     */
    public function actionDetail()
    {
        $threadId = $_GET['id'];
        $tab = Env::getRequest('tab');
        $tabs = array('assignment', 'feed', 'email', 'flow', 'file', 'team');
        if (!in_array($tab, $tabs)) {
            $uid = Ibos::app()->user->uid;
            ThreadReader::model()->record($threadId, $uid);
            $params = array(
                'thread' => ThreadUtil::getInstance()->getDetail($threadId, $uid),
                'relation' => $this->getRelationModule()
            );
            $this->ajaxReturn(array('datas' => $params), Mobile::dataType());
        } else {
            $this->$tab();
        }
    }

    /**
     * 任务指派
     */
    protected function assignment()
    {
        $threadId = $_GET['id'];
        $uid = intval(Env::getRequest('uid'));
        $status = intval(Env::getRequest('status')); // 状态{1:进行中; 2：已完成; 4：已取消}
        $data = $this->getThreadObj($threadId)->getAssignment($uid, $status);
        // $assignments = $this->handleAssignmentList( $data );
        // $members = $this->getThreadObj( $threadId )->getMembers();
        $this->ajaxReturn(array(
            'datas' => $data,
            // 'members' => $members
        ), Mobile::dataType());
    }

    /**
     * 动态
     */
    protected function feed()
    {
        $threadId = $_GET['id'];
        $thread = Thread::model()->fetchByPk($threadId);

        $properties = array(
            'module' => 'thread',
            'table' => 'thread',
            'attributes' => array(
                'rowid' => $thread['threadid'],
                'moduleuid' => Ibos::app()->user->uid,
                'touid' => $thread['designeeuid'],
                'module_rowid' => $thread['threadid'],
                'module_table' => 'thread',
                // 'url' => $sourceUrl,
                // 'detail' => Ibos::lang( 'Comment my thread', '', array( '{url}' => $sourceUrl, '{title}' => StringUtil::cutStr( $thread['subject'], 50 ) ) )
            )
        );

        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\thread\widgets\ThreadComment', $properties);
        $list = $widget->getCommentList();

        $this->ajaxReturn(array('datas' => $list), Mobile::dataType());
    }

    /**
     * 邮件
     */
    protected function email()
    {
        $threadId = $_GET['id'];
        $uid = Ibos::app()->user->uid;
        $emails = $this->getThreadObj($threadId)->getEmailWithPage($uid);
        $this->ajaxReturn(array('datas' => $emails), Mobile::dataType());
    }

    /**
     * 工作流审批
     */
    protected function flow()
    {
        $threadId = $_GET['id'];
        $uid = Ibos::app()->user->uid;
        $flows = $this->getThreadObj($threadId)->getFlowWithPage($uid);
        $flowsWithKey = $this->mergeFlowKey($flows);
        $this->ajaxReturn(array('datas' => $flowsWithKey), Mobile::dataType());
    }

    private function mergeFlowKey($list)
    {
        $flows = array();

        foreach ($list as $flow) {
            $param = array(
                'runid' => $flow['runid'],
                'flowid' => $flow['flowid'],
                'processid' => $flow['processid'],
                'flowprocess' => $flow['flowprocess']
            );
            $key = Common::param($param);
            $flow = array_merge($flow, array('key' => $key));
            array_push($flows, $flow);
        }
        return $flows;
    }

    /**
     * 文件
     */
    protected function file()
    {
        $threadId = $_GET['id'];
        if (Env::getRequest('op') == 'select') {
            $this->selectFile();
        }
        $files = $this->getThreadObj($threadId)->getFile();
        $this->ajaxReturn(array('datas' => $files), Mobile::dataType());
    }

    /**
     * 团队
     */
    protected function team()
    {
        $threadId = $_GET['id'];
        $members = $this->getThreadObj($threadId)->getMembers();
        $attentions = ThreadAttention::model()->fetchAttentions($threadId);
        $assignments = $this->getThreadObj($threadId)->getAssignment();
        $settingObj = new ThreadSetting();
        $params = array(
            // 'addMembersAble' => $settingObj->chkAddAttentionsAble( $threadId, Ibos::app()->user->uid ),
            // 'editAble' => $settingObj->chkEditAble( $threadId, Ibos::app()->user->uid ),
            'members' => ThreadUtil::getInstance()->handleMembers($threadId, $members),
            'attentions' => ThreadUtil::getInstance()->handleMembers($threadId, $attentions),
            // 'counters' => $this->getCounters(),
            // 'taskChart' => $this->getTaskChart( $members, $assignments ),
            // 'processChart' => $this->getProcessChart( $assignments )
        );
        $this->ajaxReturn(array('datas' => $params), Mobile::dataType());
    }

    /**
     * 获取关联的模块
     * @return array
     */
    private function getRelationModule()
    {
        $modules = array();
        $m = ThreadSetting::RELATIVE_MODULE;
        $setting = Ibos::app()->setting->get("setting/threadconfig");
        if (isset($setting[$m]) && is_array($setting[$m])) {
            $modules = array_keys($setting[$m]);
        }
        return $modules;
    }

}
