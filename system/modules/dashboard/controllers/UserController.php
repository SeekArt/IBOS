<?php

/**
 * 组织架构模块用户控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块用户控制器类
 *
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: UserController.php 4321 2014-10-09 07:42:26Z gzpjh $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\ArrayUtil;
use application\core\utils\Attach;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\OrgIO;
use application\core\utils\PHPExcel;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Cache;
use application\modules\department\components\DepartmentCategory as ICDepartmentCategory;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\main\utils\Main;
use application\modules\position\model\Position;
use application\modules\position\model\PositionRelated;
use application\modules\role\model\Role;
use application\modules\role\model\RoleRelated;
use application\modules\user\model\User;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\utils\User as UserUtil;
use CHtml;

class UserController extends OrganizationBaseController
{

    const IMPORT_TPL = '/data/tpl/user_import.xls';

    /*
     * 员工上下级排列数据
     */

    static public $userList = array();

    /**
     *
     * @var string 下拉列表中的<option>格式字符串
     */
    public $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";

    /**
     * 浏览操作
     * @return void
     */
    public function actionIndex()
    {
        $data['unit'] = Ibos::app()->setting->get('setting/unit');
        $data['unit']['fullname'] = isset($data['unit']['fullname']) ? $data['unit']['fullname'] : '';
        // 获取分支部门的deptid
        $deptList = Department::model()->fetchAll('isbranch = 1');
        $deptArr = Convert::getSubByKey($deptList, 'deptid');
        $data['deptStr'] = implode(',', $deptArr);
        $this->render('index', $data);
    }

    /**
     * 获取 index 页面用户列表数据方法
     * @return json
     */
    public function actionGetUserList()
    {
        $type = Env::getRequest('type');
        $deptid = Env::getRequest('deptid');
        $draw = Env::getRequest('draw');
        $search = Env::getRequest('search');
        if (!in_array($type, array('enabled', 'lock', 'disabled', 'all'))) {
            $type = 'enabled';
        }
        $condition = User::model()->getConditionByDeptIdType($deptid, $type);
        if (!empty($search['value'])) {
            //添加转义
            //这里存在有keyword单引号SQL错误
            $key = CHtml::encode($search['value']);
            $condition = "( `username` LIKE '%{$key}%' OR `realname` LIKE '%{$key}%' OR `mobile` LIKE '%{$key}%' ) AND " . $condition;
        }
        $this->ajaxReturn(array(
            'isSuccess' => true,
            'msg' => '调用成功',
            'data' => $this->handleUserListDataByCondition($condition),
            'draw' => $draw,
            'recordsFiltered' => User::model()->count($condition),
        ));
    }

    /**
     * 处理返回用户列表数据
     * @param  string $condition 查询记录的 WHERE 条件
     * @return array             按格式处理后的用户列表数据
     */
    private function handleUserListDataByCondition($condition)
    {
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');
        $userList = array_map(function ($user) {
            return array(
                'uid' => $user['uid'],
                'realname' => $user['realname'],
                'deptname' => Department::model()->fetchDeptNameByDeptId($user['deptid']),
                'posname' => Position::model()->fetchPosNameByPosId($user['positionid']),
                'rolename' => Role::model()->getRoleNameByRoleid($user['roleid']),
                'mobile' => $user['mobile'],
                'weixin' => $user['weixin'],
                'avatar_small' => Org::getDataStatic($user['uid'], 'avatar', 'small'),
            );
        }, User::model()->fetchAll(array(
            'condition' => $condition,
            'limit' => $length,
            'offset' => $start,
        )));
        return $this->addRelatedRole($userList);
    }

    /**
     * 获取部门树
     * @return json
     */
    public function actionGetDeptTree()
    {
        $this->getDeptTree();
    }

    /**
     * 新增操作
     * @return void
     */
    public function actionAdd()
    {
        if (Env::submitCheck('userSubmit')) {
            $origPass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
            if (empty($origPass)){
               $this->error(Ibos::lang('Not empty password'), $this->createUrl('user/add'));
            }
            $_POST['realname'] = CHtml::encode($_POST['realname']);
            $_POST['weixin'] = CHtml::encode($_POST['weixin']);
            $_POST['jobnumber'] = CHtml::encode($_POST['jobnumber']);
            $_POST['salt'] = StringUtil::random(6);
            $_POST['password'] = !empty($origPass) ? md5(md5($origPass) . $_POST['salt']) : '';
            $_POST['createtime'] = TIMESTAMP;
            $_POST['guid'] = StringUtil::createGuid();
            $this->dealWithSpecialParams();
            $data = User::model()->create();
            User::model()->checkUnique($data);
            $newId = User::model()->add($data, true);
            if ($newId) {
                UserCount::model()->add(array('uid' => $newId));
                $ip = Ibos::app()->setting->get('clientip');
                UserStatus::model()->add(
                    array(
                        'uid' => $newId,
                        'regip' => $ip,
                        'lastip' => $ip
                    )
                );
                UserProfile::model()->add(array('uid' => $newId));
                // 辅助部门
                if (!empty($_POST['auxiliarydept'])) {
                    $deptIds = StringUtil::getId($_POST['auxiliarydept']);
                    $this->handleAuxiliaryDept($newId, $deptIds, $_POST['deptid']);
                }
                // 辅助岗位
                if (!empty($_POST['auxiliarypos'])) {
                    $posIds = StringUtil::getId($_POST['auxiliarypos']);
                    $this->handleAuxiliaryPosition($newId, $posIds, $_POST['positionid']);
                    for ($i = 0; $i < count($posIds); $i++) {
                        $auNumber = Position::model()->getPositionUserNumById($posIds[$i]);//拿到修改之前的岗位人数
                        $auNumber = $auNumber + 1;
                        Position::model()->updatePositionNum($posIds[$i], $auNumber);//修改之前的岗位数
                    }
                }
                //岗位
                if (isset($_POST['positionid'])) {
                    $number = Position::model()->getPositionUserNumById($_POST['positionid']);
                    $number = $number + 1;
                    Position::model()->updatePositionNum($_POST['positionid'], $number);
                }
                // 辅助角色
                if (!empty($_POST['auxiliaryrole'])) {
                    $roleIds = StringUtil::getId(explode(',', $_POST['auxiliaryrole']));
                    $this->handleAuxiliaryRole($newId, $roleIds, $_POST['roleid']);
                }
                // 直属下属
                $subUids = StringUtil::getId($_POST['subordinate']);
                User::model()->updateAll(array('upuid' => $newId), sprintf("FIND_IN_SET(`uid`,'%s')", implode(',', $subUids)));
                // 重建缓存，给新加的用户生成缓存
                UserUtil::wrapUserInfo($newId, false, true); //这个方法的第三个参数为true会强制更新缓存
                if ($data['status'] != 2) {
                    // 更新组织架构js调用接口
                    Org::update();
                    // 同步用户钩子
                    Org::hookSyncUser($newId, $origPass, 1);
                }
                CacheUtil::update();
                $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('user/index'));
            } else {
                $this->error(Ibos::lang('Add user failed'), $this->createUrl('user/index'));
            }
        } else {
            $deptid = "";
            $manager = "";
            $account = Ibos::app()->setting->get('setting/account');
            if ($account['mixed']) {
                $preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
            } else {
                $preg = "^[A-Za-z0-9\!\@\#\$\%\^\&\*\.\~]{" . $account['minlength'] . ",32}$";
            }
            if ($deptid = Env::getRequest('deptid')) {
                $deptid = StringUtil::wrapId(Env::getRequest('deptid'), 'd');
                $manager = StringUtil::wrapId(Department::model()->fetchManagerByDeptid(Env::getRequest('deptid')), 'u');
            }
            $this->render('add', array(
                'deptid' => $deptid,
                'manager' => $manager,
                'passwordLength' => $account['minlength'],
                'preg' => $preg,
                'roles' => Role::model()->fetchAll()
            ));
        }
    }

    /**
     *
     */
    public function actionGetavailable()
    {
        $limit = LICENCE_LIMIT;
        $uidArray = User::model()->fetchUidA(false);
        $count = count($uidArray);
        $remain = $limit - $count;
        $this->ajaxReturn(
            array(
                'isSuccess' => true,
                'current' => $count,
                'remain' => $remain
            ));
    }

    /**
     * 编辑操作
     * @return void
     */
    public function actionEdit()
    {
        $op = Env::getRequest('op');
        if ($op && in_array($op, array('enabled', 'disabled', 'lock'))) {
            $ids = Env::getRequest('uid');
            if ($op !== 'disabled') {
            }
            return $this->setStatus($op, $ids);
        } else {
        }
        $uid = Env::getRequest('uid');
        $user = User::model()->fetchByUid($uid);
        $positionid = $user['positionid'];//拿到修改之前的positionid
        if (Env::submitCheck('userSubmit')) {
            $this->dealWithSpecialParams();
            $_POST['realname'] = CHtml::encode($_POST['realname']);
            $_POST['weixin'] = CHtml::encode($_POST['weixin']);
            $_POST['jobnumber'] = CHtml::encode($_POST['jobnumber']);
            // 为空不修改密码
            if (empty($_POST['password'])) {
                unset($_POST['password']);
            } else {
                $_POST['password'] = md5(md5($_POST['password']) . $user['salt']);
                $_POST['lastchangepass'] = TIMESTAMP;
            }
            // 辅助部门
            if (isset($_POST['auxiliarydept'])) {
                $deptIds = StringUtil::getId($_POST['auxiliarydept']);
                $this->handleAuxiliaryDept($uid, $deptIds, $_POST['deptid']);
            }
            // 辅助岗位
            if (isset($_POST['auxiliarypos'])) {
                $posIds = StringUtil::getId($_POST['auxiliarypos']);
                $auxPos = PositionRelated::model()->fetchAllPositionIdByUid($uid);
                $child = array_diff($posIds, $auxPos);
                if (!empty($child)) {
                    foreach ($child as $value) {
                        $auNumber = Position::model()->getPositionUserNumById($value);
                        $auNumber = $auNumber + 1;
                        Position::model()->updatePositionNum($value, $auNumber);
                    }
                } else {
                    $childPos = array_diff($auxPos, $posIds);
                    foreach ($childPos as $value) {
                        $auNumber = Position::model()->getPositionUserNumById($value);
                        $auNumber = $auNumber - 1;
                        if ($auNumber < 0) {
                            $auNumber = 0;
                        }
                        Position::model()->updatePositionNum($value, $auNumber);
                    }
                }
                $this->handleAuxiliaryPosition($uid, $posIds, $_POST['positionid']);
            }

            // 辅助角色
            if (isset($_POST['auxiliaryrole'])) {
                $roleIds = StringUtil::getId($_POST['auxiliaryrole']);
                $this->handleAuxiliaryRole($uid, $roleIds, $_POST['roleid']);
            }
            $data = User::model()->create();
            User::model()->checkUnique($data);
            if ($data['status'] != User::USER_STATUS_NORMAL) {
                $canDisabled = User::model()->checkCanDisabled($uid);
                if (false === $canDisabled) {
                    return $this->error(Ibos::lang('make sure at least one admin'));
                }
            }
            User::model()->updateByUid($uid, $data);
            //岗位的修改
            if (!isset($_POST['positionid'])) {
                $number = Position::model()->getPositionUserNumById($positionid);//拿到修改之前的岗位人数
                $number = $number - 1;
                if ($number <= 0) {
                    $number = 0;
                }
                Position::model()->updatePositionNum($positionid, $number);//修改之前的岗位数
            } else {
                if ($positionid != $_POST['positionid']) {
                    $beforeNum = Position::model()->getPositionUserNumById($positionid);//拿到修改之前的岗位人数
                    $beforeNum = $beforeNum - 1;
                    if ($beforeNum <= 0) {
                        $beforeNum = 0;
                    }
                    Position::model()->updatePositionNum($positionid, $beforeNum);//修改之前的岗位数减一

                    $afterNum = Position::model()->getPositionUserNumById($_POST['positionid']);//通过post过来岗位ID来得到对应的人数
                    $afterNum = $afterNum + 1;
                    Position::model()->updatePositionNum($_POST['positionid'], $afterNum);//通过post过来岗位ID来得到对应的人数加一
                }
            }
            // 直属下属
            User::model()->updateAll(array('upuid' => 0), "`upuid`={$uid}"); // 先把旧的下属upuid清0
            $subUids = StringUtil::getId($_POST['subordinate']);
            User::model()->updateAll(array('upuid' => $uid), sprintf("FIND_IN_SET(`uid`,'%s')", implode(',', $subUids)));
            UserUtil::wrapUserInfo($uid, false, true); //这个方法的第三个参数为true会强制更新缓存
            if ($data['status'] != 2) {
                // 更新组织架构js调用接口
                Org::update();
            }
            CacheUtil::update();
            $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('user/index'));
        } else {
            if (empty($user)) {
                $this->error(Ibos::lang('Request param'), $this->createUrl('user/index'));
            }
            //需要重新去查找一下刚用户的最新信息，并且要强制生成新缓存，不然总是去拿旧的缓存，数据没有更新
            $user = User::model()->fetchByUid($uid, true, true);
            $user["auxiliarydept"] = DepartmentRelated::model()->fetchAllDeptIdByUid($user['uid']);
            $user["auxiliarypos"] = PositionRelated::model()->fetchAllPositionIdByUid($user['uid']);
            $user["auxiliaryrole"] = RoleRelated::model()->fetchAllRoleIdByUid($user['uid']);
            $user['subordinate'] = User::model()->fetchSubUidByUid($user['uid']); // 获取所有直属下属uid
            $account = Ibos::app()->setting->get('setting/account');
            if ($account['mixed']) {
                $preg = "[0-9]+[A-Za-z]+|[A-Za-z]+[0-9]+";
            } else {
                $preg = "^[A-Za-z0-9\!\@\#\$\%\^\&\*\.\~]{" . $account['minlength'] . ",32}$";
            }
            $param = array(
                'user' => $user,
                'passwordLength' => $account['minlength'],
                'preg' => $preg,
                'roles' => Role::model()->fetchAll()
            );
            $this->render('edit', $param);
        }
    }

    /**
     * 导出操作
     * @return void
     */
    public function actionExport()
    {
        $uid = urldecode(Env::getRequest('uid'));
        return UserUtil::exportUser(explode(',', trim($uid, ',')));
    }

    /**
     * 导入用户一系列操作入口
     */
    public function actionImport()
    {
        $op = Env::getRequest('op');
        if (in_array($op, array('downloadTpl', 'import', 'downError'))) {
            $this->$op();
        }
    }

    /**
     * 用户上下级关系
     */
    public function actionRelation()
    {
        $users = User::model()->findUserIndexByUid();
        $position = array();
        foreach ($users as $user) {
            $position[$user['uid']] = $user['positionid'];
        }
        $PositionArray = Position::model()->findPositionNameIndexByPositionid(array_unique(array_values($position)));
        $upUsers = array(); // 最顶级人员(没上司的人)
        foreach ($users as $user) {
            $subordinate = User::model()->fetchSubUidByUid($user['uid']);
            if ($user['upuid'] == 0 && empty($subordinate)) {
                $upUsers[] = array(
                    'uid' => $user['uid'],
                    'name' => $user['realname'],
                    'position' => !empty($user['position']) ? $PositionArray[$user['position']] : '',
                );
            }
        }
        $param = array(
            'upUsers' => $upUsers,
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('dashboard')
        );
        $op = Env::getRequest('op');
        if (in_array($op, array('getUsers', 'setUpuid'))) {
            $this->$op();
        } else {
            $alias = "application.modules.dashboard.views.user.relation";
            $html = $this->renderPartial($alias, $param, true);
            $this->ajaxReturn(array('isSuccess' => true, 'html' => $html));
        }
    }

    /**
     * 获取上下级关系用户数据
     */
    protected function getUsers()
    {
        $users = User::model()->findUserIndexByUid();
        $res = array();
        foreach ($users as $user) {
            $subordinate = User::model()->fetchSubUidByUid($user['uid']);
            if ($user['upuid'] != 0 || !empty($subordinate)) {
                $res[] = array(
                    'id' => $user['uid'],
                    'uid' => $user['uid'],
                    'name' => $user['realname'],
                    'pid' => $user['upuid'],
                    'pId' => $user['upuid']
                );
            }
        }
        $this->ajaxReturn($res);
    }

    /**
     * 移动上下级关系
     */
    protected function setUpuid()
    {
        $uid = Env::getRequest('id');
        $upuid = Env::getRequest('pid');
        if (!empty($uid)) {
            User::model()->modify($uid, array('upuid' => $upuid));
            Org::update();
            CacheUtil::update();
        }
        $this->ajaxReturn(array('isSuccess' => true));
    }

    /**
     * 下载模板文件
     */
    protected function downloadTpl()
    {
        $file = PATH_ROOT . self::IMPORT_TPL;
        $fileName = iconv('utf-8', 'gbk', '用户导入数据.' . pathinfo($file, PATHINFO_EXTENSION));
        if (is_file($file)) {
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=" . $fileName);
            readfile($file);
            exit;
        } else {
            $this->error("抱歉，找不到模板文件！");
        }
    }


    protected function import()
    {
        $attachId = intval(Env::getRequest('aid'));
        $attachs = Attach::getAttachData($attachId, false);
        $attach = array_shift($attachs); // 附件
        $file = File::getAttachUrl() . '/' . $attach['attachment'];
        $data = PHPExcel::excelToArray($file, array(0, 1, 2));
        $config = array(
            'department' => 0,
            'mobile' => 1,
            'password' => 2,
            'realname' => 3,
            'gender' => 4,
            'email' => 5,
            'wechat' => 6,
            'jobnumer' => 7,
            'username' => 8,
            'birthday' => 9,
            'telephone' => 10,
            'address' => 11,
            'qq' => 12,
            'bio' => 13,
        );
        $ajaxReturn = OrgIO::import($data, $config);
        @unlink($file); // 删除文件
        $this->ajaxReturn($ajaxReturn);
    }

    /**
     * 下载导入错误文件
     * 导出CSV格式
     */
