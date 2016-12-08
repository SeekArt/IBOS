<?php
namespace application\modules\article\utils;

use application\core\utils\Ibos;
use application\modules\dashboard\model\Approval;
use application\modules\dashboard\model\ApprovalRecord;

/*
*审核工具类
*/

class VerifyUtil
{

    /*
     * 审核通过方法
     * @param integer $approvalid 审批流程ID
     * @param integer $relateid 关联模型的ID，如新闻ID或者公文ID
     * @param integer $uid 当前用户ID
     * @return integer -1表示不是当前的审核步骤人，1表示审核通过，2表示审核已经结束
     */
    public static function passVerify($approvalid, $relateid, $uid)
    {
        $lastApproval = ApprovalRecord::model()->fetchLastStep($relateid);
        //如果审核记录表对应的关联模型ID的最后一条记录的status字段为1或者3就可以审核了
        if ($lastApproval['status'] == 1 || $lastApproval['status'] == 3) {
            $approval = Approval::model()->fetchByPk($approvalid);
            //当前步骤
            $currentApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $lastApproval['step']);
            //下一个步骤
            $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $lastApproval['step'] + 1);
            if (!in_array($uid, $currentApproval['uids'])) {
                Ibos::app()->controller - ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('You do not have permission to verify the article'),
                    'data' => '',
                ));
            }
            if (!empty($nextApproval)) {
                if ($nextApproval['step'] == 'publish') {//已经完成了
                    ApprovalRecord::model()->recordStep($relateid, $uid, 2);
                    return true;
                } else {//记录签收步骤，给下一步签收人发提醒消息
                    ApprovalRecord::model()->recordStep($relateid, $uid, 1);
                    return $nextApproval['uids'];
                }
            }
        }
    }
}