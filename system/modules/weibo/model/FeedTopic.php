<?php

namespace application\modules\weibo\model;

use application\core\model\Model;
use application\core\utils\Cache;
use application\core\utils\StringUtil;

class FeedTopic extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{feed_topic}}';
    }

    /**
     * 添加话题
     * @param type $content
     * @param type $feedId
     * @param type $type
     * @return type
     */
    public function addTopic($content, $feedId = false, $type)
    {
        $content = str_replace("＃", "#", $content);
        preg_match_all("/#([^#]*[^#^\s][^#]*)#/is", $content, $arr);
        $arr = array_unique($arr[1]);
        $topicIds = array();
        foreach ($arr as $v) {
            $topicIds[] = $this->addKey($v, $feedId, $type);
        }
        if (count($topicIds) == 1) {
            return $topicIds[0];
        }
        return $topicIds;
    }

    /**
     * 添加话题详细操作
     * @param type $key
     * @param type $feedId
     * @param type $type
     * @return type
     */
    private function addKey($key, $feedId, $type)
    {
        $map['topicname'] = trim(preg_replace("/#/", '', StringUtil::filterCleanHtml($key)));
        $topic = $this->fetchByAttributes($map);
        if ($topic) {
            $this->updateCounters(array('count' => 1), sprintf("topicname = '%s'", $map['topicname']));
            if ($topic['recommend'] == 1) {
                Cache::rm('feed_topic_recommend'); //清除缓存
            }
            if ($feedId) {
                $this->addFeedJoinTopic($map['topicname'], $feedId, $type, true);
            }
        } else {
            $map['count'] = 1;
            $map['ctime'] = time();
            $topicId = $this->add($map, true);
            if ($feedId) {
                $this->addFeedJoinTopic($topicId, $feedId, $type);
            }
            return $topicId;
        }
    }

    /**
     * 添加微博与话题的关联
     * @param mixed $topicNameOrId
     * @param integer $feedId
     * @param type $type
     * @param boolean $isExist
     */
    private function addFeedJoinTopic($topicNameOrId, $feedId, $type, $isExist = false)
    {
        if ($isExist) {
            $topicId = $this->getDbConnection()->createCommand()
                ->select('topicid')
                ->from($this->tableName())
                ->where(sprintf("topicname = '%s'", $topicNameOrId))
                ->queryScalar();
        } else {
            $topicId = $topicNameOrId;
        }

        $add['feedid'] = $feedId;
        $add['topicid'] = $topicId;
        if (is_null($type)) {
            $add['type'] = 0;
        } else {
            $add['type'] = $type;
        }
        //	$add['transpond_id'] = $data['transpond_id'];
        FeedTopicLink::model()->add($add);
    }

    /**
     * 删除微博与话题关联
     * @param integer $feedId
     */
    public function deleteWeiboJoinTopic($feedId)
    {
        $del['feedid'] = $feedId;
        $topicId = $this->getDbConnection()->createCommand()
            ->select('topicid')
            ->from('{{feed_topic_link}}')
            ->where('feedid = ' . intval($feedId))
            ->queryScalar();
        if ($topicId) {
            FeedTopicLink::model()->deleteAllByAttributes($del);
            $this->updateCounters(array('count' => 1), 'topicid = ' . intval($topicId));
            $recommend = $this->getDbConnection()->createCommand()
                ->select('recommend')
                ->from($this->tableName())
                ->where('topicid = ' . $topicId)
                ->queryScalar();

            if ($recommend == 1) {
                Cache::rm('feed_topic_recommend'); //清除缓存
            }
        }
    }

}
