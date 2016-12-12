<?php
namespace application\modules\article\actions\category;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;
use application\modules\article\model\ArticleCategory;

/*
 * 分类删除接口
 */

class Delete extends Base
{

    public function run()
    {
        $catid = Env::getRequest('catid');
        // 判断顶级分类少于一个不给删除
        $category = ArticleCategory::model()->fetchByPk($catid);
        $supCategoryNum = ArticleCategory::model()->countByAttributes(array('pid' => 0));
        if (!empty($category) && $category['pid'] == 0 && $supCategoryNum == 1) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Leave at least a Category'),
            ), 'json');
        }
        $ret = $this->getController()->_category->delete($catid);
        if ($ret == -1) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Contents under this classification only be deleted when no content'),
            ), 'json');
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => !!$ret,
            'msg' => Ibos::lang('Delete succeed'),
        ), 'json');
    }

}