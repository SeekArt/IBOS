<?php

/**
 * WxEvent class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号事件处理抽象基类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx;

use application\modules\message\core\wx\Callback;

abstract class Event extends Callback
{

    protected $event = '';

    public function setEventType($eventType)
    {
        $this->event = $eventType;
    }

    public function getEventType()
    {
        return $this->event;
    }

}
