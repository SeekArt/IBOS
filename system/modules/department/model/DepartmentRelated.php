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
use application\core\utils\Convert;

class DepartmentRelated extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{department_related}}';
    }

    /**
     * 根据uid查找赋值部门ID
     * @staticvar array $uids 用户数组缓存
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchAllDeptIdByUid( $uid ) {
        static $uids = array();
        if ( !isset( $uids[$uid] ) ) {
            $deptids = $this->fetchAll( array( 'select' => 'deptid', 'condition' => '`uid` = :uid', 'params' => array( ':uid' => $uid ) ) );
            $uids[$uid] = Convert::getSubByKey( $deptids, "deptid" );
        }
        return $uids[$uid];
    }

    /**
     * 
     * @param type $deptId
     * @return type
     */
    public function fetchAllUidByDeptId( $deptId ) {
        $criteria = array( 'select' => 'uid', 'condition' => "`deptid`={$deptId}" );
        $auxiliary = Convert::getSubByKey( $this->fetchAll( $criteria ), "uid" );
        return $auxiliary;
    }

}
