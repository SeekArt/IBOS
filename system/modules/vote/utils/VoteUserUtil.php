<?php
/**
 * 投票模块用户工具类
 *
 * @namespace application\modules\vote\utils
 * @filename UserUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/31 17:24
 */

namespace application\modules\vote\utils;


use application\core\utils\System;
use application\modules\department\model\Department;
use application\modules\user\model\User;
use application\modules\vote\model\VoteItemCount;

class VoteUserUtil extends System
{
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }


    public function fetchGroupUidArr($voteId)
    {
        // 分别获取参加投票和未参加投票的用户 uid
        $joinedUidArr = $this->fetchJoinedUidArr($voteId);
        $unJoinedUidArr = $this->fetchUnJoinedUidArr($voteId);

        // 按照部门 id 将用户分组
        $joinedGroupUidArr = $this->handleUidArrForGroup($joinedUidArr);
        $unJoinedGroupUidArr = $this->handleUidArrForGroup($unJoinedUidArr);

        return array($joinedGroupUidArr, $unJoinedGroupUidArr);
    }


    /**
     * 获取参加投票的用户数组
     *
     * @param integer $voteId
     * @return array
     */
    public function fetchJoinedUidArr($voteId)
    {
        $joinedUidArr = VoteItemCount::model()->fetchJoinedUidArr($voteId);

        return $joinedUidArr;
    }

    /**
     * 获取未参加投票的用户数组
     *
     * @param integer $voteId
     * @return array
     */
    public function fetchUnJoinedUidArr($voteId)
    {
        // 该投票阅读范围内的 uid 数组
        $allUidArr = VoteUtil::fetchScopeUidArr($voteId);

        // 参加投票的 uid 数组
        $joinedUidArr = $this->fetchJoinedUidArr($voteId);

        // 未参与投票的 uid 数组
        $unJoinedUidArr = array_diff($allUidArr, $joinedUidArr);

        return $unJoinedUidArr;
    }

    /**
     * 处理用户数据，将用户分组
     *
     * @param array $unOrderUidArr
     * @return array
     */
    private function handleUidArrForGroup($unOrderUidArr)
    {
        if (empty($unOrderUidArr)) {
            return array();
        }

        // 使用部门 id，对用户进行分组
        $uidDeptArr = User::model()->fetchAllDeptId($unOrderUidArr);
        $orderUidArr = array();
        foreach ($uidDeptArr as $row) {
            $orderUidArr[$row['deptid']][] = $row['uid'];
        }

        // 获取全部部门数据
        $deptIds = array_keys($orderUidArr);
        $depts = Department::model()->fetchAllDeptName($deptIds);


        // 获取部门名称，并将该部门的用户放在该分组下
        $result = array();
        foreach ($orderUidArr as $deptId => $uidArr) {
            $deptName = '';
            if (isset($depts[$deptId]['deptname'])) {
                $deptName = $depts[$deptId]['deptname'];
            }

            $result[] = array(
                'deptname' => $deptName,
                'deptid' => $deptId,
                'users' => $uidArr,
            );
        }

        return $result;
    }

}