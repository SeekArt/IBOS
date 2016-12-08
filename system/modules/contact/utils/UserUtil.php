<?php
/**
 * 用户工具类
 *
 * @namespace application\modules\contact\utils
 * @filename UserUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/8 16:50
 */

namespace application\modules\contact\utils;


use application\core\utils\ArrayUtil;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\System;
use application\modules\contact\model\ContactHide;
use application\modules\department\model\Department as DepartmentModel;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\position\model\Position;
use application\modules\user\model\User;
use application\modules\user\utils\User as IbosUserUtil;

/**
 * Class UserUtil
 *
 * @package application\modules\contact\utils
 */
class UserUtil extends System
{
    /**
     * @var string 缓存键前缀
     */
    const CACHE_KEY_PREFIX = 'contactuserlist';

    /**
     * @param string $className
     * @return UserUtil
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
     * 在部门或子部门中搜索用户信息
     *
     * @param integer $deptId 部门 id
     * @param string $search 搜索字符串，系统会匹配用户的真实姓名、拼音和工号
     * @return array
     * @throws \Exception
     */
    public function searchUserList($deptId, $search = '')
    {
        $uidArr = UserCacheUtil::getInstance()->fetchAllUidArrByDeptId($deptId);

        // 如果是公司层级下的，即 deptid 等于 0，获取没有部门的用户
        if (empty($deptId)) {
            $uidArr = array_merge($uidArr, User::model()->fetchAllUidWithoutDeptId(false, false));
        }

        // 根据用户姓名、拼音、工号进行查询
        if (!empty($search)) {
            $uidArr = $this->filterSearch($uidArr, $search);
        }

        return UserCacheUtil::getInstance()->handleUidList(0, $uidArr);
    }
    
    /**
     * @param string $deptId
     * @return array|null
     * @throws \Exception
     */
    public function fetchDeptArr($deptId = '')
    {
        $deptId = (int)$deptId;
        
        $allDeptArr = DepartmentUtil::loadDepartment();
        if (empty($deptId)) {
            // 获取所有部门数据
            $deptArr = $allDeptArr;
        } else {
            // 获取所有子部门数据
            $deptArr = DepartmentModel::model()->fetchChildDeptByDeptid($deptId, $allDeptArr);
            // 包含本部门
            $deptArr = array_merge($deptArr, array(DeptUtil::getInstance()->fetchDeptByPk($deptId)));
        }

        // 按照部门 id 从小到大排序
        usort($deptArr, function($deptA, $deptB) {
            $deptAId = $deptA['deptid'];
            $deptBId = $deptB['deptid'];

            if ($deptAId == $deptBId) {
                return 0;
            }

            return ($deptAId < $deptBId) ? -1 : 1;
        });

        return $deptArr;
    }
    
    /**
     * 根据用户真实姓名、拼音、工号进行查询
     *
     * @todo 目前拼音搜索的效率很低
     *
     * @param $uidArr
     * @param string $searchStr
     * @return array
     */
    private function filterSearch($uidArr, $searchStr)
    {
        if (empty($searchStr)) {
            return $uidArr;
        }
        
        if (empty($uidArr)) {
            return array();
        }
        
        // 用户真实姓名、工号搜索
        $realName = $jobNum = $searchStr;
        $condition = <<<EOF
            `realname` LIKE '%{$realName}%' OR
            `jobnumber` LIKE '%{$jobNum}%'
EOF;
        $baseSearchUsers = Ibos::app()->db->createCommand()->select('uid')->from(User::model()->tableName())
            ->where(array('in', 'uid', $uidArr))
            ->andWhere($condition)
            ->queryAll();
        
        // 拼音过滤
        $pinyin = strtolower(Convert::getPY($realName));
        $pinyinLength = StringUtil::strLength($pinyin);
        $allUsers = IbosUserUtil::safeWrapUserInfo($uidArr);
        $pinyinSearchUsers = array();
        foreach ($allUsers as $loopUser) {
            $loopPinyin = strtolower(Convert::getPY($loopUser['realname']));
            if (StringUtil::subString($loopPinyin, 0, $pinyinLength) == $pinyin) {
                $pinyinSearchUsers[] = $loopUser;
            }
        }
        
        // 合并搜索结果
        $allUidArr = array_merge(ArrayUtil::getColumn($baseSearchUsers, 'uid'),
            ArrayUtil::getColumn($pinyinSearchUsers, 'uid'));
        
        // 过滤重复 uid
        return array_unique($allUidArr);
    }
    
