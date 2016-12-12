<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\mobile\utils\Mobile;
use application\modules\thread\controllers\OpController;
use application\modules\thread\model\Thread;
use application\modules\thread\model\ThreadAttention;
use application\modules\thread\utils\Thread as ThreadUtil;
use CJSON;

class ThreadController extends OpController
{

    protected $_condition; // 查询条件

    const DEFAULT_PAGE_SIZE = 10; //默认页面条数

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
     * 工作主线列表页
     */
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $status = isset($_GET['status']) ? intval($_GET['status']) : 0;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

        if (Env::getRequest('param') == 'search') {
            $this->search($offset);
        }
        $condition = " (`designeeuid`={$uid} OR `chargeuid`={$uid} OR FIND_IN_SET({$uid}, `participantuid`) ) AND `status`={$status}";
        $ob = Ibos::app()->db->createCommand();

        // 当查询已完成主线时，限制每次 10 条，查询进行中主线不做限制直接获取全部
        if ($status == 1) {
            $ob->setLimit(self::DEFAULT_PAGE_SIZE);
        }
        $result = $ob
            ->select('*')
            ->from('{{thread}}')
            ->where($condition)
            ->order('addtime DESC')
            ->offset($offset)
            ->queryAll();

        $result = $this->mergeAttentionStatus($result);

        if (count($result) < self::DEFAULT_PAGE_SIZE) {
            $hasMore = false;
        } else {
            $hasMore = true;
        }

        $this->ajaxReturn(array_merge(array('datas' => $result, 'hasMore' => $hasMore)));
    }

    private function mergeAttentionStatus($list)
    {
        $result = array();
        $attenThreadIds = ThreadAttention::model()->fetchThreadIdsByUid(Ibos::app()->user->uid);

        foreach ($list as $thread) {
            $thread = array_merge($thread, array('isAttention' => in_array($thread['threadid'], $attenThreadIds)));
            array_push($result, $thread);
        }
        return $result;
    }

    /**
     * 添加主线
     */
    public function actionAdd()
    {
        $data = CJSON::decode($_POST);
        $this->beforeSave($data); // 空值判断
        $thread = $this->handlePost($data); // 插入前数据处理
        $threadId = ThreadUtil::getInstance()->addThread($thread);
        $returnData = $this->getReturn($threadId);
        $this->ajaxReturn(array('data' => $returnData), Mobile::dataType());
    }

    /**
     * 编辑主线
     */
    public function actionEdit()
    {
        $threadId = $_GET['id'];
        if (Ibos::app()->request->getIsPostRequest()) {
            $post = CJSON::decode($_POST);
            $this->beforeSave($post); // 空值判断
            $this->beforeEdit($threadId);
            $data = $this->handlePost($post); // 插入前数据处理
            $res = ThreadUtil::getInstance()->updateThread($threadId, $data);
            $returnData = $this->getReturn($threadId);
            $this->ajaxReturn(array('isSuccess' => !!$res, 'data' => $returnData), Mobile::dataType());
        } else {
            $threadList = Thread::model()->find("threadid = {$threadId}");
            $this->ajaxReturn(array('datas' => $threadList), Mobile::dataType());
        }
    }

    /**
     * 删除主线
     */
    public function actionDelete()
    {
        $threadId = $_GET['id'];
        $this->beforeDel($threadId);
        $res = ThreadUtil::getInstance()->delThread($threadId);
        if ($res > 0) {
            $bool = true;
        } else {
            $bool = false;
        }
        $this->ajaxReturn(array('isSuccess' => $bool), Mobile::dataType());
    }

    /**
     * 重启主线
     */
    public function actionRestart()
    {
        $threadId = $_GET['id'];
        $res = Thread::model()->updateByPk($threadId, array('status' => 0, 'finishtime' => 0));
        if ($res > 0) {
            $bool = true;
        } else {
            $bool = false;
        }
        $this->ajaxReturn(array('isSuccess' => $bool), Mobile::dataType());
    }

    /**
     * 结束主线
     */
    public function actionEnd()
    {
        $threadId = $_GET['id'];
        $res = Thread::model()->updateByPk($threadId, array('status' => 1, 'finishtime' => TIMESTAMP));
        if ($res > 0) {
            $bool = true;
        } else {
            $bool = false;
        }
        $this->ajaxReturn(array('isSuccess' => $bool), Mobile::dataType());
    }

    /**
     * 搜索
     * @param integer $offset
     */
    private function search($offset)
    {
        $uid = Ibos::app()->user->uid;
        $keyword = Env::getRequest('keyword');
        $this->_condition = " subject LIKE %{$keyword}% AND (`designeeuid`={$uid} OR `chargeuid`={$uid} OR FIND_IN_SET({$uid}, `participantuid`) )";
        $result = Ibos::app()->db->createCommand()
            ->select('*')
            ->from('{{thread}}')
            ->where($this->_condition)
            ->order('addtime DESC')
            ->limit(self::DEFAULT_PAGE_SIZE)
            ->offset($offset)
            ->queryAll();
        if (count($result) < self::DEFAULT_PAGE_SIZE) {
            $hasMore = false;
        } else {
            $hasMore = true;
        }
        $this->ajaxReturn(array_merge(array('datas' => $result, 'hasMore' => $hasMore)));
    }

    /**
     * id参数检查
     * @param integer $threadId 主线id
     */
    private function chkValid($threadId)
    {
        if (!$this->getThreadObj($threadId)->chkValid()) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Thread empty')));
        }
    }

    /**
     * 添加、修改提交前负责人、主题是否为空检查
     */
    private function beforeSave($postData)
    {
        $returnUrl = $this->createUrl('list/index');
        if (empty($postData['subject'])) {
            $this->error(Ibos::lang('Subject cannot be empty'), $returnUrl);
        }
        $settingObj = $this->getSettingObj();
        if ($settingObj->isRequireCharge() && empty($postData['chargeuid'])) {
            $this->error(Ibos::lang('Head cannot be empty'), $returnUrl);
        }
        if ($settingObj->isRequireFinishtime() && empty($postData['endtime'])) {
            $this->error(Ibos::lang('Finishtime connot be empty'), $returnUrl);
        }
    }

    /**
     * 编辑前检查
     * @param integer $threadId
     */
    private function beforeEdit($threadId)
    {
        $this->chkValid($threadId);
        // 检查权限
        if (!$this->getSettingObj()->chkEditAble($threadId, Ibos::app()->user->uid)) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No permission to edit')));
        }
    }

    /**
     * 删除前检查数据可用性及权限
     * @param integer $threadId
     */
    private function beforeDel($threadId)
    {
        $this->chkValid($threadId);
        // 检查权限
        if (!$this->getSettingObj()->chkDelAble($threadId, Ibos::app()->user->uid)) {
            $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('No permission to delete')));
        }
    }

}
