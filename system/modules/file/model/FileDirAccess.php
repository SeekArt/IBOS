<?php

/**
 * 文件柜模块------ file_company_access表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------  公司文件柜权限设置数据表
 * @package application.modules.file.model
 * @version $Id: FileDirAccess.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;

class FileDirAccess extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file_dir_access}}';
    }

    /**
     * 根据fid集查找所有符合条件的权限数据，以fid作键名数组返回
     * @param mix $fids fid数组或逗号隔开字符串
     * @return array
     */
    public function fetchAllSortByFid($fids)
    {
        $fids = is_array($fids) ? implode(',', $fids) : $fids;
        $record = $this->fetchAll("FIND_IN_SET(`fid`, '{$fids}')");
        $res = array();
        foreach ($record as $r) {
            $res[$r['fid']] = $r;
        }
        return $res;
    }

}
