<?php
namespace application\modules\article\actions\data;

use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;

/*
 * 获得后台配置的接口
 */

class Option extends Base
{

    public function run()
    {
        $cateOption = $this->getCategoryOption();
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $cateOption,
        ));
    }
}