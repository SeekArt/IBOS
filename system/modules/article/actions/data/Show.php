<?php
namespace application\modules\article\actions\data;

use application\core\utils\Attach;
use application\core\utils\Ibos;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;
use application\modules\article\core\Article as ICArticle;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleReader;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\message\model\NotifyMessage;

/*
 * 查看新闻详细数据
 */

class Show extends Base
{
    public function run()
    {
        $data = $_POST;
        $articleId = intval($data['articleid']);
        $article = Article::model()->fetchByPk($articleId);
        if (empty($article)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('No permission or article not exists'),
                'data' => '',
            ));
        }
        $uid = Ibos::app()->user->uid;
        if (!$this->checkIsApprovaler($article, $uid) && !ArticleUtil::checkReadScope($uid, $article)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('You do not have permission to read the article'),
                'data' => '',
            ));
        }
        $articleData = ICArticle::getShowData($article, $uid);;
        ArticleReader::model()->addReader($articleId, $uid);
        Article::model()->updateClickCount($articleId, $articleData['clickcount']);
        $dashboardConfig = $this->getDashboardConfig();
        if ($articleData['type'] == parent::ARTICLE_TYPE_LINK) {
            $urlArr = parse_url($articleData['url']);
            $url = isset($urlArr['scheme']) ? $articleData['url'] : 'http://' . $articleData['url'];
            $articleData['url'] = $url;
        }
        $articleData['attach'] = array();
        if (isset($articleData['attachmentid']) && !empty($articleData['attachmentid'])) {
            $attach = Attach::getAttach($articleData['attachmentid']);
            foreach ($attach as $value) {
                array_push($articleData['attach'], $value);
            }
        }
        $output = array(
            'data' => $articleData,
            'isInstallEmail' => $this->getEmailInstalled(),
        );
        if ($article['status'] == 2) {
            $output['isApprovaler'] = $this->checkIsApprovaler($article, $uid);
        }
        $referrerUrl = Ibos::app()->request->getUrlReferrer();
        $hostInfoUrl = Ibos::app()->request->getHostInfo();
        $articleShowUrl = str_replace($hostInfoUrl, '', $referrerUrl);
        NotifyMessage::model()->setReadByUrl($uid, $articleShowUrl);
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $output,
        ));

    }
}