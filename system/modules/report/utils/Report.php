<?php

/**
 * 工作总结与计划模块------ 工具类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------  工具类
 * @package application.modules.report.utils
 * @version $Id: ReportUtil.php 1865 2013-12-07 07:58:56Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\utils;

use application\core\utils\Ibos;

class Report {

    /**
     * 获取各个季节开始和结束日期
     * @return array
     */
    public static function getSeason() {
        $season = array(//季度时间
            'season1' => '-01-01',
            'season2' => '-03-31',
            'season3' => '-04-01',
            'season4' => '-06-30',
            'season5' => '-07-01',
            'season6' => '-09-31',
            'season7' => '-10-01',
            'season8' => '-12-31'
        );
        return $season;
    }

    /**
     * 根据汇报类型区间返回总结开始、结束时间和下次计划开始、结束时间
     * @param integer $intervalType 区间类型
     * @param integer $intervals 自定义的间隔天数
     * @return array 返回一维数组
     */
    public static function getDateByIntervalType($intervalType, $intervals) {
        $season = self::getSeason();
        $year = date("Y");
        $month = date("m");
        $today = date('Y-m-d'); //当前日期
        switch ($intervalType) {
            case '0':   //周
                $oneday = 60 * 60 * 24;
                $time = strtotime("sunday");
                $begin = $time - ($oneday * 7);
                $end = $begin + ($oneday * 6);
                $return = array(
                    'summaryBegin' => date('Y-m-d', $begin),
                    'summaryEnd' => date('Y-m-d', $end),
                    'planBegin' => date('Y-m-d', $end + $oneday),
                    'planEnd' => date('Y-m-d', ($end + $oneday) + $oneday * 6)
                );

                break;
            case '1':   //月
                $return = array(
                    'summaryBegin' => date('Y-m-01'),
                    'summaryEnd' => date('Y-m-t'),
                    'planBegin' => date('Y-m-01', strtotime('+1 month')),
                    'planEnd' => date('Y-m-t', strtotime('+1 month'))
                );
                break;
            case '2':   //季度				
                switch ($month) {
                    case '01':
                    case '02':
                    case '03':
                        $return = array(
                            'summaryBegin' => $year . $season['season1'],
                            'summaryEnd' => $year . $season['season2'],
                            'planBegin' => $year . $season['season3'],
                            'planEnd' => $year . $season['season4']
                        );
                        break;
                    case '04':
                    case '05':
                    case '06':
                        $return = array(
                            'summaryBegin' => $year . $season['season3'],
                            'summaryEnd' => $year . $season['season4'],
                            'planBegin' => $year . $season['season5'],
                            'planEnd' => $year . $season['season6']
                        );
                        break;
                    case '07':
                    case '08':
                    case '09':
                        $return = array(
                            'summaryBegin' => $year . $season['season5'],
                            'summaryEnd' => $year . $season['season6'],
                            'planBegin' => $year . $season['season7'],
                            'planEnd' => $year . $season['season8']
                        );
                        break;
                    case '10':
                    case '11':
                    case '12':
                        $return = array(
                            'summaryBegin' => $year . $season['season7'],
                            'summaryEnd' => $year . $season['season8'],
                            'planBegin' => ($year + 1) . $season['season1'],
                            'planEnd' => ($year + 1) . $season['season2']
                        );
                        break;
                }
                break;
            case '3': //半年
                if (in_array($month, array('01', '02', '03', '04', '05', '06'))) {
                    $return = array(
                        'summaryBegin' => $year . $season['season1'],
                        'summaryEnd' => $year . $season['season4'],
                        'planBegin' => $year . $season['season5'],
                        'planEnd' => $year . $season['season8']
                    );
                } else {
                    $return = array(
                        'summaryBegin' => $year . $season['season5'],
                        'summaryEnd' => $year . $season['season8'],
                        'planBegin' => ($year + 1) . $season['season1'],
                        'planEnd' => ($year + 1) . $season['season4']
                    );
                }
                break;
            case '4': //年
                $return = array(
                    'summaryBegin' => date('Y-01-01'),
                    'summaryEnd' => date('Y-12-31'),
                    'planBegin' => date('Y-01-01', strtotime('+1 year')),
                    'planEnd' => date('Y-12-31', strtotime('+1 year'))
                );
                break;
            case '5': //其他
                $oneday = 60 * 60 * 24; //一天的秒数
                //当前时间不变   第二个时间加上间隔天数
                $dateTime1 = strtotime($today);
                $dateTime2 = $dateTime1 + ($oneday * $intervals);
                //第三个时间在第二个时间上加1  第四个时间在第三个时间上加间隔天数
                $dateTime3 = $dateTime2 + $oneday;
                $dateTime4 = $dateTime3 + ($oneday * $intervals);
                $return = array(
                    'summaryBegin' => $today,
                    'summaryEnd' => date('Y-m-d', $dateTime2),
                    'planBegin' => date('Y-m-d', $dateTime3),
                    'planEnd' => date('Y-m-d', $dateTime4)
                );
                break;
            default:
                break;
        }
        return $return;
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
     * 组合查询条件
     * @param array $search 查询数组
     * @return string
     */
    public static function joinSearchCondition($search) {
        $searchCondition = '';
        //添加对keyword的转义，防止SQL错误
        $keyword = \CHtml::encode($search['keyword']);
        $starttime = $search['starttime'];
        $endtime = $search['endtime'];
        if (!empty($keyword)) {
            $searchCondition.=" ( subject LIKE '%$keyword%' OR content LIKE '%$keyword%' ) AND ";
        }
        if (!empty($starttime)) {
            $starttime = strtotime($starttime);
            $searchCondition.=" begindate>=$starttime AND ";
        }
        if (!empty($endtime)) {
            $endtime = strtotime($endtime);
            $searchCondition.=" enddate<=$endtime AND ";
        }
        $condition = !empty($searchCondition) ? substr($searchCondition, 0, -4) : '';
        return $condition;
    }

    /**
     * 
     * @return type
     */
    public static function getSetting() {
        return IBOS::app()->setting->get('setting/reportconfig');
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

}
