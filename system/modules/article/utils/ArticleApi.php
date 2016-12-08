<?php

namespace application\modules\article\utils;

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleReader;

class ArticleApi
{

    public $iconNormalStyle = array(
        0 => 'o-art-normal',
        1 => 'o-art-pic',
        2 => 'o-art-vote'
    );
    public $iconReadStyle = array(
        0 => 'o-art-normal-gray',
        1 => 'o-art-pic-gray',
        2 => 'o-art-vote-gray'
    );

    /**
     * 渲染首页视图
     * @return type
     */
    public function renderIndex()
    {
        $data = array(
            'articles' => $this->loadNewArticle(),
            'lang' => Ibos::getLangSource('article.default'),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('article')
        );
        $viewAlias = 'application.modules.article.views.indexapi.article';
        $return['article/article'] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    /**
     * 获取最新新闻条数
     * @return integer
     */
    public function loadNew()
    {
        $uid = Ibos::app()->user->uid;
        $allDeptId = Ibos::app()->user->alldeptid . '';
        $allPosId = Ibos::app()->user->allposid . '';

        $deptCondition = '';
        $deptIdArr = explode(',', $allDeptId);
        if (count($deptIdArr) > 0) {
            foreach ($deptIdArr as $deptId) {
                $deptCondition .= "FIND_IN_SET('$deptId',deptid) OR ";
            }
            $deptCondition = substr($deptCondition, 0, -4);
        } else {
            $deptCondition = "FIND_IN_SET('',deptid)";
        }
        $condition = " ( ((deptid='alldept' OR $deptCondition OR FIND_IN_SET('{$allPosId}',positionid) OR FIND_IN_SET('{$uid}',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='{$uid}') OR (approver='{$uid}')) ) AND `status`='1'";
        $arts = Article::model()->fetchAll($condition);
        $artIds = Convert::getSubByKey($arts, 'articleid');
        $readedIds = ArticleReader::model()->fetchAll(sprintf("uid=%d", $uid));
        $rArtIds = Convert::getSubByKey($readedIds, 'articleid');
        $count = count(array_diff($artIds, $rArtIds));
        return intval($count);
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting()
    {
        return array(
            'name' => 'article/article',
            'title' => Ibos::lang('Information center', 'article.default'),
            'style' => 'in-article'
        );
    }

    /**
     * 加载指定$num条的新闻内容
     * @param integer $num
     * @return array
     */
    private function loadNewArticle($num = 3)
    {
        $uid = Ibos::app()->user->uid;
        $allDeptId = Ibos::app()->user->alldeptid . '';
        $allPosId = Ibos::app()->user->allposid . '';

        $deptCondition = '';
        $deptIdArr = explode(',', $allDeptId);
        if (count($deptIdArr) > 0) {
            foreach ($deptIdArr as $deptId) {
                $deptCondition .= "FIND_IN_SET('$deptId',deptid) OR ";
            }
            $deptCondition = substr($deptCondition, 0, -4);
        } else {
            $deptCondition = "FIND_IN_SET('',deptid)";
        }

        $condition = " ( ((deptid='alldept' OR $deptCondition OR FIND_IN_SET('{$allPosId}',positionid) OR FIND_IN_SET('{$uid}',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='{$uid}') OR (approver='{$uid}')) ) AND `status`='1'";
        $criteria = array(
            'select' => 'articleid,subject,content,addtime,type',
            'condition' => $condition,
            'order' => '`istop` DESC, `addtime` DESC',
            'offset' => 0,
            'limit' => $num
        );
        $articles = Article::model()->fetchAll($criteria);
        if (!empty($articles)) {
            foreach ($articles as &$article) {
                $read = ArticleReader::model()->fetchByAttributes(array(
                    'articleid' => $article['articleid'],
                    'uid' => $uid
                ));
                $readStatus = $read ? 1 : 0;
                if ($readStatus) {
                    $article['iconStyle'] = $this->iconReadStyle[$article['type']];
                } else {
                    $article['iconStyle'] = $this->iconNormalStyle[$article['type']];
                }
            }
        }
        return $articles;
    }

}
