<?php
namespace application\modules\article\actions\category;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;

/*
 * 分类移动接口
 */

class Move extends Base
{

    public function run()
    {

        $moveType = Env::getRequest('type');
        $pid = Env::getRequest('pid');
        $catid = Env::getRequest('catid');
        $ret = $this->getController()->_category->move($moveType, $catid, $pid);
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => !!$ret,
            'msg' => Ibos::lang('Move succeed'),
        ), 'json');

    }
}