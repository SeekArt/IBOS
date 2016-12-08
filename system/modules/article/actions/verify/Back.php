<?php
namespace application\modules\article\actions\verify;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\message\model\Notify;
use application\modules\user\model\User;

/*
 * 审核退回，需要把审核的退回的状态添加的审核步骤表，因为以后还有得到所有所有审核的步骤
 */

class Back extends Base
{

    public function run()
    {
        $data = $_POST;
        $artIds = trim($data['articleids'], ',');
        if (empty($data['articleids'])) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Select one'),
                'data' => '',
            ));
        }
        if (empty($data['reason'])) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Not back reason'),
                'data' => '',
            ));
        }
        $reason = StringUtil::filterCleanHtml($data['reason']);
        $uid = Ibos::app()->user->uid;
        $ids = explode(',', $artIds);
        $output = $data;
        if (empty($ids)) {
            $this->controller->isSuccess = false;
            $this->msg = Ibos::lang('Parameters error', 'error');
            $this->Output = $output;
        }
        $sender = User::model()->fetchRealnameByUid($uid);
        foreach ($ids as $artId) {
            $art = Article::model()->fetchByPk($artId);
            $categoryName = ArticleCategory::model()->fetchCateNameByCatid($art['catid']);
            if (!$this->checkIsApprovaler($art, $uid)) {
                Ibos::app()->controller->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('You do not have permission to verify the article'),
                    'data' => $output,
                ));
            }
            $config = array(
                '{sender}' => $sender,
                '{subject}' => $art['subject'],
                '{category}' => $categoryName,
                '{content}' => $reason,
                '{url}' => Ibos::app()->urlManager->createUrl('article/default/show', array('articleid' => $artId)),
            );
            Notify::model()->sendNotify($art['author'], 'article_back_message', $config, $uid);
            Article::model()->updateAllStatusAndApproverByPks($artId, $uid, 0);//把新闻的状态修改退回状态
            ApprovalRecord::model()->recordStep($artId, $uid, 0, $reason);//记录审核步骤退回
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Back success'),
            'data' => '',
        ));
    }
}