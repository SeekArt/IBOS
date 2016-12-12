<?php

/**
 * contact表的数据层操作文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * contact表的数据层操作类
 *
 * @package application.modules.contact.model
 * @version $Id: Contact.php 2733 2014-03-11 16:10:35Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\contact\model;

use application\core\model\Model;
use application\core\utils\Convert;

/**
 * Class Contact
 *
 * @package application\modules\contact\model
 */
class Contact extends Model
{
    
    /**
     * @param string $className
     * @return static
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{contact}}';
    }
    
    /**
     * 判断某个uid是否是另一个uid的常联系人
     *
     * @param integer $uid uid
     * @param integer $cuid 常联系人uid
     * @return boolean
     */
    public function checkIsConstant($uid, $cuid)
    {
        $record = $this->fetch(array(
            'condition' => 'uid = :uid AND cuid = :cuid',
            'params' => array(':uid' => $uid, 'cuid' => $cuid)
        ));
        if (empty($record)) {
            return false;
        }
        return true;
    }
    
    /**
     * 取得某个uid的所有常联系人
     *
     * @param integer $uid
     * @return array
     */
    public function fetchAllConstantByUid($uid)
    {
        $record = $this->fetchAll(array(
            'condition' => 'uid = :uid',
            'params' => array(':uid' => $uid)
        ));
        return Convert::getSubByKey($record, 'cuid');
    }
    
    /**
     * 添加常联系人
     *
     * @param integer $uid uid
     * @param integer $cuid 常联系人uid
     */
    public function addConstant($uid, $cuid)
    {
        $this->add(array('uid' => $uid, 'cuid' => $cuid));
    }
    
    /**
     * 取消某个常联系人
     *
     * @param integer $uid uid
     * @param integer $cuid 常联系人uid
     */
    public function deleteConstant($uid, $cuid)
    {
        $this->deleteAll(array(
            'condition' => 'uid = :uid AND cuid = :cuid',
            'params' => array(':uid' => $uid, ':cuid' => $cuid)
        ));
    }
    
}
