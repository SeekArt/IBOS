<?php

/**
 * 用户更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 用户更新缓存类,处理用户信息存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: users.php 2350 2014-02-13 02:03:59Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Syscache;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CBehavior;

class Users extends CBehavior {

    public function attach( $owner ) {
        $owner->attachEventHandler( 'onUpdateCache', array( $this, 'handleUsers' ) );
    }

    /**
     * 处理用户数据缓存
     * @param object $event
     * @return void
     */
    public function handleUsers( $event ) {
        $users = array();
        $records = User::model()->fetchAll( array( 'condition' => 'status IN (0,1)' ) );
        if ( !empty( $records ) ) {
            foreach ( $records as $record ) {
                $users[$record['uid']] = UserUtil::wrapUserInfo( $record );
            }
        }
        Syscache::model()->modifyCache( 'users', $users );
    }

}
