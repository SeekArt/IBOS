<?php

/**
 * CreditRule表的数据层操作文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  CreditRule表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;

class CreditRule extends Model
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
        return '{{credit_rule}}';
    }

    public function afterSave()
    {
        CacheUtil::update('CreditRule');
        CacheUtil::load('CreditRule');
        parent::afterSave();
    }

}
