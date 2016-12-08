<?php
namespace application\modules\article\actions\verify;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\dashboard\model\ApprovalStep;

/*
 * 我的审核中的撤回接口，如果撤回人的下一个步骤审核人已经审核了，那么就不能撤回了。
 * 也就是说撤回人的撤回的时候，审核步骤表不能有一个步骤的出现
 */

class Cancel extends Base
{

    public function run()
    {
        /*
         * 1.判断撤回人在审核表还有没有下一个步骤；2.删除审核人的步骤；3.通知撤回人步骤对应的审核用户。
         * 4.更新新闻表的approver字段。
         */
        $articleids = Env::getRequest('articleids');
        $uid = Ibos::app()->user->uid;
        $articleids = explode(',', $articleids);
        $notCancelId = array();
        $passCancelId = array();
        foreach ($articleids as $articleid) {
            //得到撤回人的步骤
            $currApproval = ApprovalRecord::model()->fetch(array(
                'condition' => "module = 'article' AND relateid = $articleid AND uid = $uid",
                'order' => 'time DESC',
            ));
            $nextStep = $currApproval['step'] + 1;
            //撤回人的下一个步骤
            $nextApproval = ApprovalRecord::model()->fetch(array(
                'condition' => "module = 'article' AND relateid = $articleid AND step = $nextStep",
                'order' => 'time DESC',
            ));
            if (count($nextApproval) != 0 || $currApproval['status'] == 2) {//有一个步骤
                $notCancelId[] = $articleid;
            } else {
                $passCancelId[] = $articleid;
                $artciel = Article::model()->fetchByPk($articleid);
                $category = ArticleCategory::model()->fetchByPk($artciel['catid']);
                $condition = 'module = :module AND relateid = :relateid AND time = :time AND step = :step AND status = :status';
                $approvalStep = ApprovalStep::model()->fetch('aid = :aid AND step = :step', array(
                    ':aid' => $category['aid'],
                    ':step' => $currApproval['step'],
                ));
                $uids = $approvalStep['uids'];
                Article::model()->updateAllStatusAndApproverByPks($articleid, $uids, 2);
                //删除撤回人的步骤
                ApprovalRecord::model()->deleteAll($condition, array(
                    ':module' => 'article',
                    ':relateid' => $articleid,
                    ':time' => $currApproval['time'],
                    ':step' => $currApproval['step'],
                    ':status' => 1,
                ));
            }
        }
        if (empty($passCancelId)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Not cancel'),
                'data' => '',
            ));
        } elseif (empty($notCancelId)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Cancel success'),
                'data' => '',
            ));
        } else {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => "有" . count($passCancelId) . Ibos::lang('Cancel success') . ",有" . count($notCancelId) . Ibos::lang('Not cancel'),
                'data' => '',
            ));
        }
    }
}