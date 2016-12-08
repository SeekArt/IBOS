<?php
namespace application\modules\article\actions\verify;

use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\dashboard\model\Approval;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\message\model\Notify;
use application\modules\user\model\User;

/*
 * 新闻审核接口
 */

class Verify extends Base
{
    public function run()
    {
        $data = $_POST;
        $artIds = trim($data['articleids'], ',');
        $uid = Ibos::app()->user->uid;
        $ids = explode(',', $artIds);
        if (empty($ids)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Parameters error', 'error'),
                'data' => '',
            ));
        }
        foreach ($ids as $artId) {
            $artApproval = ApprovalRecord::model()->fetchLastStep($artId);
            if ($artApproval['status'] == 1 || $artApproval['status'] == 3) {
                $art = Article::model()->fetchByPk($artId);
                $sender = User::model()->fetchRealnameByUid($art['author']);
                $category = ArticleCategory::model()->fetchByPk($art['catid']);
                $approval = Approval::model()->fetch("id={$category['aid']}");
                $curApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $artApproval['step']);//当前审核步骤
                $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'],
                    $artApproval['step'] + 1); // 下一步应该审核的步骤
                if (!in_array($uid, $curApproval['uids'])) {
                    Ibos::app()->controller->ajaxReturn(array(
                        'isSuccess' => false,
                        'msg' => Ibos::lang('You do not have permission to verify the article')
                    ));
                }
                if (!empty($nextApproval)) {
                    if ($nextApproval['step'] == 'publish') {//已完成标识
                        $this->verifyComplete($artId, $uid);
                    } else {//记录签收步骤，给下一步签收人发提醒消息
                        ApprovalRecord::model()->recordStep($artId, $uid, 1);//记录审核步骤通过
                        $config = array(
                            '{sender}' => $sender,
                            '{subject}' => $art['subject'],
                            '{category}' => $category['name'],
                            '{url}' => Ibos::app()->controller->createUrl('default/show', array('articleid' => $art['articleid'])),
                            '{content}' => Ibos::app()->controller->renderPartial('remindcontent', array(
                                'article' => $art,
                                'author' => $sender,
                            ), true),
                        );
                        Notify::model()->sendNotify($nextApproval['uids'], 'article_verify_message', $config, $uid);
                        //审核人为下一个审核该新闻的用户（当前审核已通过）
                        $approver = $nextApproval['uids'];
                        $approver = implode(',', $approver);
                        Article::model()->updateAllStatusAndApproverByPks($artId, $approver, 2);
                    }
                }
            }
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Verify succeed', 'message'),
            'data' => ','
        ));
    }

}