//  protected function downError() {
//      $error = Cache::model()->fetchArrayByPk( 'userimportfail' );
//      Cache::model()->delete( "`cachekey` = 'userimportfail'" );
//      $fieldArr = array(
//          Ibos::lang( 'Line' ),
//          Ibos::lang( 'Username' ),
//          Ibos::lang( 'Realname' ),
//          Ibos::lang( 'Error reason' ),
//      );
//      $str = implode( ',', $fieldArr ) . "\n";
//      foreach ( $error as $line => $row ) {
//          $param = array( $line, $row['username'], $row['realname'], $row['reason'] );
//          $str .= implode( ',', $param ) . "\n"; //用引文逗号分开
//      }
//      $outputStr = iconv( 'utf-8', 'gbk//ignore', $str );
//      $filename = Ibos::lang( 'Import error record' ) . '.csv';
//      File::exportCsv( $filename, $outputStr );
//  }
    /**
     * 下载导入用户错误文件
     * 导出Excel格式
     */
    protected function downError()
    {
        $error = Cache::model()->fetchArrayByPk('userimportfail');
        Cache::model()->delete("`cachekey` = 'userimportfail'");
        $return = array();
        foreach ($error as $key => $row) {
            $return[$key]['line'] = $key;
            $return[$key]['username'] = $row['username'];
            $return[$key]['realname'] = $row['realname'];
            $return[$key]['reason'] = $row['reason'];
        }
        $filename = $filename = date('Y-m-d') . '用户导入错误信息.xls';
        $fieldArr = array(
            Ibos::lang('Line'),
            Ibos::lang('Username'),
            Ibos::lang('Realname'),
            Ibos::lang('Error reason'),
        );
        PHPExcel::exportToExcel($filename, $fieldArr, $return);
    }

    /**
     * 编辑动作: 设置用户状态
     * @param string $status 状态标识
     * @param string $uids 用户id
     * @return void
     */
    protected function setStatus($status, $uids)
    {
        $uidArr = explode(',', trim($uids, ','));
        $attributes = array();
        switch ($status) {
            case 'lock':
                $attributes['status'] = 1;
                break;
            case 'disabled':
                $attributes['status'] = 2;
                Org::hookSyncUser($uids, '', 0);
                break;
            case 'enabled':
            default:
                $attributes['status'] = 0;
                Org::hookSyncUser($uids, '', 2);
                break;
        }

        $canDisabled = User::model()->checkCanDisabled($uidArr);
        if (false === $canDisabled) {
            return $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('make sure at least one admin')
            ));
        }
        $return = User::model()->updateByUids($uidArr, $attributes);
        UserUtil::wrapUserInfo($uidArr, false, true);
        Org::update();

        // 更新 position 的缓存信息
        CacheUtil::update(array('position'));
        CacheUtil::load(array('position'));

        return $this->ajaxReturn(array('isSuccess' => !!$return, 'msg' => Ibos::lang('Stay enable')), 'json');
    }

    /**
     * 辅助部门插入数据处理
     * @param integer $uid 用户ID
     * @param array $deptIds 辅助部门ID
     * @param string $except 主部门id
     */
    protected function handleAuxiliaryDept($uid, $deptIds, $except = '')
    {
        DepartmentRelated::model()->deleteAll('`uid` = :uid', array(':uid' => $uid));
        foreach ($deptIds as $deptId) {
            if (strcmp($deptId, $except) !== 0) {
                DepartmentRelated::model()->add(array('uid' => $uid, 'deptid' => $deptId));
            }
        }
    }

    /**
     * 辅助岗位插入数据处理
     * @param integer $uid 用户ID
     * @param array $posIds
     * @param string $except 主岗位ID
     */
    protected function handleAuxiliaryPosition($uid, $posIds, $except = '')
    {
        PositionRelated::model()->deleteAll('`uid` = :uid', array(':uid' => $uid));
        foreach ($posIds as $posId) {
            if (strcmp($posId, $except) !== 0) {
                PositionRelated::model()->add(array('uid' => $uid, 'positionid' => $posId));
            }
        }
    }

    /**
     * 辅助角色插入数据处理
     * @param integer $uid 用户ID
     * @param array $roleIds 副角色ids
     * @param string $except 主角色ID
     */
    protected function handleAuxiliaryRole($uid, $roleIds, $except = '')
    {
        RoleRelated::model()->deleteAll('`uid` = :uid', array(':uid' => $uid));
        foreach ($roleIds as $roleId) {
            if (strcmp($roleId, $except) != 0 && !empty($roleId)) {
                RoleRelated::model()->add(array('uid' => $uid, 'roleid' => $roleId));
            }
        }
    }

    /**
     * 特别参数再处理
     */
    protected function dealWithSpecialParams()
    {
        $_POST['upuid'] = implode(',', StringUtil::getUid($_POST['upuid']));
        $_POST['deptid'] = implode(',', StringUtil::getId($_POST['deptid']));
        $_POST['positionid'] = implode(',', StringUtil::getId($_POST['positionid']));
        $_POST['roleid'] = implode(',', StringUtil::getId($_POST['roleid']));
    }

    /**
     * 获取左侧分类树
     */
    protected function getDeptTree()
    {
        $component = new ICDepartmentCategory('application\modules\department\model\Department', '', array('index' => 'deptid', 'name' => 'deptname'));
        $this->ajaxReturn($component->getAjaxCategory($component->getData()), 'json');
    }

    /**
     * 用formValidator异步检查数据是否已被注册
     */
    public function actionIsRegistered()
    {
        //$fieldName获取要检查的字段名
        $fieldName = Env::getRequest('clientid');
        //$fieldValue获取此字段用户输入的值
        $fieldValue = Env::getRequest($fieldName);
        //如果有传递uid，是用户编辑资料，没有uid，是新注册资料
        $uid = Env::getRequest('uid');
        if ($uid) {
            $userInfo = User::model()->findByPk($uid);
            $fieldExists = User::model()->fetch("$fieldName = '{$fieldValue}' and $fieldName != '{$userInfo[$fieldName]}'");
        } else {
            if ($fieldValue == '' || $fieldValue == null) {
                //若用户输入为空，则判断通过
                return $this->ajaxReturn(array('isSuccess' => true), 'json');
            } else {
                //查找数据库的$fieldName字段是否有$fieldValue这个值
                $fieldExists = User::model()->find("$fieldName = :getValue", array(":getValue" => $fieldValue));
            }
        }
        //有数据则表示已经注册，返回true，没数据表示没注册，返回false
        $isRegistered = $fieldExists ? true : false;
        return $this->ajaxReturn(array('isSuccess' => !$isRegistered), 'json');
    }

    /**
     * 添加辅助角色信息
     * @param array $list 数据列表
     * @return array
     */
    protected function addRelatedRole($list)
    {
        if (empty($list)) {
            return array();
        }
        $relatedRole = array();
        $uids = Convert::getSubByKey($list, 'uid');
        foreach ($uids as $uid) {
            $relatedRole[$uid] = array_map(function ($rid) {
                return Role::model()->getRoleNameByRoleid($rid);
            }, RoleRelated::model()->fetchAllRoleIdByUid($uid));
        }
        foreach ($list as $key => $value) {
            $list[$key]['relatedRole'] = $relatedRole[$value['uid']];
        }
        return $list;
    }

}
