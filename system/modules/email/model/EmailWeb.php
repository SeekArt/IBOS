<?php

namespace application\modules\email\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;

class EmailWeb extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{email_web}}';
    }

    /**
     * 获取本地已存储的所有远程邮件
     * @param type $toid
     * @param type $webid
     * @param type $boxid
     * @param type $offset
     * @param type $limit
     * @return type
     */
    /* public function fetchAllWebEmail( $toid, $webid, $boxid, $offset, $limit ) {
      $field = 'e.emailid, e.isread, eb.fromid, eb.subject, eb.sendtime, eb.important, e.ismark, eb.attachmentid';
      $join = 'LEFT JOIN {{email_body}} eb ON e.bodyid = eb.bodyid';
      $sql = "SELECT $field FROM {{email}} e $join WHERE eb.fromwebid='$webid' AND e.isdel=0 AND " . 'e.toid=' . $toid . ' AND e.isweb=1' . ' AND e.boxid=' . $boxid;
      $sql .= " ORDER BY eb.sendtime DESC LIMIT " . $offset . "," . $limit;
      $records = $this->getDbConnection()->createCommand( $sql )->queryAll();
      return $records;
      } */

    // refactor

    /**
     * 根据uid获取所有数据
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchAllByUid($uid)
    {
        $data = array(
            'condition' => "uid = $uid",
            'order' => 'isdefault DESC',
        );
        return $this->fetchAllSortByPk('webid', $data);
    }

    /**
     * 彻底删除外部邮箱
     * @param string $id
     * @return type
     */
    public function delClear($id, $uid)
    {
        $fidArr = Ibos::app()->db->createCommand()
            ->select('fid')
            ->from($this->tableName())
            ->where("FIND_IN_SET(webid,'{$id}') AND uid = {$uid}")
            ->queryAll();
        $fids = Convert::getSubByKey($fidArr, 'fid');
        if (!empty($fids)) {
            $fid = implode(',', $fids);
            Ibos::app()->db->createCommand()->delete('{{email_folder}}', "FIND_IN_SET(fid,'{$fid}') AND uid = {$uid}");
            Ibos::app()->db->createCommand()->update('{{email}}', array('fid' => 1), "FIND_IN_SET(fid,'{$fid}') AND toid = {$uid}");
            return $this->deleteAll("FIND_IN_SET(webid,'{$id}')");
        } else {
            return 0;
        }
    }

    /**
     * 列表页取数据
     * @param integer $uid 用户ID
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    public function fetchByList($uid, $offset = 0, $limit = 10)
    {
        $list = $this->fetchAll(array(
                'condition' => 'uid = ' . intval($uid),
                'offset' => intval($offset),
                'limit' => intval($limit)
            )
        );
        return $list;
    }

}
