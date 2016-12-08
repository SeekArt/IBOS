<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\model\Source;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class FeedDigg extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{feed_digg}}';
    }

    /**
     * 查找指定动态的赞人员列表
     * @param integer $feedId 动态ID
     * @param integer $nums 获取的条数
     * @param integer $offset
     * @param string $order 排序方式
     * @return array 用户数组列表
     */
    public function fetchUserList($feedId, $nums, $offset = 0, $order = 'ctime DESC')
    {
        $criteria = array(
            'select' => 'uid,ctime',
            'condition' => sprintf('feedid = %d', $feedId),
            'order' => $order,
            'offset' => $offset,
            'limit' => $nums
        );
        $result = $this->fetchAll($criteria);
        if ($result) {
            foreach ($result as &$res) {
                $res['user'] = User::model()->fetchByUid($res['uid']);
                $res['diggtime'] = Convert::formatDate($res['ctime']);
            }
        } else {
            $result = array();
        }
        return $result;
    }

    /**
     * 检查所给的feedid是否已经赞
     * @param mixed $feedIds 动态ID数组
     * @param integer $uid
     * @return array
     */
    public function checkIsDigg($feedIds, $uid)
    {
        if (!is_array($feedIds)) {
            $feedIds = array($feedIds);
        }
        $res = array();
        $feedIds = array_filter($feedIds);
        if (!empty($feedIds)) {
            $criteria = array(
                'select' => 'feedid',
                'condition' => sprintf("`uid` = %d AND `feedid` IN (%s)", $uid, implode(',', $feedIds)),
            );

            $list = $this->fetchAll($criteria);
            foreach ($list as $v) {
                $res[$v['feedid']] = 1;
            }
        }
        return $res;
    }

    /**
     * 查找单条feed的指定uid是否已赞
     * @param integer $feedId 动态ID
     * @param integer $uid 用户ID
     * @return boolean
     */
    public function getIsExists($feedId, $uid)
    {
        $criteria = array(
            'select' => '1',
            'condition' => sprintf("feedid = %d AND uid = %d", $feedId, $uid)
        );
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    /**
     * 赞一个动态
     * @param integer $feedId 动态ID
     * @param integer $uid 用户ID
     * @return boolean 赞成功与否
     */
    public function addDigg($feedId, $uid)
    {
        $data ['feedid'] = $feedId;
        $data ['uid'] = $uid;
        $data['uid'] = !$data['uid'] ? Ibos::app()->user->uid : $data['uid'];
        if (!$data['uid']) {
            $this->addError('addDigg', '未登录不能赞');
            return false;
        }
        $isExit = $this->getIsExists($feedId, $uid);
        if ($isExit) {
            $this->addError('addDigg', '你已经赞过');
            return false;
        }

        $data ['ctime'] = time();
        $res = $this->add($data);
        if ($res) {
            $feed = Source::getSourceInfo('feed', $feedId);
            Feed::model()->updateCounters(array('diggcount' => 1), 'feedid = ' . $feedId);
            Feed::model()->cleanCache($feedId);
            $user = User::model()->fetchByUid($uid);
            $config['{user}'] = $user['realname'];
            $config['{sourceContent}'] = strip_tags($feed['source_body']);
            $config['{sourceContent}'] = str_replace('◆', '', $config['{sourceContent}']);
            $config['{sourceContent}'] = StringUtil::cutStr($config ['{sourceContent}'], 34);
            $config['{url}'] = $feed['source_url'];
            $config['{content}'] = Ibos::app()->getController()->renderPartial('application.modules.message.views.remindcontent', array(
                'recentFeeds' => Feed::model()->getRecentFeeds(),
            ), true);

            if (empty($config['{sourceContent}'])) {
                // 处理无文字微博（纯表情或纯图片）
                Notify::model()->sendNotify($feed['uid'], 'message_empty_digg', $config);
            } else {
                Notify::model()->sendNotify($feed['uid'], 'message_digg', $config);

            }
            //增加积分
            UserUtil::updateCreditByAction('diggweibo', $uid); //顶
            UserUtil::updateCreditByAction('diggedweibo', $feed['uid']); // 被顶
        }
        return $res;
    }

    /**
     * 取消赞
     * @param integer $feedId 动态ID
     * @param integer $uid 用户ID
     * @return boolean 取消成功与否
     */
    public function delDigg($feedId, $uid)
    {
        $data['feedid'] = $feedId;
        $data['uid'] = $uid;
        $data['uid'] = !$data['uid'] ? Ibos::app()->user->uid : $data['uid'];
        if (!$data['uid']) {
            $this->addError('delDigg', '未登录不能取消赞');
            return false;
        }
        $isExit = $this->getIsExists($feedId, $uid);
        if (!$isExit) {
            $this->addError('delDigg', '取消赞失败，您可能已取消过赞信息');
            return false;
        }
        $res = $this->deleteAllByAttributes($data);
        if ($res) {
            // 该条feed赞数-1
            Feed::model()->updateCounters(array('diggcount' => -1), 'feedid=' . $feedId);
            // 更新缓存
            Feed::model()->cleanCache($feedId);
        }
        return $res;
    }

}
