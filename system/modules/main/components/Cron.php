<?php

/**
 * 主模块计划任务处理组件
 * @package application.modules.main.components
 * @see application.modules.main.behaviors.onInitModuleBehavior
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: Cron.php 6001 2015-12-21 07:18:15Z tanghang $
 */

namespace application\modules\main\components;

use application\core\utils\IBOS;
use application\modules\dashboard\model\Syscache;
use application\modules\main\model\Cron as CronModel;
use CApplicationComponent;

class Cron extends CApplicationComponent {

    /**
     * 开始计划任务处理
     * @param interger $cronId 计划任务id
     * @return boolean 执行成功与否
     */
    public function run( $cronId = 0 ) {
        if ( $cronId ) {
            $cron = CronModel::model()->fetchByPk( $cronId );
        } else {
            // 如果没有指定进程id,则查询下一次应该执行的进程
            $cron = CronModel::model()->fetchByNextRun( TIMESTAMP );
        }
        // 进程名字
        $processName = 'MAIN_CRON_' . (empty( $cron ) ? 'CHECKER' : $cron['cronid']);
        // 如果指定了进程id,先却解锁系统进程
        if ( $cronId && !empty( $cron ) ) {
            IBOS::app()->process->unLock( $processName );
        }
        // 检查当前进程是否存在,存在即退出处理
        if ( IBOS::app()->process->isLocked( $processName, 600 ) ) {
            return false;
        }
        // 处理当前任务
        if ( $cron ) {
            $cron['filename'] = str_replace( array( '..', '/', '\\' ), '', $cron['filename'] );
            $cron['minute'] = explode( "\t", $cron['minute'] );
            $this->setNextTime( $cron );
            @set_time_limit( 1000 );
            @ignore_user_abort( true );
            $cronFile = $this->getRealCronFile( $cron['type'], $cron['filename'], $cron['module'] );
            if ( !@include $cronFile ) {
                return false;
            }
        }
        // 插入下一次任务记录
        $this->nextCron();
        // 解锁当前进程
        IBOS::app()->process->unLock( $processName );
        return true;
    }

    /**
     * 插入下一次任务记录
     * @return boolean
     */
    private function nextCron() {
        $cron = CronModel::model()->fetchByNextCron();
        if ( $cron && isset( $cron['nextrun'] ) ) {
            $data = $cron['nextrun'];
        } else {
            $data = TIMESTAMP + 86400 * 365;
        }
        Syscache::model()->modifyCache( 'cronnextrun', $data );
        return true;
    }

    /**
     * 设置指定定时任务下一次运行的时间
     * @param array $cron
     * @return boolean
     */
    private function setNextTime( $cron ) {
        if ( empty( $cron ) ) {
            return false;
        }
        $timeoffSet = IBOS::app()->setting->get( 'setting/timeoffset' );
        list($yearNow, $monthNow, $dayNow, $weekdayNow, $hourNow, $minuteNow) = explode( '-', gmdate( 'Y-m-d-w-H-i', TIMESTAMP + $timeoffSet * 3600 ) );

        if ( $cron['weekday'] == -1 ) {
            if ( $cron['day'] == -1 ) {
                $firstDay = $dayNow;
                $secondDay = $dayNow + 1;
            } else {
                $firstDay = $cron['day'];
                $secondDay = $cron['day'] + gmdate( 't', TIMESTAMP + $timeoffSet * 3600 );
            }
        } else {
            $firstDay = $dayNow + ($cron['weekday'] - $weekdayNow);
            $secondDay = $firstDay + 7;
        }

        if ( $firstDay < $dayNow ) {
            $firstDay = $secondDay;
        }

        if ( $firstDay == $dayNow ) {
            $todayTime = $this->todayNextRun( $cron );
            if ( $todayTime['hour'] == -1 && $todayTime['minute'] == -1 ) {
                $cron['day'] = $secondDay;
                $nextTime = $this->todayNextRun( $cron, 0, -1 );
                $cron['hour'] = $nextTime['hour'];
                $cron['minute'] = $nextTime['minute'];
            } else {
                $cron['day'] = $firstDay;
                $cron['hour'] = $todayTime['hour'];
                $cron['minute'] = $todayTime['minute'];
            }
        } else {
            $cron['day'] = $firstDay;
            $nextTime = $this->todayNextRun( $cron, 0, -1 );
            $cron['hour'] = $nextTime['hour'];
            $cron['minute'] = $nextTime['minute'];
        }

        $nextRun = @gmmktime( $cron['hour'], $cron['minute'] > 0 ? $cron['minute'] : 0, 0, $monthNow, $cron['day'], $yearNow ) - $timeoffSet * 3600;
        $data = array( 'lastrun' => TIMESTAMP, 'nextrun' => $nextRun );
        if ( !($nextRun > TIMESTAMP) ) {
            $data['available'] = '0';
        }
        CronModel::model()->modify( $cron['cronid'], $data );
        return true;
    }

