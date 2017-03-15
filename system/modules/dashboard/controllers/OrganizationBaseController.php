<?php

/**
 * 组织架构模块基本控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块基本控制器类
 *
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: BaseController.php 4064 2014-09-03 09:13:16Z zhangrong $
 */

namespace application\modules\dashboard\controllers;

class OrganizationbaseController extends BaseController
{

    // 用户管理权限
    const NO_PERMISSION = 0; // 无权限
    const ONLY_SELF = 1; //本人
    const CONTAIN_SUB = 2; //本人及下属
    const SELF_BRANCH = 4; // 当前分支机构
    const All_PERMISSION = 8; // 全部

    // 成员列表个数
    const MEMBER_LIMIT = 12;

}
