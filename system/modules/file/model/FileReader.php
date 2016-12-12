<?php

/**
 * 文件柜模块------ file_reader表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------  共享文件已读信息表
 * @package application.modules.file.model
 * @version $Id: FileReader.php 1371 2014-05-15 09:33:26Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\model;

use application\core\model\Model;

class FileReader extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{file_reader}}';
    }

    /**
     * 记录查看时间
     * @param integer $fromuid 共享人
     * @param integer $uid 登陆者uid
     * @return boolean
     */
    public function record($fromuid, $uid)
    {
        $fromuid = intval($fromuid);
        $uid = intval($uid);
        $this->deleteAll("fromuid={$fromuid} AND uid={$uid}");
        $data = array(
            'fromuid' => $fromuid,
            'uid' => $uid,
            'viewtime' => TIMESTAMP
        );
        return $this->add($data);
    }

}
