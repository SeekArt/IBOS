<?php

/**
 * 通知退回中心模块------ officialdoc_back
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzpjh <gzpjh@ibos.com.cn>
 */

/**
 * officialdoc_back 通知退回记录表的数据层操作类，继承ICModel
 * @package application.modules.official.model
 * @version $Id: officialdocBack.php 3479 2014-05-28 03:29:56Z gzpjh $
 * @author gzpjh <gzpjh@ibos.com.cn>
 */

namespace application\modules\officialdoc\model;

use application\core\model\Model;
use application\core\utils\Convert;

class OfficialdocBack extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{doc_back}}';
    }

    /**
     * 添加一条退回记录
     * @param integer $docId 退回的通知id
     * @param integer $uid 操作者uid
     * @param string $reason 退回理由
     * @param integer $time 退回时间
     * @return integer
     */
    public function addBack($docId, $uid, $reason, $time = TIMESTAMP)
    {
        return $this->add(array(
            'docid' => $docId,
            'uid' => $uid,
            'reason' => $reason,
            'time' => $time
        ));
    }

    /**
     * 获得所有退回的通知id数组
     * @return array
     */
    public function fetchAllBackDocId()
    {
        $record = $this->fetchAll();
        return Convert::getSubByKey($record, 'docid');
    }

    /**
     * 根据通知Ids删除退回记录
     * @param type $docids
     * @return type
     */
    public function deleteByDocIds($docids)
    {
        $docids = is_array($docids) ? implode(',', $docids) : $docids;
        return $this->deleteAll("FIND_IN_SET(docid,'{$docids}')");
    }

}
