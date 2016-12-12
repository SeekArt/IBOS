<?php
/**
 *
 * @namespace application\modules\contact\model
 * @filename ContactHide.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/9 17:05
 */

namespace application\modules\contact\model;


use application\core\model\Model;
use application\modules\article\utils\Article as ArticleUtil;

/**
 * Class ContactHide
 *
 * @package application\modules\contact\model
 */
class ContactHide extends Model
{
    /**
     * 手机号码字段
     */
    const MOBILE_COLUMN = 'mobile';
    
    /**
     * @param string $className
     * @return ContactHide
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{contact_hide}}';
    }
    
    /**
     * 添加一条隐藏记录
     *
     * @param string $deptId 发布范围部门
     * @param string $positionId 发布范围岗位
     * @param string $roleId 发布范围角色
     * @param string $uid 发布范围人员
     * @param string $column 需要隐藏的字段名称
     * @return mixed
     */
    public function addOne($deptId, $positionId, $roleId, $uid, $column)
    {
        return $this->add(array(
            'deptid' => $deptId,
            'positionid' => $positionId,
            'roleid' => $roleId,
            'uid' => $uid,
            'column' => $column,
        ));
    }
    
    /**
     * 根据 column 删除所有记录
     *
     * @param string $column 需要隐藏字段的名称
     * @return bool
     */
    public function delAllByColumn($column)
    {
        return $this->deleteAll('`column` = :column', array(':column' => $column));
    }
    
    /**
     * 根据隐藏字段获取记录
     *
     * @param string $column 需要隐藏字段的名称
     * @return array
     */
    public function fetchByColumn($column)
    {
        return $this->fetchByAttributes(array('column' => $column));
    }
    
    /**
     * 根据隐藏字段 column 获取用户 uid 数组
     *
     * @param string $column 需要隐藏字段的名称
     * @return array
     */
    public function fetchUidArrByColumn($column)
    {
        $model = $this->fetchByColumn($column);
        
        if (empty($model)) {
            return array();
        }
        
        $uidArr = ArticleUtil::getScopeUidArr(array(
            'deptid' => $model['deptid'],
            'positionid' => $model['positionid'],
            'roleid' => $model['roleid'],
            'uid' => $model['uid'],
        ));
        
        return $uidArr;
    }
    
    /**
     * 用户是否隐藏 column 字段
     *
     * @param integer $uid 用户 uid
     * @param string $column 需要隐藏字段的名称
     * @return bool true 隐藏，false 未隐藏
     */
    public function isHide($uid, $column)
    {
        $uidArr = $this->fetchUidArrByColumn($column);
        
        if (in_array($uid, $uidArr)) {
            return true;
        }
        
        return false;
    }
}
