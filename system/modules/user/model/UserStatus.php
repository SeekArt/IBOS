<?php

/**
 * user_status表的数据层操作文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * user_status表的数据层操作
 *
 * @package application.app.user.model
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\user\model;

use application\core\model\Model;

class UserStatus extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_status}}';
    }

}
