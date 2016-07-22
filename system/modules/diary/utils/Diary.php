<?php

/**
 * 工作日志模块------ 工具类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 工作日志模块------  工具类
 * @package application.modules.diary.utils
 * @version $Id: Diary.php 7023 2016-05-10 08:01:05Z Aeolus $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\utils;

use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\diary\model\DiaryAttention;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class Diary {

    /**
     * 取得日期和星期几的数组
     * @param string $dateStr 
     * @return array
     */
    public static function getDateAndWeekDay($dateStr) {
        list($year, $month, $day) = explode('-', $dateStr);
        $weekArray = array(
            IBOS::lang('Day', 'date'),
            IBOS::lang('One', 'date'),
            IBOS::lang('Two', 'date'),
            IBOS::lang('Three', 'date'),
            IBOS::lang('Four', 'date'),
            IBOS::lang('Five', 'date'),
            IBOS::lang('Six', 'date')
        );
        $weekday = $weekArray[date("w", strtotime($dateStr))];
        return array('year' => $year,
            'month' => $month,
            'day' => $day,
            'weekday' => IBOS::lang('Weekday', 'date') . $weekday
        );
    }

    /**
     * 组合查询条件
     * @param array $search 查询数组
     * @return string
     */
    public static function joinSearchCondition($search) {
        $searchCondition = '';
        //对keyword添加转义
        $keyword = \CHtml::encode($search['keyword']);
        $starttime = $search['starttime'];
        $endtime = $search['endtime'];
        if (!empty($keyword)) {
            $searchCondition.=" content LIKE '%$keyword%' AND ";
        }
        if (!empty($starttime)) {
            $starttime = strtotime($starttime);
            $searchCondition.=" diarytime>=$starttime AND ";
        }
        if (!empty($endtime)) {
            $endtime = strtotime($endtime);
            $searchCondition.=" diarytime<=$endtime AND ";
        }
        $condition = !empty($searchCondition) ? substr($searchCondition, 0, -4) : '';
        return $condition;
    }

    /**
     * 连接条件语句
     * @param string $condition1 条件1
     * @param string $condition2 条件2
     * @return string
     */
    public static function joinCondition($condition1, $condition2) {
        if (empty($condition1)) {
            return $condition2;
        } else {
            return $condition1 . ' AND ' . $condition2;
        }
    }

    /**
     * 根据年月取得日历
     * @param string $ym 例：201307
     * @param array $diaryList 日志信息
     * @return string
     */
    public static function getCalendar($ym, $diaryList, $currentDay) {
        if ($ym) {
            $year = substr($ym, 0, 4);
            $month = substr($ym, 4, (strlen($ym) - 4));

            if ($month > 12) {
                $year += floor($month / 12);
                $month = $month % 12;
            }
            if ($year > 2030)
                $year = 2030;
            if ($year < 1980)
                $year = 1980;
        }

        $nowtime = mktime(0, 0, 0, $month, 1, $year); //当月１号转为秒 
        $daysofmonth = date('t', $nowtime); //当月天数 
        $weekofbeginday = date('w', $nowtime); //当月第一天是星期几 
        $weekofendday = date('w', mktime(0, 0, 0, $month + 1, 0, $year)); //当月最后一天是星期几 
        $daysofprevmonth = date('t', mktime(0, 0, 0, $month, 0, $year)); //上个月天数 

        $result = array();
        $count = 1; //计数
        //列出上月后几天 
        for ($i = 1; $i <= $weekofbeginday; $i++) {
            $result[] = array('day' => $daysofprevmonth - $weekofbeginday + $i, 'className' => 'old', 'diaryid' => '');
            $count++;
        }
        //当月全部 
        for ($i = 1; $i <= $daysofmonth; $i++) {
            $css = '';
            if ($i == $currentDay) {
                $css .= "current";
            } else if ($diaryList[$i]['isLog'] == true && $diaryList[$i]['isComment'] == false) {
                $css.='log';
            } else if ($diaryList[$i]['isLog'] == true && $diaryList[$i]['isComment'] == true) {
                $css.='log comment';
            }
            $result[] = array('day' => $i, 'className' => $css, 'diaryid' => $diaryList[$i]['diaryid']);
            $count++;
        }
        //下月前几天 
        for ($i = 1; $i <= 6 - $weekofendday; $i++) {
            $result[] = array('day' => $i, 'className' => 'new', 'diaryid' => '');
        }
        return $result;
    }

    /**
     * 判断工作日志是否有查看权限
     * @param integer $uid 查看该日志的uid
     * @param integer $author 该日志的作者
     * @return boolean $flag 通过或不通过
     */
    public static function checkShowPurview($uid, $author) {
        $flag = false;
        if ($uid == $author) {
            return true;
        }
        $subUidArr = UserUtil::getAllSubs($uid, '', true);
        if (StringUtil::findIn($author, implode(',', $subUidArr))) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * 去除数组中为空的值
     * @param array $arr  要处理的数组
     * @return array  处理过后的数组
     */
    public static function removeNullVal($arr) {
        $ret = array_filter($arr, create_function('$v', 'return !empty($v);'));
        return $ret;
    }

    // refactor 重构开始部分： by banyan
    /**
     * 
     * @return type
     */
    public static function getSetting() {
        return IBOS::app()->setting->get('setting/diaryconfig');
    }

    /**
     * 检测某个uid是否被自己关注
     * @param integer $attentionUid 被关注的uid
     * @return boolean
     */
    public static function getIsAttention($attentionUid) {
        $aUids = DiaryAttention::model()->fetchAuidByUid(IBOS::app()->user->uid);
        return in_array($attentionUid, $aUids);
    }

    /**
     * 根据图章id获取后台设置的分值
     * @param integer $stamp 图章id
     * @return int 返回分数
     */
    public static function getScoreByStamp($stamp) {
        $stamps = self::getEnableStamp();
        if (isset($stamps[$stamp])) {
            return $stamps[$stamp];
        } else {
            return 0;
        }
    }

    /**
     * 取得后台设置的所有图章
     * @return array
     */
    public static function getEnableStamp() {
        $config = self::getSetting();
        //取得所有图章
        $stampDetails = $config['stampdetails'];
        $stamps = array();
        if (!empty($stampDetails)) {
            $stampidArr = explode(',', trim($stampDetails));
            if (count($stampidArr) > 0) {
                foreach ($stampidArr as $stampidStr) {
                    list($stampId, $score) = explode(':', $stampidStr);
                    if ($stampId != 0) {
                        $stamps[$stampId] = intval($score);
                    }
                }
            }
        }
        return $stamps;
    }

    /**
     * 判断是否有下属
     * @return boolean
     */
    public static function checkIsHasSub() {
        static $hasSub = null;
        if ($hasSub === null) {
            $subUidArr = User::model()->fetchSubUidByUid(IBOS::app()->user->uid);
            if (!empty($subUidArr)) {
                $hasSub = true;
            } else {
                $hasSub = false;
            }
        }
        return $hasSub;
    }

    /**
     * 获取下班时间（统计用）
     * @return string 格式：（18.00）
     */
    public static function getOffTime() {
        if (Module::getIsEnabled("calendar")) {
            $workTime = explode(',', IBOS::app()->setting->get("setting/calendarworkingtime"));
            $offTime = $workTime[1];
            $ret = self::handleOffTime($offTime);
        } else {
            $ret = '18.00';
        }
        return $ret;
    }

    /**
     * 处理统计下班输出时间
     * @param string $stamp 日程设置的下班时间
     * @return string
     */
    public static function handleOffTime($offTime) {
        $times = array(
            '0' => '00.00',
            '0.5' => '00.30',
            '1' => '01.00',
            '1.5' => '01.30',
            '2' => '02.00',
            '2.5' => '02.30',
            '3' => '03.00',
            '3.5' => '03.30',
            '4' => '04.00',
            '4.5' => '04.30',
            '5' => '05.00',
            '5.5' => '05.30',
            '6' => '06.00',
            '6.5' => '06.30',
            '7' => '07.00',
            '7.5' => '07.30',
            '8' => '08.00',
            '8.5' => '08.30',
            '9' => '09.00',
            '9.5' => '09.30',
            '10' => '10.00',
            '10.5' => '10.30',
            '11' => '11.00',
            '11.5' => '11.30',
            '12' => '12.00',
            '12.5' => '12.30',
            '13' => '13.00',
            '13.5' => '13.30',
            '14' => '14.00',
            '14.5' => '14.30',
            '15' => '15.00',
            '15.5' => '15.30',
            '16' => '16.00',
            '16.5' => '16.30',
            '17' => '17.00',
            '17.5' => '17.30',
            '18' => '18.00',
            '18.5' => '18.30',
            '19' => '19.00',
            '19.5' => '19.30',
            '20' => '20.00',
            '20.5' => '20.30',
            '21' => '21.00',
            '21.5' => '21.30',
            '22' => '22.00',
            '22.5' => '22.30',
            '23' => '23.00',
            '23.5' => '23.30',
            '24' => '24.00'
        );
        if (isset($times[$offTime])) {
            return $times[$offTime];
        } else {
            return '18.00';
        }
    }

}
