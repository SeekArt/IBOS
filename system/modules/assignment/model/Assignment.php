<?php

/**
 * 任务指派模块------ assignment表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 任务指派模块------  assignment表的数据层操作类，继承ICModel
 * @package application.modules.assignments.model
 * @version $Id: Assignment.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use CDbCriteria;
use CPagination;

class Assignment extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{assignment}}';
    }

    /**
     * 查找指派人是uid所有未完成任务
     * @param integer $uid
     */
    public function fetchUnfinishedByDesigneeuid($uid)
    {
        $record = $this->fetchAll(array(
            'condition' => sprintf('`designeeuid` = %d AND `status` != 2 AND `status` != 3', $uid),
            'order' => 'addtime DESC',
        ));
        return $record;
    }

    /**
     * 查找负责人是uid所有未完成任务
     * @param integer $uid
     */
    public function fetchUnfinishedByChargeuid($uid)
    {
        $record = $this->fetchAll(array(
            'condition' => sprintf('`chargeuid` = %d AND `status` != 2 AND `status` != 3', $uid),
            'order' => 'addtime DESC',
        ));
        return $record;
    }

    /**
     * 查找指派人是uid所有未完成任务
     * @param integer $uid
     */
    public function fetchUnfinishedByParticipantuid($uid)
    {
        $record = $this->fetchAll(array(
            'condition' => sprintf('FIND_IN_SET(%d, `participantuid`) AND `status` != 2 AND `status` != 3', $uid),
            'order' => 'addtime DESC',
        ));
        return $record;
    }

    /**
     * 获得某个用户未完成的任务数据(分为uid指派的、负责的、参与的和用户数据)
     * @param integer $uid
     * @return array
     */
    public function getUnfinishedByUid($uid)
    {
        $datas = array(
            'designeeData' => $this->fetchUnfinishedByDesigneeuid($uid), // 指派的任务
            'chargeData' => $this->fetchUnfinishedByChargeuid($uid), // 负责的任务
            'participantData' => $this->fetchUnfinishedByParticipantuid($uid) // 参与的任务
        );
        return $datas;
    }

    /**
     * 分页查找数据
     * @param string $conditions 条件
     * @param integer $pageSize 每页多少条数据
     * @return array
     */
    public function fetchAllAndPage($conditions = '', $pageSize = null)
    {
        $conditionArray = array('condition' => $conditions, 'order' => 'finishtime DESC');
        $criteria = new CDbCriteria();
        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }
        $count = $this->count($criteria);
        $pages = new CPagination($count);
        $everyPage = is_null($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pages->setPageSize(intval($everyPage));
        $pages->applyLimit($criteria);
        $datas = $this->fetchAll($criteria);
        return array('pages' => $pages, 'datas' => $datas, 'count' => $count);
    }

    /**
     * 获得某个uid未完成任务数（包括指派的任务、负责的任务、参与的任务）
     * @param integer $uid
     * @return type integer
     */
    public function getUnfinishCountByUid($uid)
    {
        $count = $this->count("`status` != 2 AND `status` != 3 AND (`designeeuid` = {$uid} OR `chargeuid` = {$uid} OR FIND_IN_SET({$uid}, `participantuid`) )");
        return intval($count);
    }

    /**
     * 根据任务ID查找任务完成状态
     * @param integer $assignmentid 任务ID
     * @return boolean
     */
    public function getStatusByAssignmentid($assignmentid)
    {
        $record = $this->fetch(array(
            'condition' => sprintf('`assignmentid` = %d AND `status` = 2 OR `status` = 3', $assignmentid)
        ));
        if (!empty($record)) {
            $bool = true;
        } else {
            $bool = false;
        }
        return $bool;
    }

}
