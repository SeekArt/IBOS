<?php
namespace application\modules\article\actions\index;

use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\model\Article;

/*
 * 置顶新闻
 */

class Top extends Base
{

    public function run()
    {
        $data = $_POST;
        $topEndTime = $data['topEndTime'];
        if (!empty($topEndTime) && strtotime($topEndTime) > TIMESTAMP) {
            $topEndTime = strtotime($topEndTime) + 24 * 60 * 60 - 1;
            Article::model()->updateTopStatus($data['articleids'], 1, TIMESTAMP, $topEndTime);
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Top succeed'),
                'data' => ''
            ));
        } else {
            Article::model()->updateTopStatus($data['articleids'], 0, '', '');
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Top succeed'),
                'data' => ''
            ));
        }


    }
}