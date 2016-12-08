<?php

/**
 * 部门模块函数库类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 部门模块函数库类
 *
 * @package application.modules.department.utils
 * @version $Id: Department.php 8717 2016-10-24 07:20:26Z gzhyj $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\department\utils;

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\article\utils\Article;
use application\modules\contact\extensions\Tree\lib\BlueM\Tree;
use application\modules\contact\utils\TreeUtil;
use application\modules\department\model as DepartmentModel;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\role\model\NodeRelated;
use application\modules\role\utils\Role as RoleUtil;
use application\modules\user\model as UserModel;
use application\modules\user\utils\User as UserUtil;


class Department
{

    const TOP_DEPT_ID = 0; //顶级部门的部门id

    public static function loadDepartment($deptidMixed = null, $param = array())
    {
        static $alldepartment = null;
        if ($alldepartment === null) {
            $alldepartment = DepartmentModel\Department::model()->findDeptmentIndexByDeptid($deptidMixed, $param);
        }
        return $alldepartment;
    }

    /**
     * 对比看下$pid是否$deptId的父级部门
     * @param integer $deptId
     * @param integer $pid
     * @return boolean
     */
    public static function isDeptParent($deptId, $pid)
    {
        $depts = self::loadDepartment();
        $_pid = $depts[$deptId]['pid'];
        if ($_pid == 0) {
            return false;
        }
        if ($_pid == $pid) {
            return true;
        }
        return self::isDeptParent($_pid, $pid);
    }

    /**
     * 获取住角色和辅助角色中最大的权限(0,1,2,4,8)
     * @param integer $uid 用户id
     * @param string $url 权限路由 (organization/user/manager或organization/user/view等等的1248权限)
     * @return integer 最大权限
     */
    public static function getMaxPurv($uid, $url)
    {
        $user = UserModel\User::model()->fetchByUid($uid);
        $roleIds = explode(',', $user['allroleid']); // 所有角色id
        $purvs = array();
        foreach ($roleIds as $roleId) {
            $p = NodeRelated::model()->fetchDataValByIdentifier($url, $roleId);
            $purvs[] = intval($p);
        }
        $viewPurv = max($purvs);
        return $viewPurv;
    }

    /**
     * 按拼音排序部门
     * @return array
     */
    public static function getDepartmentByPy()
    {
        $group = array();
        $list = self::loadDepartment();
        foreach ($list as $k => $v) {
            $py = Convert::getPY($v['deptname']);
            if (!empty($py)) {
                $group[strtoupper($py[0])][] = $k;
            }
        }
        ksort($group);
        $data = array('datas' => $list, 'group' => $group);
        return $data;
    }

    /**
     * 更新部门用户列表
     * @param  integer $departmentid 部门 id
     * @param  array $uids 用户 uid 数组
     * @return boolen               true | false
     */
    public static function updateDepartmentUserList($departmentid, $uids)
    {
        $rmUids = array();
        $deptUids = DepartmentModel\DepartmentRelated::model()->fetchAllUidByDeptId($departmentid);
        foreach ($deptUids as $deptUid) {
            if (!in_array($deptUid, $uids)) {
                $rmUids[] = $deptUid;
            }
        }
        $rmUids = implode(',', $rmUids);
        $uids = implode(',', $uids);
        $removeRes = UserModel\User::model()->updateAll(array('deptid' => 0), 'FIND_IN_SET(`uid`, :rmUids)',
            array(':rmUids' => $rmUids));
        $addRes = UserModel\User::model()->updateAll(array('deptid' => $departmentid), 'FIND_IN_SET(`uid`, :uids)',
            array(':uids' => $uids));
        UserUtil::wrapUserInfo($uids, true, true, true);
        if ($removeRes >= 0 && $addRes >= 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取得阅读范围信息
     *
     * @param $deptId
     * @param $positionId
     * @param $roleId
     * @param $uid
     * @return array
     */
    public static function getScopes($deptId, $positionId, $roleId, $uid)
    {
        $scopes = array();

        if (empty($deptId) && empty($positionId) && empty($uid) && empty($roleId)) {
            $scopes['departmentNames'] = Ibos::lang('All');
            $scopes['positionNames'] = $scopes['uidNames'] = '';
        } else {
            if ($deptId == 'alldept') {
                $scopes['departmentNames'] = Ibos::lang('All');
                $scopes['positionNames'] = $scopes['uidNames'] = $scopes['roleNames'] = '';
            } else {
                //取得部门名称集以、号分隔
                $department = DepartmentUtil::loadDepartment();
                $scopes['departmentNames'] = ArticleUtil::joinStringByArray($deptId, $department, 'deptname', '、');
                //取得职位名称集以、号分隔
                $position = PositionUtil::loadPosition();
                $scopes['positionNames'] = ArticleUtil::joinStringByArray($positionId, $position, 'posname', '、');
                // 取得角色名称集以、号分割
                $role = RoleUtil::loadRole();
                $scopes['roleNames'] = ArticleUtil::joinStringByArray($roleId, $role, 'rolename', '、');

                //取得阅读范围人员名称集以、号分隔
                if (!empty($uid)) {
                    $users = UserModel\User::model()->fetchAllByUids(explode(",", $uid));
                    $scopes['uidNames'] = ArticleUtil::joinStringByArray($uid, $users, 'realname', '、');
                } else {
                    $scopes['uidNames'] = "";
                }
            }
        }

        return $scopes;
    }

    /**
     * 获取某个部门以及其子部门的所有用户 uid
     *
     * @param integer $deptId 部门 id
     * @return array 该部门以及其子部门的所有用户 uid 数组
     */
    public static function fetchDeptUidArr($deptId)
    {
        static $resultArr = array();

        if (isset($resultArr[$deptId])) {
            return $resultArr[$deptId];
        }

        static $deptTree = array();
        if (empty($deptTree)) {
            $deptTree = self::buildDeptTree();
        }

        $node = $deptTree->getNodeById($deptId);
        $nodeArr = $node->getAllChildren();

        $deptIdArr = array();
        foreach ($nodeArr as $loopNode) {
            $deptIdArr[] = $loopNode->getId();
        }

        $allUidArr = array();
        foreach ($deptIdArr as $loopDeptId) {
            $uidArr = UserModel\User::model()->fetchAllUidByDeptid($loopDeptId, false, true);
            $allUidArr = array_merge($allUidArr, $uidArr);
        }
        $allUidArr = array_unique($allUidArr);

        $resultArr[$deptId] = $allUidArr;

        return $allUidArr;
    }

    /**
     * 创建一棵部门树
     *
     * @return Tree
     */
    public static function buildDeptTree()
    {
        static $staticDeptTree = null;

        if (!empty($staticDeptTree)) {
            return $staticDeptTree;
        }

        $allDepts = DepartmentUtil::loadDepartment();

        // 将部门数组中的键名 deptid 改为 id，pid 改为 parent。
        // Tree 类在创建树的时候，期待拿到这些数据。
        foreach ($allDepts as &$loopDept) {
            $loopDept['id'] = $loopDept['deptid'];
            unset($loopDept['deptid']);

            $loopDept['parent'] = $loopDept['pid'];
            unset($loopDept['pid']);
        }
        $deptTree = TreeUtil::getInstance()->create($allDepts);

        $staticDeptTree = $deptTree;

        return $deptTree;
    }

    /**
     * 返回直接子部门数据
     *
     * @param integer $deptId 部门 id
     * @return array 子部门数据
     */
    public static function fetchSubDepartments($deptId)
    {
        $deptId = (int)$deptId;

        return DepartmentModel\Department::model()->fetchAll('pid = :pid', array(':pid' => $deptId));
    }

    /**
     * 获取所有按照部门 id 分组后的管理者的 uid
     *
     * @return array 管理者 uid 数组
     */
    public static function fetchAllManageGroupUidArr()
    {
        // 标识是否已经执行过该方法了
        static $flag = false;
        static $manageGroupUidArr = array();

        if ($flag === true) {
            return $manageGroupUidArr;
        }

        $allDepts = self::loadDepartment();

        foreach ($allDepts as $loopDept) {
            $deptId = $loopDept['deptid'];
            $manageUid = $loopDept['manager'];
            if ($manageUid != 0) {
                $manageGroupUidArr[$deptId][] = $manageUid;
            }
        }

        $flag = true;

        return $manageGroupUidArr;
    }
}
