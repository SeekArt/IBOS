<?php
namespace application\modules\article\actions\verify;

use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\user\model\User;

/*
 * 根据新闻ID得到对应的审核流程信息
 */

class FlowLog extends Base
{
    public function run()
    {
        $data = $_POST;
        $article = Article::model()->fetchByPk($data['articleid']);
        $aitVerify = ArticleCategory::model()->getLevelByCatid($article['catid']);
        $verifyUser = User::model()->fetchByPk($article['author']);
        $publish = array(
            array(
                'author' => $verifyUser['realname'],
                'status' => Ibos::lang('Publish'),
                'reason' => '',
                'time' => date('Y-m-d H:i:s', $article['addtime']),
            ),
        );
        if ($aitVerify != 0) {
            $category = ArticleCategory::model()->fetchByPk($article['catid']);
            $flowLog = ApprovalRecord::model()->getFlowLog($data['articleid']);
            //$verify = ApprovalRecord::model()->getNotAllow($data['articleid'], $category['aid']);
            // if (!empty($verify)) {//是否还有待审核
            //     foreach ($verify as $vaule) {
            //         $username = User::model()->fetchRealnameByUid($vaule);
            //         $flowLog[] = array(
            //             'author' => $username,
            //             'status' => Ibos::lang('Wait to verify'),
            //             'reason' => '',
            //             'time' => ''
            //         );
            //     }
            // }
        }
        if (isset($flowLog)) {
            $publish = array_merge($publish, $flowLog);
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $publish,
        ));
    }
}