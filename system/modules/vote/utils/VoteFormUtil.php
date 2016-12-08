<?php
/**
 * 投票表单工具类
 *
 * @namespace application\modules\vote\utils
 * @filename VoteFormUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/31 20:29
 */

namespace application\modules\vote\utils;


use application\core\utils\ArrayUtil;
use application\core\utils\Ibos;
use application\core\utils\System;
use application\modules\user\utils\User;
use application\modules\vote\model\VoteItem;
use application\modules\vote\model\VoteItemCount;
use application\modules\vote\model\VoteTopic;

class VoteFormUtil extends System
{
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 发起投票
     *
     * @param $uid
     * @param $postData
     * @return bool
     * @throws \Exception
     */
    public function vote($uid, $postData)
    {
        // 检查参数
        RequestValidator::getInstance()->initVote($postData);

        $voteId = $postData['voteid'];
        $topics = $postData['topics'];

        // 检查投票是否存在
        VoteUtil::fetchVoteByPk($voteId);

        // 检查当前用户是否有投票的权限
        if (!VoteRoleUtil::canVote($voteId, $uid)) {
            throw new \Exception('vote: permission denied');
        }

        // 检查是否全部题目都有投票
        $allTopics = VoteTopic::model()->fetchTopics($voteId);
        $allTopicIds = ArrayUtil::getColumn($allTopics, 'topicid');
        $topicIds = ArrayUtil::getColumn($topics, 'topicid');
        foreach ($allTopicIds as $id) {
            if (!in_array($id, $topicIds)) {
                throw new \InvalidArgumentException(sprintf(Ibos::lang('lack vote topic'), $id));
            }
        }

        // 检查当前用户是否已经投票过
        if (VoteItemCount::model()->isVote($voteId, $uid)) {
            throw new \Exception(Ibos::lang('already vote'));
        }

        // 检查投票选项是否存在
        foreach ($topics as $topic) {
            foreach ($topic['itemids'] as $itemid) {
                if (!VoteItem::model()->isExists($voteId, $topic['topicid'], $itemid)) {
                    throw new \InvalidArgumentException(Ibos::lang('vote item not exists'));
                }
            }
        }

        // 开始发起投票
        foreach ($topics as $topic) {
            $itemIds = $topic['itemids'];
            foreach ($itemIds as $itemId) {
                VoteItemCount::model()->addRecord($voteId, $topic['topicid'], $itemId, $uid);
                VoteItem::model()->incrementVoteNum($itemId);
            }
        }

        return true;
    }

    /**
     * 遍历投票结果
     *
     * @param integer $voteId
     * @return array
     */
    public function listVoteResult($voteId)
    {
        $result = array();

        $voteItemCounts = VoteItemCount::model()->fetchAllByVoteId($voteId);
        foreach ($voteItemCounts as $itemCount) {
            $topicId = $itemCount['topicid'];
            $itemId = $itemCount['itemid'];
            $user = array_values(User::safeWrapUserInfo($itemCount['uid']));
            $realName = '';
            if (isset($user[0]['realname'])) {
                $realName = $user[0]['realname'];
            }

            $result[$itemCount['uid']][] = array(
                'topicid' => $topicId,
                'itemid' => $itemId,
                'select' => self::calcUserSelect($topicId, $itemId),
                'realname' => $realName,
            );
        }

        return $result;
    }

    /**
     * 根据 $topicId 和 $itemId 的值，计算用户的选项
     *
     * @param integer $topicId
     * @param integer $itemId
     * @return bool|int 找到，则返回对应的数字；否则，返回 false。
     */
    private function calcUserSelect($topicId, $itemId)
    {
        $items = VoteItem::model()->fetchItems($topicId);
        $itemIds = ArrayUtil::getColumn($items, 'itemid');
        $count = count($itemIds);

        for ($i = 0; $i < $count; $i++) {
            if ($itemIds[$i] == $itemId) {
                return self::numToLetter($i + 1);
            }
        }

        return false;
    }

    /**
     * 阿拉伯数字转英文字母
     * Example: 1 => A, 2 => B, 3 => C
     *
     * @param $num
     * @return bool|mixed
     */
    private function numToLetter($num)
    {
        $map = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H',
            9 => 'I',
        );

        if (isset($map[$num])) {
            return $map[$num];
        }

        return false;
    }
}
