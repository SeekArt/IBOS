<?php

/**
 * 工作日志模块------diary_record表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 工作日志模块------diary_record表操作类，继承ICModel
 * @package application.modules.diary.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\calendar\model\Calendars;

class DiaryRecord extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{diary_record}}';
    }

    /**
     * 根据计划时间取出当天计划任务
     * @param integer $plantime
     */
    public function fetchAllByPlantime($plantime, $uid = 0)
    {
        $uid = empty($uid) ? Ibos::app()->user->uid : $uid;
        $records = DiaryRecord::model()->fetchAll(array(
            'condition' => 'plantime=:plantime AND uid=:uid',
            'order' => 'recordid ASC',
            'params' => array(':plantime' => $plantime, ':uid' => $uid)
        ));
        return $records;
    }

    /**
     * 保存计划外或新的的计划
     * @param array $plan 数据
     * @param $diaryId 工作日志Id
     * @param $planTime 工作日志计划时间
     * @param integer $uid 用户Id
     * @param string $type 值为outside,new
     */
    public function addRecord($plan, $diaryId, $planTime, $uid, $type)
    {
        foreach ($plan as $value) {
            $diaryRecord = array(
                'diaryid' => $diaryId,
                'content' => htmlspecialchars($value['content']),
                'planflag' => $type == 'outside' ? 0 : 1,
                'schedule' => isset($value['schedule']) ? $value['schedule'] : 0,
                'plantime' => $planTime,
                'flag' => (isset($value['schedule']) && $value['schedule'] == 10) ? 1 : 0,
                'uid' => $uid,
                'timeremind' => isset($value['timeremind']) ? $value['timeremind'] : ''
            );
            $rid = $this->add($diaryRecord, true);
            //判断是否安装了日程模块，有的话判断有没提醒时间，有就写入日程
            $isInstallCalendar = Module::getIsEnabled('calendar');
            if ($isInstallCalendar && isset($value['timeremind']) && !empty($value['timeremind'])) {
                $timeArr = explode(',', $value['timeremind']);
                $st = $planTime + ($timeArr[0] * 60 * 60); // 日程开始时间戳，也是计划开始时间戳
                $et = $planTime + ($timeArr[1] * 60 * 60); // 日程结束时间戳，也是计划结束时间戳
                $calendar = array(
                    'subject' => $diaryRecord['content'],
                    'starttime' => $st,
                    'endtime' => $et,
                    'uid' => $uid,
                    'upuid' => $uid,
                    'lock' => 1,
                    'category' => 3,
                    'isfromdiary' => 1
                );
                $cid = Calendars::model()->add($calendar, true);
                //关联表
                CalendarRecord::model()->add(array('rid' => $rid, 'cid' => $cid, 'did' => $diaryId));
            }
        }
    }

    /**
     * 根据计划id获取内容
     * @param integer $recordId 计划id
     * @return string
     */
    public function fetchContentByRecordId($recordId)
    {
        $record = $this->fetch(sprintf("recordid=%d", intval($recordId)));
        if (!empty($record)) {
            return $record['content'];
        } else {
            return '';
        }
    }

}
