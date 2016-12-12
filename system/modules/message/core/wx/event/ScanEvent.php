<?php

/**
 * WxLocationEvent class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 *
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\event;

use application\modules\message\core\wx\Event;

abstract class ScanEvent extends Event
{

    /**
     * 扫描类型，一般是qrcode
     * @var string
     */
    protected $scanType = '';

    /**
     * 扫描结果，即二维码对应的字符串信息
     * @var string
     */
    protected $scanResult = '';

    /**
     * 事件KEY值，由开发者在创建菜单时设定
     * @var string
     */
    protected $eventKey = '';

    /**
     * 设置扫描类型
     * @param string $scanType
     */
    public function setScanType($scanType)
    {
        $this->scanType = $scanType;
    }

    /**
     * 获取扫描类型
     * @return string
     */
    public function getScanType()
    {
        return $this->scanType;
    }

    /**
     * 设置事件key
     * @param string $key
     */
    public function setEventKey($key)
    {
        $this->eventKey = $key;
    }

    /**
     * 获取事件key
     * @return string
     */
    public function getEventKey()
    {
        return $this->eventKey;
    }

    /**
     * 设置扫描结果
     * @param string $scanResult
     */
    public function setScanResult($scanResult)
    {
        $this->scanResult = $scanResult;
    }

    /**
     * 获取扫描结果
     * @return string
     */
    public function getScanResult()
    {
        return $this->scanResult;
    }

}
