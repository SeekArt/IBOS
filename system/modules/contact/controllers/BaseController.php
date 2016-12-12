<?php

/**
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 通讯录模块------ 通讯录基类控制器
 *
 * @package application.modules.contact.controllers
 * @version $Id: ContactBaseController.php 2669 2014-03-14 10:58:29Z gzhzh $
 */

namespace application\modules\contact\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\PHPExcel;
use application\core\utils\StringUtil;
use application\modules\contact\model\Contact;
use application\modules\contact\utils\Contact as ContactUtil;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\main\model\Setting;

/**
 * Class BaseController
 *
 * @package application\modules\contact\controllers
 */
class BaseController extends Controller
{
    /*
     * 所有字母
     */
    
    protected $allLetters = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z'
    );
    
    /**
     * 取得侧栏导航
     *
     * @return string
     */
    protected function getSidebar()
    {
        $sidebarAlias = 'application.modules.contact.views.sidebar';
        $dept = DepartmentUtil::loadDepartment();
        $params = array(
            'dept' => $dept,
            'lang' => Ibos::getLangSource('contact.default'),
            'unit' => Ibos::app()->setting->get('setting/unit')
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }
    
    /**
     * 按部门排列
     *
     * @return array
     */
    protected function getDataByDept()
    {
        $deptid = Env::getRequest('deptid');
        $allDepts = DepartmentUtil::loadDepartment();
        $unit = StringUtil::utf8Unserialize(Setting::model()->fetchSettingValueByKey('unit'));
        if ($deptid !== '0' && empty($deptid)) {
            $deptid = 'c_0';
        }
        if ($deptid === 'c_0') {
            $depts = ContactUtil::handleDeptData($allDepts, 0);
            // 按照上面的代码拿到的部门数据是不包含顶级部门的，会导致漏掉在顶级部门的用户
            // 所以这里人为将顶级部门连接进数组
            $depts = array_merge(array(array('deptid' => 0, 'deptname' => $unit['fullname'])), $depts);
        } elseif ($deptid === '0') {
            $allDepts = array_merge(array(array('deptid' => 0, 'deptname' => $unit['fullname'])), $allDepts);
            $uids = explode(',', Env::getRequest('uids'));
            if (!empty($uids)) {
                $users = User::model()->fetchAllByUids($uids);
                foreach ($users as $user) {
                    if (!isset($depts[$user['deptid']])) {
                        $depts[$user['deptid']] = $allDepts[$user['deptid']];
                    }
                    $depts[$user['deptid']]['users'][$user['uid']] = $user;
                }
            } else {
                $depts = array();
            }
            return $depts;
        } else {
            $childDepts = Department::model()->fetchChildDeptByDeptid($deptid, $allDepts);
            $selfDept = Department::model()->fetchByPk($deptid);
            $depts = array_merge(array($selfDept), $childDepts);
            $deptsTmp = ContactUtil::handleDeptData($depts, $deptid);
            $depts = array_merge(array($selfDept), $deptsTmp);
        }
        
        foreach (array_values($depts) as $k => $childDept) {
            $pDeptids = Department::model()->queryDept($childDept['deptid']);
            $depts[$k]['pDeptids'] = !empty($pDeptids) ? array_reverse(explode(',', trim($pDeptids))) : array();
            $deptUids = User::model()->fetchAllUidByDeptid($childDept['deptid'], false);
            $deptRelatedUids = DepartmentRelated::model()->fetchAllUidByDeptId($childDept['deptid']);
            $uids = array_unique(array_merge($deptUids, $deptRelatedUids));
            $uids = $this->removeDisabledUid($uids);
            $depts[$k]['users'] = !empty($uids) ? User::model()->fetchAllByUids($uids) : array();
        }
        //var_dump($depts);die;
        return $depts;
    }
    
    /**
     * 去掉禁用的uid
     *
     * @param array $uidArr 要处理的uid数组
     * @return array
     */
    private function removeDisabledUid($uidArr)
    {
        if (!is_array($uidArr)) {
            return array();
        }
        $disabledUidArr = User::model()->fetchAllUidsByStatus(2);
        foreach ($uidArr as $k => $uid) {
            if (in_array($uid, $disabledUidArr)) {
                unset($uidArr[$k]);
            }
        }
        return $uidArr;
    }
    
    /**
     * 按拼音排列
     *
     * @return array
     */
    protected function getDataByLetter()
    {
        $deptid = intval(Env::getRequest('deptid'));
        if (!empty($deptid)) {
            $deptids = Department::model()->fetchChildIdByDeptids($deptid, true);
            $uids = User::model()->fetchAllUidByDeptids($deptids, false);
        } else {
            $uids = User::model()->fetchUidA(false);
        }
        if (empty($uids)) {
            return array();
        } else {
            $res = UserUtil::getUserByPy($uids);
            return ContactUtil::handleLetterGroup($res);
        }
    }
    
    /**
     * 异步请求入口
     */
    protected function ajaxApi()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $op = Env::getRequest('op');
            if (!in_array($op, array('getProfile', 'changeConstant', 'export', 'printContact'))) {
                $this->ajaxReturn(array('isSuccess' => false, Ibos::lang('Request tainting', 'error')));
            }
            $this->$op();
        }
    }
    
    /**
     * 获取某个用户资料
     */
    protected function getProfile()
    {
        $uid = intval(Env::getRequest('uid'));
        $user = User::model()->fetchByUid($uid);
        // 部门传真
        $user['fax'] = '';
        if (!empty($user['deptid'])) {
            $dept = Department::model()->fetchByPk($user['deptid']);
            $user['fax'] = $dept['fax'];
        }
        $user['birthday'] = !empty($user['birthday']) ? date('Y-m-d', $user['birthday']) : '';
        $cuids = Contact::model()->fetchAllConstantByUid(Ibos::app()->user->uid); // 常联系人id数组
        $this->ajaxReturn(array(
            'isSuccess' => true,
            'user' => $user,
            'uid' => Ibos::app()->user->uid,
            'cuids' => $cuids
        ));
    }
    
    /**
     * 改变常联系人状态
     */
    protected function changeConstant()
    {
        $uid = Ibos::app()->user->uid;
        $cuid = intval(Env::getRequest('cuid'));
        $status = Env::getRequest('status');
        if ($status == 'mark') { // 标记为常联系人
            Contact::model()->addConstant($uid, $cuid);
        } elseif ($status == 'unmark') { // 取消常联系人
            Contact::model()->deleteConstant($uid, $cuid);
        }
        $this->ajaxReturn(array('isSuccess' => true));
    }
    
    /**
     * 导出通讯录
     * 导出CSV格式
     */