    /**
     * 根据 uid 获取用户信息
     *
     * @param integer $uid 用户 uid
     * @return array
     * @throws \Exception
     */
    public function fetchUserByPk($uid)
    {
        $user = IbosUserUtil::safeWrapOneUser($uid);
        
        if (empty($user)) {
            throw new \Exception(sprintf(Ibos::lang('User is not exists'), $uid));
        }
        
        return $user;
    }
    
    /**
     * 获取用户的详细信息
     *
     * @param integer $uid 用户 uid
     * @return array
     * @throws
     */
    public function fetchUserDetail($uid)
    {
        $user = $this->fetchUserByPk($uid);
        
        $canViewMobile = RoleUtil::getInstance()->canViewMobile($uid);
        $phoneNum = Ibos::lang('Hidden');
        if ($canViewMobile) {
            $phoneNum = $user['mobile'];
        }
        
        $positionId = $user['positionid'];
        $positionName = '';
        $positionModel = Position::model()->fetchByPk($positionId);
        if (!empty($positionModel)) {
            $positionName = $positionModel['posname'];
        }
        
        $retData = array(
            'uid' => $uid,
            'phone' => $phoneNum,
            'qq' => $user['qq'],
            'email' => $user['email'],
            'birthday' => $user['birthday'],
            'canviewmobile' => RoleUtil::getInstance()->canViewMobile($uid),
            'auxiliarydept' => DeptUtil::getInstance()->fetchAuxiliaryDept($uid),
            'auxiliaryposition' => PositionUtil::getInstance()->fetchAuxiliaryPosition($uid),
            'jobnumber' => $user['jobnumber'],
            'avatar_small' => $user['avatar_small'],
            'avatar_middle' => $user['avatar_middle'],
            'avatar_big' => $user['avatar_big'],
            'gender' => $user['gender'],
            'deptid' => $user['deptid'],
            'deptname' => $user['deptname'],
            'positionid' => $user['positionid'],
            'positionname' => $positionName,
            'bgbig' => $user['bg_big'],
            'bgmiddle' => $user['bg_middle'],
            'bgsmall' => $user['bg_small'],
        );
        
        return $retData;
    }
    
    /**
     * 添加需要隐藏手机号码的用户 uid
     *
     * @param $publishScope
     * @return bool
     * @throws \CDbException
     * @throws \Exception
     */
    public function addHiddenUidArr($publishScope)
    {
        $scopeArr = StringUtil::handleSelectBoxData($publishScope);
        
        $transaction = Ibos::app()->db->beginTransaction();
        try {
            ContactHide::model()->delAllByColumn(ContactHide::MOBILE_COLUMN);
            
            // $publishScope 的值可以为空，如果 $publishScope 为空，则只清空记录。
            if (!empty($publishScope)) {
                ContactHide::model()->addOne($scopeArr['deptid'], $scopeArr['positionid'], $scopeArr['roleid'],
                    $scopeArr['uid'], ContactHide::MOBILE_COLUMN);
            }
            
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
        
        
        return true;
    }
    
    /**
     * 获取需要隐藏手机号码的用户 uid 数组
     *
     * @return array
     */
    public function fetchHiddenUidArr()
    {
        $hiddenUidArr = ContactHide::model()->fetchUidArrByColumn(ContactHide::MOBILE_COLUMN);

        foreach ($hiddenUidArr as &$loopUid) {
            $loopUid = 'u_' . $loopUid;
        }
        
        return $hiddenUidArr;
    }
}
