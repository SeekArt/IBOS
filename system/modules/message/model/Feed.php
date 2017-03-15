<?php

namespace application\modules\message\model;

use application\core\model\Log;
use application\core\model\Model;
use application\core\model\Source;
use application\core\utils as util;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\core as WbCore;
use application\modules\weibo\model\FeedTopic;
use application\modules\weibo\model\FeedTopicLink;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;

class Feed extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{feed}}';
    }

    /**
     * 获取最近的几条微博
     * @param integer $num 要获取的条数
     * @return array 微博数据
     */
    public function getRecentFeeds($num = 4)
    {
        $criteria = array(
            'select' => 'feedid',
            'condition' => "`module` = 'weibo'",
            'order' => 'ctime DESC',
            'offset' => 0,
            'limit' => $num,
            'group' => 'uid'
        );
        $feedIds = util\Convert::getSubByKey($this->fetchAll($criteria), 'feedid');
        return $this->getFeeds($feedIds);
    }

    /**
     * 获取微博列表
     * @param array $map 查询条件
     * @param integer $limit 结果集数目，默认为10
     * @param integer $offset 记录偏移量，默认为0 （即当前页数）
     * @param string $order 排序字段
     * @return array 微博列表数据
     */
    public function getList($map, $limit = 10, $offset = 0, $order = null)
    {
        $order = !empty($order) ? $order : 'feedid DESC';
        $criteria = array(
            'select' => 'feedid',
            'condition' => $map,
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
        );
        $feedIds = util\Convert::getSubByKey($this->fetchAll($criteria), 'feedid');
        return $this->getFeeds($feedIds);
    }

    /**
     * 获取指定用户所关注人的所有微博，默认为当前登录用户
     * @param string $where 查询条件
     * @param integer $limit 结果集数目，默认为10
     * @param integer $uid 指定用户ID，默认为空
     * @param integer $fgid 关组组ID，默认为空
     * @return array 指定用户所关注人的所有微博，默认为当前登录用户
     */
    public function getFollowingFeed($where = '', $limit = 10, $offset = 0, $uid = '')
    {
        $buid = intval(empty($uid) ? Ibos::app()->user->uid : $uid);
        // 加上自己的信息，若不需要屏蔽下语句
        $_where = !empty($where) ? "(a.uid = '{$buid}' OR b.uid = '{$buid}') AND ($where)" : "(a.uid = '{$buid}' OR b.uid = '{$buid}')";
        $feedlist = $this->getDbConnection()->createCommand()
            ->select('a.feedid')
            ->from('{{feed}} AS a')
            ->leftJoin('{{user_follow}} AS b', 'a.uid = b.fid AND b.uid = ' . $buid)
            ->where($_where)
            ->order('a.feedid DESC')
            ->limit($limit)
            ->offset($offset)
            ->queryAll();
        $feedids = util\Convert::getSubByKey($feedlist, 'feedid');
        return $this->getFeeds($feedids);
    }

    /**
     * 统计指定用户所关注人的所有微博微博数，默认为当前登录用户
     * @param string $where 查询条件
     * @param integer $uid 指定用户ID，默认为空
     * @return integer
     */
    public function countFollowingFeed($where = '', $uid = '')
    {
        $buid = intval(empty($uid) ? util\Ibos::app()->user->uid : $uid);
        // 加上自己的信息，若不需要屏蔽下语句
        $_where = !empty($where) ? "(a.uid = '{$buid}' OR b.uid = '{$buid}') AND ($where)" : "(a.uid = '{$buid}' OR b.uid = '{$buid}')";
        $count = $this->getDbConnection()->createCommand()
            ->select('count(a.feedid)')
            ->from('{{feed}} AS a')
            ->leftJoin('{{user_follow}} AS b', 'a.uid = b.fid AND b.uid = ' . $buid)
            ->where($_where)
            ->queryScalar();
        return $count;
    }

    /**
     * 获取指定动态的信息
     * @param integer $feed_id 动态ID
     * @return mixed 获取失败返回false，成功返回动态信息
     */
    public function get($feedId)
    {
        $feedList = $this->getFeeds(array($feedId));
        if (!$feedList) {
            $this->addError('get', util\Ibos::lang('Get info fail', 'message.default'));
            // 获取信息失败
            return false;
        } else {
            return $feedList[0];
        }
    }

    /**
     * 获取给定动态ID的动态信息
     * @param array $feedIds 动态ID数组
     * @return array 给定动态ID的动态信息
     */
    public function getFeeds($feedIds)
    {
        !is_array($feedIds) && $feedIds = explode(',', $feedIds);
        $feedList = array();
        $feedIds = array_filter(array_unique($feedIds));
        // 获取数据
        if (count($feedIds) > 0) {
            $cacheList = util\Cache::mget($feedIds, 'feed_');
        } else {
            return false;
        }
        // 按照传入ID顺序进行排序
        foreach ($feedIds as $key => $v) {
            if ($cacheList[$v]) {
                $feedList[$key] = $cacheList[$v];
            } else {
                $feed = $this->setFeedCache(array(), $v);
                $feedList[$key] = $feed[$v];
            }
        }
        return $feedList;
    }

    /**
     * 添加动态
     * @param integer $uid 操作用户ID
     * @param string $module 动态模块,默认为weibo
     * @param string $type 动态类型
     * @param array $data 动态相关数据
     * @param integer $rowid 应用资源ID，默认为0
     * @param string $table 资源表名，默认为feed
     * @param array $extUid 额外用户ID，默认为null
     * @param array $lessUids 去除的用户ID，默认为null
     * @param boolean $isAtMe 是否为进行发送，默认为true
     * @param integer $isRepost 是否为转发
     * @param string $url 消息来源地址
     * @param string $detail 消息来源描述说明
     * @return mixed 添加失败返回false，成功返回新的微博ID
     */
    public function put($uid, $module = 'weibo', $type = '', $data = array(), $rowid = 0, $table = 'feed', $extUid = null, $lessUids = null, $isAtMe = true, $isRepost = 0)
    {
        // 判断数据的正确性
        if (!$uid || $type == '') {
            $this->addError('putFeed', util\Ibos::lang('Operation failure', 'message'));
            return false;
        }
        // 微博类型合法性验证 - 临时解决方案
        if (!in_array($type, array('post', 'repost', 'postimage'))) {
            $type = 'post';
        }
        // 模块类型验证 用于分享框 - 临时解决方案
        if (!util\Module::getIsEnabled($module)) {
            $module = 'weibo';
            $type = 'post';
            $table = 'feed';
        }

        $table = strtolower($table);
        // 添加feed表记录
        $data['uid'] = $uid;
        $data['module'] = $module;
        $data['type'] = $type;
        $data['rowid'] = $rowid;
        $data['table'] = $table;
        $data['ctime'] = time();
        $data['from'] = isset($data['from']) ? intval($data['from']) : util\Env::getVisitorClient();
        $data['isdel'] = $data['commentcount'] = $data['diggcount'] = $data['repostcount'] = 0;
        $data['isrepost'] = $isRepost;
        $content = $this->formatFeedContent($data['body']);
        $data['body'] = $content['body'];
        $data['content'] = $content['content'];
        //分享到微博的应用资源，加入原资源链接
        $data['body'] .= isset($data['source_url']) ? $data['source_url'] : '';
        $data['content'] .= isset($data['source_url']) ? $data['source_url'] : '';
        // 添加动态信息
        $feedId = $this->add($data, true);
        if (!$feedId) {
            return false;
        }
        // 目前处理方案格式化数据
        $data['content'] = str_replace(chr(31), '', $data['content']);
        $data['body'] = str_replace(chr(31), '', $data['body']);
        // 添加关联数据
        $feedData = array(
            'feedid' => $feedId,
            'feeddata' => serialize($data),
            'clientip' => util\Env::getClientIp(),
            'feedcontent' => $data['body']
        );
        $feedDataId = FeedData::model()->add($feedData, true);
        // 添加动态成功后
        if ($feedId && $feedDataId) {
            // 发送通知消息 - 重点 - 需要简化把上节点的信息去掉.
            if ($data['isrepost'] == 1) {
                // 转发微博
                if ($isAtMe) {
                    $content = $data['content']; // 内容用户
                } else {
                    $content = $data['body'];
                }
                $extUid[] = isset($data['sourceInfo']['transpond_data']) ? $data['sourceInfo']['transpond_data']['uid'] : null; // 资源作者用户
                if ($isAtMe && !empty($data['curid'])) {
                    // 上节点用户
                    $appRowData = $this->get($data['curid']);
                    $extUid[] = $appRowData['uid'];
                }
            } else {
                // 其他微博
                $content = $data['content'];
                //更新最近@的人
                Atme::model()->updateRecentAt($content); // 内容用户
            }
            // 发送@消息
            $url = isset($data['url']) ? $data['url'] : util\Ibos::app()->urlManager->createUrl('weibo/personal/feed', array('feedid' => $feedId));
            $detail = isset($data['detail']) ? $data['detail'] : util\Ibos::lang('Published weibo', 'weibo.default', array('{url}' => $url, '{title}' => util\StringUtil::cutStr(preg_replace("/[\s]{2,}/", "", util\StringUtil::filterCleanHtml($data['body'])), 50)));
            Atme::model()->addAtme('weibo', 'feed', $content, $feedId, $extUid, $lessUids, $url, $detail);

            $data['clientip'] = util\Env::getClientIp();
            $data['feedid'] = $feedId;
            $data['feeddata'] = serialize($data);

            // 主动创建渲染后的缓存
            $return = $this->setFeedCache($data);
            $return['user_info'] = User::model()->fetchByUid($uid);
            $return['feedid'] = $feedId;
            $return['rowid'] = $data['rowid'];
            // 统计数修改
            if ($module == 'weibo') {
                UserData::model()->updateKey('feed_count', 1);
                UserData::model()->updateKey('weibo_count', 1);
            }
            return $return;
        } else {
            $this->addError('putFeed', util\Ibos::lang('Operation failure', 'message'));
            return false;
        }
    }

    /**
     * 截取微博内容，将微博中的URL替换成{ts_urlX}进行字符数目统计
     * @param string $content 微博内容
     * @param string $weiboNums 微博截取数目，默认为0
     * @return array 格式化后的微博内容，body与content
     */
    public function formatFeedContent($content, $weiboNums = 0)
    {
        // 拼装数据，如果是评论再转发、回复评论等情况，需要额外叠加对话数据
        $content = StringUtil::imgToExpression($content);
        $content = str_replace(util\Ibos::app()->setting->get('siteurl'), '[SITE_URL]', util\StringUtil::pregHtml($content));
        // 格式化微博信息 - URL
        $content = preg_replace_callback('/((?:https?|mailto|ftp):\/\/([^\x{2e80}-\x{9fff}\s<\'\"“”‘’，。}]*)?)/u', 'application\core\utils\StringUtil::formatFeedContentUrlLength', $content);
        if (isset($GLOBALS['replaceHash'])) {
            $replaceHash = $GLOBALS['replaceHash'];
            unset($GLOBALS['replaceHash']);
        } else {
            $replaceHash = array();
        }
        // 获取用户发送的内容，仅仅以//进行分割
        $scream = explode('//', $content);
        // 截取内容信息为微博内容字数 - 重点
        $feedNums = 0;
        if (empty($weiboNums)) {
            $feedNums = intval(util\Ibos::app()->setting->get('setting/wbnums'));
        } else {
            $feedNums = $weiboNums;
        }
        $body = array();
        // 还原URL操作
        $patterns = array_keys($replaceHash);
        $replacements = array_values($replaceHash);
        foreach ($scream as $value) {
            $tbody[] = $value;
            $bodyStr = implode('//', $tbody);
            if (util\StringUtil::getStrLength(ltrim($bodyStr)) > $feedNums) {
                break;
            }
            $body[] = str_replace($patterns, $replacements, $value);
            unset($bodyStr);
        }
        $data['body'] = implode('//', $body);
        // 获取用户发布内容
        $scream[0] = str_replace($patterns, $replacements, $scream[0]);
        $data['content'] = trim($scream[0]);

        return $data;
    }

    /**
     * 微博操作，彻底删除、假删除、回复
     * @param integer $feed_id 微博ID
     * @param string $type 微博操作类型，deleteFeed：彻底删除，delFeed：假删除，feedRecover：恢复
     * @param string $uid 删除微博的用户ID（区别超级管理员）
     * @return array 微博操作后的结果信息数组
     */
    public function doEditFeed($feedid, $type, $uid = null)
    {
        $return = array('isSuccess' => false);
        if (empty($feedid)) {
            // 暂时什么也不做
        } else {
            $feedid = is_array($feedid) ? implode(',', $feedid) : intval($feedid);
            $con = sprintf("feedid = %d", $feedid);
            $isdel = $type == 'delFeed' ? 1 : 0;
            if ($type == 'deleteFeed') {
                // 日志记录
                $msg = array(
                    'user' => util\Ibos::app()->user->username,
                    'ip' => util\Env::getClientIp(),
                    'id' => $feedid,
                    'value' => $this->get($feedid)
                );
                Log::write($msg, 'db', 'module.weibo.deleteFeed');
                // 删除微博相关信息
                $this->_deleteFeedAttach($feedid);
                // 彻底删除微博
                $res = $this->deleteAll($con);
            } else {
                $ids = explode(',', $feedid);
                $feedList = $this->getFeeds($ids);
                $res = $this->updateAll(array('isdel' => $isdel), $con);
                // 如果是恢复微博
                if ($type == 'feedRecover') {
                    // 添加微博数
                    foreach ($feedList as $v) {
                        UserData::model()->updateKey('feed_count', 1, true, $v['user_info']['uid']);
                        UserData::model()->updateKey('weibo_count', 1, true, $v['user_info']['uid']);
                    }
                } else {
                    // 反之，减少微博数
                    foreach ($feedList as $v) {
                        UserData::model()->updateKey('feed_count', -1, false, $v['user_info']['uid']);
                        UserData::model()->updateKey('weibo_count', -1, false, $v['user_info']['uid']);
                    }
                }
                // 删除微博缓存信息
                $this->cleanCache($ids);
                // 资源微博缓存相关微博
                $query = $this->fetchAll(
                    array(
                        'select' => 'feedid',
                        'condition' => sprintf("rowid = %d", $feedid)
                    )
                );
                $sids = util\Convert::getSubByKey($query, 'feedid');
                $sids && $this->cleanCache($sids);
            }
            // 删除评论信息
            $commentQuery = $this->getDbConnection()->createCommand()
                ->select('cid')
                ->from('{{comment}}')
                ->where(sprintf("`module` = 'weibo' AND `table` = 'feed' AND `rowid` = %d", $feedid))
                ->queryAll();
            $commentIds = util\Convert::getSubByKey($commentQuery, 'cid');
            $commentIds && Comment::model()->deleteComment($commentIds, null, 'weibo');
            // 删除话题相关信息
            FeedTopic::model()->deleteWeiboJoinTopic($feedid);
            // 删除@信息
            Atme::model()->deleteAtme('feed', null, $feedid);
            // 删除话题信息
            $topics = FeedTopicLink::model()->fetchAll(array('select' => 'topicid', 'condition' => 'feedid=' . $feedid));
            $topicId = util\Convert::getSubByKey($topics, 'topicid');
            $topicId && FeedTopic::model()->updateCounters(array('count' => -1), sprintf("FIND_IN_SET(topicid,'%s')", implode(',', $topicId)));
            FeedTopicLink::model()->deleteAll('feedid=' . $feedid);
            if ($res) {
                // TODO:是否记录日志，以及后期缓存处理
                $return = array('isSuccess' => true);
                // 积分操作
                $uid && UserUtil::updateCreditByAction('deleteweibo', $uid);
            }
        }

        return $return;
    }

    /**
     * 获取指定动态的信息，用于资源模型输出
     * @param integer $id 微博ID
     * @param boolean $forApi 是否提供API数据，默认为false
     * @return array 指定微博数据
     */
    public function getFeedInfo($id, $forApi = false)
    {
        $data = util\Cache::get('feed_info_' . $id);
        if ($data !== false && ($forApi === false)) {
            return $data;
        }
        $data = util\Ibos::app()->db->createCommand()
            ->from('{{feed}} a')
            ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
            ->where('a.feedid = ' . $id)
            ->queryRow();
        $fd = StringUtil::utf8Unserialize($data['feeddata']);

        $userInfo = User::model()->fetchByUid($data['uid']);
        $data['ctime'] = util\Convert::formatDate($data['ctime'], 'n月d日H:i');
        $data['content'] = $forApi ? util\StringUtil::parseForApi($fd['body']) : $fd['body'];
        $data['realname'] = $userInfo['realname'];
        // Todo::微博用户组信息
        $data['avatar_big'] = $userInfo['avatar_big'];
        $data['avatar_middle'] = $userInfo['avatar_middle'];
        $data['avatar_small'] = $userInfo['avatar_small'];
        unset($data['feeddata']);
        // 微博转发
        if ($data['type'] == 'repost') {
            $data['transpond_id'] = $data['rowid'];
            $data['transpond_data'] = $this->getFeedInfo($data['transpond_id'], $forApi);
        }

        // 附件处理
        if (!empty($fd['attach_id'])) {
            $data['has_attach'] = 1;
            $attach = util\Attach::getAttachData($fd['attach_id']);
            $attachUrl = util\File::getAttachUrl();
            foreach ($attach as $ak => $av) {
                $_attach = array(
                    'attach_id' => $av['aid'],
                    'attach_name' => $av['filename'],
                    'attach_url' => util\File::imageName($attachUrl . '/' . $av['attachment']),
                    'extension' => util\StringUtil::getFileExt($av['filename']),
                    'size' => $av['filesize']
                );
                if ($data['type'] == 'postimage') {
                    $_attach['attach_small'] = WbCommonUtil::getThumbImageUrl($av, WbCore\WbConst::ALBUM_DISPLAY_WIDTH, WbCore\WbConst::ALBUM_DISPLAY_HEIGHT);
                    $_attach['attach_middle'] = WbCommonUtil::getThumbImageUrl($av, WbCore\WbConst::WEIBO_DISPLAY_WIDTH, WbCore\WbConst::WEIBO_DISPLAY_HEIGHT);
                }
                $data['attach'][] = $_attach;
            }
        } else {
            $data['has_attach'] = 0;
        }
        $data['feedType'] = $data['type'];
        // 微博详细信息
        $feedInfo = $this->get($id);
        $data['source_body'] = $feedInfo['body'];
        $data['api_source'] = $feedInfo['api_source'];
        //一分钟缓存
        util\Cache::set('feed_info_' . $id, $data, 60);
        if ($forApi) {
            $data['content'] = util\StringUtil::realStripTags($data['content']);
            unset($data['isdel'], $data['fromdata'], $data['table'], $data['rowid']);
            unset($data['source_body']);
        }
        return $data;
    }

    /**
     * 清除指定用户指定动态的列表缓存
     * @param array $feedIds 动态ID数组，默认为空
     * @param integer $uid 用户ID，默认为空
     * @return void
     */
    public function cleanCache($feedIds = array(), $uid = '')
    {
        if (!empty($uid)) {
            util\Cache::rm('feed_foli_' . $uid);
            util\Cache::rm('feed_uli_' . $uid);
        }
        if (empty($feedIds)) {
            return true;
        }
        if (is_array($feedIds)) {
            foreach ($feedIds as $v) {
                util\Cache::rm('feed_' . $v);
                util\Cache::rm('feed_info_' . $v);
            }
        } else {
            util\Cache::rm('feed_' . $feedIds);
            util\Cache::rm('feed_info_' . $feedIds);
        }
    }

    /**
     * 统计要查询的feed数量
     * @param string $key 关键字
     * @param string $type 微博类型，post、repost、postimage
     * @param integer $sTime 开始时间戳
     * @param integer $eTime 结束时间戳
     * @return integer
     */
    public function countSearchFeeds($key, $feedType = null, $sTime = 0, $eTime = 0)
    {
        $map = $this->mergeSearchCondition($key, $feedType, $sTime, $eTime);
        $count = $this->getDbConnection()->createCommand()
            ->select('count(a.feedid)')
            ->from(sprintf('%s a', $this->tableName()))
            ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
            ->where($map)
            ->queryScalar();
        return intval($count);
    }

    /**
     * 合并搜索关注微博的条件
     * @param string $key 关键字
     * @param type $loadId 载入微博ID，从此微博ID开始搜索
     * @return string
     */
    private function mergeSearchFollowingCondition($key, $loadId)
    {
        $me = intval(util\Ibos::app()->user->uid);
        $where = !empty($loadId) ? " a.isdel = 0 AND a.feedid <'{$loadId}'" : "a.isdel = 0";
        $where .= " AND (a.uid = '{$me}' OR b.uid = '{$me}' ) AND " . WbfeedUtil::getViewCondition($me, 'a.');
        $where .= " AND c.feedcontent LIKE '%" . util\StringUtil::filterCleanHtml($key) . "%'";
        return $where;
    }

    /**
     * 统计查询的关注微博数
     * @param string $key 关键字
     * @param integer $loadId 载入微博ID，从此微博ID开始搜索
     * @return integer
     */
    public function countSearchFollowing($key, $loadId)
    {
        $me = intval(util\Ibos::app()->user->uid);
        $where = $this->mergeSearchFollowingCondition($key, $loadId);
        $count = $this->getDbConnection()->createCommand()
            ->select('count(a.feedid)')
            ->from(sprintf("%s a", $this->tableName()))
            ->leftJoin('{{user_follow}} b', "a.uid = b.fid AND b.uid = {$me}")
            ->leftJoin('{{feed_data}} c', 'a.feedid = c.feedid')
            ->where($where)
            ->queryScalar();
        return $count;
    }

    /**
     * 合并搜索全部微博的条件
     * @param string $key 关键字
     * @param integer $loadId 载入微博ID，从此微博ID开始搜索
     * @param string $feedtype 微博类型
     * @param integer $uid
     * @return string
     */
    private function mergeSearchAllCondition($key, $loadId, $feedtype = '', $uid = 0)
    {
        $me = intval(util\Ibos::app()->user->uid);
        $map = array('and');
        if (!$uid) {
            $map[] = 'a.isdel = 0 AND ' . WbfeedUtil::getViewCondition($me);
        } else {
            $map[] = 'a.isdel = 0 AND uid = ' . $uid . ($me == $uid ? '' : ' AND ' . WbfeedUtil::getViewCondition($me));
        }
        !empty($loadId) && $map[] = 'a.feedid < ' . intval($loadId);
        $map[] = array('LIKE', 'b.feedcontent', '%' . util\StringUtil::filterCleanHtml($key) . '%');
        if ($feedtype) {
            if ($feedtype == 'post') {
                $map[] = 'a.isrepost = 0';
            }
            $map[] = 'a.type = ' . $feedtype;
        }
        return $map;
    }

    /**
     * 统计查询的所有微博数
     * @param string $key 关键字
     * @param integer $loadId 载入微博ID，从此微博ID开始搜索
     * @param string $feedtype 微博类型
     * @param integer $uid
     * @return integer
     */
    public function countSearchAll($key, $loadId, $feedtype = '', $uid = 0)
    {
        $map = $this->mergeSearchAllCondition($key, $loadId, $feedtype, $uid);
        $count = $this->getDbConnection()->createCommand()
            ->select('count(a.feedid)')
            ->from(sprintf('%s a', $this->tableName()))
            ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
            ->where($map)
            ->queryScalar();
        return $count;
    }

    /**
     * 合并搜索动态微博的条件
     * @param string $key 关键字
     * @param integer $loadId 载入微博ID，从此微博ID开始搜索
     * @param string $feedtype 微博类型
     * @param integer $uid
     * @return string
     */
    private function mergeSearchMovementCondition($key, $loadId, $feedtype = '', $uid = 0)
    {
        $me = intval(util\Ibos::app()->user->uid);
        $map = array('and');
        if (!$uid) {
            $map[] = 'a.isdel = 0 AND ' . WbfeedUtil::getViewCondition($me);
        } else {
            $map[] = 'a.isdel = 0 AND uid = ' . $uid . ($me == $uid ? '' : ' AND ' . WbfeedUtil::getViewCondition($me));
        }
        !empty($loadId) && $map[] = 'a.feedid < ' . intval($loadId);
        $map[] = array('LIKE', 'b.feedcontent', '%' . util\StringUtil::filterCleanHtml($key) . '%');
        if ($feedtype) {
            $map[] = 'a.module = ' . $feedtype;
        } else {
            $map[] = "a.module != 'weibo'";
        }
        return $map;
    }

    /**
     * 统计查询的动态微博数
     * @param string $key 关键字
     * @param integer $loadId 载入微博ID，从此微博ID开始搜索
     * @param string $feedtype 微博类型
     * @param integer $uid
     * @return string
     */
    public function countSearchMovement($key, $loadId, $feedtype = '', $uid = 0)
    {
        $map = $this->mergeSearchMovementCondition($key, $loadId, $feedtype, $uid);
        $count = $this->getDbConnection()->createCommand()
            ->select('count(a.feedid)')
            ->from(sprintf('%s a', $this->tableName()))
            ->where($map)
            ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
            ->queryScalar();
        return $count;
    }

    /**
     * 搜索微博
     * @param string $key 关键字
     * @param string $type 搜索类型，following、all、movement
     * @param integer $loadId 载入微博ID，从此微博ID开始搜索
     * @param integer $limit 结果集数目
     * @param integer $offset 页面偏移
     * @param string $feedtype 微博类型
     * @return array 搜索后的微博数据
     */
    public function searchFeed($key, $type, $loadId, $limit, $offset, $feedtype = '', $uid = 0)
    {
        $me = intval(util\Ibos::app()->user->uid);
        switch ($type) {
            case 'following':
                $buid = $me;
                $where = $this->mergeSearchFollowingCondition($key, $loadId, $buid);
                $feedlist = $this->getDbConnection()->createCommand()
                    ->select('a.feedid')
                    ->from(sprintf("%s a", $this->tableName()))
                    ->leftJoin('{{user_follow}} b', "a.uid = b.fid AND b.uid = {$buid}")
                    ->leftJoin('{{feed_data}} c', 'a.feedid = c.feedid')
                    ->where($where)
                    ->order('a.ctime DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->queryAll();
                break;
            case 'all':
                $map = $this->mergeSearchAllCondition($key, $loadId, $feedtype, $uid);
                $feedlist = $this->getDbConnection()->createCommand()
                    ->select('a.feedid')
                    ->from(sprintf('%s a', $this->tableName()))
                    ->where($map)
                    ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
                    ->order('a.ctime DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->queryAll();
                break;
            case 'movement':
                $map = $this->mergeSearchMovementCondition($key, $loadId, $feedtype, $uid);
                $feedlist = $this->getDbConnection()->createCommand()
                    ->select('a.feedid')
                    ->from(sprintf('%s a', $this->tableName()))
                    ->where($map)
                    ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
                    ->order('a.ctime DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->queryAll();
                break;
        }
        $feedids = util\Convert::getSubByKey($feedlist, 'feedid');
        $feedlist = $this->getFeeds($feedids);
        return $feedlist;
    }

    /**
     * 数据库搜索微博
     * @param string $key 关键字
     * @param string $feedType 微博类型，post、repost、postimage
     * @param integer $limit 结果集数目
     * @param integer $offset 偏移量
     * @param integer $sTime 开始时间戳
     * @param integer $eTime 结束时间戳
     * @return array 搜索后的微博数据
     */
    public function searchFeeds($key, $feedType = null, $limit = 10, $offset = 0, $sTime = 0, $eTime = 0)
    {
        $map = $this->mergeSearchCondition($key, $feedType, $sTime, $eTime);
        $list = $this->getDbConnection()->createCommand()
            ->select('a.feedid')
            ->from(sprintf('%s a', $this->tableName()))
            ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
            ->where($map)
            ->order('a.ctime DESC')
            ->limit($limit)
            ->offset($offset)
            ->queryAll();
        $feedids = util\Convert::getSubByKey($list, 'feedid');
        $feedlist = $this->getFeeds($feedids);
        return $feedlist;
    }

    /**
     * 合并查询条件,返回AR适用的条件数组
     * @param string $key 关键字
     * @param string $feedType 微博类型，post、repost、postimage
     * @param integer $sTime 开始时间戳
     * @param integer $eTime 结束时间戳
     * @return array 返回AR适用的条件数组
     */
    private function mergeSearchCondition($key, $feedType = null, $sTime = 0, $eTime = 0)
    {
        $map[] = 'and';
        $map[] = 'a.isdel = 0';
        $map[] = array('like', 'b.feedcontent', '%' . util\StringUtil::filterCleanHtml($key) . '%');
        if ($feedType) {
            $map[] = 'a.type = ' . $feedType;
            if ($feedType == 'post') {
                $map[] = 'a.isrepost = 0';
            }
        }
        if ($sTime && $eTime) {
            $map[] = sprintf("'a.ctime' BETWEEN %d AND %d", $sTime, $eTime);
        }
        return $map;
    }

    /**
     * 生成指定动态的缓存
     * @param array $value 动态相关数据
     * @param array $feedId 动态ID数组
     */
    private function setFeedCache($value = array(), $feedId = array())
    {
        if (!empty($feedId)) {
            !is_array($feedId) && $feedId = explode(',', $feedId);
            $feedId = implode(',', $feedId);
            $list = util\Ibos::app()->db->createCommand()
                ->select("a.*,b.clientip,b.feeddata")
                ->from("{{feed}} a")
                ->leftJoin('{{feed_data}} b', 'a.feedid = b.feedid')
                ->where("a.feedid IN ({$feedId})")
                ->queryAll();
            $r = array();
            foreach ($list as &$v) {
                // 格式化数据模板
                $parseData = $this->parseTemplate($v);
                $v['info'] = $parseData['info'];
                $v['title'] = $parseData['title'];
                $v['content'] = $parseData['content'];
                if (isset($parseData["attach_id"])) {
                    $v['attach_id'] = $parseData['attach_id'];
                }
                $v['body'] = $parseData['body'];
                $v['api_source'] = $parseData['api_source'];
                $v['actions'] = $parseData['actions'];
                $v['user_info'] = $parseData['userInfo'];
                util\Cache::set('feed_' . $v['feedid'], $v); // 缓存
                $r[$v['feedid']] = $v;
            }
            return $r;
        } else {
            // 格式化数据模板
            $parseData = $this->parseTemplate($value);
            $value['info'] = $parseData['info'];
            $value['title'] = $parseData['title'];
            $value['content'] = $parseData['content'];
            if (isset($parseData["attach_id"])) {
                $v['attach_id'] = $parseData['attach_id'];
            }
            $value['body'] = $parseData['body'];
            $value['api_source'] = $parseData['api_source'];
            $value['actions'] = $parseData['actions'];
            $value['user_info'] = $parseData['userInfo'];
            util\Cache::set('feed_' . $value['feedid'], $value); // 缓存
            return $value;
        }
    }

    /**
     * 解析动态模板
     * @param array $_data
     * @return boolean
     */
    private function parseTemplate($_data)
    {
        // 获取作者信息
        $user = User::model()->fetchByUid($_data['uid']);
        // 处理数据
        $_data['data'] = StringUtil::utf8Unserialize($_data['feeddata']);
        // 模版变量赋值
        $var = isset($_data['data']) ? $_data['data'] : array();
        // 因为需要直接输出 HTML，如果不进行过滤的话，可能会有安全的问题
        $varBody = isset($var['body']) ? $var['body'] : '';
        if (!empty($varBody)) {
            $varBody = StringUtil::parseHtml($varBody);
        }
        $var['body'] = $varBody;

        if (!empty($var['attach_id'])) {
            $var['attachInfo'] = util\Attach::getAttach($var['attach_id']);
            $attachUrl = util\File::getAttachUrl();
            foreach ($var['attachInfo'] as $ak => $av) {
                $attach_url = util\File::imageName($attachUrl . '/' . $av['attachment']);
                $_attach = array(
                    'attach_id' => $av['aid'],
                    'attach_name' => $av['filename'],
                    'attach_url' => $attach_url,
                    'extension' => util\StringUtil::getFileExt($av['filename']),
                    'size' => $av['filesize']
                );
                if ($_data['type'] == 'postimage') {
                    $_attach['attach_small'] = WbCommonUtil::getThumbImageUrl($av, WbCore\WbConst::ALBUM_DISPLAY_WIDTH, WbCore\WbConst::ALBUM_DISPLAY_HEIGHT);
                    $_attach['attach_middle'] = WbCommonUtil::getThumbImageUrl($av, WbCore\WbConst::WEIBO_DISPLAY_WIDTH, WbCore\WbConst::WEIBO_DISPLAY_HEIGHT);
                }
                $var['attachInfo'][$ak] = $_attach;
            }
        }
        $var['uid'] = $_data['uid'];
//		$var['time'] = $_data['ctime'];
        $var["actor"] = "<a href='{$user['space_url']}' data-toggle='usercard' data-param=\"uid={$user['uid']}\">{$user['realname']}</a>";
        $var["actor_uid"] = $user['uid'];
        $var["actor_uname"] = $user['realname'];
        $var['feedid'] = $_data['feedid'];
        //需要获取资源信息的动态：
        //所有类型的动态，只要有资源信息就获取资源信息并赋值模版变量，交给模版解析处理
        if (!empty($_data['rowid'])) {
            empty($_data['table']) && $_data['table'] = 'feed';
            $var['sourceInfo'] = Source::getSourceInfo($_data['table'], $_data['rowid'], false, $_data['module']);
        } else {
            $var['sourceInfo'] = null;
        }
        // 解析Feed模版
        $feedTemplateAlias = "application.modules.message.config.feed.{$_data['type']}Feed";
        $file = util\Ibos::getPathOfAlias($feedTemplateAlias);
        if (!file_exists($file . '.php')) {
            $feedTemplateAlias = "application.modules.message.config.feed.postFeed";
        }
        $file = util\Ibos::getPathOfAlias($feedTemplateAlias) . '.php';
        extract($var, EXTR_PREFIX_SAME, 'data');
        ob_start();
        ob_implicit_flush(false);
        require($file);
        $feedXmlContent = ob_get_clean();
        $s = simplexml_load_string($feedXmlContent);
        if (!$s) {
            return false;
        }
        $result = $s->xpath("//feed[@type='" . util\StringUtil::purify($_data['type']) . "']");
        $actions = (array)$result[0]->feedAttr;
        //输出模版解析后信息
        $return['content'] = util\StringUtil::parseHtml($var['content']);
        if (isset($var["attach_id"])) {
            $return['attach_id'] = $var['attach_id'];
        }
        $return["userInfo"] = $user;
        $return['title'] = trim((string)$result[0]->title);
        $return['body'] = trim((string)$result[0]->body);
        $return['info'] = trim((string)$result[0]['info']);
        $return['api_source'] = $var['sourceInfo'];
        $return['actions'] = $actions['@attributes'];
        // 验证转发的原信息是否存在
        if (!$this->notDel($_data['module'], $_data['type'], $_data['rowid'])) {
            $return['body'] = util\Ibos::lang('Info already delete', 'message.default'); // 此信息已被删除
        }
        return $return;
    }

    /**
     * 分享到微博
     * @example
     * 需要传入的$data值
     * sid：转发的微博/资源ID
     * app_name：app名称
     * content：转发时的内容信息，有时候会有某些标题的资源
     * body：转发时，自定义写入的内容
     * type：微博类型
     * comment：是否给原作者评论
     * @param array $data 分享的相关数据
     * @param string $from 是否发@给资源作者，默认为share
     * @param array $lessUids 去掉@用户，默认为null
     * @return array 分享操作后，相关反馈信息数据
     */
    public function shareFeed($data, $from = 'share', $lessUids = null)
    {
        // 返回的数据结果集
        $return = array('isSuccess' => false, 'data' => '转发失败');   // 分享失败
        // 验证数据正确性
        if (empty($data['sid'])) {
            return $return;
        }
        // 如果TABLE不存在，则 type是资源所在的表名 fix::
        $type = util\StringUtil::filterCleanHtml($data['type']);
        $table = isset($data['table']) ? $data['table'] : $type;

        // 当前产生微博所属的应用
        $module = isset($data['module']) ? $data['module'] : 'weibo';
        // 是否为接口形式
        $forApi = isset($data['forApi']) && $data['forApi'] ? true : false;
        if (!$oldInfo = Source::getSourceInfo($table, $data['sid'], $forApi, $data['module'])) {
            $return['data'] = '此信息不可以被转发';   // 此信息不可以被分享
            return $return;
        }
        // 内容数据
        $d['content'] = isset($data['content']) ? str_replace(util\Ibos::app()->setting->get('siteurl'), '[SITE_URL]', $data['content']) : '';
        $d['body'] = str_replace(util\Ibos::app()->setting->get('siteurl'), '[SITE_URL]', $data['body']);

        $feedType = 'repost';  // 默认为普通的转发格式
        if (!empty($oldInfo['feedType']) && !in_array($oldInfo['feedType'], array('post', 'postimage'))) {
            $feedType = $oldInfo['feedType'];
        }
        $d['sourceInfo'] = !empty($oldInfo['sourceInfo']) ? $oldInfo['sourceInfo'] : $oldInfo;
        // 是否发送@上级节点
        $isOther = ($from == 'comment') ? false : true;
        // 获取上个节点资源ID
        $d['curid'] = $data['curid'];
        // 获取转发原微博信息
        if ($oldInfo['rowid'] == 0) {
            $id = $oldInfo['source_id'];
            $table = $oldInfo['source_table'];
        } else {
            $id = $oldInfo['rowid'];
            $table = $oldInfo['table'];
        }

        $d['from'] = isset($data['from']) ? intval($data['from']) : 0;
        $res = $this->put(util\Ibos::app()->user->uid, $module, $feedType, $d, $id, $table, null, $lessUids, $isOther, 1);
        if ($res) {
            if (isset($data['comment'])) {
                // 发表评论
                $c['module'] = $module;
                $c['moduleuid'] = $data['curid'];
                $c['table'] = 'feed';
                $c['uid'] = $oldInfo['uid'];
                $c['content'] = !empty($d['body']) ? $d['body'] : $d['content'];
                $c['rowid'] = !empty($oldInfo['sourceInfo']) ? $oldInfo['sourceInfo']['source_id'] : $id;
                $c['from'] = util\Env::getVisitorClient();
                $notCount = $from == "share" ? ($data['comment'] == 1 ? false : true) : false;
                Comment::model()->addComment($c, false, $notCount, $lessUids, true);
            }
            //添加话题
            FeedTopic::model()->addTopic(html_entity_decode($d['body'], ENT_QUOTES), $res['feedid'], $feedType);
            // 渲染数据
            $rdata = $res;   // 渲染完后的结果
            $rdata['feedid'] = $res['feedid'];
            $rdata['rowid'] = $data['sid'];
            $rdata['table'] = $data['type'];
            $rdata['module'] = $module;
            $rdata['isrepost'] = 1;
            switch ($module) {
                case 'mobile':
                    //
                    break;
                default:
                    $rdata['from'] = util\Env::getFromClient($from, $module);
                    break;
            }
            $return['data'] = $rdata;
            $return['isSuccess'] = true;
            // 更新统计与清空缓存
            if ($module == 'weibo' && $type == 'feed') {
                $this->updateCounters(array('repostcount' => 1), 'feedid = ' . $data['sid']);
                $this->cleanCache($data['sid']);
                if ($data['curid'] != $data['sid'] && !empty($data['curid'])) {
                    $this->updateCounters(array('repostcount' => 1), 'feedid = ' . $data['curid']);
                    $this->cleanCache($data['curid']);
                }
            }
        } else {
            $return['data'] = $this->getError('putFeed');
        }

        return $return;
    }

    /**
     * 判断资源是否已被删除
     * @param string $module 模块名称
     * @param string $feedType 动态类型
     * @param integer $rowid 资源ID
     * @return boolean 资源是否存在
     */
    private function notDel($module, $feedType, $rowid)
    {
        // TODO:该方法为完成？
        // 非转发的内容，不需要验证
        if (empty($rowid)) {
            return true;
        }
        return true;
    }

    /**
     * 删除微博相关附件数据
     * @param array $feedIds 微博ID数组
     * @return void
     */
    private function _deleteFeedAttach($feedIds)
    {
        // 查询微博内是否存在附件
        $feeddata = $this->getFeeds($feedIds);
        $feedDataInfo = util\Convert::getSubByKey($feeddata, 'feeddata');
        $attachIds = array();
        foreach ($feedDataInfo as $value) {
            $value = StringUtil::utf8Unserialize($value);
            if (!empty($value['attach_id'])) {
                $aids = is_array($value['attach_id']) ? $value['attach_id'] : explode(',', $value['attach_id']);
                $attachIds = array_merge($attachIds, $aids);
            }
        }
        array_filter($attachIds);
        array_unique($attachIds);
        !empty($attachIds) && util\Attach::delAttach($attachIds);
    }

}
