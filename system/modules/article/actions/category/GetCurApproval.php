<?php
namespace application\modules\article\actions\category;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;
use application\modules\article\model\ArticleCategory;
use application\modules\dashboard\model\Approval;
use application\modules\dashboard\model\ApprovalStep;
use application\modules\user\model\User;

/**
 * 获取某个分类的审批流程
 */
class GetCurApproval extends Base
{

    public function run()
    {
        $catid = Env::getRequest('catid');
        $category = ArticleCategory::model()->fetchByPk($catid);
        $approval = Approval::model()->fetchByPk($category['aid']);
        $aid = ArticleCategory::model()->fetchAidByCatid($catid);
        $approverList = ApprovalStep::model()->getStepUids($aid);
        $verify = array();
        foreach ($approverList as $key => $value) {
            $username = User::model()->fetchRealnamesByUids($value);
            $verify[] = array(
                'id' => $key,
                'name' => $username,
            );
        }
        $free = Approval::model()->getFreeUidById($aid);
        $freeList = User::model()->fetchRealnamesByUids($free);
        $verifyList = array(
            'approval' => $verify,
            'free' => $freeList,
        );
        $approval['step'] = $verifyList;
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $approval,
        ));
    }
}