//	public function export() {
//		$userDatas = $this->getUserData();
//		$fieldArr = array(
//			Ibos::lang( 'Real name' ),
//			Ibos::lang( 'Position' ),
//			Ibos::lang( 'Telephone' ),
//			Ibos::lang( 'Cell phone' ),
//			Ibos::lang( 'Email' ),
//			Ibos::lang( 'QQ' )
//		);
//		$str = implode( ',', $fieldArr ) . "\n";
//		foreach ( $userDatas as $user ) {
//			$realname = $user['realname'];
//			$posname = $user['posname'];
//			$telephone = $user['telephone'];
//			$mobile = $user['mobile'];
//			$email = $user['email'];
//			$qq = $user['qq'];
//			$str .= $realname . ',' . $posname . ',' . $telephone . ',' . $mobile . ',' . $email . ',' . $qq . "\n"; //用引文逗号分开
//		}
//		$outputStr = iconv( 'utf-8', 'gbk//ignore', $str );
//		$filename = date( 'Y-m-d' ) . mt_rand( 100, 999 ) . '.csv';
//		File::exportCsv( $filename, $outputStr );
//	}
    /**
     * 导出通讯录
     * 导出Excel格式
     *
     * @author Sam <gzxgs@ibos.com.cn>
     */
    public function export()
    {
        $userDatas = $this->getUserData();
        $fieldArr = array(
            Ibos::lang('Real name'),
            Ibos::lang('Department'),
            Ibos::lang('Position'),
            Ibos::lang('Cell phone'),
            Ibos::lang('Email'),
            Ibos::lang('Job number')
        );
        $data = array();
        if (!empty($userDatas)) {
            foreach ($userDatas as $key => $user) {
                $data[$key]['realname'] = !empty($user['realname']) ? $user['realname'] : '';
                $data[$key]['department'] = $user['deptname'];
                $data[$key]['posname'] = $user['posname'];
                $data[$key]['mobile'] = $user['mobile'];
                $data[$key]['email'] = $user['email'];
                $data[$key]['qq'] = $user['jobnumber'];
            }
            $filename = date('Y-m-d') . mt_rand(100, 999) . '.xls';
            PHPExcel::exportToExcel($filename, $fieldArr, $data);
        } else {
            exit('no data');
        }
    }
    
    /**
     * 打印通讯录
     */
    public function printContact()
    {
        $datas = $this->getUserData();
        $params = array(
            'datas' => $datas,
            'lang' => Ibos::getLangSource('contact.default'),
            'uint' => Ibos::app()->setting->get('setting/unit'),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('contact')
        );
        $detailAlias = 'application.modules.contact.views.default.print';
        $detailView = $this->renderPartial($detailAlias, $params, true);
        $this->ajaxReturn(array('view' => $detailView, 'isSuccess' => true));
    }
    
    /**
     * 获取符合要求的用户数据
     *
     * @return array
     */
    protected function getUserData()
    {
        $uid = Env::getRequest('uids');
        $uidArray = StringUtil::getUidAByUDPX($uid);
        if (!empty($uidArray)) {
            $userArray = User::model()->fetchAllByUids($uidArray, false);
        } else {
            $userArray = array();
        }
        return $userArray;
    }
}
