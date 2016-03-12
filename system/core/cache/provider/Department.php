<?php

/**
 * 部门更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
/**
 * 部门更新缓存类,处理部门信息存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: department.php 949 2013-08-07 01:05:15Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Syscache;
use application\modules\department\model\Department as DeptModel;
use CBehavior;

class Department extends CBehavior {

    public function attach( $owner ) {
        $owner->attachEventHandler( 'onUpdateCache', array( $this, 'handleDepartment' ) );
    }

    /**
     * 处理部门数据缓存
     * @param object $event
     * @return void
     */
    public function handleDepartment( $event ) {
        $departments = array();
        $records = DeptModel::model()->findAll( array( 'order' => 'sort ASC' ) );
        if ( !empty( $records ) ) {
            foreach ( $records as $record ) {
                $dept = $record->attributes;
                $departments[$dept['deptid']] = $dept;
            }
        }
        Syscache::model()->modifyCache( 'department', $departments );
    }

}
