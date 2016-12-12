<?php
namespace application\modules\article\actions\data;

use application\core\utils\DateTime;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\actions\index\Base;
use application\modules\article\core\Article as ICArticle;
use application\modules\article\model\Article;
use application\modules\article\model\ArticleCategory;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\user\model\User;

/*
 * 首页，我的投稿，我的审核的首页数据接口
 */

class Index extends Base
{
    public function run()
    {
        $data['start'] = Env::getRequest('start');
        $data['length'] = Env::getRequest('length');
        $data['type'] = Env::getRequest('type');
        $data['catid'] = Env::getRequest('catid');
        $data['search'] = Env::getRequest('search');
        $data['draw'] = Env::getRequest('draw');
        $uid = Ibos::app()->user->uid;
        $childCatIds = '';
        if (isset($data['catid'])) {
            $this->catid = $data['catid'];
            $childCatIds = ArticleCategory::model()->fetchCatidByPid($this->catid, true);
        }
        $data['type'] = empty($data['type']) ? 'all' : $data['type'];
        $this->type = $data['type'];
        $default = array(self::TYPE_ALL, self::TYPE_READ, self::TYPE_UNREAD);//首页类型
        $publish = array(self::TYPE_DRAFT, self::TYPE_PUBLISH, self::TYPE_APPROVAL, self::TYPE_REBACK_TO);//我的投稿类型
        $verify = array(self::TYPE_WAIT, self::TYPE_PASSED, self::TYPE_REBACK_FROM);//我的审核类型
        if (!in_array($this->type, $default) && !in_array($this->type, $publish) && !in_array($this->type, $verify)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('No type'),
                'data' => $data,
            ));
        }
        $data['search']['value'] = empty($data['search']) ? '' : $data['search']['value'];
        if (in_array($this->type, $default)) {
            $output = $this->defaultList($this->type, $uid, $childCatIds, $data['start'], $data['length'], $data['search']['value']);
        } elseif (in_array($this->type, $publish)) {
            $output = $this->publishList($this->type, $uid, $childCatIds, $data['start'], $data['length'], $data['search']['value']);
        } elseif (in_array($this->type, $verify)) {
            $output = $this->verifyList($this->type, $uid, $childCatIds, $data['start'], $data['length'], $data['search']['value']);
        }
        Ibos::app()->controller->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '',
            'data' => $output['list'],
            'draw' => $data['draw'],
            'recordsFiltered' => $output['count'],
        ));
    }

    /*
     * 信息中心首页接口列表数据，主要有全部，已读和未读
     * @param string $type 类型：all,read,unread
     * @param integer $uid 当前用户uid
     * @param string $childCatIds 分类ID，包括下属分类ID
     * @param integer $offset 开始数
     * @param integer $limit 偏移量
     * @param string $keyword 关键字
     */
    private function defaultList($type, $uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType($type, $uid, $childCatIds, $offset, $limit, $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }

    /*
    * 信息中心我的投稿接口列表数据，主要有草稿箱，已发布，审核中，被退回
    * @param string $type 类型：draft,publish,approval,reback_to
    * @param integer $uid 当前用户uid
    * @param string $childCatIds 分类ID，包括下属分类ID
    * @param integer $offset 开始数
    * @param integer $limit 偏移量
    * @param string $keyword 关键字
    */
    private function publishList($type, $uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        if ($type == self::TYPE_DRAFT) {
            $output =  $this->draft($uid, $childCatIds, $offset, $limit, $keyword = "");
        } elseif ($type == self::TYPE_PUBLISH) {
            $output = $this->publish($uid, $childCatIds, $offset, $limit, $keyword = "");
        } elseif ($type == self::TYPE_APPROVAL) {
            $output = $this->approval($uid, $childCatIds, $offset, $limit, $keyword = "");
        } elseif ($type == self::TYPE_REBACK_TO) {
            $output = $this->rebackTo($uid, $childCatIds, $offset, $limit, $keyword = "");
        }
        return $output;
    }

    //草稿箱列表数据
    private function draft($uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType(self::TYPE_DRAFT, $uid, $childCatIds, $offset, $limit,
            $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        foreach ($list as $value) {
            $value['categoryName'] = ArticleCategory::model()->fetchCateNameByCatid($value['catid']);
        }
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }

    //已发布列表数据
    private function publish($uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType(self::TYPE_PUBLISH, $uid, $childCatIds, $offset, $limit,
            $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }

    //审核中列表数据
    private function approval($uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType(self::TYPE_APPROVAL, $uid, $childCatIds, $offset,
            $limit, $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        $length = count($list);
        for ($i = 0; $i < $length; $i++) {//得到下一步骤需要审核人的真实姓名
            $articleid = $list[$i]['articleid'];
            $flow = ApprovalRecord::model()->fetchAll(array(
                'condition' => "module = 'article' AND relateid = {$articleid} AND status != 3 AND status != 0",
                'order' => 'time ASC',
            ));
            $passFlow = array();
            foreach ($flow as $value) {
                $passFlow[$value['step']]['vetifyName'] = User::model()->fetchRealnameByUid($value['uid']);
                $passFlow[$value['step']]['status'] = 1;
            }
            //得到下一步骤需要审核的人,以步骤为键，真实名为值
            $last = ApprovalRecord::model()->fetchLastStep($articleid);
            $currentApprover = $last['step'] + 1;
            $list[$i]['current'] = array(
                'step' => $currentApprover,
                'currentName' => User::model()->fetchRealnamesByUids($list[$i]['approver']),
            );
            //得打还未审核的人的信息和步骤
            $noVerify = array();
            $article = Article::model()->fetchByPk($articleid);
            $category = ArticleCategory::model()->fetchByPk($article['catid']);
            $noVerifyList = ApprovalRecord::model()->getNotAllow($articleid, $category['aid']);
            //status == 4表示待审核
            foreach ($noVerifyList as $key => $value) {
                $noVerify[$key]['vetifyName'] = User::model()->fetchRealnamesByUids($value);
                $noVerify[$key]['status'] = 4;
            }
            //合并已经通过的数组和未审核数组
            $list[$i]['approver'] = array_merge($passFlow, $noVerify);
            //当前步骤距离现在的停留时间
            $list[$i]['stoptime'] = DateTime::getTime(TIMESTAMP - $last['time']);
        }
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }

    //被退回列表数据
    private function rebackTo($uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType(self::TYPE_REBACK_TO, $uid, $childCatIds, $offset, $limit,
            $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        for ($i = 0; $i < count($list); $i++) {
            $back = ApprovalRecord::model()->getLastBack($list[$i]['articleid']);
            $list[$i]['backtime'] = date('Y-m-d H:i', $back['time']);
            $list[$i]['backstep'] = array(
                'step' => $back['step'],
                'backname' => User::model()->fetchRealnameByUid($back['uid']),
            );
            $list[$i]['backreason'] = $back['reason'];
        }
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }
    /*
   * 信息中心我的审核接口列表数据，主要有未审核，我已通过，被我退回
   * @param string $type 类型：wait,passed,reback_from
   * @param integer $uid 当前用户uid
   * @param string $childCatIds 分类ID，包括下属分类ID
   * @param integer $offset 开始数
   * @param integer $limit 偏移量
   * @param string $keyword 关键字
   */
    private function verifyList($type, $uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        if ($type == self::TYPE_WAIT) {
            $output = $this->wait($uid, $childCatIds, $offset, $limit, $keyword = "");
        } elseif ($type == self::TYPE_PASSED) {
            $output = $this->passed($uid, $childCatIds, $offset, $limit, $keyword = "");
        } elseif ($type == self::TYPE_REBACK_FROM) {
            $output = $this->rebackFrom($uid, $childCatIds, $offset, $limit, $keyword = "");
        }
        return $output;
    }

    //未审核列表数据
    private function wait($uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType(self::TYPE_WAIT, $uid, $childCatIds, $offset,
            $limit, $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        $length = count($list);
        for ($i = 0; $i < $length; $i++) {
            $last = ApprovalRecord::model()->fetchLastStep($list[$i]['articleid']);
            $list[$i]['stoptime'] = DateTime::getTime(TIMESTAMP - $last['time']);
        }
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }

    //我已通过列表数据
    private function passed($uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType(self::TYPE_PASSED, $uid, $childCatIds, $offset, $limit,
            $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        for ($i = 0; $i < count($list); $i++) {
            $record = ApprovalRecord::model()->fetch('uid =:uid AND module = :module AND status IN (1,2) AND relateid = :relateid',
                array(':uid' => $uid, ':module' => 'article', ':relateid' => $list[$i]['articleid']));
            $list[$i]['passtime'] = date('Y-m-d H:i:s', $record['time']);
        }
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }

    //被我退回列表数据
    private function rebackFrom($uid, $childCatIds, $offset, $limit, $keyword = "")
    {
        $articleList = Article::model()->getArticleListByType(self::TYPE_REBACK_FROM, $uid, $childCatIds, $offset, $limit,
            $keyword);
        $list = ICArticle::getListData($articleList['list'], $uid);
        for ($i = 0; $i < count($list); $i++) {
            $record = ApprovalRecord::model()->fetch('uid =:uid AND module = :module AND status = :status AND relateid = :relateid',
                array(':uid' => $uid, ':module' => 'article', ':status' => 0, ':relateid' => $list[$i]['articleid']));
            $list[$i]['passtime'] = date('Y-m-d H:i:s', $record['time']);
        }
        $output['list'] = $list;
        $output['count'] = $articleList['count'];
        return $output;
    }
}