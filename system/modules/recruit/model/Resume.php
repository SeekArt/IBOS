<?php

/**
 * 招聘模块------ Resume数据表操作类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------  Resume数据表操作类
 * @package application.modules.recruit.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use CDbCriteria;
use CPagination;

class Resume extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{resume}}';
    }

    /**
     * 取出简历数据数组集合，分页显示
     * @param type $conditions
     * @param type $offset
     * @param type $pageSize
     * @return type
     */
    public function fetchAllByPage($conditions = '', $pageSize = null)
    {

        $pages = new CPagination($this->countByCondition($conditions));
        $pageSize = is_null($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pages->setPageSize(intval($pageSize));
        $criteria = new CDbCriteria(array('limit' => $pages->getLimit(), 'offset' => $pages->getOffset()));
        $pages->applyLimit($criteria);

        //双表查询
        $fields = "r.resumeid,rd.detailid,rd.realname,rd.positionid,rd.gender,rd.birthday,rd.education,rd.workyears,r.flag,r.status";
        $sql = "SELECT $fields FROM {{resume}} r LEFT JOIN {{resume_detail}} rd ON r.resumeid=rd.resumeid ";
        if (!empty($conditions)) {
            $sql .= " WHERE " . $conditions;
        }

        $offset = $pages->getOffset();
        $limit = $pages->getLimit();

        $sql .= " ORDER BY r.entrytime DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array('pages' => $pages, 'datas' => $records);
    }

    /**
     * 取得当前id的上一个id和下一个Id,以数组形式返回
     * @param integer $resumeid PK
     * @return array
     */
    public function fetchPrevAndNextPKByPK($resumeid)
    {
        $nextPK = $prevPK = 0;
        //取得当前id是第几条记录
        $sql = "SELECT resumeid FROM {{resume}} WHERE resumeid<$resumeid ORDER BY resumeid ASC LIMIT 1";
        $nextRecord = $this->getDbConnection()->createCommand($sql)->queryAll();
        if (!empty($nextRecord)) {
            $nextPK = $nextRecord[0]['resumeid'];
        }

        $sql2 = "SELECT resumeid FROM {{resume}} WHERE resumeid>$resumeid ORDER BY resumeid DESC LIMIT 1";
        $prevRecord = $this->getDbConnection()->createCommand($sql2)->queryAll();
        if (!empty($prevRecord)) {
            $prevPK = $prevRecord[0]['resumeid'];
        }
        return array('prevPK' => $prevPK, 'nextPK' => $nextPK);
    }

    /**
     * 通过PK修改一个字段的值
     * @param integer $PK ID
     * @param string $field 字段
     * @param string $value 值
     */
    public function updateFieldValueByPK($PK, $field, $value)
    {
        return $this->modify($PK, array($field => $value));
    }

    /**
     * 根据条件取得总记录数
     */
    public function countByCondition($condition = '')
    {
        if (!empty($condition)) {
            $whereCondition = " WHERE " . $condition;
            $sql = "SELECT COUNT(r.resumeid) AS number FROM {{resume}} r LEFT JOIN {{resume_detail}} rd ON r.resumeid=rd.resumeid $whereCondition";
            $record = $this->getDbConnection()->createCommand($sql)->queryAll();
            return $record[0]['number'];
        } else {
            return $this->count();
        }
    }

    /**
     * 通过PK取得状态
     * @param integer $resumeid
     * @return mixed
     */
    public function fetchStatusByResumeid($resumeid)
    {
        $record = $this->fetch(array(
            'select' => array('status'),
            'condition' => 'resumeid=:resumeid',
            'params' => array(':resumeid' => $resumeid)
        ));
        if (count($record) > 0) {
            return $record['status'];
        } else {
            return '';
        }
    }

    /**
     * 统计指定状态的简历数
     * @param mixed $status 简历状态
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return integer
     */
    public function countByStatus($status, $start, $end)
    {
        is_array($status) && $status = implode(',', $status);
        return $this->getDbConnection()->createCommand()
            ->select('count(resumeid)')
            ->from($this->tableName())
            ->where(sprintf("FIND_IN_SET(`status`,'%s') AND entrytime BETWEEN %d AND %d", $status, $start, $end))
            ->queryScalar();
    }

    /**
     * 获得指定时间内的所有简历id
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return string
     */
    public function fetchAllByTime($start, $end)
    {
        $resumes = $this->getDbConnection()->createCommand()
            ->select('resumeid')
            ->from($this->tableName())
            ->where(sprintf("entrytime BETWEEN %d AND %d", $start, $end))
            ->queryAll();
        $resumeidArr = Convert::getSubByKey($resumes, 'resumeid');
        return implode(',', $resumeidArr);
    }

    /**
     * 待安排数
     * @return integer
     */
    public function countArramge()
    {
        return Resume::model()->count("status = 4");
    }

    /**
     * 面试数
     * @return integer
     */
    public function countAudition()
    {
        return Resume::model()->count("status = 1");
    }

    /**
     * 标记数
     * @return integer
     */
    public function countFlag()
    {
        return Resume::model()->count("flag = 1");
    }

}
