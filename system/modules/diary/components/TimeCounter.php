<?php

/**
 * TimeCounter class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */

/**
 * 统计模块 - 日志 - 时间统计器基类
 * @package application.modules.diary.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\diary\components;

use application\core\utils\DateTime;
use application\modules\statistics\core\Counter;

class TimeCounter extends Counter
{

    /**
     * 统计的用户数组
     * @var array
     */
    private $_uids;

    /**
     * 统计的时间范围
     * @var array
     */
    private $_timeScope;

    /**
     * 获取统计器ID：此方法应由子类重写
     * @return boolean
     */
    public function getID()
    {
        return false;
    }

    /**
     * 获取统计器统计方法：此方法应由子类重写
     * @return boolean
     */
    public function getCount()
    {
        return false;
    }

    /**
     * 返回统计用户ID
     * @return array
     */
    public function getUid()
    {
        return $this->_uids;
    }

    /**
     * 设置统计用户ID
     * @param array $uid
     */
    public function setUid($uid)
    {
        $this->_uids = $uid;
    }

    /**
     * 设置统计时间范围
     * @param array $timeScope
     */
    public function setTimeScope($timeScope)
    {
        $this->_timeScope = $timeScope;
    }

    /**
     *  返回统计时间范围
     * @return array
     */
    public function getTimeScope()
    {
        return $this->_timeScope;
    }

    /**
     * 获取统计时间范围内的天数
     * @return integer
     */
    public function getDays()
    {
        $scope = $this->getTimeScope();
        return DateTime::getDays($scope['start'], $scope['end']);
    }

    /**
     * 获取统计时间范围内的日期，返回 值为年月日的一维数组格式
     * @staticvar array $return 静态日期缓存数组
     * @return array
     */
    public function getDateScope()
    {
        static $return = array();
        if (empty($return)) {
            $scope = $this->getTimeScope();
            $return = DateTime::getFormatDate($scope['start'], $scope['end'], 'Y-m-d');
        }
        return $return;
    }

}
