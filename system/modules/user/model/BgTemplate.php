<?php

/**
 * user模块个人中心背景图model文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * user模块 个人中心背景图model
 *
 * @package application.modules.user.model
 * @version $Id: BgTemplate.php 2177 2014-04-15 09:52:36Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\user\model;

use application\core\model\Model;

class BgTemplate extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{bg_template}}';
    }

    /**
     * 获取所有皮肤背景
     * @return type array
     */
    public function fetchAllBg()
    {
        $bgs = $this->fetchAll(array(
            'order' => 'id ASC'
        ));
        foreach ($bgs as $k => $bg) {
            $bgs[$k]['imgUrl'] = $bg['image'];
        }
        return $bgs;
    }

}
