<?php

/**
 * 部门关联表数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位关联表的数据层操作
 *
 * @package application.modules.department.model
 * @version $$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\department\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\modules\user\model\User;

class DepartmentRelated extends Model
{

    /**
     * @param string $className
     * @return DepartmentRelated
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{department_related}}';
    }

    /**
     * 根据uid查找辅助部门ID
     * @staticvar array $uids 用户数组缓存
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchAllDeptIdByUid($uid)
    {
        return User::model()->findAllDeptidByUid($uid, true);
    }

    /**
     *
     * @param type $deptId
     * @return type
     */
    public function fetchAllUidByDeptId($deptId)
    {
        return User::model()->fetchAllUidByDeptids($deptId, true, true);
    }

    public function findDeptidIndexByUidX($uidX = null)
    {
        if (null === $uidX) {
            $condition = 1;
        } else if (empty($uidX)) {
            return array();
        } else {
            $condition = User::model()->uid_find_in_set($uidX);
        }
        $related = Ibos::app()->db->createCommand()
            ->select('uid,deptid')
            ->from($this->tableName())
            ->where($condition)
            ->queryAll();
        $return = array();
        if (!empty($related)) {
            foreach ($related as $row) {
                $return[$row['uid']][] = $row['deptid'];
            }
        }
        return $return;
    }

}
