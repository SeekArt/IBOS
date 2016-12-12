<?php

/**
 * 工作日志模块------diary表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 工作日志模块------diary表操作类，继承ICModel
 * @package application.modules.diary.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\diary\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use CDbCriteria;
use CPagination;

class Diary extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{diary}}';
    }

    /**
     * 兼容Source接口
     * @param integer $id 资源ID
     * @return array
     */
    public function getSourceInfo($id)
    {
        $info = $this->fetchByPk($id);
        return $info;
    }

    /**
     * 取得列表内容，分页
     * @param string $condition 查询条件
     * @param integer $pageSize 分页大小
     * @return array
     */
    public function fetchAllByPage($condition, $pageSize = 0)
    {
        $conditionArray = array('condition' => $condition, 'order' => 'diarytime DESC');
        $criteria = new CDbCriteria();
        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }
        $count = $this->count($criteria);
        $pagination = new CPagination($count);
        $everyPage = empty($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pagination->setPageSize(intval($everyPage));
        $pagination->applyLimit($criteria);
        $diaryList = $this->fetchAll($criteria);
        return array('pagination' => $pagination, 'data' => $diaryList);
    }

    /**
     * 取得列表内容，分页
     * @param string $condition
     * @param integer $pageSize
     * @return array
     */
    public function fetchAllByPage2($condition, $pageSize = 0)
    {
        $conditionArray = array('condition' => $condition, 'order' => 'diarytime DESC');
        $criteria = new CDbCriteria();
        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }
        $count = $this->count($criteria);
        $pagination = new CPagination($count);
        $everyPage = empty($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pagination->setPageSize(intval($everyPage));
        $pagination->applyLimit($criteria);
        $diaryList = $this->fetchAll($criteria);

        $params = array();
        for ($i = 0; $i < count($diaryList); $i++) {
            //取出今天的工作计划和计划外内容,取出明天的工作计划
            $data = $this->fetchDiaryRecord($diaryList[$i]);
            $params[$i]['diary'] = $diaryList[$i];
            $params[$i]['originalPlanList'] = $data['originalPlanList'];
            $params[$i]['outsidePlanList'] = $data['outsidePlanList'];
            $params[$i]['tomorrowPlanList'] = $data['tomorrowPlanList'];
        }
        return array('pagination' => $pagination, 'data' => $params);
    }

    /**
     * 验证用户是否已添加当天的日志
     * @param integer $diarytime
     * @param integer $uid
     * @return integer
     */
    public function checkDiaryisAdd($diarytime, $uid)
    {
        return $this->count('diarytime=:diarytime AND uid=:uid', array(':diarytime' => $diarytime, ':uid' => $uid));
    }

    /**
     * 获取某个用户某个日期之前的日志
     * @param int $diarytime 参照日志的时间
     * @param int $uid 用户uid
     * @return array 返回上一篇日志数组，没有就返回空数组
     */
    public function fetchPreDiary($diarytime, $uid)
    {
        $preDiary = $this->fetch(array(
            'condition' => "uid = :uid AND diarytime < :diarytime ORDER BY diarytime DESC",
            'params' => array(':uid' => $uid, 'diarytime' => $diarytime)
        ));
        return $preDiary;
    }

    /**
     * 取得当前id的上一个id和下一个Id,以数组形式返回
     * @param integer 当前日志日志ID
     * @return array  返回此用户的上一篇和下一篇日志的ID
     */
    public function fetchPrevAndNextPKByPK($diaryid)
    {
        $diary = $this->fetchByPk($diaryid);
        $uid = $diary['uid'];
        $nextPK = $prevPK = 0;
        //取得当前id是第几条记录
        $sql = "SELECT diaryid FROM {{diary}} WHERE uid=$uid AND diaryid>$diaryid ORDER BY diaryid ASC LIMIT 1";
        $nextRecord = $this->getDbConnection()->createCommand($sql)->queryAll();
        if (!empty($nextRecord)) {
            $nextPK = $nextRecord[0]['diaryid'];
        }

        $sql2 = "SELECT diaryid FROM {{diary}} WHERE uid=$uid AND diaryid<$diaryid ORDER BY diaryid DESC LIMIT 1";
        $prevRecord = $this->getDbConnection()->createCommand($sql2)->queryAll();
        if (!empty($prevRecord)) {
            $prevPK = $prevRecord[0]['diaryid'];
        }
        return array('prevPK' => $prevPK, 'nextPK' => $nextPK);
    }

    /**
     * 通过diary数组取出该天的工作计划和计划外内容和下一次计划内容
     * @param array $diary
     */
    public function fetchDiaryRecord($diary)
    {
        $data = array();
        //取出今天的工作计划和计划外内容
        $todayRecordList = DiaryRecord::model()->fetchAll(array(
            'condition' => 'plantime=:plantime AND uid=:uid',
            'params' => array(':plantime' => $diary['diarytime'], ':uid' => $diary['uid']),
            'order' => 'recordid ASC'
        ));
        $data['originalPlanList'] = array();
        $data['outsidePlanList'] = array();
        foreach ($todayRecordList as $diaryRecord) {
            if ($diaryRecord['planflag'] == 1) {
                $data['originalPlanList'][] = $diaryRecord;
            } else {
                $data['outsidePlanList'][] = $diaryRecord;
            }
        }
        //取出下一次的工作计划
        $recordList = DiaryRecord::model()->fetchAll(array(
            'condition' => 'diaryid=:diaryid AND uid=:uid AND planflag=:planflag',
            'params' => array(':diaryid' => $diary['diaryid'], ':uid' => $diary['uid'], ':planflag' => 1),
            'order' => 'recordid ASC'
        ));
        $data['tomorrowPlanList'] = $recordList;
        return $data;
    }

    /**
     * 增加阅读记录,数据存在或者uid等于作者，返回0,其他返回修改是否成功
     * @param array $diary
     * @param integer $uid
     * @return integer
     */
    public function addReaderuidByPk($diary, $uid)
    {
        //assert( '$uid>0' );
        $readeruid = $diary['readeruid'];
        if ($uid == $diary['uid']) {
            return 0;
        }
        $readerArr = explode(',', trim($readeruid, ','));
        if (in_array($uid, $readerArr)) {
            return 0;
        } else {
            $readeruid = empty($readeruid) ? $uid : $readeruid . ',' . $uid;
            return $this->modify($diary['diaryid'], array('readeruid' => $readeruid));
        }
    }

    /**
     * 取得最近的$number数量的分享日志
     * @param integer $uid 分享给谁
     * @param integer $number 数量
     * @return array
     */
    public function fetchAllByShareCondition($uid, $number)
    {
        $sql = "SELECT * FROM {{diary}} WHERE FIND_IN_SET('$uid',shareuid) AND uid NOT IN($uid) ORDER BY diarytime DESC LIMIT $number";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return $records;
    }

    /**
     * 通过diaryid修改attention的值
     * @param integer $diaryid 主键
     * @param string $type 设置或者取消关注
     * @param integer $uid
     * @return integer 修改成功或失败
     */
    public function updateAttentionByPk($diaryid, $type, $uid)
    {
        $record = $this->fetch(array(
            'select' => array('attention'),
            'condition' => 'diaryid=:diaryid',
            'params' => array(':diaryid' => $diaryid)
        ));
        $attention = $record['attention'];
        if ($type == 'asterisk') {
            if (empty($attention)) {
                $attention = $uid;
            } else {
                $attention = ',' . $uid;
            }
        } else if ($type == 'unasterisk') {
            if (strpos($attention, $uid) !== false) {
                $attention = str_replace($uid, '', $attention);
                if (strpos($attention, ',,') !== true) {
                    $attention = str_replace(',,', ',', $attention);
                }
            }
        }
        return $this->modify($diaryid, array('attention' => $attention));
    }

    /**
     * 取出当前uid这个月的所有日志记录，得到每篇日志的有日志，已点评状态
     * @param string $ym 年月 例：201307
     * @param integer $uid
     * @return array
     */
    public function fetchAllByUidAndDiarytime($ym, $uid)
    {
        $year = substr($ym, 0, 4) + 0;
        $month = substr($ym, 4) + 0;

        $firstDay = date('Y-m-01', strtotime($year . '-' . $month));
        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));

        $startTime = strtotime($firstDay);
        $endTime = strtotime($lastDay);
        $records = Diary::model()->fetchAll(array(
            'select' => array('diaryid', 'diarytime', 'commentcount'),
            'condition' => "diarytime>=$startTime AND diarytime<=$endTime AND uid=:uid",
            'params' => array(':uid' => $uid)
        ));
        $result = array();
        foreach ($records as $diary) {
            $diarytime = $diary['diarytime'];
            $day = date("d", $diarytime) + 0;

            $result[$day]['isLog'] = true;
            $result[$day]['isComment'] = $diary['commentcount'] > 0 ? true : false;
            $result[$day]['diaryid'] = $diary['diaryid'];
        }
        list(, , $startDay) = explode('-', $firstDay);
        list(, , $endDay) = explode('-', $lastDay);
        for ($i = $startDay + 0; $i <= $endDay; $i++) {
            if (!array_key_exists($i, $result)) {
                $result[$i]['isLog'] = false;
                $result[$i]['isComment'] = false;
                $result[$i]['diaryid'] = '';
            }
        }
        return $result;
    }

    /**
     * 通过uid取得该用户所有diayrid，以逗号分隔
     * @param integer $uid
     * @return string
     */
    public function fetchAllDiaryidByUid($uid)
    {
        $records = Diary::model()->fetchAll(array(
            'select' => array('diaryid'),
            'condition' => "uid=:uid",
            'params' => array(':uid' => $uid)
        ));
        $diaryStr = '';
        foreach ($records as $diary) {
            $diaryStr .= $diary['diaryid'] . ',';
        }
        if (!empty($diaryStr)) {
            $diaryStr = substr($diaryStr, 0, -1);
        }
        return $diaryStr;
    }

    /**
     * 根据日志id取得所有附件Id
     * @param mixed $diaryIds 日志ids
     * @return string 附件ids 逗号分割的字符串
     */
    public function fetchAllAidByPks($diaryIds)
    {
        $ids = is_array($diaryIds) ? implode(',', $diaryIds) : trim($diaryIds, ',');
        $records = $this->fetchAll(array('select' => array('attachmentid'), 'condition' => "diaryid IN($ids)"));
        $result = array();
        foreach ($records as $record) {
            if (!empty($record['attachmentid'])) {
                $result[] = trim($record['attachmentid'], ',');
            }
        }
        return implode(',', $result);
    }

    /**
     * 根据日志id取得所属uid
     * @param integer $diaryId 日志id
     * @return integer
     */
    public function fetchUidByDiaryId($diaryId)
    {
        $diary = $this->fetchByPk($diaryId);
        return !empty($diary) ? intval($diary['uid']) : 0;
    }

    /**
     * 取得共享日志总评论
     * @param integer $uid 用户ID
     * @return integer
     */
    public function countCommentByUid($uid, $curUid)
    {
        $sql = "SELECT count(diaryid) as sum FROM {{diary}} WHERE uid={$uid} AND isreview=1 and FIND_IN_SET('{$curUid}', `shareuid`)";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        $sum = empty($record[0]['sum']) ? 0 : $record[0]['sum'];
        return $sum;
    }

    /**
     * 取得当前用户的总评论数
     * @param integer $uid 用户ID
     * @return integer
     */
    public function countCommentByReview($uid)
    {
        $sql = "SELECT count(diaryid) as sum FROM {{diary}} WHERE uid=$uid AND isreview=1";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        $sum = empty($record[0]['sum']) ? 0 : $record[0]['sum'];
        return $sum;
    }

    // refactor begin
    // by banyanCheung
    /**
     * 统计指定用户指定日期范围内的日志数
     * @param mixed $uid 单个用户ID或数组
     * @param integer $start 开始范围
     * @param integer $end 结束范围
     * @return integer
     */
    public function countDiaryTotalByUid($uid, $start, $end)
    {
        $uid = is_array($uid) ? implode(',', $uid) : $uid;
        $rs = $this->getDbConnection()->createCommand()
            ->select('count(diaryid)')
            ->from($this->tableName())
            ->where(sprintf("uid IN ('%s') AND diarytime BETWEEN %d AND %d", $uid, $start, $end))
            ->queryScalar();
        return $rs ? intval($rs) : 0;
    }

    /**
     * 统计用户被评阅总数
     * @param integer $uid
     * @return integer
     */
    public function countReviewTotalByUid($uid, $start, $end)
    {
        $rs = $this->getDbConnection()->createCommand()
            ->select('count(diaryid)')
            ->from($this->tableName())
            ->where(sprintf("isreview = 1 AND uid = %d AND diarytime BETWEEN %d AND %d", $uid, $start, $end))
            ->queryScalar();
        return $rs ? intval($rs) : 0;
    }

    /**
     * 统计指定用户的未评阅数
     * @param mixed $uid 用户ID
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return integer
     */
    public function countUnReviewByUids($uid, $start, $end)
    {
        is_array($uid) && $uid = implode(',', $uid);
        $rs = $this->getDbConnection()->createCommand()
            ->select('count(diaryid)')
            ->from($this->tableName())
            ->where(sprintf("isreview = 0 AND uid IN ('%s') AND diarytime BETWEEN %d AND %d", $uid, $start, $end))
            ->queryScalar();
        return $rs ? intval($rs) : 0;
    }

    /**
     * 统计指定用户指定时间内的日志提交准时率
     * @param integer $uid 用户ID
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return integer
     */
    public function countOnTimeRateByUid($uid, $start, $end)
    {
        $criteria = array(
            'select' => 'diarytime,addtime',
            'condition' => sprintf("uid = %d AND addtime BETWEEN %d AND %d", $uid, $start, $end)
        );
        $datas = $this->fetchAll($criteria);
        $diaryNums = count($datas);
        if ($diaryNums > 0) {
            $notOnTime = 0;
            foreach ($datas as $diary) {
                if ($diary['addtime'] - $diary['diarytime'] > 86400) {
                    $notOnTime++;
                }
            }
            if ($notOnTime > 0) {
                return round((1 - $notOnTime / $diaryNums) * 100);
            } else {
                return 100;
            }
        }
        return 0;
    }

    /**
     * 获取指定用户指定时间范围内的日志添加时间
     * @param integer $uid 用户ID
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return array
     */
    public function fetchAddTimeByUid($uid, $start, $end)
    {
        is_array($uid) && $uid = implode(',', $uid);
        $criteria = array(
            'select' => 'diarytime,addtime,uid',
            'condition' => sprintf("FIND_IN_SET(uid,'%s') AND diarytime BETWEEN %d AND %d", $uid, $start, $end)
        );
        return $this->fetchAll($criteria);
    }

    /**
     * 获取指定日志ID范围内的日志添加时间
     * @param mixed $diaryIds 日志ID
     * @return array
     */
    public function fetchAddTimeByDiaryId($diaryIds)
    {
        is_array($diaryIds) && $diaryIds = implode(',', $diaryIds);
        $criteria = array(
            'select' => 'diaryid,addtime',
            'condition' => sprintf("FIND_IN_SET(diaryid,'%s')", $diaryIds)
        );
        return $this->fetchAllSortByPk('diaryid', $criteria);
    }

    public function checkUidIsShared($uid, $diaryid)
    {
        $row = $this->findByPk($diaryid);
        $shareuidS = $row->attributes['shareuid'];
        if (!empty($shareuidS)) {
            $shareuidA = explode(',', $shareuidS);
        } else {
            $shareuidA = array();
        }
        if (in_array($uid, $shareuidA)) {
            return true;
        } else {
            return false;
        }
    }

}
