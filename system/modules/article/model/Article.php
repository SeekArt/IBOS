<?php

/**
 * 信息中心模块------ article表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author Ring <Ring@ibos.com.cn>
 */

/**
 * 信息中心模块------  article表的数据层操作类，继承ICModel
 * @package application.modules.article.model
 * @version $Id: Article.php 8877 2016-10-31 01:34:25Z php_lwd $
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\article\model;

use application\core\components\Category;
use application\core\model\Model;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\article\actions\index\Base;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\dashboard\model\Approval;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\dashboard\model\ApprovalStep;
use application\modules\dashboard\utils\Dashboard;
use application\modules\message\model\Notify;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\vote\components\Vote as VoteComponent;
use application\modules\vote\model\Vote;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;
use CDbCriteria;
use CPagination;

class Article extends Model
{
    /**
     * 置顶成功
     */
    const TYPE_TOP_SUCCESS = 1;

    /**
     * 置顶失败
     */
    const TYPE_TOP_FAILED = 2;

    /**
     * 取消置顶成功
     */
    const TYPE_UNTOP_SUCCESS = 3;

    /**
     * 投票状态：开启
     */
    const TYPE_VOTE_STATUS_OPEN = 1;

    /**
     * 投票状态：关闭
     */
    const TYPE_VOTE_STATUS_CLOSE = 0;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{article}}';
    }

    /**
     * 根据条件，查询出对应数据，返回一个数组，其中数组元素中的pages为翻页所需的数据，datas为列表所需的数据
     * <pre>
     *        array( 'pages' => $pages, 'datas' => $datas );
     * </pre>
     * @param string $conditions 查询条件 default='';
     * @param integer $pageSize default=null;每页显示的数据条数
     * @return array
     */
    public function fetchAllAndPage($conditions = '', $pageSize = null, $page = null)
    {
        $conditionArray = array('condition' => $conditions, 'order' => 'istop DESC,toptime DESC,addtime DESC');
        $criteria = new CDbCriteria();
        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }
        $count = $this->count($criteria);
        $pages = new CPagination($count);
        $everyPage = is_null($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pages->setPageSize(intval($everyPage));
        // 设置当前页码
        if (is_integer($page) && $page >= 0) {
            $pages->setCurrentPage($page);
        }
        $pages->applyLimit($criteria);
        $datas = $this->fetchAll($criteria);

        // 将时间戳强制转换为整形
        foreach ($datas as $k => $v) {
            $datas[$k]["addtime"] = (int)$v["addtime"];
            $datas[$k]["uptime"] = (int)$v["uptime"];
        }

        return array('pages' => $pages, 'datas' => $datas);
    }

    /**
     * 根据articleid获取一个指定字段的所有值
     * @param String $field 字段名
     * @param integer $articleids 文章ids
     * @return array
     */
    public function fetchAllFieldValueByArticleids($field, $articleids)
    {
        $returnArray = array();
        $articleids = is_array($articleids) ? implode(',', $articleids) : $articleids;
        $rows = $this->fetchAll(array('select' => $field, 'condition' => "FIND_IN_SET(articleid,'{$articleids}')"));
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $returnArray[] = $row[$field];
            }
        }
        return $returnArray;
    }

    /**
     * 取消已过期的置顶
     * @return boolean
     */
    public function cancelTop()
    {
        $result = $this->updateAll(array(
            'istop' => 0,
            'toptime' => 0,
            'topendtime' => 0
        ), 'istop = 1 AND topendtime<' . TIMESTAMP);
        return $result;
    }

    /**
     * 设置/取消置顶
     * @param string $ids 要设置或取消的id
     * @param integer $isTop 状态
     * @param integer $topTime 置顶时间
     * @param integer $topEndTime 置顶结束时间
     * @return boolean
     */
    public function updateTopStatus($ids, $isTop, $topTime, $topEndTime)
    {
        $condition = array('istop' => $isTop, 'toptime' => $topTime, 'topendtime' => $topEndTime);
        return $this->updateAll($condition, "articleid IN ($ids)");
    }

    /**
     * 取消已过期高亮
     * @return boolean
     */
    public function updateIsOverHighLight()
    {
        $result = $this->updateAll(array(
            'ishighlight' => 0,
            'highlightstyle' => '',
            'highlightendtime' => '0'
        ), 'ishighlight = 1 AND highlightendtime<' . TIMESTAMP);

        return $result;
    }

    /**
     * 设置/取消高亮
     * @param string $ids 要设置或取消的id
     * @param integer $ishighlight 状态
     * @param string $highlightstyle 高亮样式
     * @param integer $highlightendtime 高亮结束时间
     * @return boolean
     */
    public function updateHighlightStatus($ids, $ishighlight, $highlightstyle, $highlightendtime)
    {
        $condition = array(
            'ishighlight' => $ishighlight,
            'highlightstyle' => $highlightstyle,
            'highlightendtime' => $highlightendtime
        );
        return $this->updateAll($condition, "articleid IN ($ids)");
    }

    /**
     * 根据文章id，删除所有符合的数据
     * @param string $ids
     * @param string $catid
     * @return integer
     */
    public function deleteAllByArticleIds($ids)
    {
        return $this->deleteAll("articleid IN ($ids)");
    }

    /**
     * 根据文章ids更新所有符合条件的文章的状态和审批人
     * @param string $ids 文章ids
     * @param integer $approver 审批人uid
     * @param string $status 状态，默认为1公开
     * @return integer 被更新的行数
     */
    public function updateAllStatusAndApproverByPks($ids, $approver, $status = 1)
    {
        return $this->updateAll(array('status' => $status, 'approver' => $approver, 'uptime' => TIMESTAMP),
            "articleid IN ($ids)");
    }

    /**
     * 根据文章ids，更新所有符合条件的分类
     * @param string $ids
     * @param integer $catid
     * @return integer
     */
    public function updateAllCatidByArticleIds($ids, $catid)
    {
        return $this->updateAll(array('catid' => $catid), "articleid IN ($ids)");
    }

    /**
     * 更新文章点击数量
     * @param integer $id 文章id
     * @param integer $clickCount 点击数，默认为0
     * @return integer
     */
    public function updateClickCount($id, $clickCount = 0)
    {
        if (empty($clickCount)) {
            $record = parent::fetchByPk($id);
            $clickCount = $record['clickcount'];
        }
        return parent::modify($id, array('clickcount' => $clickCount + 1));
    }

    /**
     * 兼容Source接口
     * @param integer $id 资源ID
     * @return array
     */
    public function getSourceInfo($id)
    {
        $info = $this->fetchByPk($id);
        return $info;
    }

    /**
     * 根据分类id获取某个uid的未审核文章id
     * @param mixed $catid 分类id
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchUnApprovalArtIds($catid, $uid)
    {

        $backArtIds = ArticleBack::model()->fetchAllBackArtId();
        $backArtIdStr = implode(',', $backArtIds);
        $backCondition = empty($backArtIdStr) ? '' : "AND `articleid` NOT IN({$backArtIdStr})";
        $catids = ArticleCategory::model()->fetchAllApprovalCatidByUid($uid);
        if (empty($catid)) { // 所有数据,先获取uid所有要审核的分类
            $catidStr = implode(',', $catids);
            $condition = "((FIND_IN_SET( `catid`, '{$catidStr}') {$backCondition} ) OR `author` = {$uid})"; // 作者或者在有审核权限的分类
        } else {
            $catidArr = is_array($catid) ? $catid : explode(',', $catid);
            $temp = array();
            foreach ($catidArr as $cid) {
                if (in_array($cid, $catids)) {
                    $temp[] = $cid;
                }
            }
            $tempStr = implode(',', $temp);
            $catidStr = empty($tempStr) ? 0 : $tempStr;
            $allCatid = is_array($catid) ? explode(',', $catid) : $catid;
            $condition = "((`catid` IN({$catidStr}) {$backCondition} ) OR (`catid` IN({$allCatid}) AND `author` = {$uid}))"; // 是审核人，无限制，否则条件为作者
        }
        $record = $this->fetchAll(array(
            'select' => array('articleid'),
            'condition' => "`status` = 2 AND " . $condition
        ));
        $artIds = Convert::getSubByKey($record, 'articleid');
        return $artIds;
    }

    /**
     * 未读，待审核，草稿 统计数
     * @param string $type
     * @param integer $uid
     * @param type $catid
     * @param type $condition
     */
    public function getArticleCount($type, $uid, $catid = 0, $condition = '')
    {
        $condition = ArticleUtil::joinListCondition($type, $uid, $catid, $condition);
        return $this->count($condition);
    }

    /*
      * 获得全部新闻(未读和已读),已读和未读,草稿箱，已发布，审核中，被退回,待我审核，我已通过，被我退回
     * @param string $uid 当前用户
     * @param string $catid 分类id
     * @param int $offset 偏移量
     * @param int $limit  每页分页数
     * @param string $keyword 关键字
     * @return array
     */
    public function getArticleListByType($type, $uid, $catid, $offset, $limit, $keyword = "")
    {
        $default = array('all', 'read', 'unread');//首页类型
        $publish = array('draft', 'publish', 'approval', 'reback_to');//我的投稿类型
        $verify = array('wait', 'passed', 'reback_from');//我的审核类型
        if (in_array($type, $default)) {
            $condition = ArticleUtil::getListCondition($type, $uid, $catid, $keyword);
        } elseif (in_array($type, $publish)) {
            $condition = ArticleUtil::getPublishCondition($type, $uid, $catid, $keyword);
        } elseif (in_array($type, $verify)) {
            $condition = ArticleUtil::getVerifyCondition($type, $uid, $catid, $keyword);
        }
        $query = Ibos::app()->db->createCommand()
            ->from($this->tableName())
            ->where($condition);
        $queryClone = clone $query;
        $list = $query->select('*')
            ->offset($offset)
            ->limit($limit)
            ->order('istop DESC,toptime ASC,addtime DESC')
            ->queryAll();
        $count = $queryClone->select('count(*)')->queryScalar();
        return array(
            'list' => $list,
            'count' => $count,
        );
    }

    /*
     * 判断新闻是否存在
     */
    public function articleExit($articleId)
    {
        $articleId = intval($articleId);
        $article = $this->find("articleId = :articleId", array(":articleId" => $articleId));
        if (empty($article)) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * 根据新闻ID和分类ID，得到对应的新闻数据
     * @param  string $aid 新闻ID 格式为1,2,3...
     * @param integer $catid 分类ID
     */
    public function getArticleList($aid, $catid)
    {
        if (empty($catid)) {
            $list = $this->fetchAll("articleid IN ({$aid})");
            // $criteria = new CDbCriteria();
            // $criteria->addInCondition('articleid', array(1, 2, 3));
            // $list = $this->findAll($criteria);
        } else {
            $list = $this->fetchAll("articleid IN ({$aid}) AND catid = :catid", array(':catid' => $catid));
        }
        return $list;
    }

    //下面的这些方法都是为了兼容h5的新闻接口
    /**
     * 新闻置顶操作
     *
     * @param $articleids string 置顶id，有多个使用逗号分开
     * @param $topEndTime string 置顶时间
     * @return bool 操作是否成功
     */
    public function top($articleids, $topEndTime)
    {
        // 必须提供 $articleids 参数
        if (empty($articleids)) {
            return self::TYPE_TOP_FAILED;
        }

        if (!empty($topEndTime)) {
            // 置顶操作
            $topEndTime = strtotime($topEndTime) + 24 * 60 * 60 - 1;
            $this->updateTopStatus($articleids, 1, TIMESTAMP, $topEndTime);
            return self::TYPE_TOP_SUCCESS;
        } else {
            // 取消置顶
            // todo: 取消置顶另外使用一个函数实现。暂时这样
            $this->updateTopStatus($articleids, 0, '', '');
            return self::TYPE_UNTOP_SUCCESS;
        }
    }


    /**
     * 移动新闻：将新闻 $artilceids 移动到目的分类 $catid
     *
     * @param $articleids string 置顶id，有多个使用逗号分开
     * @param $catid integer 分类id
     * @return bool 操作是否成功
     */
    public function move($articleids, $catid)
    {
        $catid = (int)$catid;

        if (!empty($articleids) && !empty($catid)) {
            // 检查是否有该分类
            $category = new Category('application\modules\article\model\ArticleCategory');
            $data = $category->getData("catid = {$catid}");
            if (empty($data)) {
                return false;
            }

            $this->updateAllCatidByArticleIds(ltrim($articleids, ','), $catid);
            return true;
        }

        return false;
    }

    /**
     * 新闻删除
     *
     * @param $articleids string 待删除的新闻列表。多个新闻使用逗号隔开
     * @return bool 操作是否成功
     */
    public function del($articleids)
    {
        // 删除附件
        $attachmentids = '';
        $attachmentIdArr = $this->fetchAllFieldValueByArticleids('attachmentid', $articleids);
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
        //删除投票
        if (Module::getIsEnabled("vote")) {
            Vote::model()->deleteAllByRelationIdsAndModule($articleids, 'article');
        }
        // 删除图片
        ArticlePicture::model()->deleteAllByArticleIds($articleids);
        // 删除文章
        $ret = $this->deleteAllByArticleIds($articleids);
        // 删除待审核记录
        ArticleApproval::model()->deleteByArtIds($articleids);

        return array(
            // 删除操作是否成功
            "status" => (boolean)$ret,
            // 删除附件个数
            "count" => $count,
        );
    }

    /**
     * 新闻审批通过
     */
    public function verify($artIds)
    {
        $uid = Ibos::app()->user->uid;
        $ids = explode(',', $artIds);
        if (empty($ids)) {
            Ibos::app()->controller->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Parameters error', 'error'),
                'data' => '',
            ));
        }
        foreach ($ids as $artId) {
            $artApproval = ApprovalRecord::model()->fetchLastStep($artId);
            if ($artApproval['status'] == 1 || $artApproval['status'] == 3) {
                $art = Article::model()->fetchByPk($artId);
                $sender = User::model()->fetchRealnameByUid($art['author']);
                $category = ArticleCategory::model()->fetchByPk($art['catid']);
                $approval = Approval::model()->fetch("id={$category['aid']}");
                $curApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $artApproval['step']);//当前审核步骤
                $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'],
                    $artApproval['step'] + 1); // 下一步应该审核的步骤
                if (!in_array($uid, $curApproval['uids'])) {
                    Ibos::app()->controller->ajaxReturn(array(
                        'isSuccess' => false,
                        'msg' => Ibos::lang('You do not have permission to verify the article')
                    ));
                }
                if (!empty($nextApproval)) {
                    if ($nextApproval['step'] == 'publish') {//已完成标识
                        $this->verifyComplete($artId, $uid);
                    } else {//记录签收步骤，给下一步签收人发提醒消息
                        ApprovalRecord::model()->recordStep($artId, $uid, 1);//记录审核步骤通过
                        $config = array(
                            '{sender}' => $sender,
                            '{subject}' => $art['subject'],
                            '{category}' => $category['name'],
                            '{url}' => Ibos::app()->controller->createUrl('index/show', array('articleid' => $art['articleid'])),
                            '{content}' => Ibos::app()->controller->renderPartial('remindcontent', array(
                                'article' => $art,
                                'author' => $sender,
                            ), true),
                        );
                        Notify::model()->sendNotify($nextApproval['uids'], 'article_verify_message', $config, $uid);
                        //审核人为下一个审核该新闻的用户（当前审核已通过）
                        $approver = $nextApproval['uids'];
                        $approver = implode(',', $approver);
                        self::updateAllStatusAndApproverByPks($artId, $approver, 2);
                    }
                }
            }
        }
        $data["isSuccess"] = true;
        $data["msg"] = Ibos::lang('Verify succeed', 'message');
        return $data;
    }

    /*
     * 审核全部完成
     * @param integer $artId 新闻ID
     * @param integer $uid 用户ID
     */
    private function verifyComplete($artId, $uid)
    {
        self::updateAllStatusAndApproverByPks($artId, $uid, 1);
        //审核完成后删除所有审核步骤，可以现在为了能够知道具体的审核步骤，因此审核步骤需要留着，这个方法不用了
        // ArticleApproval::model()->deleteAll("articleid={$artId}");
        ApprovalRecord::model()->recordStep($artId, $uid, 2);//记录审核步骤结束
        $article = Article::model()->fetchByPk($artId);
        //动态推送
        if (!empty($article)) {
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                $publishscope = array(
                    'deptid' => $article['deptid'],
                    'positionid' => $article['positionid'],
                    'uid' => $article['uid'],
                    'roleid' => $article['roleid']
                );
                $data = array(
                    'title' => Ibos::lang('Feed title', '', array(
                        '{subject}' => $article['subject'],
                        '{url}' => Ibos::app()->urlManager->createUrl('article/index/show',
                            array('articleid' => $article['articleid']))
                    )),
                    'body' => $article['content'],
                    'actdesc' => Ibos::lang('Post news'),
                    'userid' => $publishscope['uid'],
                    'deptid' => $publishscope['deptid'],
                    'positionid' => $publishscope['positionid'],
                );
                if ($article['type'] == Base::ARTICLE_TYPE_PICTURE) {
                    $type = 'postimage';
                    $picture = ArticlePicture::model()->fetchPictureByArticleId(array("articleid" => $article['articleid']));
                    $data['attach_id'] = $picture['aid'];
                } else {
                    $type = 'post';
                }
                WbfeedUtil::pushFeed($article['author'], 'article', 'article', $article['articleid'], $data, $type);
            }
            $category = ArticleCategory::model()->fetchByPk($article['catid']);
            $author = User::model()->fetchByPk($article['author']);
            $config = array(
                '{sender}' => $author['realname'],
                '{subject}' => $article['subject'],
                '{content}' => Ibos::app()->controller->renderPartial('application.modules.article.views.verify.remindcontent', array(
                    'article' => $article,
                    'author' => $author['realname'],
                ), true),
                '{orgContent}' => StringUtil::filterCleanHtml($article['content']),
                '{category}' => $category['name'],
                '{url}' => Ibos::app()->urlManager->createUrl('article/index/show',
                    array('articleid' => $article['articleid'])),
                'id' => $artId,
            );
            $publishscope = array(
                'deptid' => $article['deptid'],
                'positionid' => $article['positionid'],
                'roleid' => $article['roleid'],
                'uid' => $article['uid']
            );
            $uidArr = ArticleUtil::getScopeUidArr($publishscope);
            Notify::model()->sendNotify($uidArr, 'article_message', $config);
            //更新积分
            UserUtil::updateCreditByAction('addarticle', $article['author']);
        }
    }

    /**
     * 新闻审批退回
     *
     * @param $artIds
     * @param $reason
     * @return array
     */
    public function back($artIds, $reason)
    {
        $artIds = trim($artIds, ',');
        $uid = Ibos::app()->user->uid;
        $ids = explode(',', $artIds);
        $reason = StringUtil::filterCleanHtml($reason);

        $data = array(
            "isSuccess" => false,
            "msg" => "",
        );

        if (empty($ids) || empty($artIds)) {
            $data["msg"] = Ibos::lang('Parameters error', 'error');
            return $data;
        }
        $sender = User::model()->fetchRealnameByUid($uid);
        foreach ($ids as $artId) {
            $art = Article::model()->fetchByPk($artId);
            $categoryName = ArticleCategory::model()->fetchCateNameByCatid($art['catid']);
            if (!self::checkIsApprovaler($art, $uid)) {
                Ibos::app()->controller->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('You do not have permission to verify the article'),
                    'data' => '',
                ));
            }
            $config = array(
                '{sender}' => $sender,
                '{subject}' => $art['subject'],
                '{category}' => $categoryName,
                '{content}' => $reason,
                '{url}' => Ibos::app()->urlManager->createUrl('article/index/show', array('articleid' => $artId)),
            );
            Notify::model()->sendNotify($art['author'], 'article_back_message', $config, $uid);
            self::updateAllStatusAndApproverByPks($artId, $uid, 0);//把新闻的状态修改退回状态
            ApprovalRecord::model()->recordStep($artId, $uid, 0, $reason);//记录审核步骤退回
        }

        $data["isSuccess"] = true;
        $data["msg"] = Ibos::lang('Operation succeed', 'message');
        return $data;
    }

    /**
     * 判断某个uid是否某篇未审核新闻的当前审核人
     * @param array $article 新闻数据
     * @param integer $uid 用户id
     * @return boolean
     */
    private function checkIsApprovaler($article, $uid)
    {
        $res = false;
        $artApproval = ApprovalRecord::model()->fetchLastStep($article['articleid']);
        $category = ArticleCategory::model()->fetchByPk($article['catid']);
        if (!empty($category['aid'])) {
            $approval = Approval::model()->fetchByPk($category['aid']);
            $nextApproval = Approval::model()->fetchNextApprovalUids($approval['id'], $artApproval['step']);
            if (in_array($uid, $nextApproval['uids'])) {
                $res = true;
            }
        }
        return $res;
    }

    /**
     * 检查新增新闻或编辑新闻的参数是否正确
     *
     * @param $data array 更新或编辑新闻需要使用到的参数
     */
    public function checkAddOrUpdateParams($data)
    {
        // 参数检查
        if (empty($data)) {
            return Ibos::app()->controller->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Lack of params")), Mobile::dataType());
        }
        // 必要参数
        $needParams = array(
            "publishScope",
            "status",
            "type",
            "content",
            "subject",
            "attachmentid",
            "catid",
        );
        foreach ($needParams as $param) {
            if (!array_key_exists($param, $data)) {
                return Ibos::app()->controller->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Lack of params") . "缺少 {$param} 参数"), Mobile::dataType());
            }
        }
    }

    /**
     * 添加新闻
     * 参数：type 新闻类型、picids 图片id列表、attachmentid 附件列表、votestatus 投票状态、voteItemType
     * vote：内容投票数据、votePic：图片投票数据、publishScope（选择范围）
     *
     * @param integer $uid 用户uid
     * @return int 新增新闻的id
     */
    public function addArticle($uid, $isApi = false)
    {
        // 参数处理
        $uid = (int)$uid;

        // 检查是否传递了所有必要的参数
        $flag = (bool)$this->checkAddOrUpdateArticleParams($_POST);
        if ($flag === false) {
            return $flag;
        }

        $pidids = isset($_POST["picids"]) ? $_POST['picids'] : "";

        // 检查数据是否正确
        $this->beforeSaveData($_POST, $isApi);

        // 添加文章
        $articleId = $this->addOrUpdateArticle("add", $_POST, $uid);

        $articleId = (int)$articleId;
        // 如果是图片文章
        if ($_POST['type'] == Base::ARTICLE_TYPE_PICTURE) {
            $this->addOrUpdatePicArticle($articleId, "add");
        }

        //更新附件
        $this->addOrUpdateAttach($articleId);

        //添加投票
        $this->addOrUpdateVote($articleId, $uid);

        // 消息提醒
        $this->notifyForAddArticle($articleId, $uid, $pidids);
        return $articleId;
    }

    /**
     *  添加、修改新闻前的判断动作
     * @param array $postData 提交的数据
     */
    public function beforeSaveData(&$postData, $isApi = false)
    {
        if (isset($postData['type'])) {
            if ($postData['type'] == Base::ARTICLE_TYPE_PICTURE) {
                if (empty($postData['picids'])) {
                    $msg = Ibos::lang('Picture empty tip');
                    if (true === $isApi) {
                        return Ibos::app()->controller->ajaxReturn(array("isSuccess" => false, "msg" => $msg), Mobile::dataType());
                    }
                    Ibos::app()->controller->error($msg, Ibos::app()->urlManager->createUrl('/article/index/add'));
                } else {

                }
            } elseif ($postData['type'] == Base::ARTICLE_TYPE_DEFAULT) {
                if (empty($postData['content'])) {
                    $msg = Ibos::lang('Content empty tip');
                    if (true === $isApi) {
                        return Ibos::app()->controller->ajaxReturn(array("isSuccess" => false, "msg" => $msg), Mobile::dataType());
                    }
                    Ibos::app()->controller->error($msg, Ibos::app()->urlManager->createUrl('/article/index/add'));
                }
            } elseif ($postData['type'] == Base::ARTICLE_TYPE_LINK) {
                if (empty($postData['url'])) {
                    $msg = Ibos::lang('Url empty tip');
                    if (true === $isApi) {
                        return Ibos::app()->controller->ajaxReturn(array("isSuccess" => false, "msg" => $msg), Mobile::dataType());
                    }
                    Ibos::app()->controller->error($msg, Ibos::app()->urlManager->createUrl('/article/index/add'));
                }
            }
        }
        StringUtil::ihtmlSpecialCharsUseReference($postData['subject']);
    }

    /**
     * 添加或者修改文章信息
     *
     * @param $type string 类型 add 或 update
     * @param $data array  $_POST数据
     * @param $uid  integer 用户 uid
     * @return int|mixed
     * @throws \CException
     */
    public function addOrUpdateArticle($type, $data, $uid)
    {
        $attributes = Article::model()->create();
        $attributes['approver'] = $uid;
        $attributes['author'] = $uid;
        //取得发布权限
        $publishscope = StringUtil::handleSelectBoxData($data['publishScope']);
        $attributes['deptid'] = $publishscope['deptid'];
        $attributes['deptid'] = $publishscope['deptid'];
        $attributes['positionid'] = $publishscope['positionid'];
        $attributes['roleid'] = $publishscope['roleid'];
        $attributes['uid'] = $publishscope['uid'];
        if (!isset($data['topendtime']) || strtotime($data['topendtime']) < TIMESTAMP) {
            $attributes['istop'] = 0;
        }
        if (!isset($data['highlightendtime']) || strtotime($data['highlightendtime']) < TIMESTAMP) {
            $attributes['ishighlight'] = 0;
        }
        $attributes['votestatus'] = isset($data['votestatus']) ? $data['votestatus'] : 0;
        $attributes['commentstatus'] = isset($data['commentstatus']) ? $data['commentstatus'] : 0;

        //这里是新闻的状态，1.公开，2.待审核，3.草稿，草稿不需要考虑，只需考虑另外两种的情况
        //需要考虑免审核人，如果当前发起用户为免审核人，就应该把状态置为1
        if ($attributes['status'] == 2) {
            $catid = intval($attributes['catid']);
            $category = ArticleCategory::model()->fetchByPk($catid);
            if (empty($category['aid'])) {
                $attributes['status'] = 1;
            } else {
                $approval = Approval::model()->fetchByPk($category['aid']);
                $approvalUid = explode(',', $approval['free']);
                if (in_array($uid, $approvalUid)) {
                    $attributes['status'] = 1;
                } else {
                    $approver = ApprovalStep::model()->getApprovalerStr($category['aid'], 1);
                    $attributes['approver'] = $approver;
                    $attributes['status'] = 2;
                }
            }
        }
        if ($type == "add") {
            $attributes['addtime'] = TIMESTAMP;
            $row = Ibos::app()->db->createCommand()->insert('{{article}}', $attributes);
            if ($row) {
                $id = Ibos::app()->db->getLastInsertID();
                return $id;
            }
            return null;
        } else {
            $attributes['uptime'] = TIMESTAMP;
            return Article::model()->updateByPk($attributes['articleid'], $attributes);
        }
    }

    /**
     * 检查添加或更新新闻是否提供了必要的参数
     * publishScope（阅读范围，必须）
     * type：新闻类型，必须
     * voteItemType：必须
     *
     * votestatus：投票状态、可选
     * commentstatus：评论状态、可选
     * attachmentid：附件id，可选
     * picids（如果是图片类型新闻，可选）
     *
     * @param $data array 需要检查的数组数据
     * @return bool
     */
    private function checkAddOrUpdateArticleParams($data)
    {
        $checkParams = array(
            // 阅读范围
            "publishScope",
            // 新闻类型
            "type",
            // 新闻状态
            "status",
            // 投票类型
//            "voteItemType",
        );
        foreach ($checkParams as $v) {
            if (!array_key_exists($v, $data)) {
                return false;
            }
        }
        return true;
    }

    // 图片文章添加或更新
    private function addOrUpdatePicArticle($articleId, $op = "add")
    {
        $pidids = $_POST['picids'];
        if (!empty($pidids)) {
            // 图片文章更新：删除原来的，增加新的
            if ("update" == $op) {
                ArticlePicture::model()->deleteAll('articleid=:articleid', array(':articleid' => $articleId));
            }
            // 图片文章添加：不需要删除原有数据，直接添加
            Attach::updateAttach($pidids);
            $attach = Attach::getAttachData($pidids);
            ArticlePicture::model()->addPicture($attach, $articleId);
        }
    }

    // 更新附件
    private function addOrUpdateAttach($articleId)
    {
        if (!isset($_POST["attachmentid"])) {
            return false;
        }
        $attachmentid = trim($_POST['attachmentid'], ',');
        if (!empty($attachmentid)) {
            Attach::updateAttach($attachmentid);
            $this->modify($articleId, array('attachmentid' => $attachmentid));
        }
        return true;
    }

    // 添加或更新投票
    private function addOrUpdateVote($articleId, $uid, $op = "add")
    {
        // 只允许两种操作：add 和 update
        $supportOp = array(
            "add",
            "update",
        );
        if (!in_array($op, $supportOp)) {
            throw new \Exception("addOrUpdateVote 方法不支持 add 和 update 以外的操作。");
        }
        $fields = array(
            'articleapprover', 'articlecommentenable', 'articlevoteenable', 'articlemessageenable'
        );
        $dashboardConfig = Dashboard::getDashboardConfig($fields);
        if (isset($_POST['votestatus']) && Module::getIsEnabled('vote') && $dashboardConfig['articlevoteenable']) {
            $voteData = Env::getRequest('vote');
            $voteData['publishscope'] = isset($_POST['publishscope']) ? $_POST['publishscope'] : '';
            $moduleName = isset($_POST['relatedmodule']) ? $_POST['relatedmodule'] : MODULE_NAME;
            if ($op == 'add') {
                //添加投票
                VoteComponent::add($voteData, $moduleName, $articleId);
            } elseif ($op == 'update') {
                // 更新投票
                VoteComponent::update($voteData, $moduleName, $articleId);
            }
        }
    }

    // 添加新闻的消息提醒
    private function notifyForAddArticle($articleId, $uid, $pidids)
    {
        $user = User::model()->fetchByUid($uid);
        $article = $this->fetchByPk($articleId);
        $categoryName = ArticleCategory::model()->fetchCateNameByCatid($article['catid']);
        // 如果新闻状态为：公开
        if (ArticleUtil::TYPE_ALLOW_NUM === (int)$article['status']) {
            $publishScope = array(
                'deptid' => $article['deptid'],
                'positionid' => $article['positionid'],
                'roleid' => $article['roleid'],
                'uid' => $article['uid'],
            );
            $uidArr = ArticleUtil::getScopeUidArr($publishScope);
            $content = Ibos::app()->controller->renderPartial('application.modules.article.views.index.remindcontent', array(
                'article' => $article,
                'author' => $user['realname'],
            ), true);
            $config = array(
                '{sender}' => $user['realname'],
                '{category}' => $categoryName,
                '{subject}' => $article['subject'],
                '{content}' => $content,
                '{orgContent}' => StringUtil::filterCleanHtml($article['content']),
                '{url}' => Ibos::app()->urlManager->createUrl('article/index/show', array('articleid' => $articleId)),
                'id' => $articleId,
            );
            if (count($uidArr) > 0) {
                Notify::model()->sendNotify($uidArr, 'article_message', $config);
            }

            // 动态推送
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf['wbmovement']['article']) && $wbconf['wbmovement']['article'] == 1) {
                $publishScope = array(
                    'deptid' => $article['deptid'],
                    'positionid' => $article['positionid'],
                    'roleid' => $article['roleid'],
                    'uid' => $article['uid']);
                $data = array(
                    'title' => Ibos::lang('Feed title', '', array(
                        '{subject}' => $article['subject'],
                        '{url}' => Ibos::app()->urlManager->createUrl('article/index/show', array('articleid' => $articleId))
                    )),
                    'body' => $article['subject'],
                    'actdesc' => Ibos::lang('Post news'),
                    'userid' => $publishScope['uid'],
                    'deptid' => $publishScope['deptid'],
                    'positionid' => $publishScope['positionid'],
                    'roleid' => $publishScope['roleid'],
                );
                if ($_POST['type'] == Base::ARTICLE_TYPE_PICTURE && !empty($pidids)) {
                    $type = 'postimage';
                    $picids = explode(',', $pidids);
                    $data['attach_id'] = array_shift($picids);
                } else {
                    $type = 'post';
                }
                WbfeedUtil::pushFeed(Ibos::app()->user->uid, 'article', 'article', $articleId, $data, $type);
            }
            //更新积分
            UserUtil::updateCreditByAction('addarticle', $uid);
        } else if ($article['status'] == ArticleUtil::TYPE_NOTALLOW_NUM) {
            $this->SendPending($article, $uid);
        }
    }

    /**
     * 发送待审核新闻处理方法(新增与编辑都可处理)
     * @param array $article 新闻数据
     * @param integer $uid 发送人id
     */
    private function SendPending($article, $uid)
    {
        $category = ArticleCategory::model()->fetchByPk($article['catid']);
        $approval = Approval::model()->fetchNextApprovalUids($category['aid'], 0);
        if (!empty($approval)) {
            if ($approval['step'] == 'publish') {
                self::verifyComplete($article['articleid'], $uid);
            } else {
                ApprovalRecord::model()->recordStep($article['articleid'], $uid, 3, '');
                $sender = User::model()->fetchRealnameByUid($uid);
                //发送第一步审核人
                $config = array(
                    '{sender}' => $sender,
                    '{subject}' => $article['subject'],
                    '{category}' => $category['name'],
                    '{url}' => Ibos::app()->controller->createUrl('index/show', array('articleid' => $article['articleid'])),
                    '{content}' => Ibos::app()->controller->renderPartial('application.modules.article.views.verify.remindcontent', array(
                        'article' => $article,
                        'author' => $sender,
                    ), true),
                );
                Notify::model()->sendNotify($approval['uids'], 'article_verify_message', $config, $uid);
            }
        }
    }

    /**
     * 修改文章
     */
    public function updateArtilce($articleId)
    {
        // 参数处理
        $uid = Ibos::app()->user->uid;

        // 检查是否传递了所有必要的参数
        $flag = (bool)$this->checkAddOrUpdateArticleParams($_POST);
        if ($flag === false) {
            return $flag;
        }
        if (!isset($_POST["articleid"])) {
            return false;
        }

        // 检查数据是否正确
        $this->beforeSaveData($_POST, true);

        // 修改文章
        $this->addOrUpdateArticle("update", $_POST, $uid);

        // 如果是图片文章
        if ($_POST['type'] == Base::ARTICLE_TYPE_PICTURE) {
            $this->addOrUpdatePicArticle($articleId, $op = "update");
        }

        //更新附件
        $this->addOrUpdateAttach($articleId);

        //更新投票
        $this->addOrUpdateVote($articleId, $uid, $op = "update");

        // 消息提醒
        $this->notifyForUpdateArticle($articleId, $uid);

        // 删除所有相关的回退记录
        ArticleBack::model()->deleteAll("articleid = {$articleId}");

        return $articleId;
    }

    // 更新新闻的消息提醒
    private function NotifyForUpdateArticle($articleId, $uid)
    {
        $user = User::model()->fetchByUid($uid);
        $article = $this->fetchByPk($articleId);
        $categoryName = ArticleCategory::model()->fetchCateNameByCatid($article['catid']);
        // 如果是公开新闻
        if (!empty($_POST['msgRemind']) && $article['status'] == ArticleUtil::TYPE_ALLOW_NUM) {
            $publishScope = array('deptid' => $article['deptid'], 'positionid' => $article['positionid'], 'uid' => $article['uid']);
            $uidArr = ArticleUtil::getScopeUidArr($publishScope);
            $config = array(
                '{sender}' => $user['realname'],
                '{category}' => $categoryName,
                '{subject}' => $article['subject'],
                '{content}' => Ibos::app()->controller->renderPartial('application.modules.article.views.index.remindcontent', array(
                    'article' => $article,
                    'author' => $user['realname'],
                ), true),
                '{url}' => Ibos::app()->urlManager->createUrl('article/index/show', array('articleid' => $article['articleid'])),
                'id' => $article['articleid'],
            );
            if (count($uidArr) > 0) {
                Notify::model()->sendNotify($uidArr, 'article_message', $config);
            }
        }
        // 如果是待审核新闻
        if ($article['status'] == ArticleUtil::TYPE_NOTALLOW_NUM) {
            $this->SendPending($article, $uid);
        }
    }
}
