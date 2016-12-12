<?php

/**
 * 角色更新缓存类文件
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 角色更新缓存类,处理角色数据存入系统缓存表
 * @version $Id: Role.php 930 2014-11-26 00:57:26Z gzhzh $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Syscache;
use application\modules\role\model\Role as RoleModel;
use CBehavior;

class Role extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleRole'));
    }

    /**
     * 处理角色数据缓存
     * @param object $event
     * @return void
     */
    public function handleRole($event)
    {
        $records = RoleModel::model()->fetchAllSortByPk('roleid');
        Syscache::model()->modifyCache('role', $records);
    }

}
