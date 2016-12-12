<?php

/**
 * 工作日志模块------diary_attention表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 工作日志模块------diary_attention表操作类，继承ICModel
 * @package application.modules.diary.model
 * @version $Id: DiaryAttention.php 873 2013-07-25 00:46:15Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\diary\model;

use application\core\model\Model;
use application\core\utils\Convert;

class DiaryAttention extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{diary_attention}}';
    }

    /**
     * 添加关注
     * @param integer $uid 关注人uid
     * @param integer $auid 被关注人uid
     */
    public function addAttention($uid, $auid)
    {
        $this->add(array('uid' => $uid, 'auid' => $auid));
    }

    /**
     * 取消关注
     * @param integer $uid 关注人uid
     * @param integer $auid 被关注人uid
     */
    public function removeAttention($uid, $auid)
    {
        $this->deleteAllByAttributes(array('uid' => $uid, 'auid' => $auid));
    }

    /**
     * 通过uid查找出所有关注的用户uid
     * @param integer $uid 关注人uid
     * @return array 被关注人的uid一维数组
     */
    public function fetchAuidByUid($uid)
    {
        $attentions = $this->fetchAll('uid = :uid', array(':uid' => $uid));
        $aUids = array();
        if (!empty($attentions)) {
            $aUids = Convert::getSubByKey($attentions, 'auid');
        }
        return $aUids;
    }

    /**
     * 通过uid删除关注者
     * @param integer $uid
     */
    public function delAttentionByUid($uid)
    {
        $record = $this->fetch('uid=:uid', array(':uid' => $uid));
        if ($record) {
            $this->deleteAllByAttributes(array('uid' => $uid));
        }
    }

    /**
     * 通过uid增加或者修改关注者
     * @param integer $uid
     * @param array $value
     * @return array 添加成功后的用户ID
     */
    public function addAttentionByUid($uid, $value)
    {
        foreach ($value as $v) {
            $data = array(
                'uid' => $uid,
                'auid' => $v
            );
            $this->add($data);
        }
    }

}