<?php

/**
 * ipbanned表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  ipbanned表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;

class IpBanned extends Model
{

    public function init()
    {
        $this->cacheLife = 0;
        parent::init();
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{ipbanned}}';
    }

    public function afterSave()
    {
        CacheUtil::update('Ipbanned');
        CacheUtil::load('Ipbanned');
        parent::afterSave();
    }

    public function fetchAllOrderDateline()
    {
        return parent::fetchAll(array('order' => 'dateline DESC'));
    }

    public function updateExpirationById($id, $expiration, $admin)
    {
        return $this->updateByPk($id, array('expiration' => $expiration), "admin = '{$admin}'");
    }

    public function DeleteByExpiration($expiration)
    {
        return $this->deleteAll('expiration < :exp', array(':exp' => $expiration));
    }

}
