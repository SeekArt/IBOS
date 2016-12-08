<?php

/**
 * WxEmailView class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号邮件模块查看事件处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\event;

use application\modules\message\core\wx\Event;

class EmailView extends Event
{

    /**
     * 回调的处理方法
     * @return string
     */
    public function handle()
    {
        return $this->resText('');
    }

}
