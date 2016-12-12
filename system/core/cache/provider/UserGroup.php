<?php

/**
 * 用户组更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 用户组更新缓存类,处理用户组信息存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: UserGroupCacheProvider.php 1619 2013-11-01 09:00:52Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Syscache;
use application\modules\user\model\UserGroup as UGModel;
use CBehavior;

class UserGroup extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleUserGroup'));
    }

    /**
     * 处理用户数据缓存
     * @param object $event
     * @return void
     */
    public function handleUserGroup($event)
    {
        $usergroup = array();
        $records = UGModel::model()->findAll(array('order' => 'creditslower ASC'));
        if (!empty($records)) {
            foreach ($records as $record) {
                $group = $record->attributes;
                $usergroup[$group['gid']] = $group;
            }
        }
        Syscache::model()->modifyCache('usergroup', $usergroup);
    }

}
