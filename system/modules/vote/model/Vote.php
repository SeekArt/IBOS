<?php

/**
 * 投票模块------投票数据表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------投票数据表操作类
 * @package application.modules.vote.model
 * @version $Id: Vote.php 164 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\model;

use application\core\model\Model;
use application\core\utils\ArrayUtil;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\utils\Article;
use application\modules\department\model\Department;
use application\modules\user\model\User;
use application\modules\vote\utils\VoteUtil;

class Vote extends Model
{

    /**
     * 类型：单选
     */
    const TYPE_RADIO = 0;

    /**
     * 类型：多选
     */
    const TYPE_CHECKBOX = 1;


    /**
     * 可见性：所有人可见
     */
    const ALL_VISIBLE = 0;

    /**
     * 可见性：投票后可见
     */
    const AFTER_VOTE_VISIBLE = 1;


    /**
     * 状态：进行中
     */
    const STATUS_RUNNING = 1;

    /**
     * 状态：已结束
     */
    const STATUS_END = 2;


    /**
     * 默认投票列表项目个数
     */
    const DEFAULT_PAGE_SIZE = 10;


    /**
     * 列表：调查投票 - 未参与
     */
    const LIST_VOTE_UN_JOINED = 1;

    /**
     * 列表：调查投票 - 已参与
     */
    const LIST_VOTE_JOINED = 2;

    /**
     * 列表：调查投票 - 全部
     */
    const LIST_VOTE_ALL = 3;

    /**
     * 列表：我发起的 - 进行中
     */
    const LIST_START_RUNNING = 4;

    /**
     * 列表：我发起的 - 已结束
     */
    const LIST_START_END = 5;

    /**
     * 列表：我发起的 - 全部
     */
    const LIST_START_ALL = 6;

    /**
     * 列表：管理投票 - 进行中
     */
    const LIST_MANAGE_RUNNING = 7;

    /**
     * 列表：管理投票 - 已结束
     */
    const LIST_MANAGE_END = 8;

    /**
     * 列表：管理投票 - 全部
     */
    const LIST_MANAGE_ALL = 9;


    /**
     * 投票题目内容类型：文字
     */
    const CONTENT_TYPE_TEXT = 1;

    /**
     * 投票题目内容类型：图文
     */
    const CONTENT_TYPE_PIC = 2;

    /**
     * 搜索类型：高级搜索
     */
    const SEARCH_TYPE_ADVANCED = 'advanced_search';


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{vote}}';
    }

    /**
     * 通过相关模块名称和相关id取得取得一行投票记录及这行记录的所有相关投票项
     *
     * @param string $relatedModule 相关模块名称
     * @param integer $relatedId 相关模块id
     * @return array
     */
    public function fetchVote($relatedModule, $relatedId)
    {
        $result = array();
        $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
        $params = array(
            ':relatedmodule' => $relatedModule,
            ':relatedid' => $relatedId
        );
        $vote = $this->fetch($condition, $params);
        if (!empty($vote)) {
            $voteid = $vote['voteid'];
            $voteItemList = VoteItem::model()->fetchAll("voteid=:voteid", array(':voteid' => $voteid));
            $result['voteItemList'] = $voteItemList;
            $result['vote'] = $vote;
            $result['vote']['type'] = $result['voteItemList'][0]['type'];
        }
        return $result;
    }

    /**
     * 根据关联模块名和关联模块 id 获取投票数据
     *
     * @param string $module 模块名
     * @param string $moduleId 模块关联 id
     * @return bool
     */
    public function fetchVoteByModule($module, $moduleId)
    {
        return $this->fetchByAttributes(array(
            'relatedmodule' => $module,
            'relatedid' => $moduleId,
        ));
    }

    /**
     * 根据关联模块名和关联模块 id 数组获取投票数据（多个）
     *
     * @param string $module 模块名
     * @param array $moduleIdArr 模块关联 id 数组
     * @return array|\CDbDataReader
     */
    public function fetchVotesByModule($module, array $moduleIdArr)
    {
        return Ibos::app()->db->createCommand()
            ->select('*')
            ->from($this->tableName())
            ->where('relatedmodule = :relatedmodule', array(':relatedmodule' => $module))
            ->andWhere(array('in', 'relatedid', $moduleIdArr))
            ->queryAll();
    }


    /**
     * 通过 vote 表的主键 voteid 获取投票信息
     *
     * @param $voteId
     * @return array
     */
    public function fetchVoteByPk($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);
        return $this->fetchByPk($voteId);
    }

    /**
     * 查询当前投票参与人数
     *
     * @param string $relatedModule 相关模块名称
     * @param integer $relatedId 相关id
     * @result integer 投票数
     */
    public function fetchUserVoteCount($relatedModule, $relatedId)
    {
        $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
        $params = array(
            ':relatedmodule' => $relatedModule,
            ':relatedid' => $relatedId
        );
        $vote = $this->fetch($condition, $params);
        //取得所有投票项
        $voteid = $vote['voteid'];
        $voteItemList = VoteItem::model()->fetchAll("voteid=:voteid", array(':voteid' => $voteid));
        $uidArray = array();
        foreach ($voteItemList as $voteItem) {
            $itemid = $voteItem['itemid'];
            $ItemCountList = VoteItemCount::model()->fetchAll("itemid=:itemid", array(':itemid' => $itemid));
            if (!empty($ItemCountList)) {
                foreach ($ItemCountList as $itemCount) {
                    $uid = $itemCount['uid'];
                    $uidArray[] = $uid;
                }
            }
        }
        //移除数组中的重复的值
        $result = count(array_unique($uidArray));
        return $result;
    }

    /**
     * 通过相关ids和模块名称删除评论
     *
     * @param string $relatedids 相关Id
     * @param string $relatedModule 相关模块名
     * @return integer 影响的行数
     */
    public function deleteAllByRelationIdsAndModule($relatedids, $relatedModule)
    {
        $relatedidArr = explode(',', $relatedids);
        $votes = $this->fetchVotesByModule($relatedModule, $relatedidArr);
        if (empty($votes)) {
            return false;
        }

        $voteIds = ArrayUtil::getColumn($votes, 'voteid');
        return VoteUtil::delVotes(array('voteid' => $voteIds));
    }

    /**
     * 通过 voteid 删除投票数据
     *
     * @param $voteId
     * @return bool
     */
    public function delByVoteId($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);

        $vote = $this->findByPk($voteId);
        if (empty($vote)) {
            return false;
        }

        return $vote->delete();
    }

    /**
     * 添加一条投票记录
     *
     * @param array $postData
     * @return bool
     */
    public function addRecord(array $postData)
    {
        return self::add($postData, true);
    }


    /**
     * 更新 vote model 信息
     *
     * @param $voteId
     * @param array $attributes
     * @return bool
     */
    public function updateAttributes($voteId, array $attributes)
    {
        $vote = self::findByPk($voteId);

        if (empty($vote)) {
            throw new \InvalidArgumentException(Ibos::lang('the vote not exists'));
        }

        foreach ($attributes as $k => $v) {
            $vote->$k = $v;
        }

        return $vote->save();
    }

    /**
     * 更新投票的模块关联 id
     *
     * @param integer $voteId
     * @param integer $moduleId
     * @return bool
     */
    public function updateModuleId($voteId, $moduleId)
    {
        return $this->updateAttributes($voteId, array(
            'relatedid' => $moduleId,
        ));
    }

    /**
     * 获取投票详细数据，包括投票数据、投票题目数据和投票项数据
     *
     * @param $voteId
     * @return array
     */
    public function fetchVoteDetail($voteId)
    {
        // 获取投票信息
        $vote = VoteUtil::fetchVoteByPk($voteId);

        // 格式化投票开始时间和结束时间
        $vote['starttimestr'] = date('Y年m月d日H:i', $vote['starttime']);
        $vote['endtimestr'] = date('Y-m-d H:i', $vote['endtime']);

        // 获取投票话题信息
        $topics = VoteTopic::model()->fetchTopicsDetail($voteId);

        return array($vote, $topics);
    }


    /**
     * 获取投票列表
     *
     * @param integer $type
     * @param string $search
     * @param integer $start
     * @param integer $length
     * @return array [投票模型数组, 投票记录总个数]
     */
    public function fetchList($type, $search, $start, $length)
    {

        $criteria = new \CDbCriteria();
        $criteria->offset = $start;
        $criteria->limit = $length;

        // 过滤非投票模块的内容
        $criteria->addCondition('relatedmodule = :modulename');
        $criteria->params[':modulename'] = MODULE_NAME;

        // 搜索
        $criteria = $this->returnSearchCriteria($search, $criteria);

        // 根据 type 的值，返回对应的条件
        $criteria = $this->returnTypeCriteria($type, $criteria);

        // 添加排序规则
        $criteria->order = 't.updatetime DESC';

        // 计算当前条件下有多少下数据
        $allListCount = Vote::model()->count($criteria);

        $models = Vote::model()->findAll($criteria);
        return array($models, $allListCount);
    }

    /**
     * 获取用户未参与投票列表
     *
     * @param integer $uid
     * @return array|mixed|null
     */
    public function fetchUnJoinedList($uid)
    {
        $criteria = new \CDbCriteria();
        $criteria = $this->returnUnJoinedCriteria($criteria, $uid);
        $criteria = $this->returnScopeCondition($criteria, $uid);
        $criteria->addCondition('relatedmodule = :modulename');
        $criteria->params[':modulename'] = MODULE_NAME;

        return $this->findAll($criteria);
    }


    /**
     * 执行普通搜索 || 高级搜索
     *
     * @param array $search
     * @param \CDbCriteria $criteria
     * @return \CDbCriteria
     */
    private function returnSearchCriteria(array $search, \CDbCriteria $criteria)
    {
        if (isset($search['type']) && $search['type'] == self::SEARCH_TYPE_ADVANCED) {
            // 高级搜索
            $searchSubject = @$search['subject'];
            $searchSponsor = @$search['sponsor'];
            $searchStartTime = @$search['starttime'];
            $searchEndTime = @$search['endtime'];

            // 标题搜索
            if (!empty($searchSubject)) {
                $searchSubject = StringUtil::filterCleanHtml($searchSubject);
                $criteria->addCondition("subject like '%{$searchSubject}%'");
            }

            // 搜索发布人范围内的结果
            if (!empty($searchSponsor)) {
                $publishScope = StringUtil::handleSelectBoxData($searchSponsor);
                $uidArr = Article::getScopeUidArr($publishScope);
                if (!empty($uidArr) && is_array($uidArr)) {
                    $criteria->addInCondition('t.uid', $uidArr);
                }
            }

            // 搜索时间范围内的结果
            if (!empty($searchStartTime)) {
                $searchStartTime = strtotime($searchStartTime);
                // 字符串时间转换时间戳成功
                if ($searchStartTime !== false && $searchStartTime !== -1) {
                    $criteria->addCondition('endtime > :starttime');
                    $criteria->params[':starttime'] = $searchStartTime;
                }
            }
            if (!empty($searchEndTime)) {
                $searchEndTime = strtotime($searchEndTime);
                if ($searchEndTime !== false && $searchEndTime !== -1) {
                    $criteria->addCondition('endtime < :endtime');
                    $criteria->params[':endtime'] = $searchEndTime;
                }
            }
        } else {
            // 普通搜索
            $searchValue = @$search['value'];
            if (!empty($searchValue)) {
                $searchValue = StringUtil::filterCleanHtml($searchValue);
                $criteria->addCondition("subject like '%{$searchValue}%'");
            }
        }

        return $criteria;
    }


    /**
     * 返回阅读范围权限判断条件
     *
     * @param \CDbCriteria $criteria
     * @param integer $uid
     * @return \CDbCriteria
     */
    private function returnScopeCondition(\CDbCriteria $criteria, $uid)
    {
        $user = User::model()->fetchByUid($uid);

        // 部门范围
        $userDeptId = Department::model()->queryDept($user['deptid']);
        $allDeptId = array_filter(array_unique(explode(',', $userDeptId . "," . $user['alldeptid'])));
        $deptCondition = '';
        foreach ($allDeptId as $deptId) {
            $deptCondition .= sprintf('OR FIND_IN_SET("%s",deptid)', $deptId);
        }

        // 职位范围
        $positionCondition = '';
        $allPosId = array_filter(explode(',', $user['allposid']));
        foreach ($allPosId as $posid) {
            $positionCondition .= sprintf('OR FIND_IN_SET("%s",positionid)', $posid);
        }

        // 角色范围
        $roleCondition = '';
        $allRoleId = array_filter(explode(',', $user['allroleid']));
        foreach ($allRoleId as $roleId) {
            $roleCondition .= sprintf('OR FIND_IN_SET("%s" ,roleid)', $roleId);
        }

        $scopeCondition = <<<EOF
(
    (
        deptid = 'alldept' 
        {$deptCondition}
        {$positionCondition}
        {$roleCondition}
        OR FIND_IN_SET('{$uid}', scopeuid)
    )
    OR uid = '{$uid}'
)
EOF;


        $criteria->addCondition($scopeCondition);

        return $criteria;
    }

    /**
     * 根据 type 的值，返回对应的条件
     *
     * @param $type
     * @param \CDbCriteria $criteria
     * @return \CDbCriteria
     */
    private function returnTypeCriteria($type, \CDbCriteria $criteria)
    {
        $uid = (int)Ibos::app()->user->uid;


        // 调查投票类型数组
        $voteList = array(self::LIST_VOTE_UN_JOINED, self::LIST_VOTE_JOINED, self::LIST_VOTE_ALL);
        // 我发起的类型数组
        $startList = array(self::LIST_START_RUNNING, self::LIST_START_END, self::LIST_START_ALL);
        // 管理投票类型数组
        $manageList = array(self::LIST_MANAGE_RUNNING, self::LIST_MANAGE_END, self::LIST_MANAGE_ALL);

        if (in_array($type, $voteList)) {
            // 选择范围过滤
            $criteria = $this->returnScopeCondition($criteria, $uid);

            switch ($type) {
                case self::LIST_VOTE_UN_JOINED:
                    // 调查投票 - 未参与
                    $criteria = $this->returnUnJoinedCriteria($criteria, $uid);
                    break;
                case self::LIST_VOTE_JOINED:
                    // 调查投票 - 已参与
                    $criteria = $this->returnJoinedCriteria($criteria, $uid);
                    break;
                case self::LIST_VOTE_ALL:
                    // 调查投票 - 全部
                    break;
                default:
                    break;
            }
        } elseif (in_array($type, $startList)) {
            // 选择范围过滤
            $criteria = $this->returnScopeCondition($criteria, $uid);

            $criteria->addCondition('t.uid = :uid');
            $criteria->params[':uid'] = $uid;

            switch ($type) {
                case self::LIST_START_RUNNING:
                    // 我发起的 - 进行中
                    $criteria = $this->returnRunningCriteria($criteria);
                    break;
                case self::LIST_START_END:
                    // 我发起的 - 已结束
                    $criteria = $this->returnEndCriteria($criteria);
                    break;
                case self::LIST_START_ALL:
                    // 我发起的 - 全部
                    break;
                default:
                    break;
            }
        } elseif (in_array($type, $manageList)) {
            switch ($type) {
                case self::LIST_MANAGE_RUNNING:
                    // 管理投票 - 进行中
                    $criteria = $this->returnRunningCriteria($criteria);
                    break;
                case self::LIST_MANAGE_END:
                    // 管理投票 - 已结束
                    $criteria = $this->returnEndCriteria($criteria);
                    break;
                case self::LIST_MANAGE_ALL:
                    // 管理投票 - 全部
                    break;
                default:
                    break;
            }
        } else {
            // 异常情况
            throw new \InvalidArgumentException(Ibos::lang('invalid type param'));
        }

        return $criteria;
    }


    /**
     * 返回我参与的投票的条件
     *
     * @param \CDbCriteria $criteria
     * @param $uid
     * @return \CDbCriteria
     */
    private function returnJoinedCriteria(\CDbCriteria $criteria, $uid)
    {
        $joinedVoteId = VoteItemCount::model()->fetchJoinedIds($uid);
        $criteria->addInCondition('voteid', $joinedVoteId);

        return $criteria;
    }

    /**
     * 返回我未参与的投票的条件
     *
     * @param \CDbCriteria $criteria
     * @param $uid
     * @return \CDbCriteria
     */
    private function returnUnJoinedCriteria(\CDbCriteria $criteria, $uid)
    {
        $joinedVoteId = VoteItemCount::model()->fetchJoinedIds($uid);
        $criteria->addNotInCondition('voteid', $joinedVoteId);

        return $criteria;
    }

    /**
     * 返回进行中的投票的条件
     *
     * @param \CDbCriteria $criteria
     * @return \CDbCriteria
     */
    private function returnRunningCriteria(\CDbCriteria $criteria)
    {
        $criteria->addCondition('endtime >= :now');
        $criteria->params[':now'] = TIMESTAMP;

        return $criteria;
    }

    /**
     * 返回已结束的投票的条件
     *
     * @param \CDbCriteria $criteria
     * @return \CDbCriteria
     */
    private function returnEndCriteria(\CDbCriteria $criteria)
    {
        $criteria->addCondition('endtime < :now');
        $criteria->params[':now'] = TIMESTAMP;

        return $criteria;
    }

    /**
     * 添加投票，包括文字投票和图片投票
     * @param $data
     * @param $articleId
     * @param $uid
     * @param $type
     * @param string $voteId
     * @return bool|mixed
     */
    public function addOrUpdateVote($data, $articleId, $uid, $type, $voteId = '') {
        $vote = array(
            'subject' => $data['subject'],
            'starttime' => TIMESTAMP,
            'endtime' => strtotime($data['endtime']),
            'ismulti' => $data['ismulti'],
            'maxselectnum' => $data['maxselectnum'],
            'isvisible' => $data['isvisible'],
            'status' => 1,
            'uid' => $uid,
            'relatedmodule' => 'article',
            'relatedid' => $articleId,
            'deadlinetype' => $data['deadlineType']
        );
        if ($type == 'add') {
            return $this->add($vote, true);
        } else {
            return $this->modify($voteId, $vote);
        }
    }
}
