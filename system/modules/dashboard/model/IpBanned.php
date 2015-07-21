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
 * @version $Id: IpBanned.php 4064 2014-09-03 09:13:16Z zhangrong $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class IpBanned extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{ipbanned}}';
    }

    public function fetchAllOrderDateline() {
        return parent::fetchAll( array( 'order' => 'dateline DESC' ) );
    }

    public function updateExpirationById( $id, $expiration, $admin ) {
        return $this->updateByPk( $id, array( 'expiration' => $expiration ), "admin = '{$admin}'" );
    }

    public function DeleteByExpiration( $expiration ) {
        return $this->deleteAll( 'expiration < :exp', array( ':exp' => $expiration ) );
    }

}
