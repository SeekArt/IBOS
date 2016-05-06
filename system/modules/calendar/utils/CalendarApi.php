<?php

namespace application\modules\calendar\utils;

use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Calendars;
use application\modules\calendar\utils\Calendar as CalendarUtil;

class CalendarApi {

    /**
     * 渲染首页视图
     * @return array
     */
    public function renderIndex() {
        $data = array(
            'schedules' => $this->loadNewSchedules(),
            'lant' => IBOS::getLangSource( 'calendar.default' ),
            'assetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'calendar' )
        );
        $viewAlias = 'application.modules.calendar.views.indexapi.schedule';
        $return['calendar/calendar'] = IBOS::app()->getController()->renderPartial( $viewAlias, $data, true );
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting() {
        return array(
            'name' => 'calendar/calendar',
            'title' => IBOS::lang( 'Calendar arrangement', 'calendar.default' ),
            'style' => 'in-calendar'
        );
    }

    /**
     * 获取最新日程 不作处理，返回0
     * @return integer
     */
    public function loadNew() {
        return intval( 0 );
    }

    /**
     * 读取最新的5条日程
     * @return array
     */
    private function loadNewSchedules() {
        $uid = IBOS::app()->user->uid;
        $st = time();
        $schedules = Calendars::model()->fetchNewSchedule( $uid, $st );
        if ( !empty( $schedules ) ) {
            foreach ( $schedules as $k => $schedule ) {
                $schedules[$k]['dateAndWeekDay'] = CalendarUtil::getDateAndWeekDay( date( 'Y-m-d', $schedule['starttime'] ) );
                $schedules[$k]['category'] = Calendars::model()->handleColor( $schedule['category'] );
                $schedules[$k]['cutSubject'] = StringUtil::cutStr( $schedule['subject'], 30 );
            }
        }
        return $schedules;
    }

}