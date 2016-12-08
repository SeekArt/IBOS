<?php

/**
 * message模块的默认控制器
 *
 * @version $Id$
 * @package application.modules.main.controllers
 */

namespace application\modules\message\controllers;

use application\core\controllers\Controller;
use application\core\utils\Ibos;
use application\modules\message\model\UserData;

class BaseController extends Controller
{

    /**
     * 通用获取sidebar函数
     * @param array $data 视图赋值
     * @return string 视图html
     */
    public function getSidebar($data = array())
    {
        $data['unreadMap'] = $this->getUnreadCount();
        $sidebarAlias = 'application.modules.message.views.sidebar';
        $sidebarView = $this->renderPartial($sidebarAlias, $data, true);
        return $sidebarView;
    }

    /**
     * 获取sidebar栏目的气泡提示
     * @return array
     */
    private function getUnreadCount()
    {
        $unreadCount = UserData::model()->getUnreadCount(Ibos::app()->user->uid);
        $sidebarUnreadMap['mention'] = $unreadCount['unread_atme'];
        $sidebarUnreadMap['comment'] = $unreadCount['unread_comment'];
        $sidebarUnreadMap['notify'] = $unreadCount['unread_notify'];
        $sidebarUnreadMap['pm'] = $unreadCount['unread_message'];
        return $sidebarUnreadMap;
    }

}
