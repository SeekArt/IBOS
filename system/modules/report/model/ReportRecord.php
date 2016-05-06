<?php

/**
 * 工作总结与计划模块------report_record表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------report_record表操作类，继承ICModel
 * @package application.modules.report.model
 * @version $Id: ReportRecord.php 1951 2013-12-17 03:47:48Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\model;

use application\core\model\Model;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Calendars;
use application\modules\report\model\CalendarRepRecord;
use application\modules\report\model\Report;
use CHtml;

class ReportRecord extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{report_record}}';
    }

    /**
     * 通过总结报告id和计划类型获取原计划/计划外/下次计划
     * @param integer $repid 总结报告id
     * @param integer $planflag 计划类型(0原计划1计划外2下次计划)
     * @return array 返回计划二维数组
     */
    public function fetchRecordByRepidAndPlanflag( $repid, $planflag ) {
        $records = $this->fetchAll( array(
            'condition' => "repid = :repid AND planflag = :planflag",
            'params' => array( ':repid' => $repid, ':planflag' => $planflag ),
            'order' => 'recordid ASC'
                ) );
        return $records;
    }

    /**
     * 添加计划外、下次计划
     * @param array $plans 计划数组
     * @param integer $repid 计划所属总结报告id
     * @param intger $begindate 计划区间开始时间，时间戳
     * @param integer $enddate 计划区间结束时间，时间戳
     * @param integer $uid 用户uid
     * @param intger $type 计划类型 0为原计划，1为计划外, 2为下次计划
     */
    public function addPlans( $plans, $repid, $begindate, $enddate, $uid, $type, $exedetail = '' ) {
        foreach ( $plans as $plan ) {
            $remindDate = empty( $plan['reminddate'] ) ? 0 : strtotime( $plan['reminddate'] );
            $record = array(
                'repid' => $repid,
                'content' => CHtml::encode( $plan['content'] ),
                'uid' => $uid,
                'flag' => (isset( $plan['process'] ) && $plan['process'] == 10) ? 1 : 0,
                'planflag' => $type,
                'process' => isset( $plan['process'] ) ? $plan['process'] : 0,
                'exedetail' => CHtml::encode( $exedetail ),
                'begindate' => $begindate,
                'enddate' => $enddate,
                'reminddate' => $remindDate
            );
            $rid = $this->add( $record, true );
            //判断是否安装了日程模块，有的话判断有没提醒时间，有就写入日程
            $isInstallCalendar = Module::getIsEnabled( 'calendar' );
            if ( $isInstallCalendar && $remindDate ) {
                $calendar = array(
                    'subject' => $record['content'],
                    'starttime' => $remindDate,
                    'endtime' => $remindDate,
                    'uid' => $uid,
                    'upuid' => $uid,
                    'lock' => 1,
                    'category' => 4,
                    'isalldayevent' => 1
                );
                $cid = Calendars::model()->add( $calendar, true );
                //关联表
                CalendarRepRecord::model()->add( array( 'rid' => $rid, 'cid' => $cid, 'repid' => $repid ) );
            }
        }
    }

    /**
     * 取得原计划和计划外、下一次计划内容
     * @param array $report 参照计划
     * @return array 返回计划数组
     */
    public function fetchAllRecordByRep( $report ) {
        // 原计划要读取上一次总结的下次计划
        $lastRep = Report::model()->fetchLastRepByRepid( $report['repid'], $report['uid'], $report['typeid'] );
        $orgPlanList = array();
        if ( !empty( $lastRep ) ) {
            $orgPlanList = $this->fetchRecordByRepidAndPlanflag( $lastRep['repid'], 2 );
        }
        $outSidePlanList = $this->fetchRecordByRepidAndPlanflag( $report['repid'], 1 );
        $nextPlanList = $this->fetchRecordByRepidAndPlanflag( $report['repid'], 2 );
        $record = array(
            'orgPlanList' => $orgPlanList,
            'outSidePlanList' => $outSidePlanList,
            'nextPlanList' => $nextPlanList
        );
        return $record;
    }

}
