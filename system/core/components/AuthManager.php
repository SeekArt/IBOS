<?php

/**
 * 全局授权认证组件文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
/**
 * 全局授权认证组件管理类,继承自CDbAuthManager。
 *
 * @package application.core.components
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\components;

use CDbAuthManager;

class AuthManager extends CDbAuthManager
{

    /**
     * @var string 存储认证项目的表
     */
    public $itemTable = '{{auth_item}}';

    /**
     * @var string the name of the table storing authorization item hierarchy.
     */
    public $itemChildTable = '{{auth_item_child}}';

    /**
     * @var string the name of the table storing authorization item assignments.
     */
    public $assignmentTable = '{{auth_assignment}}';

}
