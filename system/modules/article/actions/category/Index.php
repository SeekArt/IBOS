<?php
namespace application\modules\article\actions\category;

use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;
use application\modules\article\model\ArticleCategory;

/*
 * 左侧栏分类数据接口
 */

class Index extends Base
{

    public function run()
    {

        $data = ArticleCategory::model()->fetchAll(array('order' => 'sort ASC'));
        $tree = $this->getController()->_category->getAjaxCategory($data);
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $tree,
        ));
    }
}