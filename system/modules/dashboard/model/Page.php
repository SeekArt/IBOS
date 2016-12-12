<?php

/**
 * page表的数据层文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  page单页图文表
 *
 * @package application.modules.dashboard.model
 * @version $Id: Page.php 862 2014-06-09 01:56:49Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;

class Page extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{page}}';
    }

}
