<?php

/**
 * nav表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  nav表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Ibos;

class Nav extends Model
{

    public function init()
    {
        $this->cacheLife = 0;
        parent::init();
    }

    /**
     * @param string $className
     * @return Nav
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{nav}}';
    }

    public function afterSave()
    {
        CacheUtil::update('Nav');
        CacheUtil::load('Nav');
        parent::afterSave();
    }

    /**
     * 查找所有的导航设置并以父子形式返回数组
     * @return array
     */
    public function fetchAllByAllPid()
    {

        $all = Ibos::app()->db->createCommand()
            ->select('*')
            ->from($this->tableName())
            ->order(" pid ASC,sort ASC ")
            ->queryAll();
        $result = array();
        foreach ($all as $v) {
            $result[$v['id']] = $v;
        }
        foreach ($result as $key => &$row) {
            //判断父级是否存在，不存在就提出到顶级
            if (!isset($result[$row['pid']])) {
                $row['pid'] = 0;
                $row['child'] = array();
            } else {
                //存在就移动到子级
                $result[$row['pid']]['child'][] = $row;
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * 根据Id字符串删除非系统导航记录
     * @param string $ids
     * @return integer 删除的条数
     */
    public function deleteById($ids)
    {
        $id = explode(',', trim($ids, ','));
        $affecteds = $this->deleteByPk($id, "`system` = '0'");
        $affecteds += $this->deleteAll("FIND_IN_SET(pid,'" . implode(',', $id) . "')");
        return $affecteds;
    }

}
