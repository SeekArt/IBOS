<?php

/**
 * 任务指派模块------ assignment_apply表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 任务指派模块------  assignment_log表的数据层操作类，继承ICModel
 * @package application.modules.assignments.model
 * @version $Id: AssignmentApply.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\model;

use application\core\model\Model;


class AssignmentApply extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{assignment_apply}}';
    }

    /**
     * 添加一条延期申请记录
     * @param integer $uid 申请人uid
     * @param integer $assignmentId 任务id
     * @param string $delayReason 延期理由
     * @param integer $stattime 延期开始时间
     * @param integer $endtime 延期结束时间
     * @return boolean
     */
    public function addDelay($uid, $assignmentId, $delayReason, $stattime, $endtime)
    {
        $this->deleteAll("uid = {$uid} AND assignmentid = {$assignmentId}");
        $data = array(
            'uid' => $uid,
            'assignmentid' => $assignmentId,
            'isdelay' => 1,
            'delayreason' => $delayReason,
            'delaystarttime' => $stattime,
            'delayendtime' => $endtime
        );
        return $this->add($data);
    }

    /**
     * 添加一条取消申请记录
     * @param integer $uid 申请人uid
     * @param integer $assignmentId 任务id
     * @param string $cancelReason 取消理由
     * @return boolean
     */
    public function addCancel($uid, $assignmentId, $cancelReason)
    {
        $this->deleteAll("uid = {$uid} AND assignmentid = {$assignmentId}");
        $data = array(
            'uid' => $uid,
            'assignmentid' => $assignmentId,
            'iscancel' => 1,
            'cancelreason' => $cancelReason
        );
        return $this->add($data);
    }

}
