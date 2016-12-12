<?php

/**
 * announcement表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  announcement表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class Announcement extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{announcement}}';
    }

    /**
     * 按时间查找一条记录
     * @param integer $timestamp 时间戳
     * @return array
     */
    public function fetchByTime($timestamp)
    {
        $condition = array(
            'order' => 'sort DESC',
            'condition' => '`starttime` <= :timestamp AND `endtime` > :timestamp',
            'params' => array(':timestamp' => $timestamp),
        );
        return $this->fetch($condition);
    }

    /**
     * 列表页取数据
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function fetchAllOnList($limit, $offset)
    {
        $condition = array(
            'order' => 'sort ASC',
            'limit' => $limit,
            'offset' => $offset
        );
        return $this->fetchAll($condition);
    }

    /**
     * 根据Id字符串删除记录
     * @param string $ids
     * @return integer 删除的条数
     */
    public function deleteById($ids)
    {
        $id = explode(',', trim($ids, ','));
        $affecteds = $this->deleteAll("FIND_IN_SET(id,'" . implode(',', $id) . "')");
        return $affecteds;
    }

}
