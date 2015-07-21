<?php

namespace application\modules\recruit\model;

use application\core\model\Model;

class ResumeStats extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{resume_statistics}}';
    }

    /**
     * 获得某段时间的人才流动数据
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return array
     */
    public function fetchAllByTime( $start, $end ) {
        return $this->fetchAll( array(
                    'select' => '*',
                    'condition' => sprintf( "datetime BETWEEN %d AND %d", $start, $end ),
                    'order' => 'datetime ASC'
                ) );
    }

}
