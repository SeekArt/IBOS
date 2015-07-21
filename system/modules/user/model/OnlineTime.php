<?php

/**
 * user模块 在线时间model文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * user模块 在线时间model
 * 
 * @package application.app.user.model
 * @version $Id: OnlineTime.php 4064 2014-09-03 09:13:16Z zhangrong $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\user\model;

use application\core\model\Model;

class OnlineTime extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{onlinetime}}';
    }

    /**
     * 更新在线时间
     * 
     * @param integer $uid
     * @param integer $total 总在线时间
     * @param integer $thisMonth 当月在线时间
     * @param integer $lastUpdate 最后更新时间
     * @return boolean 更新成功与否
     */
    public function updateOnlineTime( $uid, $total, $thisMonth, $lastUpdate ) {
        $record = $this->findByPk( $uid );
        if ( is_null( $record ) ) {
            return false;
        }
        $record->total = $record->total + $total;
        $record->thismonth = $record->thismonth + $thisMonth;
        $record->lastupdate = $lastUpdate;
        $result = $record->save();
        return $result;
    }

    public function updateThisMonth() {
        return $this->updateAll( array( 'thismonth' => 0 ) );
    }

}
