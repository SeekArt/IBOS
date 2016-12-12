<?php

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Calendars;
use application\modules\main\model\Cron;
use application\modules\message\model\Notify;

$row = Cron::model()->fetch(array(
    'select' => '`lastrun`,`nextrun`',
    'condition' => 'filename = :filename',
    'params' => array(':filename' => basename(__FILE__))
));
//日程提醒应该是当前运行时间到下一次运行的间隔之间的日程
//只检查三天之内的跨天日程，因为数量太多影响性能，解决方法是另写日程函数，时间不够用，日后再优化
$clist = Calendars::model()->listCalendarByRange($row['lastrun'], $row['nextrun'] + 86400 * 3);
foreach ($clist['events'] as $calendar) {
    if ($calendar['isalldayevent'] == 1) {  //全天日程 
        //提醒条件：当前时间在开始日期的08点到10点之间才会提醒
        $start_date = date("Y-m-d", $calendar['starttime']);

        $remind_date_min = $start_date . " 07:59:00";
        $remind_date_max = $start_date . " 10:01:00";
        $remind_time_min = strtotime($remind_date_min);
        $remind_time_max = strtotime($remind_date_max);

        if (time() > $remind_time_min && time() < $remind_time_max) {
            $stime = date('m-d', $calendar['starttime']);
            $title = $stime . '全天日程';
            $subject = StringUtil::cutStr($calendar['subject'], 20);
            $config = array(
                '{subject}' => $subject,
                '{url}' => Ibos::app()->urlManager->createUrl('calendar/schedule/index')
            );
            Notify::model()->sendNotify($calendar['uid'], 'calendar_message', $config);
        }
    } else {
        $diff = $calendar['starttime'] - time();
        if ($diff >= 0 && $diff <= 30 * 60) {
            $stime = date('m-d H:i', $calendar['starttime']);
            $etime = date('m-d H:i', $calendar['endtime']);
            $title = $stime . ' 至 ' . $etime . '日程';
            $subject = StringUtil::cutStr($calendar['subject'], 20);
            $config = array(
                '{subject}' => $subject,
                '{url}' => Ibos::app()->urlManager->createUrl('calendar/schedule/index')
            );
            Notify::model()->sendNotify($calendar['uid'], 'calendar_message', $config);
        }
    }
}