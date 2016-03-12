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
 * @version $Id: Cache.php 4255 2014-09-27 02:48:12Z zhangrong $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\String;

class Cache extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{cache}}';
    }

    /**
     * 
     * @param string $pk
     * @return array
     */
    public function fetchArrayByPk( $pk ) {
        $array = $this->fetchByPk( $pk );
        return String::utf8Unserialize( $array['cachevalue'] );
    }

}
