<?php

/**
 * 岗位更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位更新缓存类,处理岗位数据存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: position.php 930 2013-08-05 00:57:26Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Syscache;
use application\modules\position\model\Position as PosModel;
use CBehavior;

class Position extends CBehavior {

    public function attach( $owner ) {
        $owner->attachEventHandler( 'onUpdateCache', array( $this, 'handlePosition' ) );
    }

    /**
     * 处理岗位数据缓存
     * @param object $event
     * @return void
     */
    public function handlePosition( $event ) {
        $records = PosModel::model()->fetchAllSortByPk( 'positionid' );
        Syscache::model()->modify( 'position', $records );
    }

}
