<?php

/**
 * 岗位分类更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位分类更新缓存类,处理岗位分类存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: positionCategory.php 930 2013-08-05 00:57:26Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Syscache;
use application\modules\position\model\PositionCategory as PCModel;
use CBehavior;

class PositionCategory extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handlePositionCategory'));
    }

    /**
     * 处理部门数据缓存
     * @param object $event
     * @return void
     */
    public function handlePositionCategory($event)
    {
        $categorys = array();
        $records = PCModel::model()->findAll(array('order' => 'sort ASC'));
        if (!empty($records)) {
            foreach ($records as $record) {
                $cat = $record->attributes;
                $categorys[$cat['catid']] = $cat;
            }
        }
        Syscache::model()->modifyCache('positioncategory', $categorys);
    }

}
