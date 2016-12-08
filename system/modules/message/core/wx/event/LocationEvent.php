<?php

/**
 * WxLocationEvent class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号地理位置处理事件上报处理类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\event;

use application\core\utils\Ibos;
use application\modules\message\core\wx\Event;

class LocationEvent extends Event
{

    /**
     * 纬度
     * @var string
     */
    protected $latitude = '';

    /**
     * 经度
     * @var string
     */
    protected $longitude = '';

    /**
     * 精度
     * @var string
     */
    protected $precision = '';

    /**
     * 设置纬度
     * @param string $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * 获取纬度
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * 设置经度
     * @param string $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * 获取精度
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * 设置精度
     * @param string $precision
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    /**
     * 获取精度
     * @return type
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * 插入记录
     */
    public function handle()
    {
        $var = array(
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'precision' => $this->getPrecision(),
            'appid' => $this->getAppId(),
            'uid' => Ibos::app()->user->uid,
            'time' => TIMESTAMP,
        );
        Ibos::app()->db->createCommand()->insert('{{user_location}}', $var);
        $this->resText();
    }

}
