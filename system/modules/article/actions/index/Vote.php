<?php
namespace application\modules\article\actions\index;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\vote\components\Vote as VoteUtil;

/*
 * 投票显示页接口，主要是返回对应投票视图的html代码字符串，前端可以通过ajax请求这个字符串放在流浪器解析到不同的视图
 */

class Vote extends Base
{

    public function run()
    {
        $view = Env::getRequest('view');
        $returnView = VoteUtil::getView($view);
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $returnView,
        ));
    }
}