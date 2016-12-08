<?php
/**
 * 岗位工具类
 *
 * @namespace application\modules\contact\utils
 * @filename PositionUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/9 10:04
 */

namespace application\modules\contact\utils;


use application\core\utils\System;
use application\modules\position\model\Position;
use application\modules\position\model\PositionRelated;

/**
 * Class PositionUtil
 *
 * @package application\modules\contact\utils
 */
class PositionUtil extends System
{
    /**
     * @param string $className
     * @return PositionUtil
     */
    public static function getInstance($className = __CLASS__)
    {
        static $instance = null;

        if (empty($instance)) {
            $instance = parent::getInstance($className);
        }

        return $instance;
    }
    
    /**
     * 获取一个用户所有辅助岗位信息
     *
     * @param integer $uid 用户 uid
     * @return mixed
     */
    public function fetchAuxiliaryPosition($uid)
    {
        $positionRelatedArr = PositionRelated::model()->fetchAllPositionByUid($uid);

        $positionArr = array();
        foreach ($positionRelatedArr as $loopPositionRelated) {
            $loopPositionId = $loopPositionRelated['positionid'];
            $positionArr[] = Position::model()->fetchByPk($loopPositionId);
        }

        return $positionArr;
    }
}
