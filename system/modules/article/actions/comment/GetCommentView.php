<?php
namespace application\modules\article\actions\comment;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;

class GetCommentView extends Base
{

    public function run()
    {
        $articleid = Env::getRequest('articleid');
        $article = Article::model()->fetchByPk($articleid);
        $uid = Ibos::app()->user->uid;
        $url = Ibos::app()->urlManager->createUrl('article/default/show', array(
            'articleid' => $articleid,
        ));
        $this->controller->widget('application\modules\article\core\ArticleComment', array(
            'module' => 'article',
            'table' => 'article',
            'attributes' => array(
                'rowid' => $articleid,
                'moduleuid' => $uid,
                'touid' => $article['author'],
                'module_rowid' => $articleid,
                'module_table' => 'article',
                'url' => $url,
                'detail' => Ibos::lang('Comment my article', '', array(
                    '{url}' => $url,
                    '{title}' => StringUtil::cutStr($article['subject'], 50)
                )),
            )
        ));
    }
}