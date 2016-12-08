<?php

/**
 * 招聘模块------ resume_bgchecks表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------  resume_bgchecks表的数据层操作类，继承ICModel
 * @package application.modules.resume.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use CDbCriteria;
use CPagination;


class ResumeBgchecks extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{resume_bgchecks}}';
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
        $fields = 'rd.realname,rb.checkid,rb.resumeid,rb.company,rb.position,rb.entrytime,rb.quittime';
        $sql = "SELECT {$fields} FROM {{resume_bgchecks}} rb LEFT JOIN {{resume_detail}} rd ON rb.resumeid=rd.resumeid ";
        if (!empty($condition)) {
            $sql .= " WHERE " . $condition;
        }

        $sql .= " LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array('pagination' => $pagination, 'data' => $records);
    }

    /**
     * 根据搜索条件取得总记录数
     * @return integer
     */
    public function countBySearchCondition($condition)
    {
        $whereCondition = " WHERE " . $condition;
        $sql = "SELECT COUNT(rb.checkid) AS number FROM {{resume_bgchecks}} rb LEFT JOIN {{resume_detail}} rd ON rb.resumeid=rd.resumeid {$whereCondition}";
        $record = $this->getDbConnection()->createCommand($sql)->queryAll();
        return $record[0]['number'];
    }

    /**
     * 根据背景调查id返回简历id
     * @param int $checkid 背景调查id
     * @return int 返回简历id
     */
    public function fetchResumeidByCheckid($checkid)
    {
        $bgcheck = $this->fetchByPk($checkid);
        return $bgcheck['resumeid'];
    }

}
