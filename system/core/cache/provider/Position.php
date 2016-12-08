<?php

/**
 * 岗位更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位更新缓存类,处理岗位数据存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: position.php 930 2013-08-05 00:57:26Z zhangrong $
 * @package application.core.cache.provider
 */

namespace application\core\cache\provider;

use application\core\utils\ArrayUtil;
use application\modules\dashboard\model\Syscache;
use application\modules\position\model\Position as PositionModel;
use application\modules\position\model\Position as PosModel;
use application\modules\position\model\PositionRelated;
use CBehavior;

class Position extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handlePosition'));
    }

    /**
     * 处理岗位数据缓存
     * @param object $event
     * @return void
     */
    public function handlePosition($event)
    {
        static $flag = null;
        if (null === $flag) {
            // 更新对应岗位的岗位数目
            $position = PositionRelated::model()->fetchUidListIndexByPositionid();
    
            $positionIds = array_keys($position);
            foreach ($position as $positionId => $uidList) {
                PositionModel::model()->updatePositionNum($positionId, count($uidList));
            }

            // 将用户的岗位数目设置为零
            $allPosition = PositionModel::model()->findAll();
            $allPositionIds = ArrayUtil::getColumn($allPosition, 'positionid');
            $emptyPositionIds = array_diff($allPositionIds, $positionIds);
            foreach ($emptyPositionIds as $positionId) {
                PositionModel::model()->updatePositionNum($positionId, 0);
            }
            $flag = 1;

        }

        $records = PosModel::model()->fetchAllSortByPk('positionid');
        Syscache::model()->modifyCache('position', $records);
    }


}
