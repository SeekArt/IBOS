<?php
namespace application\modules\article\actions\publish;

use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\message\model\Notify;
use application\modules\user\model\User;

/*
 *催办接口
 */

class Call extends Base
{

    public function run()
    {
        $data = $_POST;
        $articleids = $data['articleids'];
        $uid = Ibos::app()->user->uid;
        $articleids = explode(',', $articleids);
        foreach ($articleids as $articleid) {
            $article = Article::model()->fetchByPk($articleid);
            $sender = User::model()->fetchRealnameByUid($article['author']);
            $categoryName = ArticleCategory::model()->fetchCateNameByCatid($article['catid']);
            $approver = explode(',', $article['approver']);
            $config = array(
                '{sender}' => $sender,
                '{subject}' => $article['subject'],
                '{category}' => $categoryName,
                '{content}' => Ibos::app()->controller->renderPartial('remindcontent', array(
                    'article' => $article,
                    'author' => $sender,
                ), true),
                '{url}' => Ibos::app()->controller->createUrl('default/show',array('articleid' => $articleid)),
                'id' => $articleid,
            );
            Notify::model()->sendNotify($approver, 'article_verify_message', $config, $uid);
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Call suceess'),
                'data' => '',
            ));
        }

    }
}