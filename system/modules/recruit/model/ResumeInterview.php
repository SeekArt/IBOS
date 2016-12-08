<?php

/**
 * 招聘模块------ resume_interview数据表操作类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------  resume_interview数据表操作类
 * @package application.modules.recruit.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use CDbCriteria;
use CPagination;

class ResumeInterview extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{resume_interview}}';
    }

    /**
     * 取得联系记录集合，分页
     * @param string $condition 条件
     * @param integer $pageSize 分页大小，
     * @return array
     */
    public function fetchAllByPage($condition = '', $pageSize = 0)
    {
        $count = empty($condition) ? $this->count() : $this->countBySearchCondition($condition);
        $pagination = new CPagination($count);
        $pageSize = empty($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pagination->setPageSize($pageSize);

        $offset = $pagination->getOffset();
        $limit = $pagination->getLimit();

        $criteria = new CDbCriteria(array('limit' => $limit, 'offset' => $offset));
        $pagination->applyLimit($criteria);

        //双表查询
        $fields = "rd.realname,ri.*";
        $sql = "SELECT $fields FROM {{resume_interview}} ri LEFT JOIN {{resume_detail}} rd ON ri.resumeid=rd.resumeid ";
        if (!empty($condition)) {
            $sql .= " WHERE " . $condition;
        }

        $sql .= " ORDER BY ri.interviewtime DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array('pagination' => $pagination, 'data' => $records);
    }

    /**
     * 根据条件取得总记录数
     */
    public function countBySearchCondition($condition)
    {
        $whereCondition = " WHERE " . $condition;
        $sql = "SELECT COUNT(ri.resumeid) AS number FROM {{resume_interview}} ri LEFT JOIN {{resume_detail}} rd ON ri.resumeid=rd.resumeid $whereCondition";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        return $record[0]['number'];
    }

    /**
     * 根据面试记录id返回简历id
     * @param int $interviewid 面试记录id
     * @return int 返回简历id
     */
    public function fetchResumeidByInterviewid($interviewid)
    {
        $interview = $this->fetchByPk($interviewid);
        return $interview['resumeid'];
    }

}
