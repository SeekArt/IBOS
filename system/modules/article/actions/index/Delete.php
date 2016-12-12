<?php
namespace application\modules\article\actions\index;

use application\core\utils\Attach;
use application\core\utils\Ibos;
use application\modules\article\core\Article as ICArticle;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleApproval;
use application\modules\article\model\ArticleCategory;
use application\modules\article\model\ArticlePicture;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\vote\model\Vote;

/*
 * 删除新闻接口，批量删除和单个删除，由于批量删除时有新闻的删除权限的问题，因此要分开做判断
 */

class Delete extends Base
{
    public function run()
    {
        $data = $_POST;
        $uid = Ibos::app()->user->uid;
        $articleids = trim($data['articleids'], ',');
        $catid = '';
        if (isset($data['catid'])) {
            $this->catid = $data['catid'];
            $catid = ArticleCategory::model()->fetchCatidByPid($this->catid, true);
        }
        $lists = Article::model()->getArticleList($articleids, $catid);
        $listId = array();//删除的全部ID
        $deleteId = array();//有权限删除的ID
        foreach ($lists as $list) {
            $listId[] = $list['articleid'];
        }
        $deleteList = ICArticle::getListData($lists, $uid);
        foreach ($deleteList as $value) {
            if ($value['allowDel']) {
                $deleteId[] = $value['articleid'];
            }
        }
        $noDelete = array_diff($listId, $deleteId);//得到没有权限不能删除的ID
        if (empty($noDelete)) {
            $this->deleteIds($articleids);
            ApprovalRecord::model()->deleteByRelateid($articleids);//删除审核记录
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Del succeed', 'message'),
                'data' => $data,
            ));
        } else {
            $articleids = implode(',', $deleteId);
            ApprovalRecord::model()->deleteByRelateid($articleids);//删除审核记录
            $this->deleteIds($articleids);
            $count = count($noDelete);
            Ibos::app()->controller->ajaxRetrun(array(
                'isSuccess' => true,
                'msg' => "有{$count}不能删除，你的权限不够",
                'data' => $data,
            ));
        }
    }

    /*
     * 删除新闻
     * @param string $articleids 1,2,3.....
     */
    private function deleteIds($articleids)
    {
        $attachmentids = '';
        $attachmentIdArr = Article::model()->fetchAllFieldValueByArticleids('attachmentid', $articleids);
        foreach ($attachmentIdArr as $attachmentid) {
            if (!empty($attachmentid)) {
                $attachmentids .= $attachmentid . ',';
            }
        }
        $count = 0;
        if (!empty($attachmentids)) {
            $splitArray = explode(',', trim($attachmentids, ','));
            $attachmentidArray = array_unique($splitArray);
            $attachmentids = implode(',', $attachmentidArray);
            $count = Attach::delAttach($attachmentids);
        }
        if ($this->getVoteInstalled()) {
            Vote::model()->deleteAllByRelationIdsAndModule($articleids, 'article');
        }
        //刪除图片
        ArticlePicture::model()->deleteAllByArticleIds($articleids);
        //删除新闻
        Article::model()->deleteAllByArticleIds($articleids);
        //删除待审核记录
        ArticleApproval::model()->deleteByArtIds($articleids);
    }
}