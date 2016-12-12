<?php

/**
 * 招聘模块------ resume_detail数据表操作类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------  resume_detail数据表操作类
 * @package application.modules.recruit.model
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\model;

use application\core\model\Model;
use application\core\utils\Convert;

class ResumeDetail extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{resume_detail}}';
    }

    /**
     * 通过resumeids取出email地址
     * @param string $resumeids
     * @param string $join 连接符
     * @return string $emails 返回以$join连接的字符串
     */
    public function fetchEmailsByResumeids($resumeids, $join = ';')
    {
        $emails = '';
        $select = 'email';
        $condition = "resumeid IN ($resumeids)";
        $data = $this->fetchAll(array('select' => $select, 'condition' => $condition));
        if (count($data) > 0) {
            foreach ($data as $record) {
                if (!empty($record['email'])) {
                    $emails .= $record['email'] . $join;
                }
            }
        }
        return $emails;
    }

    /**
     * 通过查询realname关键字取得字段realname和resumeid
     * @param string $keyword
     * @return array
     */
    public function fetchPKAndRealnameByKeyword($keyword)
    {
        $condition = "realname LIKE '%$keyword%'";
        $records = $this->fetchAll(array(
            'select' => array('resumeid', 'realname'),
            'condition' => $condition,
        ));
        return $records;
    }

    /**
     * 根据简历Id取得真实姓名
     * @param integer $pk
     * @return null
     */
    public function fetchRealnameByResumeid($resumeid)
    {
        $record = $this->fetch(array(
            'select' => array('realname'),
            'condition' => 'resumeid=:resumeid',
            'params' => array(':resumeid' => $resumeid)
        ));
        if (count($record) > 0) {
            return $record['realname'];
        } else {
            return null;
        }
    }

    /**
     * 根据传递的名字取得简历的ID
     * @param string $realname
     * @return int 返回简历ID
     */
    public function fetchResumeidByRealname($realname)
    {
        $record = $this->fetch(array(
            'select' => array('resumeid'),
            'condition' => 'realname = :realname',
            'params' => array(':realname' => $realname)
        ));
        if (count($record) > 0) {
            return $record['resumeid'];
        } else {
            return null;
        }
    }

    /**
     * 关联查找所有简历里存在的姓名，按简历时间排序,返回只有姓名的一维数组
     * @return array
     */
    public function fetchAllRealnames()
    {
        $fields = "r.resumeid,rd.detailid,rd.realname,rd.positionid,rd.gender,r.status";
        $sql = "SELECT $fields FROM {{resume}} r LEFT JOIN {{resume_detail}} rd ON r.resumeid=rd.resumeid ORDER BY r.entrytime DESC";
        $resumes = $this->getDbConnection()->createCommand($sql)->queryAll();
        $realnames = Convert::getSubByKey($resumes, 'realname');
        return $realnames;
    }

    /**
     * 返回所有的简历 detailid、realname 组成的数组
     * @return array
     */
    public function fetchAllRealnamesAndDetailids()
    {
        $fields = "r.resumeid,rd.detailid,rd.realname,rd.positionid,rd.gender,r.status";
        $sql = "SELECT $fields FROM {{resume}} r LEFT JOIN {{resume_detail}} rd ON r.resumeid=rd.resumeid ORDER BY r.entrytime DESC";
        $resumes = $this->getDbConnection()->createCommand($sql)->queryAll();
        foreach ($resumes as $resume) {
            $result[] = array('realname' => $resume['realname'], 'detailid' => $resume['detailid']);
        }
        return isset($result) ? $result : array();
    }

    /**
     * 通过简历id获取某个字段的一维数组
     * @param mix $resumeids
     * @param string $field
     * @return array
     */
    public function fetchFieldByRerumeids($resumeids, $field)
    {
        $resumeids = is_array($resumeids) ? implode(',', $resumeids) : $resumeids;
        $return = $this->fetchAll(array(
            'select' => $field,
            'condition' => "FIND_IN_SET(`resumeid`, '{$resumeids}')",
        ));
        return Convert::getSubByKey($return, $field);
    }

    /**
     * 根据 detailid 获取对应的 realname
     * @param  integer $detailid detail 表主键
     * @return string            realname
     */
    public function fetchRealnameByDetailid($detailid)
    {
        return $this->fetchFieldByDetailid($detailid, 'realname');
    }

    /**
     * 根据 detailid 获取对应的 resumeid
     * @param  integer $detailid detail 表主键
     * @return string            resumeid
     */
    public function fetchResumeidByDetailid($detailid)
    {
        return $this->fetchFieldByDetailid($detailid, 'resumeid');
    }

    /**
     * 根据主键 detailid 获取对应的字段数据
     * @param  integer $detailid 主键 detailid
     * @param  string $field 需要获取的字段名
     * @return string            对应需要获取的字段数据
     */
    public function fetchFieldByDetailid($detailid, $field)
    {
        $resume = $this->findBypK($detailid);
        return !empty($resume) ? $resume[$field] : null;
    }

}
