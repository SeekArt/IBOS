<?php

/**
 * 消息模块配置文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 消息模块配置文件类
 * @package application.modules.message
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\message;

use application\core\modules\Module;

class MessageModule extends Module
{

    protected function preinit()
    {
        parent::filterOpen();
    }
}
