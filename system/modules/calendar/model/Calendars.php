<?php

/**
 * 日程安排模快------calendars表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 日程安排模快------calendars表操作类，继承ICModel
 * @package application.modules.calendar.model
 * @version $Id: Calendars.php 1425 2013-10-29 16:16:43Z gzhzh $
 * @author gzhzh <gzhzh.com.cn>
 */

namespace application\modules\calendar\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\modules\calendar\utils\Calendar as CalendarUtil;
use application\modules\user\utils as UserUtil;
use CDbCriteria;
use CPagination;

class Calendars extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{calendars}}';
    }

    /**
     * 根据时间段、uid 获取对应用户的普通日程数据
     * @param integer $startTime 开始时间
     * @param integer $endTime 结束时间
     * @param integer $uid 日程拥有者用户 uid
     * @return array 日程相关数据数组
     */
    public function getCommonCalendarList($startTime, $endTime, $uid)
    {
        //$condition = '`isalldayevent` = 0 AND `instancetype` != 1 AND uid = :uid AND endtime BETWEEN :starttime AND :endtime AND (endtime-starttime) < 24*60*60';
        //$condition = '`instancetype` != 1 AND uid = :uid AND endtime BETWEEN :starttime AND :endtime AND (endtime-starttime) <= 24*60*60';
        $condition = '`instancetype` != 1 AND uid = :uid AND endtime BETWEEN :starttime AND :endtime';
        $params = array(':uid' => $uid, ':starttime' => $startTime, ':endtime' => $endTime);
        $comCalendar = $this->findAll(
            array(
                'condition' => $condition,
                'params' => $params,
                'order' => 'starttime ASC',
            )
        );

        //查询周期性事务
        $loops = $this->fetchAll(array(
            'condition' => "`instancetype`= 1 AND recurringbegin<=" . $endTime . " AND (`recurringend`>=" . $startTime . " OR `recurringend`=0) AND `uid` = :uid",
            'params' => array(':uid' => $uid)
        ));
        $events = $this->parsePeriodicEvents($startTime, $endTime, $loops);

        $allCalendar = $events;
        foreach($comCalendar as $dayCalendar) {
            $allCalendar[] = $dayCalendar->attributes;
        }

        // 判断当前用户对日程用户的日程处理权限 0查看 1编辑 false当前用户没有权限操作该用户的日程
        $editAble = UserUtil\User::checkUserCalendarPermission(Ibos::app()->user->uid, $uid);
        if ($editAble === false) {
            return false;
        }
        $result['error'] = null;
        $result['issort'] = true;
        $result["start"] = "/Date(" . $startTime . "000" . ")/";
        $result["end"] = "/Date(" . $endTime . "000" . ")/";
        $result['events'] = array();
        foreach ($allCalendar as $calendar) {
            $spanday = date('Y-m-d', $calendar['starttime']) < date('Y-m-d', $calendar['endtime']) ? 1 : 0; //是否是跨天日程
            $result['events'][] = array(
                'id' => $calendar['calendarid'], //周期性事务ID做特别标识，方便实例
                'title' => $calendar['subject'], // 日程内容
                'start' => CalendarUtil::php2JsTime($calendar['starttime']), // 日程开始时间，格式： /Date("4330003332000")/
                'end' => CalendarUtil::php2JsTime($calendar['endtime']), // 日程结束时间，格式： /Date("4330003332000")/
                'allDay' => $calendar['isalldayevent'], //是否全天日程
                'acrossDay' => $spanday, //是否跨天日程
                'type' => $calendar['instancetype'], // 实例类型 0为普通， 1为周期性日程，2为周期性日程的实例
                'category' => $calendar['category'], // 颜色主题
                'editable' => $editAble, // 是否可编辑
                'location' => $calendar['location'], // 地点，暂时无用
                'attends' => '', //$attends ??
                'status' => $calendar['status'], // 日程状态，未进行、完成、删除
                // date( 'Y-m-d', $row['starttime'] ), // 日程开始日期
                'loopId' => $calendar['masterid'] // 被实例周期性事务的ID
            );
        }
        return $result;
    }

    /**
     * 显示某个日程
     * @param string $showDate 显示的时间段
     * @param string $viewType 视图类型(日/周/月)
     * @param int $uid 用户ID
     * @return array
     */
    public function listCalendar($st, $et, $uid)
    {
        $curUid = Ibos::app()->user->uid;
        // $result = $this->getCalendarViewFormat( $showDate, $viewType );
        //获取某段时间内的日程安排
        $list['calendar'] = Calendars::model()->listCalendarByRange($st, $et, $uid);
        $allowEdit = CalendarUtil::getIsAllowEdit(); // 是否允许上司修改下属日程
        //对输出数据进行处理
        $tmpret['events'] = array();
        foreach ($list['calendar']['events'] as $key => $row) {
            $spanday = date('Y-m-d', $row['starttime']) < date('Y-m-d', $row['endtime']) ? 1 : 0; //是否是跨天日程
            if ($row['lock']) {
                // 锁定的日程
                $editAble = 0;
            } elseif ($row['uid'] == $curUid || $allowEdit || $curUid == $row['upuid']) {
                // 自己没锁定的日程或者后台设置允许修改下属日程或者这日程是登录者添加的
                $editAble = 1;
            } else {
                $editAble = 0;
            }
            $tmpret['events'][] = array(
                'id' => $row['calendarid'], //周期性事务ID做特别标识，方便实例
                'title' => $row['subject'], // 日程内容
                'start' => CalendarUtil::php2JsTime($row['starttime']), // 日程开始时间，格式： /Date("4330003332000")/
                'end' => CalendarUtil::php2JsTime($row['endtime']), // 日程结束时间，格式： /Date("4330003332000")/
                'allDay' => $row['isalldayevent'], //是否全天日程
                'acrossDay' => $spanday, //是否跨天日程
                'type' => $row['instancetype'], // 实例类型 0为普通， 1为周期性日程，2为周期性日程的实例
                'category' => $row['category'], // 颜色主题
                'editable' => $editAble, // 是否可编辑
                'location' => $row['location'], // 地点，暂时无用
                'attends' => '', //$attends ??
                'status' => $row['status'], // 日程状态，未进行、完成、删除
                // date( 'Y-m-d', $row['starttime'] ), // 日程开始日期
                'loopId' => $row['masterid'] // 被实例周期性事务的ID
            );
        }
        foreach ($tmpret['events'] as $key => $row) {
            $beginarr[$key] = $row['start'];
        }
        // 将数据根据开始时间升序排列
        // 把 $ret['events'] 作为最后一个参数，以通用键排序
        if (!empty($beginarr)) {
            array_multisort($beginarr, SORT_ASC, $tmpret['events']);
        }
        $ret = $list['calendar']; //以日程信息为主
        $ret['events'] = $tmpret['events'];

        return $ret;
    }

    /**
     * 获取某段时间内的日程安排
     * @param dateline $sd 显示开始时间区域
     * @param dateline $ed 显示结束时间区域
     * @param int $uid 用户UID
     * @param int $num 规定要返回的周期条数
     * @return array 返回数据,注：如果是周期性日程，ID是伪ID，10位负数（时间戳）加上所属周期的真实ID
     */
    public function listCalendarByRange($sd, $ed, $uid = '', $num = null)
    {
        $ret = array();
        $ret['events'] = array();
        $ret["issort"] = true;
        $ret["start"] = "/Date(" . $sd . "000" . ")/";
        $ret["end"] = "/Date(" . $ed . "000" . ")/";
        $ret['error'] = null;
        $whereuid = empty($uid) ? '1' : '`uid`=' . $uid;
        $select = '`calendarid`, `subject`, `starttime`, `endtime`, `mastertime`, `masterid`, `isalldayevent`, `category`, `instancetype`, `recurringtime`, `recurringtype`, `status`, `recurringbegin`, `recurringend`, `upuid`, `uid`, `lock`, `isfromdiary` ';
        //普通日程
        $handle = $this->fetchAll(array(
            'select' => $select,
            'condition' => "instancetype!=1 AND status!=3 AND {$whereuid} AND endtime BETWEEN {$sd} AND {$ed}",
            'order' => 'starttime ASC'
        ));
        if (!empty($handle)) {
            foreach ($handle as $timestask) {
                $ret['events'][] = $timestask;
            }
        }
        //查询周期性事务
        $loops = $this->fetchAll(array(
            'select' => $select,
            'condition' => "`instancetype`=1 AND recurringbegin<=" . $ed . " AND (`recurringend`>=" . $sd . " OR `recurringend`=0) AND $whereuid",
            'params' => array(':uid' => $uid)
        ));
//        if (!empty($loops)) {
//            foreach ($loops as $loop) {
//                //取得周期性事务的实例
//                $examples = $this->fetchAll(array(
//                    'condition' => "`instancetype`=2 AND `masterid`=" . $loop['calendarid'],
//                ));
//                $mastertimearr = array();
//                if (!empty($examples)) {
//                    //取得实例事务所属的周期性事务的某一日时间
//                    foreach ($examples as $example) {
//                        $mastertimearr[] = $example['mastertime'];
//                    }
//                }
//                switch ($loop['recurringtype']) {
//                    case 'week': //如果是周
//                        $weekarr = explode(',', $loop['recurringtime']);
//                        $dayarr = array();
//                        $rstart = strtotime(date('Y-m-d', $sd)); //开始的日期
//                        $rend = strtotime(date('Y-m-d ', $ed)); //结束的日期
//                        $validitydays = (ceil(($rend - $rstart)) / (60 * 60 * 24)); //有效天数
//                        for ($i = 0; $i < $validitydays + 1; $i++) {
//                            $dayarr[] = mktime(0, 0, 0, date('m', $sd), date('d', $sd) + $i, date('Y', $sd)); //用mktime会自动较正日期
//                        }
//                        $cloneid = $loop['calendarid']; //复制$row['Id']，因为下面遍历要生成伪ID，$row['Id']会被改变
//                        foreach ($dayarr as $key => $value) {
//                            $weekday = date('N', $value); //求出当前号是星期几
//                            if (in_array($weekday, $weekarr)) {
//                                $loop['starttime'] = strtotime(date('Y-m-d', $value) . ' ' . date('H:i:s', $loop['starttime']));
//                                $loop['endtime'] = strtotime(date('Y-m-d', $value) . ' ' . date('H:i:s', $loop['endtime']));
//                                $issub = in_array(date('Y-m-d', $loop['starttime']), $mastertimearr);
//                                if ($loop['endtime'] > $sd && $loop['starttime'] >= $loop['recurringbegin'] && ($loop['endtime'] <= ($loop['recurringend'] + 24 * 60 * 60 - 1) || $loop['recurringend'] == 0) && !$issub) {
//                                    $loop['calendarid'] = '-' . $loop['starttime'] . $cloneid; //生成伪ID
//                                    $ret['events'][] = $loop;
//                                }
//                            }
//                        }
//                        break;
//                    case 'month': //如果是月
//                        $day = date('d', $sd);
//                        if ($loop['recurringtime'] > $day) { //有一种情况，如果页面列表是两个月之间的日期，如1月28号至2月3号，只用$sd提取的月就是1月，但是如果周期是在2号，那么就要用$ed提取的月份进行操作
//                            $date = date('Y-m-', $sd) . $loop['recurringtime'] . ' ';
//                        } else {
//                            $date = date('Y-m-', $ed) . $loop['recurringtime'] . ' ';
//                        }
//                        $stime = date('H:i:s', $loop['starttime']); //日程开始点数
//                        $etime = date('H:i:s', $loop['endtime']); //日程结束点数
//                        $loop['starttime'] = strtotime($date . $stime);
//                        $loop['endtime'] = strtotime($date . $etime);
//                        $issub = in_array(date('Y-m-d', $loop['starttime']), $mastertimearr);
//                        if ($loop['starttime'] >= $sd && $loop['endtime'] <= $ed && $loop['starttime'] >= $loop['recurringbegin'] && ($loop['endtime'] <= ($loop['recurringend'] + 24 * 60 * 60 - 1) || $loop['recurringend'] == 0) && !$issub) {
//                            $loop['calendarid'] = '-' . $loop['starttime'] . $loop['calendarid'];
//                            $ret['events'][] = $loop;
//                        }
//                        break;
//                    case 'year': //如果是年
//                        $recurringtime = $loop['recurringtime'];  //年事务循环的是几月几号
//                        $date = date('Y-', $sd) . $recurringtime . ' ';
//                        $stime = date('H:i:s', $loop['starttime']); //日程开始点数
//                        $etime = date('H:i:s', $loop['endtime']); //日程结束点数
//                        $loop['starttime'] = strtotime($date . $stime);
//                        $loop['endtime'] = strtotime($date . $etime);
//                        $issub = in_array(date('Y-m-d', $loop['starttime']), $mastertimearr);
//                        if ($loop['starttime'] >= $sd && $loop['endtime'] <= $ed && $loop['starttime'] >= $loop['recurringbegin'] && ($loop['endtime'] <= ($loop['recurringend'] + 24 * 60 * 60 - 1) || $loop['recurringend'] == 0) && !$issub) {
//                            $loop['calendarid'] = '-' . $loop['starttime'] . $loop['calendarid'];
//                            $ret['events'][] = $loop;
//                        }
//                        break;
//                }
//            }
//            foreach ($ret['events'] as $key => $row) {
//                $starttimearr[$key] = $row['starttime'];
//            }
//            // 将数据根据开始时间升序排列
//            // 把 $ret['events'] 作为最后一个参数，以通用键排序
//            if (!empty($starttimearr)) {
//                array_multisort($starttimearr, SORT_ASC, $ret['events']);
//            }
//            if (!is_null($num)) {
//                $ret['events'] = array_slice($ret['events'], 0, $num);
//            }
//        }

        $events = $this->parsePeriodicEvents($sd, $ed, $loops);
        $ret['events'] = array_merge($ret['events'], $events);
        return $ret;
    }

    /**
     * 更新某个日程
     * @param int $id 日程的ID
     * @param dateline $st 开始时间
     * @param dateline $et 结束时间
     * @param string $sj 标题
     * @param int $cg 分类
     * @param int $su 完成状态
     * @param int $iad 全天日程
     * @return array 返回状态
     */
    public function updateSchedule($calendarid, $st, $et, $sj, $cg, $iad, $su = null)
    {
        $modifyData = array(
            'starttime' => CalendarUtil::js2PhpTime($st),
            'endtime' => CalendarUtil::js2PhpTime($et),
            'subject' => $sj,
            'category' => $cg,
            'status' => $su,
            'isalldayevent' => $iad
        );
        if (is_null($su)) {
            unset($modifyData['status']);
        }
        $modifyResult = $this->modify($calendarid, $modifyData);
        if ($modifyResult) {
            $ret['isSuccess'] = true;
            $ret['msg'] = '操作成功';
        } else {
            $ret['isSuccess'] = false;
            $ret['msg'] = '操作失败';
        }
        return $ret;
    }

    /**
     * 取得周期性事务并分页显示
     * @param string $conditions
     * @param int $pageSize
     * @return array
     */
    public function fetchLoopsAndPage($conditions = '', $pageSize = null)
    {

        $pages = new CPagination($this->countByCondition($conditions));
        $pageSize = is_null($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pages->setPageSize(intval($pageSize));
        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $criteria = new CDbCriteria(array('limit' => $limit, 'offset' => $offset));
        $pages->applyLimit($criteria);
        $fields = '`calendarid`, `subject`, `starttime`, `endtime`, `mastertime`, `masterid`, `isalldayevent`, `category`, `instancetype`, `recurringtime`, `recurringtype`, `status`, `recurringbegin`, `recurringend`, `uptime`, `upuid`, `uid`, `lock` ';
        $sql = "SELECT $fields FROM {{calendars}}";
        if (!empty($conditions)) {
            $sql .= " WHERE " . $conditions;
        }
        $sql .= " ORDER BY uptime DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array('pages' => $pages, 'datas' => $records);
    }

    /**
     * 根据条件取得总记录数
     */
    public function countByCondition($condition = '')
    {
        if (!empty($condition)) {
            $whereCondition = " WHERE " . $condition;
            $sql = "SELECT COUNT(*) AS number FROM {{calendars}} $whereCondition";
            $record = $this->getDbConnection()->createCommand($sql)->queryAll();
            return $record[0]['number'];
        } else {
            return $this->count();
        }
    }

    /**
     * 通过周期性事务(日程)ID获取这条数据并处理后返回
     * @param int $editCalendarid 日程ID号
     * @return array
     */
    public function fetchEditLoop($editCalendarid)
    {
        $editData = $this->fetchByPk($editCalendarid);
        $editData['starttime'] = date('H:i', $editData['starttime']);
        $editData['endtime'] = date('H:i', $editData['endtime']);
        $editData['recurringbegin'] = date('Y-m-d', $editData['recurringbegin']);
        if ($editData['recurringend'] == 0) {
            $editData['recurringend'] = '';
        } else {
            $editData['recurringend'] = date('Y-m-d', $editData['recurringend']);
        }
        return $editData;
    }

    /**
     * 取最新的5条日程，用于首页
     * @param int $uid 用户ID
     * @param int $st 开始时间（即现在时间），时间戳
     * @return array  返回最新的5条日程
     */
    public function fetchNewSchedule($uid, $st)
    {
        $todaystart = strtotime(date('Y-m-d'));
        $schedules = $this->fetchAll(array(
            'select' => 'calendarid,subject,mastertime,starttime,endtime,isalldayevent,category',
            'condition' => 'uid = :uid AND (endtime > :time OR (starttime >= :todaystart && isalldayevent = 1)) AND status = 0 AND instancetype != 1',
            'params' => array(':uid' => $uid, ':time' => $st, ':todaystart' => $todaystart),
            'order' => '`starttime` ASC',
            'limit' => 5
        ));
        return $schedules;
    }

    /**
     * 颜色主题，用于周期性事务页面输出
     * @param int $cagory 主题分类（-1至7（不包括0），每个数字代表一种颜色，-1是默认颜色）
     * @return array  返回这个主题对应的颜色
     */
    public function handleColor($cagory)
    {
        $colorArr = array(
            '-1' => '3497DB',
            '0' => '3497DB',
            '1' => 'A6C82F',
            '2' => 'F4C73B',
            '3' => 'EE8C0C',
            '4' => 'E76F6F',
            '5' => 'AD85CC',
            '6' => '98B2D1',
            '7' => '82939E'
        );
        if (isset($colorArr[$cagory])) {
            return $colorArr[$cagory];
        } else {
            return $colorArr['-1'];
        }
    }

    /**
     * 解析周期性事件记录，返回事件数组
     * @param int $sd 日期范围开始
     * @param int $ed 日期范围结束
     * @param array $loops 周期性时间记录数组，array( 0=> <prefix>_calendars 表的一行记录 )
     * @return array
     */
    public function parsePeriodicEvents($sd, $ed, $loops)
    {
        if (empty($loops)) {
            return array();
        }
        $ret['events'] = array();
        foreach ($loops as $loop) {
            //取得周期性事务的实例
            $examples = $this->fetchAll(array(
                'condition' => "`instancetype`=2 AND `masterid`=" . $loop['calendarid'],
            ));
            $mastertimearr = array();
            if (!empty($examples)) {
                //取得实例事务所属的周期性事务的某一日时间
                foreach ($examples as $example) {
                    $mastertimearr[] = $example['mastertime'];
                }
            }
            switch ($loop['recurringtype']) {
                case 'week': //如果是周
                    $weekarr = explode(',', $loop['recurringtime']);
                    $dayarr = array();
                    $rstart = strtotime(date('Y-m-d', $sd)); //开始的日期
                    $rend = strtotime(date('Y-m-d ', $ed)); //结束的日期
                    $endtimeofeveryday = date('H:i:s', $loop['endtime']);
                    $starttimeofeveryday = date('H:i:s', $loop['starttime']);
                    $validitydays = (ceil(($rend - $rstart)) / (60 * 60 * 24)); //有效天数
                    for ($i = 0; $i < $validitydays + 1; $i++) {
                        $dayarr[] = mktime(0, 0, 0, date('m', $sd), date('d', $sd) + $i, date('Y', $sd)); //用mktime会自动较正日期
                    }
                    $cloneid = $loop['calendarid']; //复制$row['Id']，因为下面遍历要生成伪ID，$row['Id']会被改变
                    foreach ($dayarr as $key => $value) {
                        $weekday = date('N', $value); //求出当前号是星期几
                        if (in_array($weekday, $weekarr)) {
                            $loop['starttime'] = strtotime(date('Y-m-d', $value) . ' ' . $starttimeofeveryday);
                            $loop['endtime']   = strtotime(date('Y-m-d', $value) . ' ' . $endtimeofeveryday);
                            $issub = in_array(date('Y-m-d', $loop['starttime']), $mastertimearr);
                            if ($loop['endtime'] > $sd && $loop['starttime'] >= $loop['recurringbegin'] && ($loop['endtime'] <= ($loop['recurringend'] + 24 * 60 * 60 - 1) || $loop['recurringend'] == 0) && !$issub) {
                                $loop['calendarid'] = '-' . $loop['starttime'] . $cloneid; //生成伪ID
                                $ret['events'][] = $loop;
                            }
                        }
                    }
                    break;
                case 'month': //如果是月
                    $day = date('d', $sd);
                    if ($loop['recurringtime'] > $day) { //有一种情况，如果页面列表是两个月之间的日期，如1月28号至2月3号，只用$sd提取的月就是1月，但是如果周期是在2号，那么就要用$ed提取的月份进行操作
                        $date = date('Y-m-', $sd) . $loop['recurringtime'] . ' ';
                    } else {
                        $date = date('Y-m-', $ed) . $loop['recurringtime'] . ' ';
                    }
                    $stime = date('H:i:s', $loop['starttime']); //日程开始点数
                    $etime = date('H:i:s', $loop['endtime']); //日程结束点数
                    $loop['starttime'] = strtotime($date . $stime);
                    $loop['endtime'] = strtotime($date . $etime);
                    $issub = in_array(date('Y-m-d', $loop['starttime']), $mastertimearr);
                    if ($loop['starttime'] >= $sd && $loop['endtime'] <= $ed && $loop['starttime'] >= $loop['recurringbegin'] && ($loop['endtime'] <= ($loop['recurringend'] + 24 * 60 * 60 - 1) || $loop['recurringend'] == 0) && !$issub) {
                        $loop['calendarid'] = '-' . $loop['starttime'] . $loop['calendarid'];
                        $ret['events'][] = $loop;
                    }
                    break;
                case 'year': //如果是年
                    $recurringtime = $loop['recurringtime'];  //年事务循环的是几月几号
                    $date = date('Y-', $sd) . $recurringtime . ' ';
                    $stime = date('H:i:s', $loop['starttime']); //日程开始点数
                    $etime = date('H:i:s', $loop['endtime']); //日程结束点数
                    $loop['starttime'] = strtotime($date . $stime);
                    $loop['endtime'] = strtotime($date . $etime);
                    $issub = in_array(date('Y-m-d', $loop['starttime']), $mastertimearr);
                    if ($loop['starttime'] >= $sd && $loop['endtime'] <= $ed && $loop['starttime'] >= $loop['recurringbegin'] && ($loop['endtime'] <= ($loop['recurringend'] + 24 * 60 * 60 - 1) || $loop['recurringend'] == 0) && !$issub) {
                        $loop['calendarid'] = '-' . $loop['starttime'] . $loop['calendarid'];
                        $ret['events'][] = $loop;
                    }
                    break;
            }
        }
        foreach ($ret['events'] as $key => $row) {
            $starttimearr[$key] = $row['starttime'];
        }
        // 将数据根据开始时间升序排列
        // 把 $ret['events'] 作为最后一个参数，以通用键排序
        if (!empty($starttimearr)) {
            array_multisort($starttimearr, SORT_ASC, $ret['events']);
        }
        return $ret['events'];
    }
}
