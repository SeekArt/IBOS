<?php

/**
 * @namespace ${NAMESPACE}
 * @filename OnUpdateUser.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/18 15:03
 */

namespace application\modules\contact\behaviors;

use application\modules\contact\utils\DeptCacheUtil;
use application\modules\contact\utils\UserCacheUtil;

class UpdateContactCache extends \CBehavior
{
    /**
     * @param \CComponent $owner
     */
    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleReBuildCache'));

        $owner->attachEventHandler('onInitModule', array($this, 'handleInitCache'));
    }

    /**
     * 执行通讯录模块缓存的更新操作
     *
     * @param \CEvent $event
     * @return bool
     */
    public function handleReBuildCache(\CEvent $event)
    {
        DeptCacheUtil::getInstance()->rebuildCache();
        UserCacheUtil::getInstance()->rebuildCache();
    }

    /**
     * 执行通讯录模块缓存的初始化操作
     *
     * @param \CEvent $event
     * @return bool
     */
    public function handleInitCache(\CEvent $event)
    {
        DeptCacheUtil::getInstance()->buildCache();
        UserCacheUtil::getInstance()->buildCache();
    }

}