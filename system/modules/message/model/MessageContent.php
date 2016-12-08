<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\message\utils\Message as MessageUtil;
use application\modules\user\model\User;

class MessageContent extends Model
{

    const ONE_ON_ONE_CHAT = 1;   // 1对1聊天
    const MULTIPLAYER_CHAT = 2;   // 多人聊天
    const SYSTEM_NOTIFY = 3;   // 系统私信

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{message_content}}';
    }

    /**
     * 获取私信列表 - 分页型
     * @param integer $uid 用户UID
     * @param integer $type 私信类型，1表示一对一私信，2表示多人聊天，默认为1
     * @param integer $limit 结果集数目，默认为10
     * @param integer $offset 结果偏移量，默认为0
     * @return array 私信列表信息
     */
    public function fetchAllMessageListByUid($uid, $type = 1, $limit = 10, $offset = 0)
    {
        $uid = intval($uid);
        $type = is_array($type) ? ' IN (' . implode(',', $type) . ')' : "={$type}";
        $list = Ibos::app()->db->createCommand()
            ->from("{{message_user}} AS mu")
            ->join('{{message_list}} AS ml', "`mu`.`listid`=`ml`.`listid`")
            ->where("`mu`.`uid`={$uid} AND `ml`.`type`{$type} AND `mu`.`isdel` = 0 AND mu.messagenum > 0")
            ->order('mu.new DESC,mu.listctime DESC')
            ->limit($limit, $offset)
            ->queryAll();
        $this->parseMessageList($list); // 引用
        return $list;
    }

    /**
     * 获取指定私信列表中的私信内容
     * @param integer $listId 私信列表ID
     * @param integer $uid 用户ID
     * @param integer $sinceId 最早会话ID
     * @param integer $maxId 最新会话ID
     * @param integer $count 旧会话加载条数，默认为20
     * @return array 指定私信列表中的私信内容
     */
    public function fetchAllMessageByListId($listId, $uid, $sinceId = null, $maxId = null, $count = 20)
    {
        $listId = intval($listId);
        $uid = intval($uid);
        $sinceId = intval($sinceId);
        $maxId = intval($maxId);
        $count = intval($count);

        // 验证该用户是否为该私信成员
        if (!$listId || !$uid || !$messageInfo = $this->isInList($listId, $uid, false)) {
            return false;
        }

        $where = "`listid`={$listId} AND `isdel`=0";
        if ($sinceId > 0) {
            $where .= " AND `messageid` > {$sinceId}";
            $maxId > 0 && $where .= " AND `messageid` < {$maxId}";
            $limit = intval($count) + 1;
        } else {
            $maxId > 0 && $where .= " AND `messageid` < {$maxId}";
            // 多查询一条验证，是否还有后续信息
            $limit = intval($count) + 1;
        }
        $res = array();
        $res['data'] = $this->fetchAll(
            array(
                'condition' => $where,
                'order' => 'messageid DESC',
                'limit' => $limit
            )
        );
        $res['count'] = count($res['data']);
        if ($sinceId > 0) {
            $res['sinceid'] = isset($res['data'][0]['messageid']) ? $res['data'][0]['messageid'] : 0;
            $res['maxid'] = $res['count'] > 0 ? $res['data'][$res['count'] - 1]['messageid'] : 0;
            if ($res['count'] < $limit) {
                $res['maxid'] = 0;
            }
        } else {
            $res['sinceid'] = $res['data'][0]['messageid'];
            // 结果数等于查询数，则说明还有后续message
            if ($res['count'] == $limit) {
                // 去除结果的最后一条
                array_pop($res['data']);
                // 计数减一
                $res['count']--;
                // 取最后一条结果message_id
                $res['maxid'] = $res['data'][$res['count'] - 1]['messageid'];
            } else if ($res['count'] < $limit) {
                // 取最后一条结果message_id设置为0，表示结束
                $res['maxid'] = 0;
            }
        }

        return $res;
    }

    /**
     * 发送私信
     * @param array $data 私信信息，包括touid接受对象、title私信标题、content私信正文
     * @param integer $fromUid 发送私信的用户ID
     * @return boolean 是否发送成功
     */
    public function postMessage($data, $fromUid)
    {
        $fromUid = intval($fromUid);
        $data['touid'] = is_array($data['touid']) ? $data['touid'] : explode(',', $data['touid']);
        $data['users'] = array_filter(array_merge(array($fromUid), $data['touid']));  // 私信成员
        $data['mtime'] = time(); // 发起时间
        // Todo::判断接受者能否接受私信?
        // 添加或更新私信list
        if (false == $data['listid'] = $this->addMessageList($data, $fromUid)) {
            // 私信发送失败
            $this->addError('message', Ibos::lang('private message send fail', 'message.default'));
            return false;
        }
        // 存储私信成员
        if (false === $this->addMessageUser($data, $fromUid)) {
            $this->addError('message', Ibos::lang('private message send fail', 'message.default'));
            return false;
        }
        // 存储内容
        if (false == $this->addMessage($data, $fromUid)) {
            $this->addError('message', Ibos::lang('private message send fail', 'message.default'));
            return false;
        }
        $author = User::model()->fetchByUid($fromUid);
        $config['name'] = $author['realname'];
        $config['content'] = $data['content'];
        $config['ctime'] = date('Y-m-d H:i:s', $data['mtime']);
        $config['source_url'] = Ibos::app()->urlManager->createUrl('message/pm/index');
        // 推送私信
        MessageUtil::push('pm', $data['touid'], array('message' => $data['content']));
        return $data['listid'];
    }

    /**
     * 获取指定私信列表，指定结果集的最早会话ID，用于动态加载
     * @param integer $listId 私信列表ID
     * @param integer $nums 结果集数目
     * @return integer 最早会话ID
     */
    public function getSinceMessageId($listId, $nums)
    {
        $map['listid'] = $listId;
        $map['isdel'] = 0;
        $criteria = array(
            'select' => 'messageid',
            'condition' => '`listid` = :listid AND `isdel` = 0',
            'params' => array(':listid' => $listId),
            'order' => 'messageid DESC',
            'limit' => $nums
        );

        $info = $this->fetchAll($criteria);
        if ($nums > 0) {
            return intval($info[$nums - 1]['messageid'] - 1);
        } else {
            return 0;
        }
    }

    /**
     * 获取指定用户的未读的私信对话数目
     * @param integer $uid 用户ID
     * @return integer 指定用户未读的私信对话数目
     */
    public function countUnreadList($uid)
    {
        $unread = Ibos::app()->db->createCommand()
            ->select('count(*)')
            ->from('{{message_user}} AS mu')
            ->leftJoin('{{message_list}} AS ml', '`mu`.`listid` = `ml`.`listid`')
            ->where("mu.uid = {$uid} AND mu.new = 1")
            ->queryScalar();
        return intval($unread);
    }

    /**
     * 获取指定用户未读的私信数目
     * @param integer $uid 用户ID
     * @param integer $type 私信类型，1表示一对一私信，2表示多人聊天
     * @return integer 指定用户未读的私信数目
     */
    public function countUnreadMessage($uid, $type = 0)
    {
        $condition = "mu.uid = {$uid} AND mu.new = 2";
        if ($type) {
            $type = is_array($type) ? $type : explode(',', $type);
            $typeScope = implode(',', $type);
            $condition .= " AND `type` IN ({$typeScope})";
        }
        $unread = Ibos::app()->db->createCommand()
            ->select('count(*)')
            ->from('{{message_user}} AS mu')
            ->leftJoin('{{message_list}} AS ml', '`mu`.`listid` = `ml`.`listid`')
            ->where($condition)
            ->queryScalar();
        return intval($unread);
    }

    /**
     * 获取指定用户的列表私信数，用于分页
     * @param integer $uid 用户ID
     * @param mixed $type 可用数组形式
     * @return integer
     */
    public function countMessageListByUid($uid, $type = 1)
    {
        $uid = intval($uid);
        $type = is_array($type) ? ' IN (' . implode(',', $type) . ')' : "={$type}";
        $count = Ibos::app()->db->createCommand()
            ->select('count(*)')
            ->from("{{message_user}} AS mu")
            ->join('{{message_list}} AS ml', "`mu`.`listid`=`ml`.`listid`")
            ->where("`mu`.`uid`={$uid} AND `ml`.`type`{$type} AND `mu`.`isdel` = 0 AND mu.messagenum > 0")
            ->queryScalar();
        return intval($count);
    }

    /**
     * 回复私信
     * @param integer $listId 回复的私信list_id
     * @param string $content 回复内容
     * @param integer $fromUid 回复者ID
     * @return mix 回复失败返回false，回复成功返回本条新回复的messageid
     */
    public function replyMessage($listId, $content, $fromUid)
    {
        $listId = intval($listId);
        $fromUid = intval($fromUid);
        $time = time();

        // 获取当前私信列表list的type、min_max
        $listInfo = MessageList::model()->fetch(
            array(
                'select' => 'type,usernum,minmax',
                'condition' => 'listid = :listid',
                'params' => array(':listid' => $listId)
            )
        );
        if (!in_array($listInfo['type'], array(self::ONE_ON_ONE_CHAT, self::MULTIPLAYER_CHAT))) {
            return false;
        } else if (!$this->isInList($listId, $fromUid, false)) {
            return false;
        }
        // 添加新记录
        $data['listid'] = $listId;
        $data['content'] = $content;
        $data['mtime'] = $time;
        $newMessageId = $this->addMessage($data, $fromUid);
        unset($data);
        $command = Ibos::app()->db->createCommand();
        if (!$newMessageId) {
            return false;
        } else {
            $listData['lastmessage'] = serialize(array('fromuid' => $fromUid, 'content' => StringUtil::filterCleanHtml($content)));
            if (1 == $listInfo['type']) {
                // 一对一
                $listData['usernum'] = 2;
                // 重置最新记录
                MessageList::model()->updateByPk($listId, $listData);
                // 重置其他成员信息
                if ($listInfo['usernum'] < 2) {
                    $userData = array(
                        'listid' => $listId,
                        'uid' => array_diff(explode('_', $listInfo['minmax']), array($fromUid)),
                        'ctime' => $time
                    );
                    $this->addMessageUser($userData, $fromUid);
                } else {
                    // 重置其他成员信息
                    $command->setText("UPDATE {{message_user}} SET `new` = 2,`messagenum` = `messagenum`+1,`listctime` = {$time} WHERE `listid` = {$listId} AND `uid`!={$fromUid}")->execute();
                }
            } else {
                // 多人
                // 重置最新记录
                MessageList::model()->updateByPk($listId, $listData);
                // 重置其他成员信息
                $command->setText("UPDATE {{message_user}} SET `new` = 2,`messagenum` = `messagenum`+1,`listctime` = {$time} WHERE `listid` = {$listId} AND `uid`!={$fromUid}")->execute();
            }
            // 重置回复者的成员信息
            $command->setText("UPDATE {{message_user}} SET `ctime` = {$time},`messagenum` = `messagenum`+1,`listctime` = {$time} WHERE `listid` = {$listId} AND `uid`={$fromUid}")->execute();
        }
        return $newMessageId;
    }

    /**
     * 验证指定用户是否是指定私信列表的成员
     * @param integer $listId 私信列表ID
     * @param integer $uid 用户ID
     * @param boolean $showDetail 是否显示详细，默认为false
     * @return array 如果是成员返回相关信息，不是则返回空数组
     */
    public function isInList($listId, $uid, $showDetail = false)
    {
        $listId = intval($listId);
        $uid = intval($uid);
        $showDetail = $showDetail ? 1 : 0;
        static $isMember = array();
        if (!isset($isMember[$listId][$uid][$showDetail])) {
            $map['listid'] = $listId;
            $map['uid'] = $uid;
            $rec = MessageUser::model()->findByAttributes($map);
            if ($showDetail) {
                $isMember[$listId][$uid][$showDetail] = $rec->attributes;
            } else {
                $isMember[$listId][$uid][$showDetail] = $rec['uid'];
            }
        }
        return $isMember[$listId][$uid][$showDetail];
    }

    /**
     * 格式化，私信列表数据
     * @param array $list 私信列表数据
     * @return array 返回格式化后的私信列表数据
     */
    private function parseMessageList(&$list)
    {
        foreach ($list as &$v) {
            $v['lastmessage'] = StringUtil::utf8Unserialize($v['lastmessage']);
            $v['lastmessage']['touid'] = $this->parseToUidByMinMax($v['minmax'], $v['lastmessage']['fromuid']);
            $v['lastmessage']['user'] = User::model()->fetchByUid($v['lastmessage']['fromuid']);
            $v['touserinfo'] = User::model()->fetchAllByUids($v['lastmessage']['touid']);
        }
    }

    /**
     * 格式化用户数组，去除指定用户
     * @param string $min_max_uids 从小到大用“_”的用户ID字符串
     * @param integer $fromUid 指定用户ID
     * @return array 用户数组，去除指定用户
     */
    private function parseToUidByMinMax($minMaxUids, $fromUid)
    {
        $minMaxUids = explode('_', $minMaxUids);
        // 去除当前用户UID
        $toUid = array_values(array_diff($minMaxUids, array($fromUid)));

        // 自己发私信给自己的情况下，$toUid 的值为空数组。这个时候，直接设置 $toUid 为 $fromUid 即可。
        if (empty($toUid)) {
            $toUid = array($fromUid);
        }

        return $toUid;
    }

    /**
     * 添加新的私信列表
     * @param array $data 私信列表相关数据
     * @param integer $fromUid 发布人ID
     * @return mix 添加失败返回false，成功返回新的私信列表ID
     */
    private function addMessageList($data, $fromUid)
    {
        if (!$data['content'] || !is_array($data['users']) || !$fromUid) {
            return false;
        }
        $list['fromuid'] = $fromUid;
        $list['title'] = isset($data['title']) ? StringUtil::filterCleanHtml($data['title']) : StringUtil::filterCleanHtml(StringUtil::cutStr($data['content'], 20));
        $list['usernum'] = count($data['users']);
        $list['type'] = is_numeric($data['type']) ? $data['type'] : (2 == $list['usernum'] ? 1 : 2);
        $list['minmax'] = $this->getUidMinMax($data['users']);
        $list['mtime'] = $data['mtime'];
        $list['lastmessage'] = serialize(array(
            'fromuid' => $fromUid,
            'content' => StringUtil::filterDangerTag($data['content'])
        ));

        $listRec = MessageList::model()->findByAttributes(array('type' => $list['type'], 'minmax' => $list['minmax']));
        $listId = !empty($listRec) ? $listRec['listid'] : null;
        if ($list['type'] == 1 && $listId) {
            $_list['usernum'] = $list['usernum'];
            $_list['lastmessage'] = $list['lastmessage'];
            $saved = MessageList::model()->updateAll(
                $_list, '`type` = :type AND `minmax` = :minmax AND `listid`=:listid', array(
                    ':type' => $list['type'],
                    ':minmax' => $list['minmax'],
                    ':listid' => $listId
                )
            );
            if (!$saved) {
                $listId = false;
            }
        } else {
            $listId = MessageList::model()->add($list, true);
        }
        return $listId;
    }

    /**
     * 添加私信列表的成员
     * @param array $data 添加私信成员相关信息；私信列表ID：list_id，私信成员ID数组：member，当前时间：mtime
     * @param integer $fromUid 发布人ID
     * @return mix 添加成功返回新的私信成员表ID，添加失败返回false
     */
    private function addMessageUser($data, $fromUid)
    {
        if (!$data['listid'] || !is_array($data['users']) || !$fromUid) {
            return false;
        }
        $user['listid'] = $data['listid'];
        $user['listctime'] = $data['mtime'];
        
        // 去重
        $data['users'] = array_unique($data['users']);
        foreach ($data['users'] as $k => $u) {
            $userInfo = MessageUser::model()->findByAttributes(array('listid' => $data['listid'], 'uid' => $u));
            if (!empty($userInfo)) {
                $user['ctime'] = $userInfo['ctime'];
                $user['new'] = $u == $fromUid ? $userInfo['new'] : 2;
                $user['messagenum'] = $userInfo['messagenum'] + 1;
                MessageUser::model()->updateAll($user, "`listid` = :listid AND uid = :uid", array(':listid' => $data['listid'], ':uid' => $u));
            } else {
                $user['ctime'] = $u == $fromUid ? time() : 0;
                $user['new'] = $u == $fromUid ? 0 : 2;
                $user['messagenum'] = 1;
                $user['uid'] = $u;
                MessageUser::model()->add($user);
            }
        }
    }

    /**
     * 添加会话
     * @param array $data 会话相关数据
     * @param integer $fromUid 发布人ID
     * @return mix 添加失败返回false，添加成功返回新的会话ID
     */
    private function addMessage($data, $fromUid)
    {
        if (!$data['listid'] || !$data['content'] || !$fromUid) {
            return false;
        }
        $message['listid'] = $data['listid'];
        $message['fromuid'] = $fromUid;
        $message['content'] = $data['content'];
        $message['isdel'] = 0;
        $message['mtime'] = $data['mtime'];

        return $this->add($message, true);
    }

    /**
     * 输出从小到大用“_”连接的字符串
     * @param array $uids 用户ID数组
     * @return string 从小到大用“_”连接的字符串
     */
    private function getUidMinMax($uids)
    {
        sort($uids);
        return implode('_', $uids);
    }

}
