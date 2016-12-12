<?php

/**
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 */
/**
 * 模块引导记录表
 * @package application.core.model
 * @version $Id: ModuleGuide.php 1821 2014-03-07 17:23:08Z gzhzh $
 */

namespace application\modules\main\model;

use application\core\model\Model;

class ModuleGuide extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{module_guide}}';
    }

    /**
     * 根据引导页面id和uid取得引导数据
     * @param string $route 引导的页面id
     * @param integer $uid 用户id
     * @return type
     */
    public function fetchGuide($route, $uid)
    {
        return $this->fetch(array(
            'condition' => "route = :route AND uid = :uid",
            'params' => array(':route' => $route, ':uid' => $uid)
        ));
    }

}
