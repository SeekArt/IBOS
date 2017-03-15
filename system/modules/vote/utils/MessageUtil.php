<?php
/**
 * 未读消息工具类
 *
 * @namespace application\modules\vote\utils
 * @filename MessageUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/26 19:34
 */

namespace application\modules\vote\utils;


use application\core\utils\ArrayUtil;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\System;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\message\model\Notify;
use application\modules\user\model\Reader;
use application\modules\vote\model\Vote;
use application\modules\vote\VoteModule;

class MessageUtil extends System
{
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 获取用户未读未参与投票个数
     *
     * @param $uid
     */
    public function fetchUnreadUnJoinedMsgNum($uid)
    {
        $unJoinedList = Vote::model()->fetchUnJoinedList($uid);
        $readList = Reader::model()->fetchListByModuleName(VoteModule::MODULE_NAME);

        $unJoinedIds = ArrayUtil::getColumn($unJoinedList, 'voteid');
        $readIds = ArrayUtil::getColumn($readList, 'moduleid');

        $unReadIds = array_diff($unJoinedIds, $readIds);
        return count($unReadIds);
    }

    /**
     * 发送添加投票提示信息
     *
     * @param integer $voteId 投票 id
     * @param string $subject 投票标题
     * @param string $publishScope 投票阅读范围
     * @return bool
     */
    public function sendAddVoteNotify($voteId, $subject, $publishScope)
    {
        $config = array(
            '{url}' => Ibos::app()->urlManager->createUrl('vote/default/show', array('id' => $voteId)),
            '{sender}' => Ibos::app()->user->realname,
            '{subject}' => $subject,
        );
        $node = 'vote_publish_message';
        $this->publishNotify($node, $publishScope, $config);

        return true;
    }

    /**
     * 发送更新投票提醒消息
     *
     * @param integer $voteId 投票 id
     * @param string $subject
     * @param array $publishScope
     * @return bool
     */
    public function sendUpdateVoteNotify($voteId, $subject, $publishScope)
    {
        $config = array(
            '{url}' => Ibos::app()->urlManager->createUrl('vote/default/show', array('id' => $voteId)),
            '{sender}' => Ibos::app()->user->realname,
            '{subject}' => html_entity_decode($subject),
        );
        $node = 'vote_update_message';
        $this->publishNotify($node, $publishScope, $config);

        return true;
    }


    /**
     * 发布投票消息提醒
     *
     * @param string $node
     * @param array $publishScope
     * @param $config
     * @return bool
     */
    private function publishNotify($node, $publishScope, $config)
    {
        $publishScope = StringUtil::handleSelectBoxData($publishScope);
        $uidArr = ArticleUtil::getScopeUidArr($publishScope);
        if (count($uidArr) > 0) {

            Notify::model()->sendNotify($uidArr, $node, $config);
        }

        return true;
    }

}
