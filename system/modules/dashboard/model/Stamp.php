<?php

/**
 * stamp表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  stamp表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\File;
use application\core\utils\Convert;

class Stamp extends Model
{

    const STAMP_PATH = 'data/stamp/'; // 图章存放地址

    /**
     * 允许缓存
     * @var boolean
     */

    protected $allowCache = true;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{stamp}}';
    }

    /**
     * 返回最大排序号
     * @return integer
     */
    public function getMaxSort()
    {
        $record = $this->fetch(array('order' => 'sort DESC', 'select' => 'sort'));
        return !empty($record) ? intval($record['sort']) : 0;
    }

    /**
     * 获取图章地址
     * @param integer $id
     * @return string
     */
    public function fetchStampById($id)
    {
        $stamp = $this->findByPk($id);
        return $stamp['stamp'];
    }

    /**
     * 获取图标地址
     * @param integer $id
     * @return string
     */
    public function fetchIconById($id)
    {
        $stamp = $this->findByPk($id);
        return $stamp['icon'];
    }

    /**
     * 采用静态缓存方法封装
     * @staticvar array $stamps
     * @param type $pk
     * @return array
     */
    public function fetchByPk($pk)
    {
        static $stamps = array();
        if (!isset($stamps[$pk])) {
            $stamps[$pk] = parent::fetchByPk($pk);
        }
        return $stamps[$pk];
    }

    /**
     * 删除图章记录里的图像文件
     * @param integer $id 图章ID
     * @param string $index 文件类型的索引
     */
    public function delImg($id, $index = '')
    {
        $stamp = $this->fetchByPk($id);
        if (!empty($stamp[$index])) {
            if (File::fileExists($stamp[$index])) {
                File::deleteFile($stamp[$index]);
            }
        }
    }

    /**
     * 根据ID删除记录与图章和图标
     * @param array $ids id数组
     * @param string $stampPath 图章与图标所在路径
     * @return void
     */
    public function deleteByIds($ids)
    {
        $id = $files = array();
        foreach ($ids as $removeId) {
            $record = $this->fetchByPk($removeId);
            if (!empty($record['stamp'])) {
                $files[] = $record['stamp'];
            }
            if (!empty($record['icon'])) {
                $files[] = $record['icon'];
            }
            $id[] = $record['id'];
        }
        $this->deleteByPk($id);
        foreach ($files as $file) {
            if (File::fileExists($file)) {
                File::deleteFile($file);
            }
        }
    }

    /**
     * 获取所有图章id
     * @return array
     */
    public function fetchAllIds()
    {
        $stamps = $this->fetchAll();
        $ids = Convert::getSubByKey($stamps, 'id');
        return $ids;
    }

}
