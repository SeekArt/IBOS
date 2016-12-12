<?php

/**
 * WxCalendarEvent class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号日程待办事件处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\event;

use application\core\utils\Ibos;
use application\modules\calendar\model\Calendars;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\Factory;
use application\modules\message\core\wx\WxApi;

class CalendarClick extends ClickEvent
{

    /**
     * 回调的处理方法
     * @return string
     */
    public function handle()
    {
        switch ($this->getKey()) {
            case 'mycalendar':
                $res = $this->resRecentCalendar();
                break;
            case 'mytasks':
                $factory = new Factory();
                $res = $factory->createHandle('callback\Calendar', array('corpId' => $this->corpId, 'appId' => $this->appId))->resRecentTasks();
                break;
            default:
                $res = $this->resText(Code::UNSUPPORTED_EVENT_TYPE);
                break;
        }
        return $res;
    }

    protected function resRecentCalendar()
    {
        $day = date('Y-m-d', TIMESTAMP);
        $st = strtotime($day . ' 00:00:00');
        $et = strtotime($day . ' 23:59:59');
        $lists = Calendars::model()->listCalendarByRange($st, $et, Ibos::app()->user->uid, 9);
        $items[0] = array(
            'title' => "今天的日程",
            'description' => '',
            'picurl' => 'http://app.ibos.cn/img/banner/calendar.png',
            'url' => ''
        );
        $hostinfo = WxApi::getInstance()->getHostInfo();
        foreach ($lists['events'] as $key => $row) {
            $key++;
            $picUrl = 'http://app.ibos.cn/img/banner/' . $key . '.png';
            $route = 'http://app.ibos.cn?host=' . urlencode($hostinfo) . '/#/calendar/' . $row['calendarid'];
            $item = array(
                'title' => sprintf('【%s - %s】%s', date('H:i', $row['starttime']), date('H:i', $row['endtime']), $row['subject']),
                'description' => '',
                'picurl' => $picUrl,
                'url' => WxApi::getInstance()->createOauthUrl($route, $this->appId)
            );
            $items[] = $item;
        }
        return $this->resNews($items);
    }

}
