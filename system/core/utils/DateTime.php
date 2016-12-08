<?php

namespace application\core\utils;

class DateTime
{

    private static $_SMDay = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); //定义公历月分天数
    private static $_LStart = 1950; //农历从1950年开始
    private static $_LMDay = array(
        //差：该年的农历正月初一到该年公历1月1日的天数；1~12：农历月份天数；闰：如有闰月，记录该月平月天数
        //    差  1  2  3  4  5  6  7  8  9 10 11 12 闰
        array(47, 29, 30, 30, 29, 30, 30, 29, 29, 30, 29, 30, 29),
        array(36, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30),
        array(6, 29, 30, 29, 30, 59, 29, 30, 30, 29, 30, 29, 30, 29), //五月29 闰五月30
        array(44, 29, 30, 29, 29, 30, 30, 29, 30, 30, 29, 30, 29),
        array(33, 30, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30),
        array(23, 29, 30, 59, 29, 29, 30, 29, 30, 29, 30, 30, 30, 29), //三月29 闰三月30
        array(42, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30),
        array(30, 30, 29, 30, 29, 30, 29, 29, 59, 30, 29, 30, 29, 30), //八月30 闰八月29
        array(48, 30, 30, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30),
        array(38, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29),
        array(27, 30, 29, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 30), //六月30 闰六月29
        array(45, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30),
        array(35, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(24, 30, 29, 30, 58, 30, 29, 30, 29, 30, 30, 30, 29, 29), //四月29 闰四月29
        array(43, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 30),
        array(32, 29, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29),
        array(20, 30, 30, 59, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30), //三月30 闰三月29
        array(39, 30, 30, 29, 30, 30, 29, 29, 30, 29, 30, 29, 30),
        array(29, 29, 30, 29, 30, 30, 29, 59, 30, 29, 30, 29, 30, 30), //七月30 闰七月29
        array(47, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29),
        array(36, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30),
        array(26, 29, 30, 29, 29, 59, 30, 29, 30, 30, 30, 29, 30, 30), //五月30 闰五月29
        array(45, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30),
        array(33, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30),
        array(22, 30, 30, 29, 59, 29, 30, 29, 29, 30, 30, 29, 30, 30), //四月30 闰四月29
        array(41, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30),
        array(30, 30, 30, 29, 30, 29, 30, 29, 59, 29, 30, 29, 30, 30), //八月30 闰八月29
        array(48, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29),
        array(37, 30, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 29),
        array(27, 30, 29, 29, 30, 29, 60, 29, 30, 30, 29, 30, 29, 30), //六月30 闰六月30
        array(46, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30),
        array(35, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30, 30),
        array(24, 30, 29, 30, 58, 30, 29, 29, 30, 29, 30, 30, 30, 29), //四月29 闰四月29
        array(43, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30),
        array(32, 30, 29, 30, 30, 29, 29, 30, 29, 29, 59, 30, 30, 30), //十月30 闰十月29
        array(50, 29, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30),
        array(39, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 29, 29),
        array(28, 30, 29, 30, 29, 30, 59, 30, 30, 29, 30, 29, 29, 30), //六月30 闰六月29
        array(47, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(36, 30, 29, 29, 30, 29, 30, 29, 30, 29, 30, 30, 30),
        array(26, 29, 30, 29, 29, 59, 29, 30, 29, 30, 30, 30, 30, 30), //五月30 闰五月29
        array(45, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30),
        array(34, 29, 30, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30),
        array(22, 29, 30, 59, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30), //三月30 闰三月29
        array(40, 30, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30),
        array(30, 29, 30, 30, 29, 30, 29, 30, 59, 29, 30, 29, 30, 30), //八月30 闰八月29
        array(49, 29, 30, 29, 30, 30, 29, 30, 29, 30, 30, 29, 29),
        array(37, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(27, 30, 29, 29, 30, 58, 30, 30, 29, 30, 30, 29, 30, 29), //五月29 闰五月29
        array(46, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29),
        array(35, 30, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 29),
        array(23, 30, 30, 29, 59, 30, 29, 29, 30, 29, 30, 29, 30, 30), //四月30 闰四月29
        array(42, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29),
        array(31, 30, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29, 30),
        array(21, 29, 59, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 30), //二月30 闰二月29
        array(39, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29),
        array(28, 30, 29, 30, 29, 30, 29, 59, 30, 30, 29, 30, 30, 30), //七月30 闰七月29
        array(48, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29, 30),
        array(37, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30),
        array(25, 30, 30, 29, 29, 59, 29, 30, 29, 30, 29, 30, 30, 30), //五月30 闰五月29
        array(44, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30),
        array(33, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29, 30, 29),
        array(22, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 30, 29, 30), //四月30 闰四月29
        array(40, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30),
        array(30, 29, 30, 29, 30, 29, 30, 29, 30, 59, 30, 29, 30, 30), //九月30 闰九月29
        array(49, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29, 30, 29),
        array(38, 30, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30),
        array(27, 29, 30, 29, 30, 29, 59, 29, 30, 29, 30, 30, 30, 29), //六月29 闰六月30
        array(46, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30),
        array(35, 30, 29, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30),
        array(24, 29, 30, 30, 59, 30, 29, 29, 30, 29, 30, 29, 30, 30), //四月30 闰四月29
        array(42, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29),
        array(31, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30),
        array(21, 29, 59, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 30), //二月30 闰二月29
        array(40, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(28, 30, 29, 30, 29, 29, 59, 30, 29, 30, 30, 30, 29, 30), //六月30 闰六月29
        array(47, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 30, 29),
        array(36, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29),
        array(25, 30, 30, 30, 29, 59, 29, 30, 29, 29, 30, 30, 29, 30), //五月30 闰五月29
        array(43, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 30),
        array(33, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29),
        array(22, 29, 30, 59, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30), //三月30 闰三月29
        array(41, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30),
        array(30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 59, 30, 30), //十一月30 闰十一月29
        array(49, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30),
        array(38, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30),
        array(27, 30, 30, 29, 30, 29, 59, 29, 29, 30, 29, 30, 30, 29), //六月29 闰六月30
        array(45, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30),
        array(34, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 29),
        array(23, 30, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 29, 30), //五月30 闰五月29
        array(42, 30, 29, 30, 30, 29, 30, 29, 30, 30, 29, 30, 29),
        array(31, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30),
        array(21, 29, 59, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 30), //二月30 闰二月29
        array(40, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30, 30),
        array(29, 30, 29, 30, 29, 29, 30, 58, 30, 29, 30, 30, 30, 29), //七月29 闰七月29
        array(47, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30),
        array(36, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30),
        array(25, 30, 29, 30, 30, 59, 29, 30, 29, 29, 30, 29, 30, 29), //五月29 闰五月30
        array(44, 29, 30, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29),
        array(32, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30, 29),
        array(22, 29, 30, 59, 29, 30, 29, 30, 30, 29, 30, 30, 29, 29), //三月29 闰三月30		
    );

    //农历名称转换
    private static function LYearName($year)
    {
        $Name = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $tmp = '';
        for ($i = 0; $i < 4; $i++)
            for ($k = 0; $k < 10; $k++)
                if ($year[$i] == $k)
                    $tmp .= $Name[$k];
        return $tmp;
    }

    private static function LMonName($month)
    {
        if ($month >= 1 && $month <= 12) {
            $Name = array(1 => "正", "二", "三", "四", "五", "六", "七", "八", "九", "十", "十一", "十二");
            return $Name[$month];
        }
        return $month;
    }

    private static function LDayName($day)
    {
        if ($day >= 1 && $day <= 30) {
            $Name = array(1 =>
                "初一", "初二", "初三", "初四", "初五", "初六", "初七", "初八", "初九", "初十",
                "十一", "十二", "十三", "十四", "十五", "十六", "十七", "十八", "十九", "二十",
                "廿一", "廿二", "廿三", "廿四", "廿五", "廿六", "廿七", "廿八", "廿九", "三十"
            );
            return $Name[$day];
        }
        return $day;
    }

    //公历转农历(Sdate：公历日期)
    public static function S2L($date)
    {
        list($year, $month, $day) = explode("-", $date);
        if ($year <= 1951 || $month <= 0 || $day <= 0 || $year >= 2051)
            return false;
        //获取查询日期到当年1月1日的天数
        $date1 = strtotime($year . "-01-01"); //当年1月1日
        $date2 = strtotime($year . "-" . $month . "-" . $day);
        $days = round(($date2 - $date1) / 3600 / 24);
        $days += 1;
        //获取相应年度农历数据，化成数组Larray
        $Larray = self::$_LMDay[$year - self::$_LStart];
        if ($days <= $Larray[0]) {
            $Lyear = $year - 1;
            $days = $Larray[0] - $days;
            $Larray = self::$_LMDay[$Lyear - self::$_LStart];
            if ($days < $Larray[12]) {
                $Lmonth = 12;
                $Lday = $Larray[12] - $days;
            } else {
                $Lmonth = 11;
                $days = $days - $Larray[12];
                $Lday = $Larray[11] - $days;
            }
        } else {
            $Lyear = $year;
            $days = $days - $Larray[0];
            for ($i = 1; $i <= 12; $i++) {
                if ($days > $Larray[$i])
                    $days = $days - $Larray[$i];
                else {
                    if ($days > 30) {
                        $days = $days - $Larray[13];
                        $Ltype = 1;
                    }

                    $Lmonth = $i;
                    $Lday = $days;
                    break;
                }
            }
        }
        //return mktime(0, 0, 0, $Lmonth, $Lday, $Lyear);
        return array('Lyear' => $Lyear, 'Lmonth' => $Lmonth, 'Lday' => $Lday);
        //$Ldate = $Lyear."-".$Lmonth."-".$Lday;
        //$Ldate = $this->LYearName($Lyear)."年".$this->LMonName($Lmonth)."月".$this->LDayName($Lday);
        //if($Ltype) $Ldate.="(闰)";
        //return $Ldate;
    }

    public static function getWeekDay($timestamp = TIMESTAMP)
    {
        $weekArr = array("日", "一", "二", "三", "四", "五", "六");
        $weekDay = $weekArr[date("w", $timestamp)];
        return $weekDay;
    }

    /**
     * 生成一个日期+星期+农历的字符串,如4月16日星期二农历三月初七
     * @return string
     */
    public static function getlunarCalendar()
    {
        $dateStr = date('Y-m-d-');
        $dateArr = explode('-', $dateStr);
        list($year, $month, $day) = $dateArr;
        if (strpos($month, '0') === 0) {
            $month = substr($month, 1);
        }
        if (strpos($day, '0') === 0) {
            $day = substr($day, 1);
        }
        $LunarCalendar = self::S2L(date('Y-m-d'));
        $LunarCalendarStr = self::LMonName($LunarCalendar['Lmonth']) . "月" . self::LDayName($LunarCalendar['Lday']);

        return $year . '年' . $month . '月' . $day . '日' . '，星期' . self::getWeekDay() . '，农历' . $LunarCalendarStr;
    }

    /**
     * 获取字符串时间（$strTime）所代表的时间范围
     * @param string $strTime
     * @return array 时间范围
     */
    public static function getStrTimeScope($strTime, $time = TIMESTAMP)
    {
        switch ($strTime) {
            case 'today':
                $start = strtotime('today 00:00:00', $time);
                $end = strtotime('today 23:59:59', $time);
                break;
            case 'yesterday':
                $start = strtotime('yesterday 00:00:00', $time);
                $end = strtotime('yesterday 23:59:59', $time);
                break;
            case 'thisweek':
                $start = strtotime('Monday 00:00:00 this week', $time);
                $end = strtotime('Sunday 23:59:59 this week', $time);
                break;
            case 'lastweek':
                $start = strtotime('Monday 00:00:00 last week', $time);
                $end = strtotime('Sunday 23:59:59 last week', $time);
                break;
            case 'thismonth':
                $start = strtotime("first day of this month 00:00:00", $time);
                $end = strtotime("last day of this month 23:59:59", $time);
                break;
            case 'lastmonth':
                $start = strtotime("first day of last month 00:00:00", $time);
                $end = strtotime("last day of last month 23:59:59", $time);
                break;
            default:
                /**
                 * 返回当天时间的范围
                 */
                $start = strtotime("{$strTime} 00:00:00", $time) ? strtotime("{$strTime} 00:00:00", $time) : null;
                $end = strtotime("{$strTime} 23:59:59", $time) ? strtotime("{$strTime} 23:59:59", $time) : null;
                break;
        }
        return array(
            'start' => $start,
            'end' => $end
        );
    }

    /**
     * 根据秒数获得更人性化的时间提示
     * @param int $secs 秒数
     * @return string 格式化后的时间格式
     */
    public static function getTime($secs, $format = 'dhis')
    {
        $day = floor($secs / 86400);
        $hour = floor(($secs % 86400) / 3600);
        $min = floor(($secs % 3600) / 60);
        $sec = floor($secs % 60);
        $lang = Ibos::getLangSource('date');
        $timestr = "";
        if ($day > 0 && stristr($format, 'd')) {
            $timestr .= $day . $lang['Day'];
        }
        if ($hour > 0 && stristr($format, 'h')) {
            $timestr .= $hour . $lang['Hour'];
        }
        if ($min > 0 && stristr($format, 'i')) {
            $timestr .= $min . $lang['Min'];
        }
        if ($sec > 0 && stristr($format, 's')) {
            $timestr .= $sec . $lang['Sec'];
        }
        return $timestr;
    }

    /**
     * 根据两个时间戳来求出期间的天数
     * @param integer $start 开始时间
     * @param integer $end 结束时间
     * @return integer 天数
     */
    public static function getDays($start, $end)
    {
        $days = ($end - $start) / 86400;
        return intval($days);
    }

    /*
     * 计算两个日期相隔多少年，多少月，多少天
     * param string $date1[格式如：2011-11-5]
     * param string $date2[格式如：2012-12-01]
     * return array array('年','月','日');
     */

    public static function getDiffDate($date1, $date2)
    {
        if (strtotime($date1) > strtotime($date2)) {
            $tmp = $date2;
            $date2 = $date1;
            $date1 = $tmp;
        }
        list($Y1, $m1, $d1) = explode('-', $date1);
        list($Y2, $m2, $d2) = explode('-', $date2);
        $y = $Y2 - $Y1;
        $m = $m2 - $m1;
        $d = $d2 - $d1;
        if ($d < 0) {
            $d += (int)date('t', strtotime("-1 month $date2"));
            $m--;
        }
        if ($m < 0) {
            $m += 12;
            $y--;
        }
        return array('y' => $y, 'm' => $m, 'd' => $d);
    }

    /**
     * 根据月份返回第几季度
     * @param integer $month 月份
     * @return integer 季度
     */
    public static function getSeasonByMonty($month)
    {
        switch ($month) {
            case 1:
            case 2:
            case 3:
                $season = 1;
                break;
            case 4:
            case 5:
            case 6:
                $season = 2;
                break;
            case 7:
            case 8:
            case 9:
                $season = 3;
                break;
            case 10:
            case 11:
            case 12:
                $season = 4;
                break;
        }
        return $season;
    }

    /**
     * 根据两个时间戳来格式化$type指定的日期类型
     * @param type $start
     * @param type $end
     * @param type $filter
     * @return type
     */
    public static function getFormatDate($start, $end, $type = 'Y-m-d')
    {
        $return = array();
        switch ($type) {
            case 'Y-m-d':
                $days = self::getDays($start, $end);
                $return = self::formatByYMD($days, $start);
                break;
            case 'weekend':

                break;
            default:
                break;
        }
        return $return;
    }

    /**
     * 获取某日所在的当前一个星期时间
     * @return array
     */
    public static function getWeeks($date = null)
    {
        $dete = is_null($date) ? date('Y-m-d') : $date;
        $whichD = date('w', strtotime($dete));
        $weeks = array();
        for ($i = 0; $i < 7; $i++) {
            if ($i < $whichD) {
                $date = strtotime($dete) - ($whichD - $i) * 24 * 3600;
            } else {
                $date = strtotime($dete) + ($i - $whichD) * 24 * 3600;
            }
            $weeks[$i] = date('Y-m-d', $date);
        }
        return $weeks;
    }

    /**
     * 按年月日格式格式化并返回一维数组
     * @param integer $days 天数
     * @param integer $start 开始的日期时间戳
     * @return array
     */
    private static function formatByYMD($days, $start)
    {
        $return = array();
        for ($i = 0; $i <= $days; $i++) {
            $return[] = date('Y-m-d', strtotime("+{$i} day", $start));
        }
        return $return;
    }

}
