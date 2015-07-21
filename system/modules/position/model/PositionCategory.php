<?php

/**
 * 岗位分类表数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位分类表的数据层操作
 * 
 * @package application.modules.position.model
 * @version $Id: PositionCategory.php 4064 2014-09-03 09:13:16Z zhangrong $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\position\model;

use application\core\model\Model;
use application\core\utils\Cache;

class PositionCategory extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{position_category}}';
    }

    public function afterDelete() {
        Cache::update( 'PositionCategory' );
        parent::afterDelete();
    }

    public function afterSave() {
        Cache::update( 'PositionCategory' );
        parent::afterSave();
    }

}
