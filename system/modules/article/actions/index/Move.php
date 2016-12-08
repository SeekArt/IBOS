<?php
namespace application\modules\article\actions\index;

use application\core\utils\Ibos;
use application\modules\article\model\Article;

/*
 * 移动新闻
 */

class Move extends Base
{
    public function run()
    {
        $data = $_POST;
        $articleids = $data['articleids'];
        if (empty($articleids)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang(Ibos::lang('Move Success')),
                'data' => '',
            ));
        }
        $catid = $data['catid'];
        $result = Article::model()->updateAllCatidByArticleIds(ltrim($articleids, ','), $catid);
        if ($result) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Move Success'),
                'data' => ''
            ));
        } else {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Move False'),
                'data' => ''
            ));
        }
    }
}