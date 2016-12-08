<?php

namespace application\modules\report\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\modules\report\utils\Report as ReportUtil;

class ReportStats extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{report_statistics}}';
    }

    /**
     * 统计指定用户指定时间范围内的总结分数
     * @param integer $uid
     * @param integer $start
     * @param integer $end
     * @param integer $typeid
     * @return integer
     */
    public function countScoreByUid($uid, $start, $end, $typeid)
    {
        $score = $this->getDbConnection()->createCommand()
            ->select('SUM(integration)')
            ->from($this->tableName())
            ->where(sprintf("uid = %d AND scoretime BETWEEN %d AND %d AND typeid = %d", $uid, $start, $end, $typeid))
            ->queryScalar();
        return intval($score);
    }

    /**
     *
     * @param type $uid
     * @param type $start
     * @param type $end
     * @return type
     */
    public function fetchAllStampByUid($uid, $start, $end, $typeid)
    {
        $criteria = array(
            'select' => 't.stamp',
            'join' => 'LEFT JOIN ' . Report::model()->tableName() . ' rep ON t.repid = rep.repid',
            'condition' => sprintf("t.uid = %d AND rep.begindate > %d AND rep.enddate < %d AND t.typeid = %d", $uid, $start, $end, $typeid)
        );
        $datas = $this->fetchAll($criteria);
        return Convert::getSubByKey($datas, 'stamp');
    }

    /**
     * 获取指定用户指定时间范围内的积分统计数据
     * @param integer $uid
     * @param integer $start
     * @param integer $end
     * @return array
     */
    public function fetchAllStatisticsByUid($uid, $start, $end, $typeid)
    {
        $criteria = array(
            'condition' => sprintf("uid = %d AND scoretime BETWEEN %d AND %d AND typeid = %d", $uid, $start, $end, $typeid)
        );
        return $this->fetchAllSortByPk('repid', $criteria);
    }

    /**
     * 给一篇总结打分
     * @param integer $repId 总结id
     * @param integer $uid 总结所属uid
     * @param integer $stamp 图章id
     */
    public function scoreReport($repId, $uid, $stamp)
    {
        $record = $this->fetchByAttributes(array('repid' => $repId));
        $attributes = array(
            'repid' => $repId,
            'uid' => $uid,
            'stamp' => $stamp,
            'integration' => ReportUtil::getScoreByStamp($stamp),
            'scoretime' => TIMESTAMP
        );
        // 为空则添加，否则修改分数
        if (empty($record)) {
            $this->add($attributes);
        } else {
            $this->modify($record['id'], $attributes);
        }
    }

}
