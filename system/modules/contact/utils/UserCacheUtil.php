<?php
/**
 * 用户缓存工具类
 *
 * @namespace application\modules\contact\utils
 * @filename UserCacheUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/17 10:55
 */

namespace application\modules\contact\utils;

use application\modules\user\model\User;


/**
 * Class UserCacheUtil
 *
 * @package application\modules\contact\utils
 */
class UserCacheUtil extends AbstractCacheUtil
{
    /**
     * @var string 缓存名
     */
    protected $cacheName = 'UserTree';

    /**
     * @var string 缓存键：部门下所有用户
     */
    protected $usersKey = 'users';

    /**
     * @var string 缓存键：部门以及子部门下的所有用户
     */
    protected $allUserKey = 'allusers';

    /**
     * @var string 缓存键：部门以及子部门下的所有用户，并根据部门分组
     */
    protected $allGroupUserKey = 'allgroupusers';

    /**
     * @param string $className
     * @return UserCacheUtil
     */
    public static function getInstance($className = __CLASS__)
    {
        static $instance = null;

        if (empty($instance)) {
            $instance = parent::getInstance($className);
        }

        return $instance;
    }

    /**
     * 创建缓存
     *
     * @return array 返回新建缓存
     */
    public function buildCache()
    {
        $userCache = $this->getCache();

        if (empty($userCache)) {
            // 获取所有用户和部门的关联数组
            $allUidDeptIdArr = User::model()->fetchAllUidDeptId(false, true, false);
            $allGroupUidDeptIdArr = User::model()->handleForGroup($allUidDeptIdArr);

            $userCache = array('users' => $allGroupUidDeptIdArr);
            $this->setCache($userCache);
        }

        return $userCache;
    }


    /**
     * 通过部门 id 获取该部门下的用户 uid 数组
     *
     * @param integer $deptId
     * @return array 用户 uid 数组
     */
    public function fetchUidArrByDeptId($deptId)
    {
        $deptId = (int)$deptId;
        $userTree = $this->getCache();

        // 缓存命中
        if (isset($userTree[$this->usersKey][$deptId])) {
            return $userTree[$this->usersKey][$deptId];
        }

        return array();
    }

    /**
     * 通过部门 id 获取该部门以及子部门下的 uid 数组
     *
     * @param integer $deptId 部门 id
     * @return array 用户 uid 数组
     */
    public function fetchAllUidArrByDeptId($deptId)
    {
        $deptId = (int)$deptId;
        $userTree = $this->getCache();

        // 缓存命中
        if (isset($userTree[$this->allUserKey][$deptId])) {
            return $userTree[$this->allUserKey][$deptId];
        }

        $deptNodeArr = DeptCacheUtil::getInstance()->fetchAllChildren($deptId);

        $uidArr = array();
        foreach ($deptNodeArr as $loopDeptNode) {
            $uidArr = array_merge($uidArr, $this->fetchUidArrByDeptId($loopDeptNode->getId()));
        }

        // 去重
        $uidArr = array_unique($uidArr);

        // 更新 cacheValue 的值
        $userTree[$this->allUserKey][$deptId] = $uidArr;
        $this->updateLocalCache($userTree);

        return $uidArr;
    }

    /**
     * 计算某个部门以及子部门下的用户人数
     *
     * @param integer $deptId 部门 id
     * @return integer 用户数目
     */
    public function countAllUidByDeptId($deptId)
    {
        $deptId = (int)$deptId;
        $uidArr = $this->fetchAllUidArrByDeptId($deptId);

        return count($uidArr);
    }

    /**
     * @param integer $deptId 部门 id
     * @return array
     */
    public function fetchAllGroupUsers($deptId)
    {
        $deptId = (int)$deptId;
        $userTree = $this->getCache();

        // 缓存命中
        if (isset($userTree[$this->allGroupUserKey][$deptId])) {
            return $userTree[$this->allGroupUserKey][$deptId];
        }

        $deptNodeArr = DeptCacheUtil::getInstance()->fetchAllChildren($deptId);

        $result = array();
        foreach ($deptNodeArr as $loopDeptNode) {
            $loopDeptId = $loopDeptNode->getId();

            $uidArr = UserCacheUtil::getInstance()->fetchUidArrByDeptId($loopDeptId);

            if (empty($uidArr)) {
                continue;
            }

            $uidList = $this->handleUidList($loopDeptId, $uidArr);

            $result[] = array(
                'deptid' => $loopDeptId,
                'deptname' => $loopDeptNode->get('deptname'),
                'deptnum' => UserCacheUtil::getInstance()->countAllUidByDeptId($loopDeptId),
                'crumb' => DeptUtil::getInstance()->fetchCrumb($loopDeptId),
                'users' => $uidList,
            );

        }

        // 如果是公司层级下的，即 deptid 等于 0，获取没有部门的用户
        if (empty($deptId)) {
            array_unshift($result, $this->fetchUserListWithoutDept());
        }

        // 更新 cacheValue 的值
        $userTree[$this->allGroupUserKey][$deptId] = $result;
        $this->updateLocalCache($userTree);

        return $result;
    }

    /**
     * 获取没有部门的用户列表
     *
     * @return array
     */
    public function fetchUserListWithoutDept()
    {
        $corpShortName = CorpUtil::getInstance()->fetchCorpShortName();

        $uidArr = User::model()->fetchAllUidWithoutDeptId(false, false);
        $uidList = array();
        foreach ($uidArr as $loopUid) {
            $uidList[] = array(
                'uid' => $loopUid,
                'isadmin' => false,
            );
        }

        return array(
            'deptid' => 0,
            'deptname' => $corpShortName,
            'deptnum' => User::model()->countNums(false, false),
            'crumb' => array($corpShortName),
            'users' => $uidList,
        );
    }

    /**
     * 处理 uid 数组列表
     *
     * @param integer $deptId 部门 id
     * @param array $uidArr 需要处理的 uid 数组
     * @return array
     */
    public function handleUidList($deptId, $uidArr)
    {
        $result = array();

        foreach ($uidArr as $loopUid) {
            $result[] = array(
                'uid' => $loopUid,
                'isadmin' => RoleUtil::getInstance()->isDeptAdmin($deptId, $loopUid),
            );
        }

        return $result;
    }

}