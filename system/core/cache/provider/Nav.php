<?php

/**
 * 全局导航更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 全局导航更新缓存类,处理导航设置存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: nav.php 858 2013-07-19 01:52:54Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Nav as NavModel;
use application\modules\dashboard\model\Syscache;
use CBehavior;

class Nav extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleNav'));
    }

    public function handleNav($event)
    {
        $navs = NavModel::model()->fetchAllByAllPid();
        Syscache::model()->modifyCache('nav', $navs);
    }

}
