<?php

namespace application\modules\recruit\model;

use application\core\model\Model;

class ResumeStats extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{resume_statistics}}';
    }

    /**
     * 获得某段时间的人才流动数据
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return array
     */
    public function fetchAllByTime($start, $end)
    {
        return $this->fetchAll(array(
            'select' => '*',
            'condition' => sprintf("datetime BETWEEN %d AND %d", $start, $end),
            'order' => 'datetime ASC'
        ));
    }

    /**
     * 更新招聘人才流动数据
     * @param  string $field 更新的字段名
     * $field = new|pending|interview|employ|eliminate
     * @param  string $oldField 旧字段名，默认空
     * 默认不对旧字段做处理，如果赋值了的话会对对应字段的数据减一操作
     * 当简历状态变更操作在同一天进行的话，需要将原简历状态下的统计数据减一再对新状态的统计数据加一
     * @return boolean          true | false
     */
    public function updateState($field, $oldField = '')
    {
        $fieldList = array('new', 'pending', 'interview', 'employ', 'eliminate');
        if (!in_array($field, $fieldList))
            return false;

        $datetime = strtotime(date('Y-m-d', time()));
        $state = $this->find('`datetime` = ' . $datetime);
        if (empty($state))
            $result = $this->add(array($field => 1, 'datetime' => $datetime));
        else {
            if (!empty($oldField) && in_array($oldField, $fieldList) && $oldField !== $field)
                $result = $this->updateAll(array($field => $state->$field + 1, $oldField => $state->$oldField - 1), sprintf("`datetime` = %d", $datetime));
            else
                $result = $this->updateAll(array($field => $state->$field + 1), sprintf("`datetime` = %d", $datetime));
        }
        return !!$result;
    }

}
