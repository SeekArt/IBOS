<?php

/**
 * 文件柜模块------ file_cloud_set表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------  云盘验证信息存储表
 * @package application.modules.file.model
 * @version $Id: FileCloudSet.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;

class FileCloudSet extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file_cloud_set}}';
    }

}
