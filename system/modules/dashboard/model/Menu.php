<?php

/**
 * menu表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  menu表的数据层操作
 * 
 * @package application.modules.dashboard.model
 * @version $Id: Menu.php 5159 2015-06-16 08:25:25Z tanghang $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class Menu extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{menu}}';
    }

    public function fetchByModule( $module ) {
        //assert( '!empty($module)' );
        $condition = "`m` = '{$module}' AND `pid` = 0 AND `disabled` = 0";
        return parent::fetch( $condition );
    }

    public function fetchAllRootMenu() {
        $condition = 'pid = 0 AND disabled = 0';
        $result = $this->fetchAll( $condition );
        return $result;
    }

}
