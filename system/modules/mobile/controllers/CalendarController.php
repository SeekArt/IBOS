<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\calendar\controllers\ScheduleController;
use application\modules\calendar\model\Calendars;
use application\modules\calendar\utils\Calendar as CalendarUtil;
use application\modules\message\model\Notify;
use application\modules\user\model\User;

class CalendarController extends ScheduleController
{
    /**
     * 显示日程
     */
    public function actionIndex()
    {
        //显示日期
        $st = Env::getRequest('startDate');
        $et = Env::getRequest('endDate');
        $st = $st ? $st : date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $et = $et ? $et : date('Y-m-d', mktime(0, 0, 0, date('m') + 1, 0, date('Y')));

        $ret = Calendars::model()->listCalendar(strtotime($st), strtotime($et), $this->uid);
        $this->ajaxReturn($ret);
    }

    /**
     * 显示日程详细
     */
    public function actionShow()
    {
        $id = Env::getRequest('id');
        $ret = Calendars::model()->fetchByPk($id);
        $ret['editable'] = ($ret['uid'] == Ibos::app()->user->uid) && ($ret['uid'] == $ret['upuid']);
        $this->ajaxReturn($ret);
    }

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     * @param string $routes
     * @return boolean
     */
    public function filterRoutes($routes)
    {
        return true;
    }
}
