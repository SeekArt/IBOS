<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\modules\user\model\User;

class MessageUser extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{message_user}}';
    }

    /**
     * 获取指定私信列表中的成员信息
     * @param integer $listId 私信列表ID
     * @param string $field 私信成员表中的字段
     * @return array 指定私信列表中的成员信息
     */
    public function getMessageUsers($listId, $field = null)
    {
        $listId = intval($listId);
        static $users = array();
        if (!isset($users[$listId])) {
            $criteria = array(
                'select' => $field,
                'condition' => "`listid`={$listId}"
            );
            $users[$listId] = $this->fetchAll($criteria);
            foreach ($users[$listId] as $userListKey => $userListValue) {
                $users[$listId][$userListKey]['user'] = User::model()->fetchByUid($userListValue['uid']);
            }
        }

        return $users[$listId];
    }

    /**
     * 设置指定用户所有私信为已读
     * @param integer $uid 成员用户ID
     * @param integer $val 要设置的值
     * @return boolean 是否设置成功
     */
    public function setMessageAllRead($uid, $val = 0)
    {
        $condition = 'uid = ' . intval($uid);
        $updateRows = $this->updateAll(array('new' => $val), $condition);
        return !!$updateRows;
    }

    /**
     * 设置指定用户指定私信为已读
     * @param integer $uid 成员用户ID
     * @param array $listIds 私信列表ID数组
     * @param integer val 要设置的值
     * @return boolean 是否设置成功
     */
    public function setMessageIsRead($uid, $listIds = null, $val = 0)
    {
        $condition = 'uid = ' . intval($uid);
        if (!empty($listIds)) {
            !is_array($listIds) && $listIds = explode(',', $listIds);
            $condition .= ' AND `listid` IN (' . implode(',', $listIds) . ')';
        } else {
            $condition .= ' AND `new` = 2';
        }
        $updateRows = $this->updateAll(array('new' => $val), $condition);
        return !!$updateRows;
    }

    /**
     * 指定用户删除指定的私信列表
     * @param integer $uid 用户ID
     * @param string $listId 逗号分隔的私信列表ID
     * @return boolean 是否删除成功
     */
    public function deleteMessageByListId($uid, $listId)
    {
        if (!$listId || !$uid) {
            return false;
        }
        $res = $this->updateAll(array('messagenum' => 0), "FIND_IN_SET(listid,'{$listId}') AND uid = {$uid}");
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

}
