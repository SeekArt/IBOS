<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\model\Source;
use application\core\utils as util;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CHtml;

class Comment extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{comment}}';
    }

    /**
     * 获取某条评论下的回复ID
     *
     * @param integer $cid
     * @return array
     */
    public function fetchReplyIdByCid($cid)
    {
        $criteria = array(
            'select' => 'cid',
            'condition' => '`rowid` = :rowid',
            'params' => array(':rowid' => $cid),
        );
        $result = $this->fetchAll($criteria);

        return util\Convert::getSubByKey($result, 'cid');
    }

    /**
     * 添加评论操作
     *
     * @param array $data 评论数据
     * @param boolean $notCount 是否统计到未读评论
     * @param array $lessUids 除去@用户ID
     * @return boolean 是否添加评论成功
     */
    public function addComment($data, $forApi = false, $notCount = false, $lessUids = null, $isShare = false)
    {
        // 检测数据安全性
        $add = $this->escapeData($data);
        if ($add['content'] === '') {
            // 评论内容不可为空
            $this->addError('comment',
                util\Ibos::lang('Required comment content', 'message.default'));

            return false;
        }
        $add['isdel'] = 0;
        $add['detail'] = isset($data['detail']) ? str_replace("{realname}", '我',
            $data['detail']) : '';
        $res = $this->add($add, true);

        if ($res) {
            // 获取排除@用户ID
            isset($data['touid']) && !empty($data['touid']) && $lessUids[] = intval($data['touid']);
            // 获取用户发送的内容，仅仅以//进行分割
            $scream = explode('//', $data['content']);
            // 发送@消息
            $url = isset($data['url']) ? $data['url'] : '';
            $detail = isset($data['detail']) ? str_replace("{realname}",
                User::model()->fetchRealnameByUid($data['touid']),
                $data['detail']) : '';
            Atme::model()->addAtme('message', 'comment', trim($scream[0]), $res,
                null, $lessUids, $url, $detail);
            // 被评论内容的“评论统计数”加1，同时可检测出module，table，rowid的有效性
            if ($add['table'] == 'feed') {
                $table = 'application\modules\message\model\Feed';
            } else {
                $table = 'application\modules\\' . $add['module'] . '\\model\\' . ucfirst($add['table']);
            }
            if (!$isShare) {
                $pk = $table::model()->getTableSchema()->primaryKey;
                $table::model()->updateCounters(array('commentcount' => 1),
                    "`{$pk}` = {$add['rowid']}");
            }
//            $pk = $table::model()->getTableSchema()->primaryKey;
//            $table::model()->updateCounters(array('commentcount' => 1),
//                "`{$pk}` = {$add['rowid']}");
            // 给模块UID添加一个未读的评论数 原作者
            if (util\Ibos::app()->user->uid != $add['moduleuid'] && $add['moduleuid'] != '') {
                !$notCount && UserData::model()->updateKey('unread_comment', 1,
                    true, $add['moduleuid']);
            }
            // 回复发送提示信息
            if (!empty($add['touid']) && $add['touid'] != util\Ibos::app()->user->uid && $add['touid'] != $add['moduleuid']) {
                !$notCount && UserData::model()->updateKey('unread_comment', 1,
                    true, $add['touid']);
            }
            // 加积分操作
            if ($add['table'] == 'feed') {
                if (util\Ibos::app()->user->uid != $add['uid']) {
                    UserUtil::updateCreditByAction('addcomment',
                        util\Ibos::app()->user->uid);
                    UserUtil::updateCreditByAction('getcomment',
                        $data['moduleuid']);
                }
                Feed::model()->cleanCache($add['rowid']);
            }
            // 发送提醒，这里在数据库的值(notify_node的comment)暂时设为0，不需要发送
            if ($add['touid'] != util\Ibos::app()->user->uid || $add['moduleuid'] != util\Ibos::app()->user->uid && $add['moduleuid'] != '') {
                $author = User::model()->fetchByUid(util\Ibos::app()->user->uid);
                $config['{name}'] = $author['realname'];
                $sourceInfo = Source::getCommentSource($add, $forApi);
                $config['{url}'] = isset($add['url']) ? $add['url'] : '';
                $config['{sourceContent}'] = util\StringUtil::parseHtml($sourceInfo['source_content']);
                if (!empty($add['touid'])) {
                    // 回复
                    $config['{commentType}'] = '回复了我的评论:';
                    Notify::model()->sendNotify($add['touid'], 'comment',
                        $config);
                } else {
                    // 评论
                    $config['{commentType}'] = '评论了我的微博:';
                    if (!empty($add['moduleuid'])) {
                        Notify::model()->sendNotify($add['moduleuid'],
                            'comment', $config);
                    }
                }
            }
        }

        return $res;
    }

    /**
     * 删除评论
     *
     * @param array $ids 评论ID数组
     * @param integer $uid 用户UID
     * @param array $module 评论所属应用   积分加减时用到
     * @return boolean 是否删除评论成功
     */
    public function deleteComment($ids, $uid = null, $module = '')
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $map = array('and');
        $map[] = array('in', 'cid', $ids);
        $comments = $this->getDbConnection()->createCommand()
            ->select('cid,module,table,rowid,moduleuid,uid')
            ->from($this->tableName())
            ->where($map)
            ->queryAll();
        if (empty($comments)) {
            return false;
        }
        // 删除@信息
        foreach ($comments as $value) {
            Atme::model()->deleteAtme($value['table'], null, $value['cid'],
                null);
        }

        // 模块回调，减少模块的评论计数
        // 已优化: 先统计出哪篇资源需要减几, 然后再减. 这样可以有效减少数据库操作次数
        $_comments = array();
        // 统计各table、rowid对应的评论
        foreach ($comments as $comment) {
            $_comments[$comment['table']][$comment['rowid']]['id'] = $comment['cid'];
            $_comments[$comment['table']][$comment['rowid']]['module'] = $comment['module'];
        }
        // 删除评论：先删除评论，再处理统计
        $cids = util\Convert::getSubByKey($comments, 'cid');
        $res = $this->updateAll(array('isdel' => 1),
            "`cid` IN (" . implode(',', $cids) . ")");
        if ($res) {
            // 更新统计数目
            foreach ($_comments as $tableName => $rows) {
                foreach ($rows as $rowid => $c) {
                    // 模块表格“评论统计”统一使用 commentcount 字段名
                    if ($tableName == 'feed') {
                        $_table = 'application\modules\message\model\Feed';
                    } else {
                        $_table = 'application\modules\\' . $c['module'] . '\model\\' . ucfirst($tableName);
                    }
                    $field = $_table::model()->getTableSchema()->primaryKey;
                    if (empty($field)) {
                        $field = $tableName . 'id';
                    }
                    $_table::model()->updateCounters(array('commentcount' => -count($c['id'])),
                        "`{$field}`={$rowid}");
                    if ($module == 'weibo' || $module == 'feed') {
                        $_table::model()->cleanCache($rowid);
                    }
                }
            }
            if ($uid) {
                UserUtil::updateCreditByAction('delcomment', $uid);
            }
        }
        $this->addError('deletecomment',
            $res != false ? util\Ibos::lang('Operation succeed',
                'message') : util\Ibos::lang('Operation failure', 'message'));

        return $res;
    }

    /**
     * 获取评论列表，已在后台被使用
     *
     * @param mixed $map 查询条件
     * @param string $order 排序条件，默认为cid ASC
     * @param integer $limit 结果集数目，默认为10
     * @param boolean $isReply 是否显示回复信息
     * @return array 评论列表信息
     */
    public function getCommentList($map = null, $order = 'cid ASC', $limit = 10, $offset = 0, $isReply = false)
    {
        if ($this->getCurrentModule() == "message") {
            $confilter = array();
        } else {
            $confilter = array('in', 'module', array('message', $this->getCurrentModule()));
        }
        $list = $this->getDbConnection()->createCommand()
            ->select('*')
            ->from($this->tableName())
            ->where($map)
            ->andWhere('isdel = 0')
            ->andWhere($confilter)
            ->order($order)
            ->limit($limit)
            ->offset($offset)
            ->queryAll();
        $uid = util\Ibos::app()->user->uid;
        $isAdministrator = util\Ibos::app()->user->isadministrator;
        foreach ($list as $k => &$v) {
            if (!empty($v['tocid']) && $isReply) {
                $replyInfo = $this->getCommentInfo($v['tocid'], false);
                $v['replyInfo'] = util\Ibos::lang('Reply comment',
                    'message.default', array(
                        '{param}' => "uid=" . $replyInfo['user_info']['uid'],
                        '{space_url}' => $replyInfo['user_info']['space_url'],
                        '{realname}' => $replyInfo['user_info']['realname'],
                        '{url}' => $v['url'],
                        '{detail}' => $replyInfo['content']
                    ));
            } elseif ($v['module'] === 'weibo') {
                $feedData = FeedData::model()->fetchByPk($v['rowid']);
                $feed = Feed::model()->fetchByPk($v['rowid']);
                $user = User::model()->fetchByUid($feed['uid']);
                $v['replyInfo'] = util\Ibos::lang('Reply comment',
                    'message.default', array(
                        '{param}' => "uid=" . $feed['uid'],
                        '{space_url}' => $user['space_url'],
                        '{realname}' => $user['realname'],
                        '{url}' => $v['url'],
                        '{detail}' => $feedData['feedcontent']
                    ));
            } else {
                $v['replyInfo'] = '';
            }

            // 解析评论表情
            $v['content'] = util\StringUtil::parseHtml($v['content']);
            $v['content'] = util\StringUtil::purify($v['content']);

            $v['isCommentDel'] = $isAdministrator || $uid === $v['uid'];
            $v['user_info'] = User::model()->fetchByUid($v['uid']);
            $v['sourceInfo'] = Source::getCommentSource($v);
            if (!empty($v['attachmentid'])) {
                $v['attach'] = util\Attach::getAttach($v['attachmentid']);
            }
        }

        return $list;
    }

    /**
     * 获取评论信息
     *
     * @param integer $id 评论ID
     * @param boolean $source 是否显示资源信息，默认为true
     * @return array 获取评论信息
     */
    public function getCommentInfo($id, $source = true)
    {
        $id = intval($id);
        if (empty($id)) {
            $this->addError('get',
                util\Ibos::lang('Parameters error', 'error'));  // 错误的参数
            return false;
        }
        $info = util\Cache::get('comment_info_' . $id);
        if ($info) {
            return $info;
        } else {
            $info = $this->fetchByPk($id);
            $info['user_info'] = User::model()->fetchByUid($info['uid']);
            $source && $info['sourceInfo'] = Source::getCommentSource($info);
            $source && util\Cache::set('comment_info_' . $id,
                $info); // (回复)没有读全所有评论信息则不缓存 by hzh
            return $info;
        }
    }

    /**
     * 根据条件数组统计评论/回复条数
     *
     * @param array $map
     * @return integer
     */
    public function countCommentByMap($map)
    {
        return $this->getDbConnection()->createCommand()
            ->select('count(cid)')
            ->from($this->tableName())
            ->where($map)
            ->queryScalar();
    }

    /**
     * 检测数据安全性
     *
     * @param array $data 待检测的数据
     * @return array 验证后的数据
     */
    private function escapeData($data)
    {
        $add['module'] = $data['module'];
        $add['table'] = $data['table'];
        $add['rowid'] = intval($data['rowid']);
        $add['uid'] = util\Ibos::app()->user->uid;
        $add['moduleuid'] = intval($data['moduleuid']);
        $add['content'] = $data['content'];
        $add['tocid'] = isset($data['tocid']) ? intval($data['tocid']) : 0;
        $add['touid'] = isset($data['touid']) ? intval($data['touid']) : 0;
        $add['data'] = serialize(isset($data['data']) ? $data['data'] : array());
        $add['ctime'] = TIMESTAMP;
        $add['from'] = isset($data['from']) ? intval($data['from']) : util\Env::getVisitorClient();
        $add['attachmentid'] = isset($data['attachmentid']) ? $data['attachmentid'] : '';
        $add['url'] = isset($data['url']) ? $data['url'] : '';

        return $add;
    }

    /**
     * 评论处理方法，包含彻底删除、假删除与恢复功能
     *
     * @param integer $id 评论ID
     * @param string $type 操作类型，delComment假删除、deleteComment彻底删除、commentRecover恢复
     * @return array 评论处理后，返回的数组操作信息
     */
    public function doEditComment($id, $type)
    {
        $return = false;
        if (empty($id)) {
            // do nothing
        } else {
            $cid = is_array($id) ? implode(',', $id) : intval($id);
            $con = sprintf("cid = %d", $cid);
            if ($type == 'deleteComment') {
                $res = $this->deleteAll($con);
            } else {
                if ($type == 'commentRecover') {
                    $res = $this->commentRecover($id);
                } else {
                    $res = $this->deleteComment($id);
                }
            }
            if ($res != false) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * 评论恢复操作
     *
     * @param integer $id 评论ID
     * @return boolean 评论是否恢复成功
     */
    public function commentRecover($id)
    {
        if (empty($id)) {
            return false;
        }
        $con = 'cid = ' . $id;
        $criteria = array(
            'select' => 'cid,module,`table`,rowid,moduleuid,uid',
            'condition' => $con
        );
        $comment = $this->fetch($criteria);
        $save['isdel'] = 0;
        if ($this->updateAll($save, $con)) {
            $tableName = $comment['table'];
            $_table = 'application\modules\\' . $comment['module'] . '\model\\' . ucfirst($tableName);
            $field = $_table::model()->getTableSchema()->primaryKey;
            if (empty($field)) {
                $field = $tableName . 'id';
            }
            $_table::model()->updateCounters(array('commentcount' => 1),
                "`" . $field . "`=" . $comment['rowid']);
            // 删除微博缓存
            switch ($comment['table']) {
                case 'feed':
                    $feedIds = $this->fetch(array(
                        'select' => 'rowid',
                        'condition' => $con
                    ));
                    $feedId = array($feedIds['rowid']);
                    Feed::model()->cleanCache($feedId);
                    break;
            }

            return true;
        }

        return false;
    }

    /**
     * 拿出新闻或者日志或者其他id的第一层评论
     *
     * @param  integer $id 新闻或者日志或者其他id
     * @return array  外层评论cid
     */
    public function getCidsByRowId($id)
    {
        $correctModuleName = $this->getCurrentModule();
        $list = $this->getDbConnection()->createCommand()
            ->select('cid')
            ->from($this->tableName())
            ->where("rowid = :id", array(':id' => $id))
            ->andWhere('module = :module', array(':module' => $correctModuleName))
            ->queryColumn();
        return $list;
    }


    /**
     * 获取 getCommentList 需要的条件（$map）
     *
     * @param integer $rowid
     * @return string
     */
    public function getMapForGetCommentList($rowid)
    {
        // 获取该条新闻下的所有评论（包括回复）
        $cidList = $this->getCidsByRowId($rowid);
        array_push($cidList, $rowid);

        // 过滤 cid，确保所有 cid 为 integer 类型
        $cidList = array_map(function ($item) {
            return (int)$item;
        }, $cidList);

        $cids = implode(',', $cidList);
        $map = "`rowid` IN ({$cids})";

        return $map;
    }

    /**
     * 返回当前模块（如，article）
     * 备注：主要用于过滤多余的评论。当不同模块的 rowid 一致的时候，如果不通过模块名过滤，就会出现其他模块的评论。
     *
     * @return array
     */
    public function getCurrentModule()
    {
        $correctModuleName = util\Ibos::app()->setting->get('correctModuleName');
        if (empty($correctModuleName)) {
            $correctModuleName = util\Ibos::getCurrentModuleName();
        }

        return $correctModuleName;
    }

}
