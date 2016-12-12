<?php

/**
 * 移动端新闻控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端新闻控制器文件
 *
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: NewsController.php 7959 2016-08-19 08:55:42Z gzhyj $
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\article\core\Article as ICArticle;
use application\modules\article\model as model;
use application\modules\article\utils\Article as UtilsArticle;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\dashboard\model\ApprovalStep;
use application\modules\message\model\Comment as CommentModel;
use application\modules\mobile\components\Article;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;

class NewsController extends BaseController
{
    /**
     * @var string 当前 controller 对应的模块
     */
    protected $_module = 'article';

    /**
     * API 接口：新闻列表，用于获取主页面各项数据统计
     */
    public function actionIndex()
    {
        $catid = (int)Env::getRequest('catid');
        $type = Env::getRequest('type');
        $search = Env::getRequest('search');
        $page = (int)Env::getRequest("page");            // 第几页，从零开始
        $pageSize = (int)Env::getRequest("pagesize");    // 每页文章个数，默认为 10

        // 参数处理
        if ($page < 0) {
            // $page 的默认值为 0.（从第一页开始）
            $page = 0;
        }

        if (empty($pageSize)) {
            // pageSize 默认值为 10
            $pageSize = 10;
        }

        //手机端特殊判断,待定 todo::
        if (Mobile::dataType() == 'jsonp') {
            if ($catid == -1) {
                $type = 'new';
                $catid = 0;
            }
            if ($catid == -2) {
                $type = 'old';
                $catid = 0;
            }
        }
        $article = new Article();
        $articleList = $article->getList($type, $catid, $search, $pageSize, $page);
        if ($catid == 0) {
            $category = model\ArticleCategory::model()->fetchAll("pid = 0");
        } else {
            $category = model\ArticleCategory::model()->fetchAll("pid = {$catid}");
        }

        // 数据处理
        foreach ($articleList["datas"] as $key => $article) {
            // 1. $articleList["datas"][index]["uptime"] 返回的是一个标签，如：<span title="2016-7-7 16:15">昨天&nbsp;16:15</span>
            // 需要将其转换为时间戳格式
            $articleList["datas"][$key]["uptime"] = (int)UtilsArticle::formatToTimestamp($article["uptime"]);

            // 2. readstatus字段返回了两次，删除readStatus这个字段，保留readstatus
            if (isset($articleList["datas"][$key]["readStatus"])) {
                unset($articleList["datas"][$key]["readStatus"]);
            }
        }

        // 如果是获取未审核新闻列表
        // 添加审核流程数据
        if (UtilsArticle::TYPE_NOTALLOW === $type) {
            $articles = $articleList["datas"];
            $preData = ApprovalStep::model()->getPreApprovalStepData();
            foreach ($articles as &$article) {
                ApprovalStep::model()->getApprovalStepData($article, $preData);
            }

            foreach ($articleList["datas"] as $k => &$article) {
                $article["approver"] = $articles[$k]["approval"];
                $back = ApprovalRecord::model()->getLastBack($article['articleid']);
                $article["back"] = !empty($back) ? true : false;
            }
        }

        // 判断是否还有更多新闻
        $hasMore = false;
        $pagesData = $articleList["pages"];
        if ($pagesData["pageCount"] > $pagesData["page"] + 1) {
            $hasMore = true;
        }

        // 不获取子分类数据
        if (!empty($catid)) {
            foreach ($articleList["datas"] as $k => $v) {
                if ((int)$v["catid"] !== $catid) {
                    unset($articleList["datas"][$k]);
                }
            }
            $articleList["datas"] = array_values($articleList["datas"]);
        }

        $data = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
            "data" => $articleList["datas"],
            "pages" => $articleList["pages"],
            "category" => $category,
            "hasMore" => $hasMore,
        );
        return $this->ajaxReturn($data, Mobile::dataType());
    }


    /**
     * API 接口：返回新闻分类数据
     */
    public function actionCategory()
    {
        $retData = array(
            "isSuccess" => false,
            "msg" => "",
        );

        $article = new Article();
        $data = $article->getCategory($isFormat = false);

        $retData["isSuccess"] = true;
        $retData["msg"] = Ibos::lang('Call Success');
        $retData["data"] = $data;
        return $this->ajaxReturn($retData, Mobile::dataType());
    }

    /**
     * API 接口：返回具体某条新闻的数据
     */
    public function actionShow()
    {
        $uid = Ibos::app()->user->uid;
        $newsid = (int)Env::getRequest('articleid');

        // 返回数据
        $retData = array(
            "isSuccess" => false,
            "msg" => "",
        );

        // 参数处理
        if ($newsid <= 0) {
            $retData["msg"] = Ibos::lang("Lack of params");
            return $this->ajaxReturn($retData, Mobile::dataType());
        }

        // 如果新闻不存在
        if (!model\Article::model()->articleExit($newsid)) {
            $retData["msg"] = Ibos::lang("Article is not exists");
            return $this->ajaxReturn($retData, Mobile::dataType());
        }

        $article = new Article();
        $data = $article->getNews($newsid, $uid);
        if (!empty($data)) {
            if (!empty($data['attachmentid'])) {
                $data["attach"] = array_values(Attach::getAttach($data["attachmentid"]));
                $attachmentArr = explode(",", $data['attachmentid']);
            } else {
                $data["attach"] = array();
            }
        }

        // 数据处理
        // 1. 添加新闻查阅人员（阅读过该新闻的人）
        $readers = model\ArticleReader::model()->getReader($newsid);

        $readerArr = array();
        foreach ($readers as $readerGroup) {
            foreach ($readerGroup as $reader) {
                $readerArr[] = $reader;
            }
        }
        $data["readers"] = $readerArr;

        // 2. 获取该文章的审核过程数据
        $temp[0] = $data;
        $temp = ICArticle::handleApproval($temp);

        // 3. 返回当前用户权限（是否能编辑 AllowEdit、是否能删除 AllowDel）
        $temp = ICArticle::handlePurv($temp);

        $data = $temp[0];

        // 4. 将 $data 中的 addtime、uptime 索引转换为时间戳
        $data["addtime"] = (int)UtilsArticle::formatToTimestamp($data["addtime"]);
        $data["uptime"] = (int)UtilsArticle::formatToTimestamp($data["uptime"]);


        $retData["isSuccess"] = true;
        $retData["msg"] = Ibos::lang("Call Success");
        $retData["data"] = $data;
        return $this->ajaxReturn($retData, Mobile::dataType());
    }

    /**
     * API 接口：将某篇新闻标识为已读（单条）
     */
    public function actionRead()
    {
        // 返回数据
        $retData = array(
            "isSuccess" => false,
        );

        $data['articleid'] = (int)Env::getRequest('articleid');

        // 如果新闻不存在
        if (!model\Article::model()->articleExit($data["articleid"])) {
            $retData["msg"] = Ibos::lang("Article is not exists");
            return $this->ajaxReturn($retData, Mobile::dataType());
        }

        $data['uid'] = Ibos::app()->user->uid;
        $data['addtime'] = TIMESTAMP;
        $data['readername'] = User::model()->fetchRealnameByUid(Ibos::app()->user->uid);
        $artReader = model\ArticleReader::model()->add($data);
        if ($artReader > 0) {
            $retData["isSuccess"] = true;
            $retData["msg"] = Ibos::lang("Call Success");
        }

        return $this->ajaxReturn($retData, Mobile::dataType());
    }

    /**
     * API 接口：将全部未读新闻标识为已读
     */
    public function actionReadAll()
    {
        $retData = array(
            "isSuccess" => false,
            "msg" => "",
        );
        // 获取所有未读新闻
        $article = new Article();
        $articleList = $article->getList(UtilsArticle::TYPE_NEW);
        $articleListUnread = $articleList["datas"];

        // 更新状态失败次数
        $failedNum = 0;

        $data = array(
            "uid" => Ibos::app()->user->uid,
            "addtime" => TIMESTAMP,
            'readername' => User::model()->fetchRealnameByUid(Ibos::app()->user->uid),
        );

        foreach ($articleListUnread as $articleUnread) {
            $data["articleid"] = $articleUnread["articleid"];
            $artReader = model\ArticleReader::model()->add($data);

            if ($artReader < 0) {
                $failedNum++;
            }
        }

        if ($failedNum == 0) {
            $retData["isSuccess"] = true;
            $retData["msg"] = Ibos::lang("Call Success");
        } else {
            $retData["msg"] = Ibos::lang("Call Failed");
        }
        return $this->ajaxReturn($retData, Mobile::dataType());
    }

    /**
     * API 接口：置顶操作
     */
    public function actionTop()
    {
        $articleids = Env::getRequest('articleids');
        $topEndTime = Env::getRequest('topEndTime');

        switch (model\Article::model()->top($articleids, $topEndTime)) {
            case model\Article::TYPE_TOP_SUCCESS:
                // 置顶成功
                return $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Top Success')), Mobile::dataType());
                break;
            case model\Article::TYPE_UNTOP_SUCCESS:
                // 取消置顶成功
                return $this->ajaxReturn(array('isSuccess' => true, 'msg' => Ibos::lang('Unstuck Success')), Mobile::dataType());
                break;
            case model\Article::TYPE_TOP_FAILED:
                // 置顶失败
                return $this->ajaxReturn(array('isSuccess' => false, 'msg' => Ibos::lang('Top Failed')), Mobile::dataType());
                break;
            default:
                // unknown type
        }
    }

    /**
     * API 接口：移动操作
     */
    public function actionMove()
    {
        $retData = array(
            "isSuccess" => false,
            "msg" => "",
        );

        $articleids = (int)Env::getRequest('articleids');
        $catid = (int)Env::getRequest('catid');

        if (model\Article::model()->move($articleids, $catid)) {
            $retData["isSuccess"] = true;
            $retData["msg"] = Ibos::lang('Move Success');
            return $this->ajaxReturn($retData, Mobile::dataType());
        } else {
            $retData["msg"] = Ibos::lang("Move Failed");
            return $this->ajaxReturn($retData, Mobile::dataType());
        }
    }

    /**
     * API 接口：删除操作
     */
    public function actionDel()
    {
        $retData = array(
            "isSuccess" => false,
            "msg" => "",
        );
        $articleids = trim(Env::getRequest('articleids'), ',');
        $r = model\Article::model()->del($articleids);

        if ($r["status"]) {
            $retData["isSuccess"] = true;
            $retData["msg"] = Ibos::lang("Del Success");
            $retData["data"]["delAttachCount"] = $r["count"];
        } else {
            $retData["msg"] = Ibos::lang("Del Failed");
        }

        return $this->ajaxReturn($retData, Mobile::dataType());
    }

    /**
     * API 接口：审批通过
     */
    public function actionVerify()
    {
        $artIds = trim(Env::getRequest('articleids'), ',');
        $ret = model\Article::model()->verify($artIds);
        return $this->ajaxReturn($ret, Mobile::dataType());
    }


    /**
     * API 接口：审批退回
     */
    public function actionBack()
    {
        $artIds = Env::getRequest('articleids');
        $reason = Env::getRequest('reason');

        $res = model\Article::model()->back($artIds, $reason);
        return $this->ajaxReturn($res, Mobile::dataType());
    }

    /**
     * API 接口：查阅情况详情（获取阅读过该新闻的所有人员）
     */
    public function actionGetReaders()
    {
        $artileid = Env::getRequest("articleid");

        // 参数处理
        $artileid = (int)$artileid;

        $readers = model\ArticleReader::model()->getReader($artileid);

        $data = array(
            "isSuccess" => true,
            "msg" => Ibos::lang("Call Success"),
            "data" => $readers,
        );

        return $this->ajaxReturn($data, Mobile::dataType());
    }

    /**
     * API 接口：获取新闻评论
     */
    public function actionGetComments()
    {
        $retData = array(
            "isSuccess" => false,
            "msg" => "",
        );

        $articleid = (int)Env::getRequest("articleid");
        // 默认使用：cid asc
        $defaultOrder = "cid asc";
        $isDesc = Env::getRequest("isDesc");
        if (!empty($isDesc)) {
            $defaultOrder = "cid desc";
        }

        $articleIsExist = model\Article::model()->articleExit($articleid);
        if (false === $articleIsExist) {
            $retData["msg"] = Ibos::lang("Article is not exists");
            return $this->ajaxReturn($retData, Mobile::dataType());
        }

        $map = CommentModel::model()->getMapForGetCommentList($articleid);
        $commentList = CommentModel::model()->getCommentList($map, $defaultOrder);
        $retData["data"] = $commentList;
        $retData["msg"] = Ibos::lang("Call Success");
        $retData["isSuccess"] = true;
        return $this->ajaxReturn($retData, Mobile::dataType());
    }

    /**
     * API 接口：添加评论
     */
    public function actionAddComment()
    {
        // 参数处理
        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
        $rowId = filter_input(INPUT_POST, 'rowid', FILTER_SANITIZE_NUMBER_INT);

        $testArr = array($type, $rowId);
        if (in_array(null, $testArr) || in_array('', $testArr)) {
            return $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang('Error param')), Mobile::dataType());
        }

        // $type 参数只支持：comment（评论）和 reply（回复）
        if (!in_array($type, array("comment", "reply"))) {
            $msg = Ibos::lang("Error param") . "请检查 type 参数";
            return $this->ajaxReturn(array("isSuccess" => false, "msg" => $msg), Mobile::dataType());
        }
        // 如果评论类型为：comment
        if ("comment" === $type) {
            $_POST["module"] = "article";
            $_POST["table"] = "article";
        } elseif ("reply" === $type) {
            $_POST["module"] = "message";
            $_POST["table"] = "comment";
            $_POST["tocid"] = $rowId;
        }

        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\article\core\ArticleComment');
        return $widget->addComment();
    }

    /**
     * API 接口：删除评论
     */
    public function actionDelComment()
    {
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\article\core\ArticleComment');
        return $widget->delComment();
    }

    /**
     * API 接口：添加文章
     */
    public function actionAddArticle()
    {
        $uid = (int)Ibos::app()->user->uid;

        // 检查参数
        model\Article::model()->checkAddOrUpdateParams($_POST);

        $articleId = model\Article::model()->addArticle($uid, true);
        return $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Call Success"), "data" => array("articleid" => $articleId)), Mobile::dataType());
    }

    /**
     * API 接口：更新文章
     */
    public function actionUpdateArticle()
    {
        $articleid = Env::getRequest("articleid");

        // 检查参数
        if (empty($articleid)) {
            return $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Article is not exists")), Mobile::dataType());
        }
        model\Article::model()->checkAddOrUpdateParams($_POST);

        $flag = model\Article::model()->updateArtilce($articleid);
        if (false === $flag) {
            return $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Update Article Failed")), Mobile::dataType());
        }
        return $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Update Article Success")), Mobile::dataType());
    }

    /**
     * 返回路由映射表。如果需要实现权限验证，
     * 备注：需要在这里建立路由映射。
     *
     * @return array
     */
    public function routeMap()
    {
        return array(
            "mobile/news/index" => "article/default/index",
            "mobile/news/category" => "article/category/index",
            "mobile/news/show" => "article/default/show",
            "mobile/news/del" => "article/default/del",
            "mobile/news/updateArticle" => "article/default/edit",
            "mobile/news/verify" => "article/default/edit",
            "mobile/news/back" => "article/default/edit",
            "mobile/news/top" => "article/default/edit",
            "mobile/news/move" => "article/default/edit",
            "mobile/news/getcomments" => "article/comment/getcommentlist",
            "mobie/news/addcomment" => "article/comment/addcomment",
            "mobie/news/delcomment" => "article/comment/delcomment",
        );
    }
}
