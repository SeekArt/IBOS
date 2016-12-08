<?php

/**
 * 更新消息节点缓存文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 更新消息节点
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: notifyNode.php 2041 2013-12-28 01:23:17Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\core\utils\Cache;
use application\modules\dashboard\model\Syscache;
use application\modules\message\model\Notify;
use CBehavior;

class NotifyNode extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleNode'));
    }

    /**
     *
     * @param type $event
     */
    public function handleNode($event)
    {
        Cache::set('NotifyNode', null);
        $notifyNode = Notify::model()->fetchAllSortByPk('node', array('order' => '`module` DESC'));
        Syscache::model()->modifyCache('notifyNode', $notifyNode);
    }

}
