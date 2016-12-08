<?php

/**
 * 任务指派模块------ assignment_log表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 任务指派模块------  assignment_log表的数据层操作类，继承ICModel
 * @package application.modules.assignments.model
 * @version $Id: AssignmentLog.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
namespace application\modules\assignment\model;

use application\core\model\Model;
use application\core\utils\Env;
use application\modules\user\model\User;

class AssignmentLog extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{assignment_log}}';
    }

    /**
     * 写入一条任务日志信息
     * @param integer $uid 用户uid
     * @param integer $assignmentId 任务id
     * @param string $type 日志类型：(add-新建,del-删除,update-修改,view-查看,push-推办任务,finish-完成任务,restart-重启任务,delay-延期,applydelay-申请延期,agreedelay-同意延期,refusedelay-拒绝延期,cancel-取消,applycancel-申请取消,agreecancel-同意取消,refusecancel-拒绝取消)
     * @param string $content 日志内容
     * @return boolean
     */
    public function addLog($uid, $assignmentId, $type, $content)
    {
        $realname = User::model()->fetchRealnameByUid($uid);
        $data = array(
            'assignmentid' => $assignmentId,
            'uid' => $uid,
            'time' => TIMESTAMP,
            'ip' => Env::getClientIp(),
            'type' => $type,
            'content' => $realname . $content
        );
        return $this->add($data);
    }
}
