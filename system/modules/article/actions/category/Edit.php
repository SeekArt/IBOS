<?php
namespace application\modules\article\actions\category;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;
use application\modules\article\model\ArticleCategory;

/*
 * 分类编辑接口
 */

class Edit extends Base
{

    public function run()
    {
        $pid = intval(Env::getRequest('pid'));
        $name = \CHtml::encode(trim(Env::getRequest('name')));
        $catid = intval(Env::getRequest('catid'));
        $aid = intval(Env::getRequest('aid'));
        if ($pid == $catid) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Parent and current can not be the same'),
                'data' => '',
            ), 'json');
        }
        $ret = ArticleCategory::model()->modify($catid, array('pid' => $pid, 'name' => $name, 'aid' => $aid));
        // 数据库没有改动时返回为0，所以不能用 !!$ret 判定
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $aid,
        ));
    }
}