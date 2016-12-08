<?php

/**
 * 日程安排模快------tasks表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模快------tasks表操作类，继承ICModel
 * @package application.modules.calendar.model
 * @version $Id: Tasks.php 1425 2013-10-29 16:16:43Z gzhzh $
 * @author gzhzh <gzhzh.com.cn>
 */

namespace application\modules\calendar\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use CDbCriteria;
use CJSON;
use CPagination;

class Tasks extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{tasks}}';
    }

    /**
     * 通过任务所属的用户ID和完成状态，返回任务信息并分页
     * @param int $uid 任务所属用户ID
     * @param integer $complete 完成状态，0未完成，1完成
     * @param integer $pagesize 每页显示多少条
     * @return array 返回任务的数据和分页信息
     */
    public function fetchTaskByComplete($condition, $complete = 0, $pagesize = null)
    {
        if (!empty($condition)) {
            $condition .= " AND `allcomplete`=" . $complete;
        } else {
            $condition = "`allcomplete`=" . $complete;
        }
        if ($complete == 0) {
            $tasks = Tasks::model()->fetchAll(array('condition' => $condition, 'order' => 'sort ASC'));
            $data['todolist'] = CJSON::encode($tasks);
        } elseif ($complete == 1) {
            $tasks = $this->fetchAllAndPage($condition . " AND pid=''", $pagesize);
            rsort($tasks['datas'], SORT_NUMERIC);
            foreach ($tasks['datas'] as $v) {
                $subTasks = $this->fetchAll('pid=:pid', array(':pid' => $v['id']));  //所有此页父任务的子任务
                $subTasks = $this->fetchAll(array(
                    'condition' => sprintf("pid='%s'", $v['id']),
                    'order' => 'completetime DESC, sort ASC'
                ));
                $tasks['datas'] = array_merge($tasks['datas'], $subTasks);  //把子任务合并到父任务的数组中
            }
            $data = array(
                'pages' => $tasks['pages'],
                'todolist' => CJSON::encode($tasks['datas'])
            );
        }
        return $data;
    }

    /**
     * 取出任务数据数组集合，分页显示
     * @param string $conditions
     * @param integer $pageSize
     * @return array
     */
    public function fetchAllAndPage($conditions = '', $pageSize = null)
    {
        $pages = new CPagination($this->countByCondition($conditions));
        $pageSize = is_null($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pages->setPageSize(intval($pageSize));
        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $criteria = new CDbCriteria(array('limit' => $limit, 'offset' => $offset));
        $pages->applyLimit($criteria);
        $fields = "*";
        $sql = "SELECT $fields FROM {{tasks}}";
        if (!empty($conditions)) {
            $sql .= " WHERE " . $conditions;  //`pid`='' AND 
        }
        $sql .= " ORDER BY completetime DESC LIMIT $offset,$limit";  //注意前面的空号
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array('pages' => $pages, 'datas' => $records);
    }

    /**
     * 根据条件取得总记录数
     */
    public function countByCondition($condition = '')
    {
        if (!empty($condition)) {
            $whereCondition = " WHERE `pid`='' AND " . $condition;
            $sql = "SELECT COUNT(*) AS number FROM {{tasks}} $whereCondition";
            $record = $this->getDbConnection()->createCommand($sql)->queryAll();
            return $record[0]['number'];
        } else {
            return $this->count();
        }
    }

    /**
     * 通过UID读取完成或未完成的任务
     * @param integer $uid 任务所属的用户ID
     * @param integer $complete 0为未完成，1为完成
     * @return array 返回已经完成的任务数组
     */
    public function fetchTasksByUid($uid, $complete)
    {
        $tasks = $this->fetchAll(array(
            'select' => 'id, pid, text, date, mark, complete, allcomplete',
            'condition' => 'uid=:uid AND allcomplete=:allcomplete',
            'params' => array(':uid' => $uid, ':allcomplete' => $complete)
        ));
        return $tasks;
    }

    /**
     * 通过ID修改此任务和子任务是否被标记
     * @param string $id 任务ID
     * @param int $mark 0为未标记，1为标记
     */
    public function modifyTasksMark($id, $mark)
    {
        return $this->updateAll(array('mark' => $mark), 'id=:id', array(':id' => $id));
    }

    /**
     * 通过ID修改此任务和子任务完成状态
     * @param string $id 任务ID
     * @param int $complete 0为未完成，1为完成
     */
    public function modifyTasksComplete($id, $complete)
    {
        $task = $this->fetchByPk($id);
        if (empty($task['pid'])) {  //如果是父级任务
            $this->updateAll(array('complete' => $complete, 'allcomplete' => $complete, 'completetime' => $complete ? TIMESTAMP : 0), 'id=:id OR pid=:id', array(':id' => $id));
        } elseif (!empty($task['pid']) && $complete == 0) {  //如果是子任务未完成，则把此任务和父任务完成状态取消
            $this->updateAll(array('complete' => 0), 'id=:id OR id=:pid', array(':id' => $id, ':pid' => $task['pid']));
            $this->updateAll(array('allcomplete' => 0), 'id=:pid OR pid=:pid', array(':pid' => $task['pid']));
        } elseif (!empty($task['pid']) && $complete == 1) {  //如果是子任务完成，判断父任务的所有子任务是否都已完成
            $this->modify($id, array('complete' => 1, 'completetime' => TIMESTAMP));
            $allSubTask = $this->fetchAll('pid=:pid', array(':pid' => $task['pid']));
            $newArr = Convert::getSubByKey($allSubTask, 'complete'); //取出所有子任务的完成状态，若全部为1(即0不在数组)，则把父任务变成完成状态
            if (!in_array(0, $newArr)) {
                $this->modify($task['pid'], array('complete' => 1, 'completetime' => TIMESTAMP));
                $this->updateAll(array('allcomplete' => 1), 'id=:pid OR pid=:pid', array(':pid' => $task['pid']));
            }
        }
        $ret = $this->fetchByPk($id);
        return $ret;
    }

    /**
     * 通过ID删除此任务和所有子任务
     * @param string $id 任务ID
     */
    public function removeTasksById($id)
    {
        return $this->deleteAll('id=:id OR pid=:id', array('id' => $id));
    }

    /**
     * 用于搜索，把所有父任务有关键字的id找出来
     * @param int $uid 用户ID
     * @param int $complete 0为未完成，1完成
     * @param string $keyword 关键字
     * @return array  返回符合添加的ID数组
     */
    public function fetchPTasks($uid, $complete, $keyword)
    {
        $pTasks = $this->fetchAll(array(
            'select' => 'id',
            'condition' => "uid=:uid AND pid=:pid AND allcomplete=:allcomplete AND text LIKE :keyword",
            'params' => array(':uid' => $uid, ':pid' => '', ':allcomplete' => $complete, ':keyword' => "%$keyword%")
        ));
        return $pTasks;
    }

    /**
     * 用于搜索把子任务有关键字的找出来，取得所有的父ID
     * @param int $uid 用户ID
     * @param int $complete 0为未完成，1完成
     * @param string $keyword 关键字
     * @return array 返回所有符合条件的父ID数组
     */
    public function fetchCTasks($uid, $complete, $keyword)
    {
        $cTasks = $this->fetchAll(array(
            'select' => 'pid',
            'condition' => "uid=:uid AND pid!=:pid AND allcomplete=:allcomplete AND text LIKE :keyword",
            'params' => array(':uid' => $uid, ':pid' => '', ':allcomplete' => $complete, ':keyword' => "%$keyword%")
        ));
        return $cTasks;
    }

    /**
     * 带有完成时间的任务操作后，相应的日程作出相应操作前所需的数据
     * @param string $taskid 任务id
     * @return array  返回所需保存或者修改的数据
     */
    public function handleCalendar($taskid)
    {
        $task = $this->fetchByPk($taskid);
        $data = array(
            'taskid' => $taskid,
            'subject' => $task['text'] . Ibos::lang('From task'),
            'starttime' => strtotime($task['date']),
            'endtime' => strtotime($task['date']),
            'isalldayevent' => 1,
            'lock' => 1,
            'uid' => $task['uid'],
            'uptime' => time(),
            'upuid' => $task['upuid'],
            'category' => 1
        );
        return $data;
    }

    /**
     * 根据任务的完成状态添加或修改相应日程
     * @param string $id 任务id
     * @param integer $complete 完成状态(0：未完成  1：完成)
     * @return void
     */
    public function updateCalendar($id, $complete)
    {
        $schedule = Calendars::model()->fetchByAttributes(array('taskid' => $id));
        if ($complete) {
            $st = TIMESTAMP - 3600;
            $et = TIMESTAMP;
            if (!empty($schedule)) { // 存在日程就修改
                return Calendars::model()->modify($schedule['calendarid'], array('status' => 1, 'starttime' => $st, 'endtime' => $et, 'isalldayevent' => 0));
            } else { // 不存在就添加
                $calendar = $this->handleCompTaskCalendar($id, $st, $et);
                return Calendars::model()->add($calendar);
            }
        } else {
            if (!empty($schedule)) {
                return Calendars::model()->modify($schedule['calendarid'], array('status' => 3));
            }
        }
    }

    /**
     * 处理完成任务添加到日程的数据
     * @param string $taskid 任务id
     * @param string $st 日程开始时间
     * @param string $et 日程结束时间
     * @return array
     */
    public function handleCompTaskCalendar($taskid, $st, $et)
    {
        $task = $this->fetchByPk($taskid);
        $data = array(
            'taskid' => $taskid,
            'subject' => $task['text'] . Ibos::lang('From task'),
            'starttime' => $st,
            'endtime' => $et,
            'isalldayevent' => 0,
            'lock' => 0,
            'uid' => $task['uid'],
            'uptime' => time(),
            'upuid' => $task['upuid'],
            'category' => 1
        );
        return $data;
    }

}
