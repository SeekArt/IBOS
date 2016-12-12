<?php
namespace application\modules\article\actions\index;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;

/*
* 统计未读等个数接口,以后所有需要统计的个数都统一放到这里
*/

class GetCount extends Base
{

    public function run()
    {
        $catid = intval(Env::getRequest('catid'));
        $childCatIds = '';
        if (isset($catid)) {
            $this->catid = $catid;
            $childCatIds = ArticleCategory::model()->fetchCatidByPid($this->catid, true);
        }
        $uid = Ibos::app()->user->uid;
        $unread = Article::model()->getArticleListByType(self::TYPE_UNREAD, $uid, $childCatIds, 10, 0);
        $approval = Article::model()->getArticleListByType(self::TYPE_APPROVAL, $uid, $childCatIds, 10, 0);
        $back = Article::model()->getArticleListByType(self::TYPE_REBACK_TO, $uid, $childCatIds, 10, 0);
        $verify = Article::model()->getArticleListByType(self::TYPE_WAIT, $uid, $childCatIds, 10, 0);
        $output['unread'] = $unread['count'];//未读
        $output['approval'] = $approval['count'];//审核中
        $output['reback_to'] = $back['count'];//被退回
        $output['wait'] = $verify['count'];//待我审核
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $output,
        ));
    }
}