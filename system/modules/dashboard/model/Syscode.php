<?php

/**
 * syscode表的数据层文件
 *
 * @author Ring <Ring@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  syscode表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class Syscode extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{syscode}}';
    }

    public function fetchAllByAllPid()
    {
        $return = array();
        $roots = $this->fetchAll('`pid` = 0 ORDER BY `sort` ASC');
        foreach ($roots as $root) {
            $root['child'] = $this->fetchAll("`pid` = {$root['id']} ORDER BY `sort` ASC");
            $return[$root['id']] = $root;
        }
        return $return;
    }

    /**
     * 根据Id字符串删除非系统代码
     * @param string $ids
     * @return integer 删除的条数
     */
    public function deleteById($ids)
    {
        $id = explode(',', trim($ids, ','));
        return $this->deleteByPk($id, "`system` = '0'");
    }

    /**
     * 根据父类代码编号返回子类代码信息的数组
     * @param string $number
     * @param string $key 以哪个字段作为下标
     * @return array
     */
    public function fetchSubByPnum($number, $key = 'id', $flag = false)
    {
        $res = array();
        $pData = $this->fetchByNum($number);
        if (!empty($pData)) {
            $res = $this->fetchAll(array(
                'condition' => sprintf('`pid` = %d', $pData['id']),
                'order' => '`sort` ASC'
            ));
        }
        if ($flag === false) {
            foreach ($res as $v) {
                $kVal = $v[$key];
                $res[$kVal] = $v;
            }
        }
        return $res;
    }

    /**
     * 根据代码编号返回代码信息
     * @param string $number 代码编号
     * @param int $pid 默认只查找父类(即pid等于0)
     */
    public function fetchByNum($number, $pid = 0)
    {
        return $this->fetch(sprintf("`pid`=%d AND `number`='%s'", intval($pid), $number));
    }

}
