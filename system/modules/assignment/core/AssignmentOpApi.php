<?php

/**
 * 指派任务模块
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 指派任务模块------  任务操作api
 * @package application.modules.thread.core
 * @version $Id: AssignmentOpApi.php 3297 2014-07-25 09:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\core;

use application\core\utils\Attach;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\System;
use application\modules\assignment\model\Assignment;
use application\modules\assignment\model\AssignmentLog;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
use application\modules\message\model\Comment;
use application\modules\message\model\Notify;
use application\modules\user\model\User;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;

Class AssignmentOpApi extends System
{

    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 添加指派任务
     * @param type $post
     * @return type
     */
    public function addAssignment($post)
    {
        $uid = $post['uid'];
        $assignment = AssignmentUtil::handlePostData($post);
        $assignment['designeeuid'] = $uid;
        $assignment['addtime'] = TIMESTAMP;
        $assignmentId = Assignment::model()->add($assignment, true);
        if (!empty($assignment['attachmentid'])) {
            Attach::updateAttach($assignment['attachmentid']);
        }
        // 消息提醒
        $chargeuid = StringUtil::getId($post['chargeuid']);
        $participantuid = StringUtil::getId($post['participantuid']);
        $uidArr = array_merge($participantuid, $chargeuid);
        $this->sendNotify($uid, $assignmentId, $assignment['subject'], $uidArr, 'assignment_new_message');
        // 动态推送
        $wbconf = WbCommonUtil::getSetting(true);
        if (isset($wbconf['wbmovement']['assignment']) && $wbconf['wbmovement']['assignment'] == 1) {
            $data = array(
                'title' => Ibos::lang('Feed title', '', array(
                    '{subject}' => html_entity_decode($assignment['subject']),
                    '{url}' => Ibos::app()->urlManager->createUrl('assignment/default/show', array('assignmentId' => $assignmentId))
                )),
                'body' => html_entity_decode($assignment['subject']),
                'actdesc' => Ibos::lang('Post assignment', 'assignment.default'),
                'userid' => implode(',', $uidArr),
                'deptid' => '',
                'positionid' => '',
            );
            WbfeedUtil::pushFeed($uid, 'assignment', 'assignment', $assignmentId, $data, 'post');
        }
        // 发表一条添加评论
        $this->addStepComment($uid, $assignmentId, Ibos::lang('Add the assignment', 'assignment.default'));
        // 记录日志
        AssignmentLog::model()->addLog($uid, $assignmentId, 'add', Ibos::lang('Add the assignment', 'assignment.default'));
        return $assignmentId;
    }

    /**
     * 记录步骤，添加评论信息
     * @param integer $assignmentId 任务的id
     * @param string $content 评论的内容
     */
    public function addStepComment($uid, $assignmentId, $content)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);
        // 获取接收数据
        $data = array(
            'module' => 'assignment',
            'table' => 'assignment',
            'rowid' => $assignmentId,
            'moduleuid' => $assignment['designeeuid'],
            'uid' => $uid,
            'content' => $content,
            'touid' => 0,
            'ctime' => TIMESTAMP,
            'url' => Ibos::app()->controller->createUrl('default/show', array('assignmentId' => $assignmentId))
        );
        Comment::model()->add($data);
        Assignment::model()->updateCounters(array('commentcount' => 1), "`assignmentid` = {$assignmentId}");
    }

    /**
     * 任务通用发送提醒消息
     * @param integer $assignmentId 任务id
     * @param string $subject 消息内容
     * @param integer $toUid 发送给谁
     * @param string $node 消息节点
     * @param string $result 是否是申请结果的提醒(是的话传递“同意“或”拒绝“文字)
     */
    public function sendNotify($uid, $assignmentId, $subject, $toUid, $node, $result = null)
    {
        $config = array(
            '{sender}' => User::model()->fetchRealnameByUid($uid),
            '{subject}' => html_entity_decode($subject),
            '{url}' => Ibos::app()->urlManager->createUrl('assignment/default/show', array('assignmentId' => $assignmentId)),
            'id' => $assignmentId,
        );
        if (isset($result)) {
            $config['{result}'] = $result;
        }
        if (!empty($toUid)) {
            $toUid = $this->removeSelf($toUid, $uid);
            Notify::model()->sendNotify($toUid, $node, $config, $uid);
        }
    }

    /**
     * 处理一个uids数据，去除登陆者uid
     * @param mix $uids 用户uids
     * @return array
     */
    private function removeSelf($uids, $curUid)
    {
        $uids = is_array($uids) ? $uids : explode(',', $uids);
        foreach ($uids as $k => $uid) {
            if ($uid == $curUid) {
                unset($uids[$k]);
            }
        }
        return $uids;
    }

}
