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
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\position\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;

class PositionCategory extends Model
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
        return '{{position_category}}';
    }

    public function afterDelete()
    {
        CacheUtil::update('PositionCategory');
        CacheUtil::load('PositionCategory');
        parent::afterDelete();
    }

    public function afterSave()
    {
        CacheUtil::update('PositionCategory');
        CacheUtil::load('PositionCategory');
        parent::afterSave();
    }

}