    /**
     *
     * @param type $cron
     * @param type $hour
     * @param type $minute
     * @return type
     */
    private function todayNextRun( $cron, $hour = -2, $minute = -2 ) {
        $timeoffSet = IBOS::app()->setting->get( 'setting/timeoffset' );
        $hour = $hour == -2 ? gmdate( 'H', TIMESTAMP + $timeoffSet * 3600 ) : $hour;
        $minute = $minute == -2 ? gmdate( 'i', TIMESTAMP + $timeoffSet * 3600 ) : $minute;

        $nextTime = array();
        if ( $cron['hour'] == -1 && !$cron['minute'] ) {
            $nextTime['hour'] = $hour;
            $nextTime['minute'] = $minute + 1;
        } elseif ( $cron['hour'] == -1 && $cron['minute'] != '' ) {
            $nextTime['hour'] = $hour;
            if ( ($nextMinute = $this->nextMinute( $cron['minute'], $minute )) === false ) {
                ++$nextTime['hour'];
                $nextMinute = $cron['minute'][0];
            }
            $nextTime['minute'] = $nextMinute;
        } elseif ( $cron['hour'] != -1 && $cron['minute'] == '' ) {
            if ( $cron['hour'] < $hour ) {
                $nextTime['hour'] = $nextTime['minute'] = -1;
            } elseif ( $cron['hour'] == $hour ) {
                $nextTime['hour'] = $cron['hour'];
                $nextTime['minute'] = $minute + 1;
            } else {
                $nextTime['hour'] = $cron['hour'];
                $nextTime['minute'] = 0;
            }
        } elseif ( $cron['hour'] != -1 && $cron['minute'] != '' ) {
            $nextMinute = $this->nextMinute( $cron['minute'], $minute );
            if ( $cron['hour'] < $hour || ($cron['hour'] == $hour && $nextMinute === false) ) {
                $nextTime['hour'] = -1;
                $nextTime['minute'] = -1;
            } else {
                $nextTime['hour'] = $cron['hour'];
                $nextTime['minute'] = $nextMinute;
            }
        }

        return $nextTime;
    }

    /**
     * 多个分钟区间时，获取下一个分钟
     * @param array $nextMinutes
     * @param integer $minuteNow
     * @return boolean
     */
    private function nextMinute( $nextMinutes, $minuteNow ) {
        foreach ( $nextMinutes as $nextMinute ) {
            if ( $nextMinute > $minuteNow ) {
                return $nextMinute;
            }
        }
        return false;
    }

    /**
     * 获取不同类型的定时任务执行脚本
     * @param string $type 定时任务类型
     * @param string $fileName 文件名
     * @param string $module 该定时任务所属模块
     * @return string
     */
    private function getRealCronFile( $type, $fileName, $module = '' ) {
        if ( $type == 'user' ) {
            $cronFile = './system/extensions/cron/' . $fileName;
        } else {
            $cronFile = sprintf( './system/modules/%s/cron/%s', $module, $fileName );
        }
        return $cronFile;
    }

}
