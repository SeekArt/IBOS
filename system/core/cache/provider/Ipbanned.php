<?php

/**
 * IPBANNED更新缓存文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * IPBANNED更新缓存类,处理禁止ip缓存
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: ipbanned.php 1208 2013-09-11 09:12:31Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\IpBanned as IpBannedModel;
use application\modules\dashboard\model\Syscache;
use CBehavior;

class Ipbanned extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleIpbanned'));
    }

    /**
     * 处理更新ipbanned缓存
     * @param object $event
     * @return void
     */
    public function handleIpbanned($event)
    {
        IpBannedModel::model()->DeleteByExpiration(TIMESTAMP);
        $data = array();
        $bannedArr = IpBannedModel::model()->fetchAll();
        if (!empty($bannedArr)) {
            $data['expiration'] = 0;
            $data['regexp'] = $separator = '';
        }
        foreach ($bannedArr as $banned) {
            $data['expiration'] = !$data['expiration'] || $banned['expiration'] < $data['expiration'] ? $banned['expiration'] : $data['expiration'];
            $data['regexp'] .= $separator .
                ($banned['ip1'] == '-1' ? '\\d+\\.' : $banned['ip1'] . '\\.') .
                ($banned['ip2'] == '-1' ? '\\d+\\.' : $banned['ip2'] . '\\.') .
                ($banned['ip3'] == '-1' ? '\\d+\\.' : $banned['ip3'] . '\\.') .
                ($banned['ip4'] == '-1' ? '\\d+' : $banned['ip4']);
            $separator = '|';
        }
        Syscache::model()->modifyCache('ipbanned', $data);
    }

}
