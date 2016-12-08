<?php

/**
 * 投票模块------vote_item_count表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------vote_item_count表操作类
 * @package application.modules.vote.model
 * @version $Id: VoteItemCount.php 164 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\modules\vote\utils\VoteUtil;

class VoteItemCount extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{vote_item_count}}';
    }

    /**
     * 仅获取用户参与过的投票的id
     *
     * @param $uid
     * @return
     */
    public function fetchJoinedIds($uid)
    {
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);

        $res = Ibos::app()->db->createCommand()
            ->select('voteid')
            ->from($this->tableName())
            ->where('uid = :uid', array(':uid' => $uid))
            ->queryColumn();

        return $res;
    }

    /**
     * 通过 voteid 和 uid 获取投票记录
     *
     * @param $voteId
     * @param $uid
     * @return array
     */
    public function fetchByVoteIdAndUid($voteId, $uid)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);

        return $this->fetch('voteid = :voteid AND uid = :uid', array(':voteid' => $voteId, ':uid' => $uid));
    }

    /**
     * 通过 topicId 和 uid 获取投票记录
     *
     * @param integer $topicId
     * @param integer $uid
     * @return array
     */
    public function fetchAllByTopicIdAndUid($topicId, $uid)
    {
        $topicId = filter_var($topicId, FILTER_SANITIZE_NUMBER_INT);
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);

        return $this->fetchAll('topicid = :topicid AND uid = :uid', array(':topicid' => $topicId, ':uid' => $uid));
    }


    /**
     * 获取参与某个调查投票的人数
     *
     * @param $voteId
     * @return array
     */
    public function fetchVoteUserNum($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);

        return count($this->fetchJoinedUidArr($voteId));
    }

    /**
     * 通过 voteid 获取所有记录
     *
     * @param $voteId
     * @return array
     */
    public function fetchAllByVoteId($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);

        return $this->fetchAll('voteid = :voteid', array(':voteid' => $voteId));
    }

    /**
     * 添加一条记录
     *
     * @param integer $voteId
     * @param integer $topicId
     * @param integer $itemId
     * @param integer $uid
     * @return mixed
     */
    public function addRecord($voteId, $topicId, $itemId, $uid)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);
        $topicId = filter_var($topicId, FILTER_VALIDATE_INT);
        $itemId = filter_var($itemId, FILTER_SANITIZE_NUMBER_INT);
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);

        return $this->add(array(
            'voteid' => $voteId,
            'topicid' => $topicId,
            'itemid' => $itemId,
            'uid' => $uid,
        ));
    }

    /**
     * 通过 voteId 删除所有投票记录
     *
     * @param integer $voteId
     * @return bool
     * @throws \CDbException
     */
    public function delAll($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);

        $itemCounts = $this->findAll('voteid = :voteid', array(':voteid' => $voteId));
        foreach ($itemCounts as $itemCount) {
            $itemCount->delete();
        }

        return true;
    }

    /**
     * 检查用户是否已经参与了投票
     *
     * @param integer $voteId
     * @param integer $uid
     * @return bool
     */
    public function isVote($voteId, $uid)
    {
        $res = $this->fetch('voteid = :voteid AND uid = :uid', array(':voteid' => $voteId, ':uid' => $uid));
        if (empty($res)) {
            return false;
        }

        return true;
    }


    /**
     * 获取参与某个调查投票的用户 id 数组
     *
     * @param $voteId
     * @return array
     */
    public function fetchJoinedUidArr($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);

        $userArr = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where('voteid = :voteid', array(':voteid' => $voteId))
            ->group('uid')
            ->queryColumn();

        return $userArr;
    }


    /**
     * 获取未参与某个调查投票的用户 id 数组
     *
     * @param integer $voteId
     * @return array
     */
    public function fetchUnJoinedUidArr($voteId)
    {
        // 该投票阅读范围内的 uid 数组
        $allUidArr = VoteUtil::fetchScopeUidArr($voteId);

        // 参加投票的 uid 数组
        $joinedUidArr = $this->fetchJoinedUidArr($voteId);

        // 未参与投票的 uid 数组
        $unJoinedUidArr = array_diff($allUidArr, $joinedUidArr);

        return $unJoinedUidArr;
    }

    /**
     * 获取参加投票的用户数
     *
     * @param integer $voteId
     * @return integer
     */
    public function fetchJoinedUserNum($voteId)
    {
        return count($this->fetchJoinedUidArr($voteId));
    }

    /**
     * 获取未参加投票的用户数
     *
     * @param integer $voteId
     * @return integer
     */
    public function fetchUnJoinedUserNum($voteId)
    {
        return count($this->fetchUnJoinedUidArr($voteId));
    }

}
