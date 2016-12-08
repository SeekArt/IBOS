<?php

namespace application\modules\email\model;

use application\core\model\Model;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Database;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\email\utils\Email as EmailUtil;

class EmailBody extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{email_body}}';
    }

    /**
     *
     * 检查邮件是否已经存在，用于解决外部邮件重复接收问题
     * 使用发送时间和发送人来判断是否重复
     *
     * @param integer $sendtime 发送时间戳
     * @param string $fromwebmail 发送人邮箱地址
     * @return boolean 已经存在返回true，否则返回false
     */
    public static function isExist($sendtime, $fromwebmail)
    {
        $result = Ibos::app()->db->createCommand()
            ->select('bodyid')
            ->from('{{email_body}}')
            ->where('`sendtime` = ' . $sendtime . ' AND `fromwebmail` = ' . "'" . $fromwebmail . "'")
            ->queryRow();
        if ($result && !empty($result)) {
            return true;
        }
        return false;
    }

    /**
     * 删除草稿
     * @param string $bodyIds 邮件主体ID
     * @param integer $archiveId 分类存档表ID
     * @return integer 影响的行数
     */
    public function delBody($bodyIds, $archiveId = 0)
    {
        $table = sprintf('{{%s}}', $this->getTableName($archiveId));
        $bodys = Ibos::app()->db->createCommand()
            ->select('attachmentid')
            ->from($table)
            ->where("FIND_IN_SET(bodyid,'{$bodyIds}')")
            ->queryAll();
        $attachIds = Convert::getSubByKey($bodys, 'attachmentid');
        $attachId = StringUtil::filterStr(implode(',', $attachIds));
        if (!empty($attachId)) {
            Attach::delAttach($attachId);
        }
        return Ibos::app()->db->createCommand()->delete($table, "FIND_IN_SET(bodyid,'{$bodyIds}')");
    }

    /**
     * 对邮件主体的再处理
     * @param array $data 初始邮件主体数据
     * @return array 处理之后的邮件主体数据
     */
    public function handleEmailBody($data)
    {
        $data['toids'] = implode(',', StringUtil::getId($data['toids']));
        $data['sendtime'] = TIMESTAMP;
        $data['isneedreceipt'] = isset($data['isneedreceipt']) ? 1 : 0;
        // 是否有抄送/密送
        if (empty($data['isOtherRec'])) {
            $data['copytoids'] = $data['secrettoids'] = '';
        } else {
            $data['copytoids'] = implode(',', StringUtil::getId($data['copytoids']));
            $data['secrettoids'] = implode(',', StringUtil::getId($data['secrettoids']));
        }
        // 是否包括外部收件人
        if (empty($data['isWebRec'])) {
            $data['towebmail'] = '';
        }
        if (!isset($data['fromwebmail'])) {
            $data['fromwebmail'] = '';
        }
//		isset( $data['isremind'] ) && $data['isremind'] = 1;
        !empty($data['attachmentid']) && $data['attachmentid'] = StringUtil::filterStr($data['attachmentid']);
        // 邮件与附件大小
        $data['size'] = EmailUtil::getEmailSize($data['content'], $data['attachmentid']);
        return $data;
    }

    /**
     * 移动存档方法
     * @param array $bodyIds
     * @param integer $source
     * @param integer $target
     * @return boolean|integer
     */
    public function moveByBodyId($bodyIds, $source, $target)
    {
        $source = intval($source);
        $target = intval($target);
        if ($source != $target) {
            $db = Ibos::app()->db->createCommand();
            $text = sprintf("REPLACE INTO {{%s}} SELECT * FROM {{%s}} WHERE bodyid IN ('%s')", $this->getTableName($target), $this->getTableName($source), implode(',', $bodyIds));
            $db->setText($text)->execute();
            return $db->delete(sprintf('{{$s}}', $this->getTableName($source)), "FIND_IN_SET(bodyid,'" . implode(',', $bodyIds) . ")");
        } else {
            return false;
        }
    }

    /**
     * 根据存档表id获取存档表名
     * @param integer $tableId 存档表id
     * @return string
     */
    public function getTableName($tableId = 0)
    {
        $tableId = intval($tableId);
        return $tableId ? "email_body_{$tableId}" : 'email_body';
    }

    /**
     * 获取当前表的状态
     * @param integer $tableId 存档表id
     * @return array
     */
    public function getTableStatus($tableId = 0)
    {
        return Database::getTableStatus($this->getTableName($tableId));
    }

    /**
     * 删除表
     * @param integer $tableId 存档表ID
     * @param boolean $force 强制删除
     * @return boolean
     */
    public function dropTable($tableId, $force = false)
    {
        $tableId = intval($tableId);
        if ($tableId) {
            $rel = Database::dropTable($this->getTableName($tableId), $force);
            if ($rel === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * 创建一个表
     * @param integer $maxTableId
     * @return boolean
     */
    public function createTable($maxTableId)
    {
        if ($maxTableId) {
            return Database::cloneTable($this->getTableName(), $this->getTableName($maxTableId));
        } else {
            return false;
        }
    }

    /**
     * 根据 bodyid 获取一封完整的邮件信息
     * @param  integer $id bodyid
     * @param  integer $archiveId 判断表
     * @return array
     */
    public function fetchById($id, $archiveId = 0)
    {
        $mainTable = Email::model()->getTableName($archiveId);
        $bodyTable = $this->getTableName($archiveId);
        $field = 'eb.bodyid, fromid, toids, copytoids, secrettoids, subject, content, sendtime, attachmentid, issend, ';
        $field .= 'important, size, fromwebmail, towebmail, issenderdel, isneedreceipt, emailid, toid, isread, isdel, ';
        $field .= 'fid, isreceipt, ismark, isweb';
        $email = Ibos::app()->db->createCommand()
            ->select($field)
            ->from('{{' . $bodyTable . '}} eb')
            ->leftJoin('{{' . $mainTable . '}} e', 'eb.bodyid = e.bodyid')
            ->where('eb.bodyid = ' . intval($id))
            ->queryRow();
        return is_array($email) ? $email : array();
    }

    /**
     * debug 718
     * 从已发送的邮箱列表中删除对应的邮件
     * @param string $condition 查询条件
     * @return boolen  处理结果
     */
    public function deleteSenderEmail($condition)
    {
        $mainTable = Email::model()->tableName();
        $bodyTable = $this->tableName();
        $sql = sprintf("UPDATE {$bodyTable} SET issenderdel = 1 WHERE %s", $condition);
        return Ibos::app()->db->createCommand($sql)->query() ? true : false;
    }

}
