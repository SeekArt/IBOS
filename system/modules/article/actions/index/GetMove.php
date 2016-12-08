<?php
namespace application\modules\article\actions\index;

use application\core\utils\Ibos;

/*
获取新闻分类的数据，然后给前端去显示,不在是返回一个渲染好的视图了，而是返回的是数据中包含有html代码
*/

class GetMove extends Base
{
    public function run()
    {
        $move = $this->getCategoryOption();
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $move,
        ));
    }
}