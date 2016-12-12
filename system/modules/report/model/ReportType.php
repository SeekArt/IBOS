<?php

/**
 * 工作总结与计划模块------report_type表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------report_type表操作类，继承ICModel
 * @package application.modules.report.model
 * @version $Id: ReportType.php 1951 2013-12-17 03:47:48Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\model;

use application\core\model\Model;

class ReportType extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{report_type}}';
    }

    /**
     * 通过用户id查找其所有汇报类型
     * @param  mixed $uids uid数组或者逗号隔开的用户id
     * @return array 返回该用户的汇报类型，包括系统自带类型
     */
    public function fetchAllTypeByUid($uids)
    {
        $ids = is_array($uids) ? implode(',', $uids) : trim($uids, ',');
        $types = $this->fetchAll("uid = 0 OR FIND_IN_SET(uid, '{$ids}') ORDER BY issystype DESC, sort ASC, typeid ASC");
        return $types;
    }

    /**
     * 通过汇报类型id找到汇报区间
     * @param integer $typeid 汇报类型
     * @return integer 返回汇报区间(0:周 1:月 2:季 3:半年 4:年)
     */
    public function fetchIntervaltypeByTypeid($typeid)
    {
        $type = $this->fetchByPk($typeid);
        return $type['intervaltype'];
    }

    /**
     * 获取所有系统汇报类型
     * @return array
     */
    public function fetchSysType()
    {
        return $this->fetchAllByAttributes(array('issystype' => 1));
    }

}
