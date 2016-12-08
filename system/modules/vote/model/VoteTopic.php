<?php
/**
 * 投票模块------vote_topic 数据层文件
 *
 * @namespace application\modules\vote\model
 * @filename VoteTopic.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/19 11:05
 */

namespace application\modules\vote\model;


use application\core\model\Model;
use application\core\utils\ArrayUtil;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

class VoteTopic extends Model
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{vote_topic}}';
    }

    /**
     * 添加一条记录
     *
     * @param integer $voteId
     * @param integer $type
     * @param string $subject
     * @param $maxSelectNum
     * @param integer $itemNum
     * @return false|int
     */
    public function addRecord($voteId, $type, $subject, $maxSelectNum, $itemNum)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);
        $type = filter_var($type, FILTER_SANITIZE_NUMBER_INT);
        $subject = StringUtil::filterCleanHtml($subject);
        $maxSelectNum = filter_var($maxSelectNum, FILTER_SANITIZE_NUMBER_INT);
        $itemNum = filter_var($itemNum, FILTER_SANITIZE_NUMBER_INT);

        $voteTopic = new self();
        $voteTopic->voteid = $voteId;
        $voteTopic->type = $type;
        $voteTopic->subject = $subject;
        $voteTopic->maxselectnum = $maxSelectNum;
        $voteTopic->itemnum = $itemNum;

        if ($voteTopic->save()) {
            return $voteTopic->topicid;
        }

        return false;
    }

    /**
     * 获取所有 topics
     *
     * @param $voteId
     * @return array
     */
    public function fetchTopics($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);

        return $this->fetchAll('voteid = :voteid', array(':voteid' => $voteId));
    }

    /**
     * 判断 topic 是否存在
     *
     * @param integer $topicId
     * @return bool
     */
    public function isExists($topicId)
    {
        return $this->exists('topicid = :topicid', array(':topicid' => $topicId));
    }

    /**
     * 通过 voteId 删除所有 topic
     *
     * @param $voteId
     * @return bool
     * @throws \CDbException
     */
    public function delAllByVoteId($voteId)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);

        $topics = $this->findAll('voteid = :voteid', array(':voteid' => $voteId));
        foreach ($topics as $topic) {
            $topic->delete();
        }

        return true;
    }

    /**
     * 获取某个投票下的所有题目及其子项目信息
     *
     * @param $voteId
     * @return array
     */
    public function fetchTopicsDetail($voteId)
    {
        $result = array();
        $uid = Ibos::app()->user->uid;
        $topics = $this->fetchTopics($voteId);

        // 该投票下没有一条题目
        if (empty($topics)) {
            return array();
        }

        // 获取每个话题下的投票项目
        foreach ($topics as $topic) {
            $topicId = $topic['topicid'];
            $topicItems = VoteItem::model()->fetchItems($topicId);
            // 获取当前用户的投票情况
            $voteItemCount = VoteItemCount::model()->fetchAllByTopicIdAndUid($topicId, $uid);
            $selectItemId = array();
            if (!empty($voteItemCount)) {
                $selectItemId = ArrayUtil::getColumn($voteItemCount, 'itemid');
            }

            $result[] = array(
                'voteid' => $topic['voteid'],
                'topicid' => $topicId,
                'maxselectnum' => $topic['maxselectnum'],
                'subject' => $topic['subject'],
                'type' => $topic['type'],
                'items' => $topicItems,
                'selectitemid' => $selectItemId,
            );
        }

        return $result;
    }
}