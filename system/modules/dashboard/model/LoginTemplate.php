<?php

/**
 * login_template表的数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  login_template表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\File;

class LoginTemplate extends Model
{

    const BG_PATH = 'data/login/';

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{login_template}}';
    }

    /**
     * 采用静态缓存方法封装
     * @staticvar array $stamps
     * @param type $pk
     * @return array
     */
    public function fetchByPk($pk)
    {
        static $tpls = array();
        if (!isset($tpls[$pk])) {
            $tpls[$pk] = parent::fetchByPk($pk);
        }
        return $tpls[$pk];
    }

    /**
     * 删除记录里的背景图像文件
     * @param type $id
     */
    public function delImg($id)
    {
        $tpl = $this->fetchByPk($id);
        if (File::fileExists(self::BG_PATH . $tpl['image'])) {
            File::deleteFile(self::BG_PATH . $tpl['image']);
        }
    }

    /**
     * 根据ID删除记录与背景图片
     * @param array $ids id数组
     * @param string $bgPath 背景图所在路径
     * @return void
     */
    public function deleteByIds($ids)
    {
        $id = $files = array();
        foreach ($ids as $removeId) {
            $record = $this->fetchByPk($removeId);
            if (!empty($record['image'])) {
                $files[] = $record['image'];
            }
            $id[] = $record['id'];
        }
        $this->deleteByPk($id);

        foreach ($files as $file) {
            if (File::fileExists(self::BG_PATH . $file)) {
                File::deleteFile(self::BG_PATH . $file);
            }
        }
    }

}
