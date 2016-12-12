<?php
/**
 * 投票角色权限工具类
 *
 * @namespace application\modules\vote\utils
 * @filename VoteRoleUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/24 11:33
 */

namespace application\modules\vote\utils;


use application\core\utils\Ibos;
use application\modules\role\utils\Role;
use application\modules\vote\model\Vote;
use application\modules\vote\model\VoteItemCount;

class VoteRoleUtil
{
    /**
     * 检查当前用户是否有浏览调查的权限
     *
     * @return bool
     * @throws \CHttpException
     */
    public static function canView()
    {
        return Role::checkRouteAccess('vote/default/index');
    }


    /**
     * 检查当前用户是否有发布调查的权限
     *
     * @return bool
     * @throws \CHttpException
     */
    public static function canPublish()
    {
        return Role::checkRouteAccess('vote/form/addorupdate');
    }

    /**
     * 检查当前用户是否有管理调查的权限
     *
     * @return bool
     * @throws \CHttpException
     */
    public static function canManage()
    {
        return Role::checkRouteAccess('vote/default/export');
    }


    /**
     * 检查当前用户是否有导出投票的权限
     *
     * @param $voteId
     * @return bool
     */
    public static function canExport($voteId)
    {
        $vote = VoteUtil::fetchVoteByPk($voteId);
        $uid = Ibos::app()->user->uid;

        // 发起人可以导出投票结果
        if ($vote['uid'] == $uid) {
            return true;
        }

        // 有管理调查权限的人也可以导出投票结果
        if (self::canManage() == true) {
            return true;
        }

        return false;
    }


    /**
     * 检查用户是否有投票权限（只有选择范围的用户才有投票的权限）
     *
     * @param $voteId
     * @param $uid
     * @return bool
     */
    public static function canVote($voteId, $uid)
    {
        $uidArr = VoteUtil::fetchScopeUidArr($voteId);
        if (in_array($uid, $uidArr)) {
            return true;
        }

        return false;
    }

    /**
     * 用户是否能查看投票的投票结果
     *
     * @param integer $voteId
     * @param integer $uid
     * @return bool
     * @throws \Exception
     */
    public static function canViewVoteResult($voteId, $uid)
    {
        $voteId = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);

        $vote = VoteUtil::fetchVoteByPk($voteId);

        // 投票发起人可以查看投票结果
        if ($vote['uid'] == $uid) {
            return true;
        }

        // 任何人都可见
        if ($vote['isvisible'] == Vote::ALL_VISIBLE) {
            return true;
        }

        // 投票已结束，任何人可见
        $voteStatus = VoteUtil::calcVoteStatus($vote['endtime']);
        if ($voteStatus == Vote::STATUS_END) {
            return true;
        }

        // 投票后可见 - 检查 $uid 是否已投票
        return VoteItemCount::model()->isVote($voteId, $uid);
    }
}