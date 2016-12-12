<?php

/**
 * 移动端模块------ 文章类组件文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author Aeolus <Aeolus@ibos.com.cn>
 */
/**
 * 移动端模块------ 文章评论类组件
 * @package application.modules.mobile.components
 * @version $Id: IMCArticle.php 581 2013-06-13 09:50:04Z Aeolus $
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\mobile\components;

use application\core\components\Category;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\core\Article as ICArticle;
use application\modules\article\model\Article as ArticleModel;
use application\modules\article\model\ArticleCategory;
use application\modules\article\model\ArticlePicture;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\user\model\User;
use application\modules\article\model\ArticleReader;

class Article
{

    /**
     * 分类id
     * @var integer
     * @access protected
     */
    protected $catid = 0;

    /**
     * 条件
     * @var string
     * @access protected
     */
    protected $condition = '';

    /**
     * 是否有安装新闻模块
     * @return boolean
     */
    protected function getArticleInstalled()
    {
        //Yii::import( 'application.modules.article.components.*' );
        //$installed = new ICArticle();
        //$installed->setInit( 'vote' );
        //return $installed->getInit();
    }

    public function getList($type = 1, $catid = 0, $search = "", $pageSize = 10, $page = 0)
    {
        //DEBUG 测试 只是显示新闻数据
        $uid = Ibos::app()->user->uid;
        $childCatIds = '';
        if (!empty($catid)) {
            $this->catid = $catid;
            $childCatIds = ArticleCategory::model()->fetchCatidByPid($this->catid, true);
        }
        if (!empty($search)) {
            $this->condition = "subject like '%$search%'";
        }
        $this->condition = ArticleUtil::joinListCondition($type, $uid, $childCatIds, $this->condition);

        $datas = ArticleModel::model()->fetchAllAndPage($this->condition, $pageSize, $page);

        $articleList = ICArticle::getListData($datas['datas'], $uid);
        $params = array(
            'pages' => $datas['pages'],
            'datas' => $articleList
        );

        $needParams = array(
            "commentcount",         // 评论数
            "type",                 // 内容类型
        );

        foreach ($articleList as $key => $value) {
            $value['content'] = StringUtil::cutStr(strip_tags($value['content']), 30);
            // 清空空字段（排除在 $needParms 中的字段）
            foreach ($articleList[$key] as $k => $v) {
                if (empty($v) && !in_array($k, $needParams)) {
                    unset($articleList[$key][$k]);
                }
            }
            $articleList[$key]['readstatus'] = ($articleList[$key]['readStatus'] == 1);
        }

        $return['datas'] = $articleList;
        $return['pages'] = array(
            'pageCount' => $datas['pages']->getPageCount(),
            'page' => $datas['pages']->getCurrentPage(),
            'pageSize' => $datas['pages']->getPageSize()
        );
        return $return;
    }

    public function getCategory($isFormat = true)
    {
        $category = new Category('application\modules\article\model\ArticleCategory');
        $data = $category->getData();
        $data = array_values($data);

        if ($isFormat) {
            $format = "<li> <a href='#news' onclick='news.loadList(\$catid)'>\$spacer<i class='ao-file'></i>\$name</a> </li>";
            $return = StringUtil::getTree($data, $format, 0, '&nbsp;&nbsp;&nbsp;&nbsp;', array('', '', ''));
        } else {
            $return = $data;
        }

        return $return;
    }


    public function getNews($id, $uid)
    {
        $article = ArticleModel::model()->fetchByPk($id);
        $attribute = ICArticle::getShowData($article,$uid);
        if (isset($attribute['author'])) {
            $attribute['author'] = User::model()->fetchRealnameByUid($attribute['author']);
        }
        if ($attribute['type'] == 1) {
            $attribute['pictureData'] = ArticlePicture::model()->fetchPictureByArticleId($id);
        }

        $uid = Ibos::app()->user->uid;
        // 点击数递增
        ArticleModel::model()->updateClickCount($id, $attribute["clickcount"]);

        // 为当前文章添加阅读者
        ArticleReader::model()->addReader($id, $uid);
        return $attribute;
    }

}
