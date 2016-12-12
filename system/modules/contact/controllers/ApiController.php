<?php
/**
 * 通讯录 API 控制器
 *
 * @namespace application\modules\contact\controllers
 * @filename ApiController.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/7 14:12
 */

namespace application\modules\contact\controllers;


use application\core\controllers\ApiController as IbosApiController;
use application\modules\contact\utils\CorpUtil;
use application\modules\contact\utils\DeptUtil;
use application\modules\contact\utils\UserCacheUtil;
use application\modules\contact\utils\UserUtil;

/**
 * Class ApiController
 *
 * @package application\modules\contact\controllers
 */
class ApiController extends IbosApiController
{
    /**
     * @var bool
     */
    protected $needLogin = true;
    
    /**
     * @var array 请求验证规则
     */
    protected $validateRules = array(
        'deptlist' => array(
            'optional' => 'deptid',
        ),
        'userlist' => array(
            'optional' => array(
                array('search'),
                array('deptid'),
            ),
        ),
        'groupuserlist' => array(
            'optional' => array(
                array('deptid'),
                array('search'),
            ),
        ),
        'search' => array(
            'optional' => array(
                array('deptid'),
                array('search'),
            ),
        ),
        'dept' => array(
            'required' => 'deptid',
            'integer' => 'deptid',
        ),
        'user' => array(
            'required' => 'userid',
            'integer' => 'userid',
        ),
        'addhidemobile' => array(
            'optional' => 'publishscope',
        ),
    );
    
    
    /**
     * API 接口：返回部门列表数据
     */
    public function actionDeptList()
    {
        $deptId = $this->getRequest('deptid');
        
        $deptDetail = DeptUtil::getInstance()->fetchDeptOrDetail($deptId);
        $deptDetail['depts'] = DeptUtil::getInstance()->fetchDeptList($deptId);
        
        return $this->ajaxBaseReturn(true, $deptDetail);
    }
    
    /**
     * API 接口：返回用户列表数据
     */
    public function actionUserList()
    {
        $deptId = $this->getRequest('deptid');

        $uidArr = UserCacheUtil::getInstance()->fetchUidArrByDeptId($deptId);
        $uidList = UserCacheUtil::getInstance()->handleUidList($deptId, $uidArr);
        
        return $this->ajaxBaseReturn(true, $uidList);
    }
    
    
    /**
     * API 接口：返回分组后的用户列表数据
     */
    public function actionGroupUserList()
    {
        $deptId = $this->getRequest('deptid');
        
        $userList = UserCacheUtil::getInstance()->fetchAllGroupUsers($deptId);
        
        return $this->ajaxBaseReturn(true, $userList);
    }
    
    /**
     * API 接口：用户搜索
     */
    public function actionSearch()
    {
        $deptId = $this->getRequest('deptid');
        $search = $this->getRequest('search');
        
        $userList = UserUtil::getInstance()->searchUserList($deptId, $search);
        
        return $this->ajaxBaseReturn(true, $userList);
    }
    
    /**
     * API 接口：查看企业数据
     */
    public function actionCorp()
    {
        return $this->ajaxBaseReturn(true, CorpUtil::getInstance()->fetchCorpDetail());
    }
    
    /**
     * API 接口：查看部门数据
     */
    public function actionDept()
    {
        $deptId = $this->getRequest('deptid');
        
        return $this->ajaxBaseReturn(true, DeptUtil::getInstance()->fetchDeptDetail($deptId));
    }
    
    /**
     * API 接口：查看用户数据
     */
    public function actionUser()
    {
        $userId = $this->getRequest('userid');
        
        $userDetail = UserUtil::getInstance()->fetchUserDetail($userId);
        
        return $this->ajaxBaseReturn(true, $userDetail);
    }
    
    /**
     * API 接口：返回隐藏手机号码的用户 uid 列表
     */
    public function actionHiddenUidArr()
    {
        $uidArr = UserUtil::getInstance()->fetchHiddenUidArr();
        
        return $this->ajaxBaseReturn(true, array('users' => $uidArr));
    }
    
    /**
     * API 接口：设置需要号码隐藏的人员
     */
    public function actionAddHideMobile()
    {
        $publishScope = $this->getRequest('publishscope');
        
        UserUtil::getInstance()->addHiddenUidArr($publishScope);
        
        return $this->ajaxBaseReturn(true, array());
    }
    
}
