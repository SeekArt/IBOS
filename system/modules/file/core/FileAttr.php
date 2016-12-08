<?php

/**
 * 文件柜模块------ 文件属性类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 文件柜模块------ 继承
 * @package application.modules.file.core
 * @version $Id: FileAttr.php 3297 2014-06-19 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\core;

use CApplicationComponent;

Class FileAttr extends CApplicationComponent
{

    /**
     * 所属文件夹id
     * @var integer
     */
    protected $pid;

    /**
     * 所属用户id
     * @var integer
     */
    protected $uid;

    /**
     * 所属云盘id
     * @var integer
     */
    protected $cloudid;

    /**
     * 所属类型(0为个人，1为公司)
     * @var integer
     */
    protected $belongType;

    public function setPid($pid)
    {
        $this->pid = intval($pid);
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function setUid($uid)
    {
        $this->uid = intval($uid);
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setCloudid($cloudid)
    {
        $this->cloudid = intval($cloudid);
    }

    public function getCloudid()
    {
        return $this->cloudid;
    }

    public function setBelongType($belongType)
    {
        $this->belongType = intval($belongType);
    }

    public function getBelongType()
    {
        return $this->belongType;
    }

}
