<?php
namespace application\modules\article\actions\category;

use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;
use application\modules\article\model\ArticleCategory;

class Add extends Base
{
    public function run()
    {
        $data = $_POST;
        $pid = $data['pid'];
        $name = \CHtml::encode(trim($data['name']));
        //只有超级管理员才能添加分类具有那个审核流程
        if (!isset($data['aid'])) {
            $data['aid'] = 0;
        }
        $aid = intval($data['aid']);
        // 查询出最大的sort
        $cond = array('select' => 'sort', 'order' => "`sort` DESC");
        $sortRecord = ArticleCategory::model()->fetch($cond);
        if (empty($sortRecord)) {
            $sortId = 0;
        } else {
            $sortId = $sortRecord['sort'];
        }
        // 排序号默认在最大的基础上加1，方便上移下移操作
        $newSortId = $sortId + 1;
        $ret = ArticleCategory::model()->add(
            array(
                'sort' => $newSortId,
                'pid' => $pid,
                'name' => $name,
                'aid' => $aid
            ), true
        );
        $this->getController()->ajaxReturn(array(
            'isSuccess' => !!$ret,
            'id' => $ret,
            'url' => 'javascript:;',
            'aid' => $aid,
            'catid' => $ret,
            'target' => '_self',
        ), 'json');
    }
}