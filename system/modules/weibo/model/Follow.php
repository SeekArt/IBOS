<?php

namespace application\modules\weibo\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\message\model\Notify;
use application\modules\message\model\UserData;
use application\modules\user\model\User;

class Follow extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_follow}}';
    }

    /**
     * 添加关注 (关注用户)
     * @example
     * null：参数错误
     * 11：已关注
     * 12：关注成功(且为单向关注)
     * 13：关注成功(且为互粉)
     * @param integer $uid 发起操作的用户ID
     * @param integer $fid 被关注的用户ID或被关注的话题ID
     * @return boolean 是否关注成功
     */
    public function doFollow($uid, $fid)
    {
        if (intval($uid) <= 0 || $fid <= 0) {
            // 错误的参数
            $this->addError('doFollow', Ibos::lang('Parameters error', 'error'));
            return false;
        }
        if ($uid == $fid) {
            // 不能关注自己
            $this->addError('doFollow', Ibos::lang('Following myself forbidden', 'message.default'));
            return false;
        }
        if (!User::model()->fetchByUid($fid)) {
            // 被关注的用户不存在
            $this->addError('doFollow', Ibos::lang('Following people noexist', 'message.default'));
            return false;
        }
        // 获取双方的关注关系
        $followState = $this->getFollowState($uid, $fid);
        // 未关注状态
        if (0 == $followState['following']) {
            // 添加关注
            $map['uid'] = $uid;
            $map['fid'] = $fid;
            $map['ctime'] = time();
            $result = $this->add($map);
            $user = User::model()->fetchByUid($uid);
            $config = array(
                '{user}' => $user['realname'],
                '{url}' => Ibos::app()->urlManager->createUrl('weibo/personal/follower')
            );
            // 通知
            Notify::model()->sendNotify($fid, 'user_follow', $config);
            if ($result) {
                // 关注成功
                $this->addError('doFollow', Ibos::lang('Add follow success', 'message.default'));
                $this->_updateFollowCount($uid, $fid, true);   // 更新统计
                $followState['following'] = 1;
                return $followState;
            } else {
                // 关注失败
                $this->addError('doFollow', Ibos::lang('Add follow fail', 'message.default'));
                return false;
            }
        } else {
            // 已关注
            $this->addError('doFollow', Ibos::lang('Following', 'message.default'));
            return false;
        }
    }

    /**
     * 取消关注（关注用户 / 关注话题）
     * @example
     * 00：取消失败
     * 01：取消成功
     * @param integer $uid 发起操作的用户ID
     * @param integer $fid 被取消关注的用户ID或被取消关注的话题ID
     * @return boolean 是否取消关注成功
     */
    public function unFollow($uid, $fid)
    {
        $map['uid'] = $uid;
        $map['fid'] = $fid;
        // 获取双方的关注关系
        $followState = $this->getFollowState($uid, $fid);
        if ($followState['following'] == 1) {
            // 已关注
            if ($this->deleteAllByAttributes($map)) {
                // 操作成功
                $this->addError('unFollow', Ibos::lang('Operation succeed', 'message'));
                $this->_updateFollowCount($uid, $fid, false); // 更新统计
                $followState['following'] = 0;
                return $followState;
            } else {
                // 操作失败
                $this->addError('unFollow', Ibos::lang('Operation failure', 'message'));
                return false;
            }
        } else {
            // 未关注
            // 操作失败
            $this->addError('unFollow', Ibos::lang('Operation failure', 'message'));
            return false;
        }
    }

    /**
     * 获取用户uid与用户fid的关注状态，以uid为主
     * @param integer $uid 用户ID
     * @param integer $fid 用户ID
     * @return integer 用户关注状态，格式为array('following'=>1,'follower'=>1)
     */
    public function getFollowState($uid, $fid)
    {
        $followState = $this->getFollowStateByFids($uid, $fid);
        return $followState[$fid];
    }

    /**
     * 批量获取用户uid与一群人fids的彼此关注状态
     * @param integer $uid 用户ID
     * @param array $fids 用户ID数组
     * @return array 用户uid与一群人fids的彼此关注状态
     */
    public function getFollowStateByFids($uid, $fids)
    {
        is_array($fids) && array_map('intval', $fids);
        $_fids = is_array($fids) ? implode(',', $fids) : $fids;
        if (empty($_fids)) {
            return array();
        }
        $followData = $this->fetchAll(" ( uid = '{$uid}' AND fid IN({$_fids}) ) OR ( uid IN({$_fids}) AND fid = '{$uid}')");
        $followStates = $this->_formatFollowState($uid, $fids, $followData);
        return $followStates[$uid];
    }

    /**
     * 获取指定用户的关注列表  分页
     * @param integer $uid 用户ID
     * @param integer $offset 页面偏移
     * @param integer $limit 每页条数
     * @return array 指定用户的关注用户
     */
    public function getFollowingList($uid, $offset = 0, $limit = 10)
    {
        $list = $this->fetchAll(array(
                'condition' => "`uid`={$uid}",
                'order' => '`followid` DESC',
                'offset' => $offset,
                'limit' => $limit
            )
        );
        return $list;
    }

    /**
     * 获取指定用户的关注列表  不分页
     * @param integer $uid 用户ID
     * @return array 指定用户的关注用户
     */
    public function getFollowingListAll($uid)
    {
        $list = $this->fetchAll(array('condition' => "`uid`={$uid}", 'order' => '`followid` DESC'));
        return $list;
    }

    /**
     * 获取指定用户的粉丝列表
     * @param integer $uid 用户ID
     * @param integer $limit 结果集数目，默认为10
     * @return array 指定用户的粉丝列表
     */
    public function getFollowerList($uid, $offset = 0, $limit = 10)
    {
        $criteria = array(
            'condition' => "`fid`={$uid}",
            'order' => '`followid` DESC',
            'offset' => $offset,
            'limit' => $limit
        );
        // 粉丝列表
        $list = $this->fetchAll($criteria);
        // 格式化数据
        foreach ($list as $key => $value) {
            $uid = $value['uid'];
            $fid = $value['fid'];
            $list[$key]['uid'] = $fid;
            $list[$key]['fid'] = $uid;
        }
        return $list;
    }

    /**
     * 获取指定用户的关注与粉丝数
     * @param array $uids 用户ID数组
     * @return array 指定用户的关注与粉丝数
     */
    public function getFollowCount($uids)
    {
        $count = array();
        // 初始化关注状态
        foreach ($uids as $uid) {
            $count[$uid] = array('following' => 0, 'follower' => 0);
        }
        $followingMap = array('in', 'uid', $uids);
        $followerMap = array('in', 'fid', $uids);
        // 关注数
        $following = $this->getDbConnection()->createCommand()
            ->select('COUNT(1) AS `count`,`uid`')
            ->from($this->tableName())
            ->where(array('and', $followingMap))
            ->group('uid')
            ->queryAll();
        foreach ($following as $v) {
            $count[$v['uid']]['following'] = $v['count'];
        }
        // 粉丝数
        $follower = $this->getDbConnection()->createCommand()
            ->select('COUNT(1) AS `count`,`fid`')
            ->from($this->tableName())
            ->where(array('and', $followerMap))
            ->group('fid')
            ->queryAll();
        foreach ($follower as $v) {
            $count[$v['fid']]['follower'] = $v['count'];
        }

        return $count;
    }

    /**
     * 根据两个uid获取这两个人互相关注的用户ID
     * @param integer $uid 第一个用户ID
     * @param integer $secondUid 第二个用户ID
     * @return array 互相关注的用户ID数组
     */
    public function getBothFollow($uid, $secondUid)
    {
        $con = "uid = %d";
        $firstfids = $this->fetchAll(array('select' => 'fid', 'condition' => sprintf($con, $uid)));
        $secondfids = $this->fetchAll(array('select' => 'fid', 'condition' => sprintf($con, $secondUid)));
        $first = Convert::getSubByKey($firstfids, 'fid');
        $second = Convert::getSubByKey($secondfids, 'fid');
        $bothfollowUid = array_intersect($first, $second);
        return $bothfollowUid;
    }

    /**
     * 获取第二关注的用户列表 ($uid关注的人也关注$secondUid)
     * @param integer $uid 当前用户
     * @param integer $secondUid 查找第二关注的用户
     * @return array
     */
    public function getSecondFollow($uid, $secondUid)
    {
        $followList = $this->getFollowingListAll($uid);
        $fids = Convert::getSubByKey($followList, 'fid');
        $criteria = array(
            'select' => 'uid',
            'condition' => sprintf("FIND_IN_SET(uid,'%s') AND fid = %d", implode(',', $fids), $secondUid)
        );
        $result = $this->fetchAll($criteria);
        return Convert::getSubByKey($result, 'uid');
    }

    /**
     * 更新关注数目
     * @param integer $uid 操作用户ID
     * @param array $fids 被操作用户ID数组
     * @param boolean $inc 是否为加数据，默认为true
     * @return void
     */
    private function _updateFollowCount($uid, $fids, $inc = true)
    {
        !is_array($fids) && $fids = explode(',', $fids);
        // 添加关注数
        UserData::model()->updateKey('following_count', count($fids), $inc, $uid);
        foreach ($fids as $fid) {
            // 添加粉丝数
            UserData::model()->updateKey('follower_count', 1, $inc, $fid);
            UserData::model()->updateKey('new_folower_count', 1, $inc, $fid);
        }
    }

    /**
     * 格式化，用户的关注数据
     * @param integer $uid 用户ID
     * @param array $fids 用户ID数组
     * @param array $followData 关注状态数据
     * @return array 格式化后的用户关注状态数据
     */
    private function _formatFollowState($uid, $fids, $followData)
    {
        $followStates = array();
        !is_array($fids) && $fids = explode(',', $fids);
        foreach ($fids as $fid) {
            $followStates[$uid][$fid] = array('following' => 0, 'follower' => 0);
        }
        foreach ($followData as $v) {
            if ($v['uid'] == $uid) {
                $followStates[$v['uid']][$v['fid']]['following'] = 1;
            } else if ($v['fid'] == $uid) {
                $followStates[$v['fid']][$v['uid']]['follower'] = 1;
            }
        }
        return $followStates;
    }

}
