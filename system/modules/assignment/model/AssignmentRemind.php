<?php

/**
 * 任务指派模块------ assignment_remind表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 任务指派模块------  assignment_remind表的数据层操作类，继承ICModel
 * @package application.modules.assignments.model
 * @version $Id: AssignmentRemind.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\model;

use application\core\model\Model;
use application\core\utils\Convert;

class AssignmentRemind extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{assignment_remind}}';
    }

    /**
     * 获取某个uid所有提醒设置,返回格式:任务id=>提醒时间
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchAllByUid($uid)
    {
        $record = $this->fetchAll("uid = {$uid}");
        $res = array();
        foreach ($record as $remind) {
            $res[$remind['assignmentid']] = $remind['remindtime'];
        }
        return $res;
    }

    /**
     * 获得某个用户的某个任务提醒的日程id
     * @param integer $assignmentId 任务id
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchCalendarids($assignmentId, $uid)
    {
        $records = $this->fetchAll("assignmentid = {$assignmentId} AND uid = {$uid}");
        return Convert::getSubByKey($records, 'calendarid');
    }

    /**
     * 根据 uid 获取用户到点需要进行提醒的未提醒任务提醒
     * @param  integer $uid 用户 uid
     * @return array      任务提醒列表数组
     */
    public function fetchNeedRemindReminder($uid)
    {
        $condition = sprintf("`uid` = :uid AND `remindtime` <= %d AND `status` = 0", time());
        $params = array(':uid' => $uid);
        return $this->findAll($condition, $params);
    }

    /*
    * 根据用户的uid和指派的任务id更新任务指派的提醒为已提醒
    */
    public function updateNeedRemindReminder($uid, $assignmentId)
    {
        $this->updateAll(array('status' => 1), "uid = :uid AND assignmentid = :assginmentid", array(':uid' => $uid, ':assginmentid' => $assignmentId));
    }
}
