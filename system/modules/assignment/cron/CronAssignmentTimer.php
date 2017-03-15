<?php
/**
 * 任务指派计划任务
 * 检查是否有到点需要进行提醒的任务提醒
 * 有的话加入到消息提醒中
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2016 IBOS Inc
 * @author gzhyj <gzhyj@ibos.com.cn>
 */

use application\modules\assignment\model\AssignmentRemind;
use application\modules\assignment\model\Assignment;
use application\modules\user\model\User;
use application\modules\message\model\NotifyMessage;
use application\core\utils\Ibos;

if (!isset(Ibos::app()->user->uid)) {
    return true;
}

$uid = Ibos::app()->user->uid;
$remindList = AssignmentRemind::model()->fetchNeedRemindReminder($uid);
if (!empty($remindList)) {
    foreach ($remindList as $remind) {
        $assignment = Assignment::model()->findByPk($remind['assignmentid']);
        $senderName = User::model()->fetchRealnameByUid($remind['uid']);
        $title = Ibos::lang('assignment/default/Timing assign title', '', array('{subject}' => $assignment['subject'], '{content}' => $remind['content']));
        $body = Ibos::lang('assignment/default/Timing assign content', '', array('{url}' => Ibos::app()->createUrl('assignment/default/show', array('assignmentId' => $remind['assignmentid'])), '{subject}' => $assignment['subject'], '{content}' => $remind['content']));
        $assignData = array(
            'uid' => $uid,
            'node' => 'assignment_push_message',
            'module' => 'assignment',
            'title' => $title,
            'body' => $body,
            'ctime' => time(),
            'url' => Ibos::app()->createUrl('assignment/default/show', array('assignmentId' => $remind['assignmentid'])),
        );
        $addRes = NotifyMessage::model()->add($assignData);
        if ($addRes) {
            $remind->status = 1;
            $remind->update();
        }
    }
}