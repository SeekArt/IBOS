<?php

namespace application\modules\diary\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\modules\diary\utils\Diary as DiaryUtil;

class DiaryStats extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{diary_statistics}}';
    }

    /**
     * 统计指定用户指定时间范围内的日志分数
     * @param integer $uid
     * @param integer $start
     * @param integer $end
     * @return integer
     */
    public function countScoreByUid($uid, $start, $end)
    {
        $criteria = array(
            'select' => "diaryid",
            'condition' => sprintf("uid = %d AND addtime BETWEEN %d AND %d", $uid, $start, $end)
        );
        $res = Diary::model()->fetchAll($criteria);
        $diaids = Convert::getSubByKey($res, 'diaryid');
        $score = $this->getDbConnection()->createCommand()
            ->select('SUM(integration)')
            ->from($this->tableName())
            ->where(sprintf("FIND_IN_SET(diaryid,'%s')", implode(',', $diaids)))
            ->queryScalar();
        return intval($score);
    }

    /**
     *
     * @param integer $uid
     * @param integer $start
     * @param integer $end
     * @return array
     */
    public function fetchAllStampByUid($uid, $start, $end)
    {
        $criteria = array(
            'select' => 'stamp',
            'condition' => sprintf("uid = %d AND scoretime BETWEEN %d AND %d", $uid, $start, $end)
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
    public function fetchAllStatisticsByUid($uid, $start, $end)
    {
        $criteria = array(
            'condition' => sprintf("uid = %d AND scoretime BETWEEN %d AND %d", $uid, $start, $end)
        );
        return $this->fetchAllSortByPk('diaryid', $criteria);
    }

    /**
     * 给一篇日志打分
     * @param integer $diaryId 日志id
     * @param integer $uid 日志所属uid
     * @param integer $stamp 图章id
     */
    public function scoreDiary($diaryId, $uid, $stamp)
    {
        $record = $this->fetchByAttributes(array('diaryid' => $diaryId));
        $attributes = array(
            'diaryid' => $diaryId,
            'uid' => $uid,
            'stamp' => $stamp,
            'integration' => DiaryUtil::getScoreByStamp($stamp),
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
