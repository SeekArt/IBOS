<?php
namespace application\modules\article\actions\publish;

use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\dashboard\model\ApprovalStep;

/*
 * 撤回接口
 */

class Cancel extends Base
{

    public function run()
    {
        //1.通过分类id重新找到第一步需要审核的人，2.更新新闻状态为草稿，3.删除所有的以前的审核步骤
        $data = $_POST;
        $articleid = $data['articleid'];
        $article = Article::model()->fetchByPk($articleid);
        $categoty = ArticleCategory::model()->fetchByPk($article['catid']);
        $uids = ApprovalStep::model()->getApprovalerStr($categoty['aid'], 1);
        Article::model()->updateAllStatusAndApproverByPks($articleid, $uids, 3);
        ApprovalRecord::model()->deleteByRelateid($articleid);
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Cancel success'),
            'data' => Ibos::app()->controller->createUrl('publish/index'),
        ));
    }
}