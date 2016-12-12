<?php

/**
 * WxClickEvent class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号点击事件处理抽象基类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\event;

use application\modules\message\core\wx\Event;

abstract class ClickEvent extends Event
{

    protected $key = '';

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

}
