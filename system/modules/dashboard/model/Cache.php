<?php

/**
 * cache表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  cache表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\StringUtil;

class Cache extends Model
{
    
    /**
     * @param string $className
     * @return Cache
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{cache}}';
    }

    /**
     *
     * @param string $pk
     * @return array
     */
    public function fetchArrayByPk($pk)
    {
        $array = $this->fetchByPk($pk);
        return StringUtil::utf8Unserialize($array['cachevalue']);
    }

}
