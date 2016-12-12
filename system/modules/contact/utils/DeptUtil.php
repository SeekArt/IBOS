<?php
/**
 * 部门工具类
 *
 * @namespace application\modules\contact\utils
 * @filename DeptUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/7 19:02
 */

namespace application\modules\contact\utils;


use application\core\utils\Ibos;
use application\core\utils\System;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\user\model\User;

/**
 * Class DeptUtil
 *
 * @package application\modules\contact\utils
 */
class DeptUtil extends System
{
    /**
     * @param string $className
     * @return DeptUtil
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
     * 返回部门或公司的详细数据
     *
     * @param integer $deptId 部门 id
     * @return array
     */
    public function fetchDeptOrDetail($deptId)
    {
        $deptDetail = array();
        $corpShortName = CorpUtil::getInstance()->fetchCorpShortName();
        
        if (empty($deptId)) {
            // 返回公司数据
            $deptDetail['deptid'] = 0;
            $deptDetail['deptname'] = $corpShortName;
            $deptDetail['deptnum'] = User::model()->countNums(false, false);
            $deptDetail['crumb'] = array($corpShortName);
            $deptDetail['isdept'] = false;
        } else {
            // 返回部门数据
            $deptNode = DeptCacheUtil::getInstance()->getCache()->getNodeById($deptId);

            $deptDetail['deptid'] = $deptId;
            $deptDetail['deptname'] = $deptNode->get('deptname');
            $deptDetail['deptnum'] = UserCacheUtil::getInstance()->countAllUidByDeptId($deptId);
            $deptDetail['isdept'] = true;
            $deptDetail['crumb'] = $this->fetchCrumb($deptId);
        }
        
        return $deptDetail;
    }
    
    /**
     * 获取部门信息，如果部门不存在，则抛出异常。
     *
     * @param integer $deptId 部门 id
     * @return array
     * @throws \Exception
     */
    public function fetchDeptByPk($deptId)
    {
        $deptModel = Department::model()->fetchByPk($deptId);
        
        if (empty($deptModel)) {
            throw new \Exception(sprintf(Ibos::lang('Dept is not exists'), $deptId));
        }
        
        return $deptModel;
    }
    
    /**
     * 返回部门详细数据
     *
     * @param integer $deptId 部门 id
     * @return array 部门详细数据
     */
    public function fetchDeptDetail($deptId)
    {
        $deptModel = $this->fetchDeptByPk($deptId);
        
        $deptDetail = array(
            'deptname' => '',
            'fax' => '',
            'managername' => '',
            'address' => '',
            'func' => '',
        );
        
        $manageUid = isset($deptModel['manager']) ? $deptModel['manager'] : 0;
        $manager = User::model()->fetchByPk($manageUid);

        $deptDetail['deptname'] = isset($deptModel['deptname']) ? $deptModel['deptname'] : '';
        $deptDetail['fax'] = isset($deptModel['fax']) ? $deptModel['fax'] : '';
        $deptDetail['address'] = isset($deptModel['addr']) ? $deptModel['addr'] : '';
        $deptDetail['func'] = isset($deptModel['func']) ? $deptModel['func'] : '';
        $deptDetail['tel'] = isset($deptModel['tel']) ? $deptModel['tel'] : '';
        $deptDetail['managername'] = isset($manager['realname']) ? $manager['realname'] : '';
        $deptDetail['bgbig'] = $this->getBgImageUrl();
        return $deptDetail;
    }
    
    /**
     * 返回企业背景图片 url
     *
     * @return string
     */
    public function getBgImageUrl()
    {
        return Ibos::app()->assetManager->getAssetsUrl(MODULE_NAME) . '/image/corp_bg_big.png';
    }
    
    /**
     * 获取部门 id 为 deptid 的部门的所有上层部门名称。
     * Example：array*()
     *
     * @param integer $deptId 部门 id
     * @return array
     */
    public function fetchCrumb($deptId)
    {
        static $crumbArr = array();
        if (isset($crumbArr[$deptId])) {
            return $crumbArr[$deptId];
        }

        $deptNodeArr = DeptCacheUtil::getInstance()->fetchAllParent($deptId);
        $deptNameArr = array();
        foreach ($deptNodeArr as $loopDeptNode) {
            $deptNameArr[] = $loopDeptNode->get('deptname');
        }
        $deptNameArr = array_reverse($deptNameArr);
        
        $crumb = array_merge(array(CorpUtil::getInstance()->fetchCorpShortName()), $deptNameArr);
        $crumbArr[$deptId] = $crumb;

        return $crumb;
    }
    
    /**
     * 获取部门列表
     *
     * @param integer $deptId
     * @return array
     */
    public function fetchDeptList($deptId)
    {
        $deptId = (int)$deptId;
        $retData = array();
        
        // 获取子部门数据
        $deptNodeArr = DeptCacheUtil::getInstance()->getCache()->getNodeById($deptId)->getChildren();

        // 按照部门的 sort 字段从小到大排序
        usort($deptNodeArr, function($item1, $item2) {
            $item1Sort = $item1->get('sort');
            $item2Sort = $item2->get('sort');

            if ($item1Sort == $item2Sort) {
                return 0;
            }

            return ($item1Sort < $item2Sort) ? -1 : 1;
        });

        foreach ($deptNodeArr as $deptNode) {
            $deptId = $deptNode->getId();
            $retData[] = array(
                'deptid' => $deptId,
                'deptname' => $deptNode->get('deptname'),
                'deptnum' => UserCacheUtil::getInstance()->countAllUidByDeptId($deptId),
                'hasmore' => $deptNode->hasChildren(),
            );
        }
        
        return $retData;
    }
    
    /**
     * 获取该用户的所有辅助部门信息
     *
     * @param integer $uid 用户 uid
     * @return array
     */
    public function fetchAuxiliaryDept($uid)
    {
        // 获取该用户的所有辅助部门 id
        $deptIdArr = DepartmentRelated::model()->fetchAllDeptIdByUid($uid);
        
        // 清空空数据
        $deptIdArr = array_filter($deptIdArr);
        
        if (empty($deptIdArr)) {
            return array();
        }
        
        $deptModelArr = Department::model()->findDepartmentByDeptid($deptIdArr);
        
        return $deptModelArr;
    }
    
}
