<?php
namespace application\modules\article\actions\index;

use application\core\utils\ArrayUtil;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\model\ArticleCategory;
use application\modules\article\model\ArticleReader;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\message\model\NotifyMessage;

/*
 * 全部设为已读的接口
 */

class Read extends Base
{

    public function run()
    {
        $articleid = intval(Env::getRequest('articleid'));
        if ($articleid != 0) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Param error'),
                'data' => '',
            ));
        }
        //得到全部分类的ID
        $category = ArticleCategory::model()->fetchAll();
        $categotyIds = ArrayUtil::getColumn($category, 'catid');
        $childCatIds = implode(',', $categotyIds);
        $uid = Ibos::app()->user->uid;
        $condition = ArticleUtil::getListCondition(self::TYPE_UNREAD, $uid, $childCatIds, '');
        $articleList = Ibos::app()->db->createCommand()
            ->from('{{article}}')
            ->where($condition)
            ->queryAll();
        $articleids = ArrayUtil::getColumn($articleList, 'articleid');
        foreach ($articleids as $articleid) {
            ArticleReader::model()->addReader($articleid, $uid);
            $articleShowUrl = Ibos::app()->controller->createUrl('default/show', array('articleid' => $articleid));
            NotifyMessage::model()->setReadByUrl($uid, $articleShowUrl);
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Read all'),
            'data' => '',
        ));
    }
}