<?php

/**
 * 组织架构模块部门控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块部门控制器类,提供增删查改功能
 *
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: DepartmentController.php 4064 2014-09-03 09:13:16Z zhangrong $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CHtml;

class DepartmentController extends OrganizationBaseController
{

    /**
     * 下拉选择框字符串格式
     * @var string
     */
    public $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";

    /**
     * 增加操作
     * @return void
     */
    public function actionAdd()
    {
        if (Env::submitCheck('addsubmit')) {
            $this->dealWithBranch();
            $this->dealWithSpecialParams();
            $data = Department::model()->create();
            $data['isbranch'] = isset($_POST['isbranch']) ? 1 : 0;
            $newId = Department::model()->add($data, true);
            Department::model()->modify($newId, array('sort' => $newId));
            $newId && Org::update();
            Cache::update('setting');
            $this->success(Ibos::lang('Save succeed', 'message'), $this->createUrl('user/index'));
        } else {
            $dept = DepartmentUtil::loadDepartment();
            $param = array(
                'tree' => StringUtil::getTree($dept, $this->selectFormat),
            );
            $this->render('add', $param);
        }
    }

    /**
     * 编辑操作
     * @return void
     */
    public function actionEdit()
    {
        if (Env::getRequest('op') == 'get') {
            return $this->get();
        } elseif (Env::getRequest('op') == 'member') {
            return $this->member();
        }
        $pid = Env::getRequest('pid');
        if (Env::getRequest('op') == 'structure') { // 排序
            $_deptid = Env::getRequest('id');
            $deptid = StringUtil::getId($_deptid);
            $index = Env::getRequest('index'); // 排序后位置,0表示第一位，1表示第二位...
            $pid = empty($pid) ? '0' : StringUtil::getId($pid);
            $status = $this->setStructure($index, $deptid['0'], $pid['0']);
            Org::update();
            return $this->ajaxReturn(array('isSuccess' => $status), 'json');
        }
        $deptId = Env::getRequest('deptid');
        // 总部
        if ($deptId == '0') {
            //不再组织架构这里单独处理总公司，只保留全局设置的
        } else {
            if ($deptId == $pid) {
                $this->error(Ibos::lang('update failed, up dept cannot be itself'));
            }
            $this->dealWithBranch();
            $this->dealWithSpecialParams();
            $data = Department::model()->create();
            $data['isbranch'] = isset($_POST['isbranch']) ? 1 : 0;
            $editStatus = Department::model()->modify($data['deptid'], $data);
            $editStatus && Org::update();
            Cache::update('setting');
        }
        return $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('user/index'));
    }

    /**
     * 删除操作
     * @return void
     */
    public function actionDel()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $delId = Env::getRequest('id');
            if (Department::model()->countChildByDeptId($delId)) {
                $delStatus = false;
                $msg = Ibos::lang('Remove the child department first');
            } else {
                $delStatus = Department::model()->remove($delId);
                // 删除辅助部门关联
                DepartmentRelated::model()->deleteAll('deptid = :deptid', array(':deptid' => $delId));
                $relatedIds = User::model()->fetchAllUidByDeptid($delId);
                // 更新用户部门信息
                if (!empty($relatedIds)) {
                    User::model()->updateByUids($relatedIds, array('deptid' => 0));
                }
                $delStatus && Org::update();
                $msg = Ibos::lang('Operation succeed', 'message');
            }
            return $this->ajaxReturn(array('isSuccess' => !!$delStatus, 'msg' => $msg), 'json');
        }
    }

    /**
     * 获取部门成员
     */
    public function member()
    {
        $id = Env::getRequest('id');
        if (empty($id) && $id == 0) {
            $this->render('editHeadDept');
        } else {
            if (Env::submitCheck('postsubmit')) {
                $member = Env::getRequest('member');
                $uids = StringUtil::getUidAByUDPX($member);
                $batchSetRes = DepartmentUtil::updateDepartmentUserList($id, $uids);
                if ($batchSetRes) {
                    Org::update();
                    $this->success(Ibos::lang('Save succeed', 'message'));
                } else {
                    $this->error(Ibos::lang('Save failed', 'message'));
                }
            } else {
                // 该部门下人员
                $uids = User::model()->fetchAllUidByDeptid($id, false, true);
                // 搜索处理
                if (Env::submitCheck('search')) {
                    $key = $_POST['keyword'];
                    $uidStr = implode(',', $uids);
                    $users = User::model()->fetchAll("`realname` LIKE '%{$key}%' AND FIND_IN_SET(`uid`, '{$uidStr}')");
                    $pageUids = Convert::getSubByKey($users, 'uid');
                } else {
                    $count = count($uids);
                    $pages = Page::create($count, self::MEMBER_LIMIT);
                    $offset = $pages->getOffset();
                    $limit = $pages->getLimit();
                    $pageUids = array_slice($uids, $offset, $limit);
                    $data['pages'] = $pages;
                }
                $data['id'] = $id;
                // for input
                $data['uids'] = $uids;
                // for js
                $data['uidString'] = '';
                foreach ($uids as $uid) {
                    $data['uidString'] .= "'u_" . $uid . "',";
                }
                $data['uidString'] = trim($data['uidString'], ',');
                // 当前页要显示的uid（只作显示，并不为实际表单提交数据）
                $data['pageUids'] = $pageUids;
                $this->render('member', $data);
            }
        }
    }

    /**
     * 获取部门编辑数据
     * @return void
     */
    protected function get()
    {
        $id = Env::getRequest('id');
        if ($id == 0) { // 总公司
            $this->render('editHeadDept');
        } else {
            $result = Department::model()->fetchByPk($id);
            $result['manager'] = StringUtil::wrapId(array($result['manager']));
            $result['leader'] = StringUtil::wrapId(array($result['leader']));
            $result['subleader'] = StringUtil::wrapId(array($result['subleader']));
            $depts = DepartmentUtil::loadDepartment();
            $param = array(
                'id' => $id,
                'department' => $result,
                'tree' => StringUtil::getTree($depts, $this->selectFormat, $result['pid']),
            );
            $this->render('edit', $param);
        }
    }

    /**
     * 改变前后排序结果
     * @param integer $index 目标位置
     * @param integer $deptid 当前部门ID
     * @param integer $pid 目标PID
     * @return boolean
     */
    protected function setStructure($index, $deptid, $pid)
    {
        $depts = Department::model()->fetchAll(array('condition' => "`pid`={$pid} AND `deptid`!={$deptid}", 'order' => "`sort` ASC")); // 把移动到的父级原有的部门找出来
        foreach ($depts as $k => $dept) {
            $newSort = $k;
            if ($newSort >= $index) {
                $newSort = $k + 1; // 比新插入的部门后，排序加1
            }
            Department::model()->modify($dept['deptid'], array('sort' => $newSort + 1), '', array(), false); // 排序从1开始的，所以+1
        }
        Department::model()->modify($deptid, array('sort' => $index + 1, 'pid' => $pid));
        return true;
    }

    /**
     * 处理分支判断
     * @return void
     */
    protected function dealWithBranch()
    {
        $isBranch = Env::getRequest('isbranch');
        $pid = Env::getRequest('pid');
        if ($isBranch) {
            // 如果有部门要设置分支机构，其上级只能为顶级或分支机构
            if ($pid == 0 || Department::model()->getIsBranch($pid)) {
                // do nothing
            } else {
                $this->error(Ibos::lang('Incorrect branch setting'));
            }
        }
    }

    /**
     * 特别参数再处理
     * @return void
     */
    protected function dealWithSpecialParams()
    {
        $_POST['deptname'] = CHtml::encode($_POST['deptname']);
        $_POST['tel'] = CHtml::encode($_POST['tel']);
        $_POST['fax'] = CHtml::encode($_POST['fax']);
        $_POST['addr'] = CHtml::encode($_POST['addr']);
        $_POST['func'] = CHtml::encode($_POST['func']);
        $_POST['manager'] = implode(',', StringUtil::getUid($_POST['manager']));
        $_POST['leader'] = implode(',', StringUtil::getUid($_POST['leader']));
        $_POST['subleader'] = implode(',', StringUtil::getUid($_POST['subleader']));
    }

    /**
     * 批量修改用户部门 ajax 接口
     * @return ajax
     */
    public function actionBatchAlterUserDept()
    {
        $department = explode('_', Env::getRequest('id'));
        $member = Env::getRequest('member');
        $uids = StringUtil::getUidAByUDPX($member);
        $batchSetRes = UserUtil::batchSetUserDepartment($department[1], $uids);
        if ($batchSetRes) {
            Org::update();
            $this->ajaxReturn(array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Save succeed', 'message'),
            ));
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Save failed', 'message'),
            ));
        }
    }

}
