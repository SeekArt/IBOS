<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Stamp;
use application\modules\user\model\User;

class AssignmentFinishedController extends BaseController
{

    /**
     * 图章id(暂定3个，4干得不错，2有进步，3继续努力)
     * @var array
     */
    private $_stamps = array(4, 2, 3);
    // 查询条件
    protected $_condition;

    const DEFAULT_PAGE_SIZE = 10; // 默认页面条数

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
     * 已完成的任务列表页
     */
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        //是否搜索
        if (Env::getRequest('param') == 'search') {
            $this->search($offset);
        }
        $condition = "(`status` = 2 OR `status` = 3) AND (`designeeuid` = {$uid} OR `chargeuid` = {$uid} OR FIND_IN_SET({$uid}, `participantuid`))";
        $result = Ibos::app()->db->createCommand()
            ->select('*')
            ->from('{{assignment}}')
            ->where($condition)
            ->order('finishtime DESC')
            ->limit(self::DEFAULT_PAGE_SIZE)
            ->offset($offset)
            ->queryAll();
        if (count($result) < self::DEFAULT_PAGE_SIZE) {
            $hasMore = false;
        } else {
            $hasMore = true;
        }
        $this->ajaxReturn(array_merge(array(
            'datas' => $result,
            'hasMore' => $hasMore,
            'stamps' => $this->getStamps()
        )));
    }

    /**
     * 搜索
     * @return void
     */
    private function search($offset)
    {
        $uid = Ibos::app()->user->uid;
        // 关键字
        $keyword = Env::getRequest('keyword');
        if (!empty($keyword)) {
            $this->_condition = " (`subject` LIKE '%$keyword%' ";
            $users = User::model()->fetchAll("`realname` LIKE '%$keyword%'");
            if (!empty($users)) { // 用户关键字
                $uids = Convert::getSubByKey($users, 'uid');
                $uidStr = implode(',', $uids);
                $this->_condition .= " OR FIND_IN_SET(`designeeuid`, '{$uidStr}') OR FIND_IN_SET( `chargeuid`, '{$uidStr}' ) ";
                foreach ($uids as $uid) {
                    $this->_condition .= " OR FIND_IN_SET({$uid}, `participantuid`)";
                }
            }
            $this->_condition .= ')';
        }
        $condition = "(`status` = 2 OR `status` = 3) AND (`designeeuid` = {$uid} OR `chargeuid` = {$uid} OR FIND_IN_SET({$uid}, `participantuid`)) and ";
        $result = Ibos::app()->db->createCommand()
            ->select('*')
            ->from('{{assignment}}')
            ->where($condition . $this->_condition)
            ->order('finishtime DESC')
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
     * 获取图章信息
     * @return array
     */
    public function getStamps()
    {
        $stamps = array();
        foreach ($this->_stamps as $id) {
            $stamp = Stamp::model()->fetchByPk($id);
            $stamps[] = array(
                'path' => $stamp['icon'],
                'stampPath' => $stamp['stamp'],
                'stamp' => $stamp['stamp'],
                'title' => $stamp['code'],
                'value' => $id
            );
        }
        return $stamps;
    }

}
