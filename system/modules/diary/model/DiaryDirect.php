<?php
/**
 * 设置是否只看直属下属-----diary_direct表操作类文件
 * @encoding UTF-8
 * @author php_lxy
 * @link http://www.ibos.com.cn/
 * @copyright &copy; 2012-2016 IBOS Inc
 * @date Date: 2016/10/26 15:31
 */

namespace application\modules\diary\model;

use application\core\model\Model;

class DiaryDirect extends Model
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{diary_direct}}';
    }
}