<?php
namespace application\modules\article\actions\data;

use application\core\utils\Attach;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\actions\index\ApiInterface;
use application\modules\article\actions\index\Base;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\article\model\ArticlePicture;
use application\modules\dashboard\model\ApprovalRecord;

/*
 * 新闻编辑接口,由于需要权限的问题，所以现在不能用op（操作方法）来区别
 */

class Edit extends Base
{

    public function run()
    {

        $data = $_POST;
        $articleId = $data['articleid'];
        $uid = Ibos::app()->user->uid;
        $data = Article::model()->fetchByPk($articleId);
        $data['subject'] = html_entity_decode($data['subject']);
        //新闻为发布状态的话，再次去编辑，此时如果新闻有审核记录，应该把审核记录删除，
        //如果为其他的状态，不应删除审核记录，编辑人员可以看到之前的审核信息
        if ($data['status'] == 1) {
            ApprovalRecord::model()->deleteApproval($articleId);
        }
        if (empty($data)) {
            $this->controller->isSuccess = false;
            $this->msg = Ibos::lang('No permission or article not exists');
            $this->Output = $data;
        }
        //选人框
        $data['publishscope'] = StringUtil::joinSelectBoxValue($data['deptid'], $data['positionid'], $data['uid'],
            $data['roleid']);
        //是否是免审人能直接发布
        $allowPublish = ArticleCategory::model()->checkIsAllowPublish($data['catid'], $uid);
        //主要是得到对应分类是否有审核的级别，如果没有审核则返回0，有审核则返回对应的审核层数
        $aitVerify = ArticleCategory::model()->getLevelByCatid($data['catid']);
        // 图片新闻
        if ($data['type'] == parent::ARTICLE_TYPE_PICTURE) {
            $pictureData = ArticlePicture::model()->fetchPictureByArticleId($articleId);
            $data['picids'] = '';
            foreach ($pictureData as $k => $value) {
                $value['filepath'] = File::imageName($value['filepath']);
                $data['picids'] .= $value['aid'] . ',';
            }
            $data['picids'] = substr($data['picids'], 0, -1);
        }
        $output = array(
            'data' => $data,
            'allowPublish' => $allowPublish,
            'aitVerify' => $aitVerify,
        );
        if (isset($pictureData)) {
            $output['pictureData'] = $pictureData;
        }
        //显示附件
        if (!empty($data['attachmentid'])) {
            $output['attach'] = Attach::getAttach($data['attachmentid']);
            $attach = array();
            foreach ($output['attach'] as $value) {
                array_push($attach, $value);
            }
            $output['attach'] = $attach;
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $output,
        ));

    }
}