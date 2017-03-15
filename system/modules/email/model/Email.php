<?php

/**
 * Email model class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 邮件主表模型
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.email.model
 * @version $Id$
 */

namespace application\modules\email\model;

use application\core\model\Model;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Database;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\email\controllers\BaseController;
use application\modules\main\model\Attachment;
use application\modules\message\model\Notify;
use application\modules\thread\utils\Thread as ThreadUtil;
use application\modules\user\model\User;
use CHtml;

class Email extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{email}}';
    }

    /**
     * 获取上一封邮件
     * @param integer $id 当前邮件ID
     * @param integer $uid 当前用户ID
     * @param integer $fid 文件夹ID
     * @param integer $archiveId 存档表ID
     * @return array
     */
    public function fetchPrev($id, $uid, $fid, $archiveId = 0)
    {
        $condition = sprintf('e.fid = %d AND toid = %d AND eb.issend = 1 AND e.isdel = 0 AND e.emailid > %d', $fid, $uid, $id);
        $order = 'emailid ASC';
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    /**
     * 获取下一封邮件
     * @param integer $id 当前邮件ID
     * @param integer $uid 当前用户ID
     * @param integer $fid 文件夹ID
     * @param integer $archiveId 存档表ID
     * @return array
     */
    public function fetchNext($id, $uid, $fid, $archiveId = 0)
    {
        $condition = sprintf('e.fid = %d AND toid = %d AND eb.issend = 1 AND e.isdel = 0 AND e.emailid < %d', $fid, $uid, $id);
        $order = 'emailid DESC';
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    /*
     * 如果是已删除的状态的上一封和下一封
     */
    public function fetchNextDel($id, $uid, $archiveId = 0, $isdel = 0, $issend = 1)
    {
        $condition = sprintf('toid = %d AND eb.issend = %d AND e.isdel = %d AND e.emailid < %d', $uid, $issend, $isdel, $id);
        $order = 'emailid DESC';
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    public function fetchPrevDel($id, $uid, $archiveId = 0, $isdel = 0, $issend = 1)
    {
        $condition = sprintf('toid = %d AND eb.issend = %d AND e.isdel = %d AND e.emailid > %d', $uid, $issend, $isdel, $id);
        $order = 'emailid ASC';
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    /*
     * 如果是已发送的状态的上一封和下一封
     */
    public function fetchNextSend($id, $uid, $archiveId = 0, $isdel = 0, $issend = 1)
    {
        $condition = sprintf('fromid = %d AND eb.issend = %d AND e.isdel = %d AND e.emailid < %d', $uid, $issend, $isdel, $id);
        $order = 'emailid DESC';
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    public function fetchPrevSend($id, $uid, $archiveId = 0, $isdel = 0, $issend = 1)
    {
        $condition = sprintf('fromid = %d AND eb.issend = %d AND e.isdel = %d AND e.emailid > %d', $uid, $issend, $isdel, $id);
        $order = 'emailid ASC';
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    /**
     * 查找一条完整的email数据
     * @param integer $id 邮件索引ID
     * @param integer $archiveId 存档表ID
     * @return array
     */
    public function fetchById($id, $archiveId = 0)
    {
        $mainTable = $this->getTableName($archiveId);
        $bodyTable = EmailBody::model()->getTableName($archiveId);
        $email = Ibos::app()->db->createCommand()
            ->select('*')
            ->from('{{' . $mainTable . '}} e')
            ->leftJoin('{{' . $bodyTable . '}} eb', 'e.bodyid = eb.bodyid')
            ->where('emailid = ' . intval($id))
            ->queryRow();
        return is_array($email) ? $email : array();
    }

    public function fetchAllBodyIdByKeywordFromAttach($keyword, $whereAdd = '1', $queryArchiveId = 0)
    {
        $kwBodyIds = array();
        //查询附件名，返回相关附件信息
        $queryParam = "uid = " . Ibos::app()->user->uid;
        $kwAttachments = Attachment::model()->fetchAllByKeywordFileName($keyword, $queryParam);
        if (!empty($kwAttachments)) {
            // 思路：把这些含关键字的附件ID求出与邮件中的附件ID交集，把交集中的邮件ID添加到sql条件中
            // 取出附件的ID
            $kwAids = array_keys($kwAttachments);
            //查找含有附件的邮件
            $emailData = $this->fetchAllByArchiveIds('e.*,eb.*,', "{$whereAdd} AND attachmentid!=''", $queryArchiveId);
            foreach ($emailData as $email) {
                //求名字中含有关键字的附件的ID与邮件的附件ID交集
                if (array_intersect($kwAids, explode(',', $email['attachmentid']))) {
                    //记录该邮件的bodyid
                    $kwBodyIds[] = $email['bodyid'];
                }
            }
        }
        return $kwBodyIds;
    }

    /**
     * 设置指定$uid的邮件为已读
     * @param integer $uid 用户ID
     * @return integer 更新的行数
     */
    public function setAllRead($uid)
    {
        return $this->setField('isread', 1, 'toid = ' . intval($uid));
    }

    /**
     * 设置指定id的邮件为已读
     * @param integer $id
     * @return integer 更新的行数
     */
    public function setRead($id)
    {
        return $this->setField('isread', 1, 'emailid = ' . intval($id));
    }

    /**
     * 更新email表字段值
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param string $conditions 更新条件
     * @return integer 更新的行数
     */
    public function setField($field, $value, $conditions = '')
    {
        return $this->updateAll(array($field => $value), $conditions);
    }

    /**
     * 发送邮件
     * @param integer $bodyId 邮件主体ID
     * @param array $bodyData 邮件主体
     * @param integer $inboxId 收件箱ID
     */
    public function send($bodyId, $bodyData, $inboxId = BaseController::INBOX_ID, $threadId = 0)
    {
        // 所有用户ID集合
        $toids = $bodyData['toids'] . ',' . $bodyData['copytoids'] . ',' . $bodyData['secrettoids'];
        $toid = StringUtil::filterStr($toids);
        foreach (explode(',', $toid) as $uid) {
            $email = array(
                'toid' => $uid,
                'fid' => $inboxId,
                'bodyid' => $bodyId,
            );
            $newId = $this->add($email, true);
            // 发送提醒处理
            // DEBUG:: 效率问题。可能会发生在推送QQ提醒时 by banyan
            $file = Ibos::getPathOfAlias('application.modules.email.views.remindcontent') . '.php';
            extract(array('body' => $bodyData), EXTR_PREFIX_SAME, 'data');
            ob_start();
            ob_implicit_flush(false);
            require($file);
            $content = ob_get_clean();
            $config = array(
                '{sender}' => Ibos::app()->user->realname,
                '{subject}' => html_entity_decode($bodyData['subject'], ENT_QUOTES, CHARSET),
                '{url}' => Ibos::app()->urlManager->createUrl('email/content/show', array('id' => $newId)),
                '{content}' => $content,
                '{orgContent}' => StringUtil::filterCleanHtml($bodyData['content']),
                'id' => $newId,
            );
            Notify::model()->sendNotify($uid, 'email_message', $config);
            // 是否关联主线
            if ($threadId) {
                $fromUid = Ibos::app()->user->uid;
                $dynamic = Ibos::lang('Relative thread', '', array(
                    '{realname}' => User::model()->fetchRealnameByUid($uid),
                    '{url}' => Ibos::app()->urlManager->createUrl('email/content/show', array('id' => $newId)),
                    '{subject}' => $bodyData['subject']
                ));
                ThreadUtil::getInstance()->relative($fromUid, $threadId, 'email', $newId, $dynamic);
            }
        }
    }

    public function recall($emailIds, $uid)
    {
        $emails = $this->fetchAll(sprintf("FIND_IN_SET( `bodyid`, '%s' )", $emailIds));
        $ids = array();
        foreach ($emails as $email) {
            if (!$email['isread']) {
                $ids[] = $email['emailid'];
            }
        }
        if (!empty($ids)) {
            return $this->completelyDelete($ids, $uid);
        }
        return 0;
    }

    /**
     * 彻底删除邮件及其主体
     * @param array $emailIds
     * @return integer 删除条数
     */
    public function completelyDelete($emailIds, $uid, $archiveId = 0)
    {
        $isSuccess = 0;
        $emailIds = is_array($emailIds) ? $emailIds : array($emailIds);
        $mainTable = sprintf('{{%s}}', $this->getTableName($archiveId));
        $bodyTable = sprintf('{{%s}}', EmailBody::model()->getTableName($archiveId));
        $this->setDeleteBodyId($mainTable, $emailIds);
        $bodyIds = Ibos::app()->db->createCommand()
            ->select('bodyid')
            ->from($mainTable)
            ->where("FIND_IN_SET(emailid,'" . implode(',', $emailIds) . "')")
            ->queryAll();
        if ($bodyIds) {
            $bodyIds = Convert::getSubByKey($bodyIds, 'bodyid');
        }
        foreach ($bodyIds as $i => $bodyId) {
            $body = Ibos::app()->db->createCommand()->select('fromid,attachmentid')
                ->from($bodyTable)
                ->where("bodyid = {$bodyId} AND fromid = {$uid}")
                ->queryRow();
            if ($body || !isset($emailIds[$i])) {
                // 将邮件移到草稿箱
                Ibos::app()->db->createCommand()->update($bodyTable, array("issend" => 0), "bodyid = :bodyid", array(":bodyid" => $bodyId));

                if (isset($emailIds[$i])) {
                    $readerRows = Ibos::app()->db->createCommand()->select('bodyid')
                        ->from($mainTable)
                        ->where("emailid = $emailIds[$i] AND isread != 0 AND toid != {$uid}")
                        ->queryRow();
                } else {
                    $readerRows = false;
                }
                if ($readerRows) {
                    if (Ibos::app()->db->createCommand()->update($bodyTable, array('issenderdel' => 1), 'bodyid = ' . $bodyId)) {
                        $isSuccess = 1;
                    }
                } else {
                    if (isset($emailIds[$i])) {
                        $nextStep = Ibos::app()->db->createCommand()->delete($mainTable, 'emailid = ' . $emailIds[$i]);
                    } else {
                        Ibos::app()->db->createCommand()->delete($bodyTable, 'bodyid = ' . $bodyId);
                        $nextStep = true;
                    }
                    if ($nextStep) {
                        if ($body['attachmentid'] !== '') {
                            Attach::delAttach($body['attachmentid']);
                        }
                        $isSuccess = 1;
                    }
                }
            } else {
                $lastRows = Ibos::app()->db->createCommand()->select('toid')
                    ->from($mainTable)
                    ->where("bodyid = {$bodyId} AND toid != {$uid}")
                    ->queryRow();
                if (!$lastRows) { //如果是最后一个收件人,删除
                    Ibos::app()->db->createCommand()->delete($mainTable, 'emailid = ' . $emailIds[$i]);
                    $attachmentId = Ibos::app()->db->createCommand()
                        ->select('attachmentid')
                        ->from($bodyTable)
                        ->where('bodyid = ' . $bodyId)
                        ->queryScalar();
                    if ($attachmentId && $attachmentId !== '') {
                        Attach::delAttach($attachmentId);
                    }
                    $isSuccess++;
                } else { //否则只删除emailid
                    Ibos::app()->db->createCommand()->delete($mainTable, "emailid = {$emailIds[$i]} AND toid = {$uid}");
                    $isSuccess++;
                }
            }
            $this->verifyIsDeleteData($bodyId, $archiveId);
        }
        if ($isSuccess > 0) {
            $this->deleteWebEmail($bodyTable);
        }
        return $isSuccess;
    }

    /**
     * 处理 completelyDelete() 方法中逻辑漏洞
     * 每次删除操作结束后根据 bodyid 检查一遍这封邮件是否同时被所有收件方以及发件方删除
     * 是的话删除该条邮件数据记录
     * @param  string $bodyId 邮件 ID
     * @param  integer $archiveId 判断表
     * @return void
     * @author isakura@yumisakura.cn
     */
    public function verifyIsDeleteData($bodyId, $archiveId = 0)
    {
        $mainTable = sprintf('{{%s}}', $this->getTableName($archiveId));
        $bodyTable = sprintf('{{%s}}', EmailBody::model()->getTableName($archiveId));
        $bodyRow = EmailBody::model()->find(
            array(
                'select' => 'toids, copytoids, secrettoids',
                'condition' => "FIND_IN_SET(`bodyid`, :bodyid) AND issenderdel = 1",
                'params' => array(':bodyid' => $bodyId),
            )
        );
        if (!empty($bodyRow)) {
            $toids = !empty($bodyRow->toids) ? $bodyRow->toids : '';
            $toids .= !empty($bodyRow->copytoids) ? ',' . $bodyRow->copytoids : '';
            $toids .= !empty($bodyRow->secrettoids) ? ',' . $bodyRow->secrettoids : '';
            $row = Ibos::app()->db->createCommand()
                ->select('emailid')
                ->from($mainTable)
                ->where(array(
                    'AND',
                    sprintf("FIND_IN_SET(`toid`, '%s')", $toids),
                    sprintf("FIND_IN_SET(`bodyid`, '%s')", $bodyId)
                ))
                ->queryRow();
            if (empty($row)) {
                Ibos::app()->db->createCommand()->delete($bodyTable, sprintf("FIND_IN_SET(`bodyid`, '%s')", $bodyId));
            }
        }
    }

    /**
     * 根据文件夹id和uid获取邮件ID
     * @param integer $fid 文件夹
     * @param integer $uid 用户ID
     * @return array 邮件ID数组
     */
    public function fetchAllEmailIdsByFolderId($fid, $uid)
    {
        $record = $this->fetchAllByAttributes(array('fid' => $fid, 'toid' => $uid), array('select' => 'emailid'));
        $emailIds = Convert::getSubByKey($record, 'emailid');
        return $emailIds;
    }

    /**
     * 根据条件搜索指定的一个或者多个邮件表中的邮件
     * @param string $conditions
     * @param mixed $tids 表ID [int][array]
     * @author denglh
     */
    public function fetchAllByArchiveIds($field = '*', $conditions = '', $archiveId = 0, $tableAlias = array('e', 'eb'), $offset = null, $length = null, $order = SORT_DESC, $sort = 'sendtime')
    {
        $aidList = is_array($archiveId) ? $archiveId : array($archiveId);
        $emailData = array();
        //声明一个数组记录已查询的tid
        $queryTable = array();
        foreach ($aidList as $aid) {
            $emailTableName = $this->getTableName($aid);
            $emailbodyTableName = EmailBody::model()->getTableName($aid);
            if (in_array($emailTableName, $queryTable)) {
                continue;
            }
            $list = Ibos::app()->db->createCommand()
                ->select($field)
                ->from(sprintf("{{%s}} %s", $emailTableName, $tableAlias[0]))
                ->leftJoin(sprintf("{{%s}} %s", $emailbodyTableName, $tableAlias[1]), "{$tableAlias[0]}.bodyid = {$tableAlias[1]}.bodyid")
                ->group('eb.bodyid')
                ->where($conditions)
                ->queryAll();
            $sortRefer = array();
            $emailFetchData = array();
            foreach ($list as $email) {
                $email['aid'] = $aid;
                $sortRefer[$email['emailid']] = $email[$sort];
                $emailFetchData[] = $email;
            }
            $queryTable[] = $emailTableName;
        }
        foreach ($emailFetchData as $emailInfo) {
            $emailData[$emailInfo['emailid']] = $emailInfo;
        }
        //根据关键字排序
        array_multisort($sortRefer, $order, $emailData);
        if (!is_null($offset) && !is_null($length)) {
            //截取数组
            $emailData = array_slice($emailData, $offset, $length, false);
        }
        return $emailData;
    }

    /**
     * 根据列表查询参数获得列表数据
     * @param string $operation 列表动作
     * @param integer $uid 用户ID
     * @param integer $fid 文件夹ID
     * @param integer $archiveId 存档表ID
     * @param integer $limit 条数
     * @param integer $offset 当前页
     * @return array
     */
    public function fetchAllByListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $limit = 10, $offset = 0, $subOp = '')
    {
        $param = $this->getListParam($operation, $uid, $fid, $archiveId, false, $subOp);
        if (empty($param['field'])) {
            $param['field'] = 'e.emailid, e.isread, eb.fromid, eb.subject, eb.sendtime, eb.fromwebmail,' .
                'eb.important, e.ismark, eb.attachmentid';
        }
        if (empty($param['order'])) {
            $param['order'] = "eb.sendtime DESC";
        }
        $sql = "SELECT %s FROM %s WHERE %s";
        if (!empty($param['group'])) {
            $sql .= ' GROUP BY ' . $param['group'];
        }
        $sql .= " ORDER BY {$param['order']} LIMIT {$offset},{$limit}";
        $db = Ibos::app()->db->createCommand();
        $list = $db->setText(sprintf($sql, $param['field'], $param['table'], $param['condition']))->queryAll();
        foreach ($list as &$value) {
            if (!empty($value['fromid'])) {
                $value['fromuser'] = User::model()->fetchRealnameByUid($value['fromid']);
            } else {
                $value['fromuser'] = $value['fromwebmail'];
            }
        }
        return (array)$list;
    }

    /**
     * 获取已删除邮件列表时，使用 UNION 合并上被删除的已发送邮件
     * 在 fetchAllByListParam() 方法中被使用到
     * @param string $sql 需要加上的后半段 UNION 语句
     * @param string $field sql 中需要替换掉 SELECT 中的变量
     * @param integer $archiveId 判断表
     * @author isakura@yumisakura.cn
     */
    private function addSenderDeledEmail($sql, $field, $archiveId = 0)
    {
        $mainTable = $this->getTableName($archiveId);
        $bodyTable = EmailBody::model()->getTableName($archiveId);
        return sprintf($sql, $field);
    }

    /**
     * 根据列表数据获取指定动作未读邮件数
     * @param string $operation 列表动作
     * @param integer $uid 用户ID
     * @param integer $fid 文件夹ID
     * @param integer $archiveId 存档表ID
     * @return integer 统计数
     */
    public function countUnreadByListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $subOp = '')
    {
        $param = $this->getListParam($operation, $uid, $fid, $archiveId, true, $subOp);
        return $this->countListParam($param);
    }

    /**
     * 根据列表查询参数统计总数
     * @param string $operation 列表动作
     * @param integer $uid 用户ID
     * @param integer $fid 文件夹ID
     * @param integer $archiveId 存档表ID
     * @return integer 统计数
     */
    public function countByListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $subOp = '')
    {
        $param = $this->getListParam($operation, $uid, $fid, $archiveId, false, $subOp);
        return $this->countListParam($param);
    }

    /**
     * 执行查询列表参数操作
     * @param array $param 列表查询参数
     * @return integer
     */
    private function countListParam($param)
    {
        if (empty($param['field'])) {
            $param['field'] = 'emailid';
        }
        if (empty($param['order'])) {
            $param['order'] = "eb.sendtime DESC";
        }
        $sql = "SELECT COUNT(%s) as count FROM %s WHERE %s";
        if (!empty($param['group'])) {
            $sql .= ' GROUP BY ' . $param['group'];
        }
        $result = Ibos::app()->db->createCommand()
            ->setText(sprintf($sql, $param['field'], $param['table'], $param['condition']))
            ->queryAll();
        //含有gourp by的分组统计返回一个多维数组，每个数组含有每个分组的条数（邮件的主体对应多少封邮件）
        //但是我们只需要知道有多少个多维数组（邮件主体）就可以了
        //而不含有group by的统计只返回一个包含所有主体数的邮件，所以这两种情况要分开处理
        $count = count($result) == 1 ? $result[0]['count'] : count($result);
        return intval($count);
    }

    /**
     * 获取列表查询参数
     * @param string $operation 列表动作
     * @param integer $uid 用户ID
     * @param integer $fid 文件夹ID
     * @param integer $archiveId 存档表ID
     * @return array 列表查询参数数组
     */
    public function getListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $getUnread = false, $subOp = '')
    {
        if (!$uid) {
            $uid = Ibos::app()->user->uid;
        }
        $mainTable = $this->getTableName($archiveId);
        $bodyTable = EmailBody::model()->getTableName($archiveId);
        $param = array(
            'field' => '',
            'table' => "{{{$mainTable}}} e LEFT JOIN {{{$bodyTable}}} eb on e.bodyid = eb.bodyid",
            'condition' => $getUnread ? 'e.isread = 0 AND ' : '',
            'order' => '',
            'group' => '',
        );
        switch ($operation) {
            case 'inbox': // 收件箱
                $param['condition'] .= "e.toid ='{$uid}' AND e.fid ='1' AND e.isdel ='0' AND e.isweb = '0'";
                break;
            case 'todo': // 待办邮件
                $param['condition'] .= "e.toid ='{$uid}' AND e.isdel = 0 AND e.ismark = 1";
                break;
            case 'draft':// 草稿箱
                $param['field'] = '*';
                $param['table'] = "{{{$bodyTable}}} eb";
                $param['condition'] = "eb.fromid = '{$uid}' AND eb.issend != 1";
                break;
            case 'send': // 发件箱
                $param['field'] = '*';
                $param['table'] = "{{{$bodyTable}}} eb";
                $param['condition'] = "eb.fromid = '{$uid}' AND eb.issenderdel != 1 AND eb.issend = 1";
                break;
            case 'archive':// 存档邮件
                if ($archiveId && $subOp) {
                    //存在归档表
                    if ($subOp == 'in') {
                        $param['condition'] .= "e.toid ='{$uid}' AND e.fid = 1 AND e.isdel = 0";
                    } elseif ($subOp == 'send') {
                        $param['field'] = "*";
                        $param['group'] = 'eb.bodyid';
                        $param['condition'] .= "eb.fromid = '{$uid}' AND eb.issend = 1 AND e.fid = 1 AND eb.issenderdel != 1";
                    }
                    break;
                }
            case 'del': // 已删除
                $param['condition'] .= "e.toid ='{$uid}' AND (e.isdel = 3 OR e.isdel = 4 OR e.isdel = 1)";
                $param['group'] = 'eb.bodyid';
                break;
            case 'folder': // 个人文件夹
                if ($fid) {
                    $param['condition'] .= "(e.toid='{$uid}' OR eb.fromid='{$uid}') AND e.fid = {$fid} AND e.isdel !=3";
                    break;
                }
            case 'web' :
                $param['condition'] .= "e.toid ='{$uid}' AND e.isdel =0 AND eb.issend = 1 AND e.isweb = 1";
                break;
            default:
                $param['condition'] .= '1=2';
                break;
        }
        return $param;
    }

    /**
     * debug
     * @param array $emailids
     * @param integer $source
     * @param integer $target
     * @return boolean|integer
     */
    public function moveByBodyId($emailids, $source, $target)
    {
        $source = intval($source);
        $target = intval($target);
        if ($source != $target) {
            $db = Ibos::app()->db->createCommand();
            $text = sprintf("REPLACE INTO {{%s}} SELECT * FROM {{%s}} WHERE bodyid IN ('%s')", $this->getTableName($target), $this->getTableName($source), implode(',', $emailids));
            $db->setText($text)->execute();
            return $db->delete($this->getTableName($source), "FIND_IN_SET(bodyid,'" . implode(',', $emailids) . ")");
        } else {
            return false;
        }
    }

    /**
     * 获取所有存档表的id
     * @return array
     */
    public function fetchTableIds()
    {
        $tableIds = array('0' => 0);
        $name = $this->getTableSchema()->name;
        $tables = Ibos::app()->db->createCommand()
            ->setText("SHOW TABLES LIKE '" . str_replace('_', '\_', $this->tableName() . '_%') . "'")
            ->queryAll(false);
        foreach ($tables as $table) {
            $tableName = $table[0];
            preg_match('/^' . $name . '_([\d])+$/', $tableName, $match);
            if (empty($match[1])) {
                continue;
            } else {
                $tableId = intval($match[1]);
            }
            $tableIds[$tableId] = $tableId;
        }
        return $tableIds;
    }

    /**
     *
     * @param type $conditions
     * @return type
     */
    public function getSplitSearchContdition($conditions)
    {
        $whereArr = array();
        if (!empty($conditions['emailidmin'])) {
            $whereArr[] = 'e.emailid >= ' . $conditions['emailidmin'];
        }
        if (!empty($conditions['emailidmax'])) {
            $whereArr[] = 'e.emailid <= ' . $conditions['emailidmax'];
        }
        if (!empty($conditions['timerange'])) {
            // 计算时间（几个月以前）
            $timeRange = TIMESTAMP - (intval($conditions['timerange']) * 86400 * 30);
            $whereArr[] = 'b.sendtime <= ' . $timeRange;
        }
        $whereSql = !empty($whereArr) && is_array($whereArr) ? implode(' AND ', $whereArr) : '';
        return $whereSql;
    }

    /**
     * 统计分表存档时的数据条数
     * @param integer $tableId 存档表ID
     * @param string $conditions 附加条件
     * @return integer 统计数目
     */
    public function countBySplitCondition($tableId, $conditions = '')
    {
        $condition = $this->mergeSplitCondition($conditions);
        $db = Ibos::app()->db->createCommand();
        $count = $db->select('COUNT(*)')
            ->from('{{' . $this->getTableName($tableId) . '}} e')
            ->rightJoin('{{' . EmailBody::model()->getTableName($tableId) . '}} b', 'e.`bodyid` = b.`bodyid`')
            ->where($condition)
            ->queryScalar();
        return intval($count);
    }

    /**
     * 查找分表存档的数据列表
     * @param integer $tableId 存档表ID
     * @param string $conditions 附加条件
     * @param integer $offset 页数
     * @param integer $limit 每页多少条
     * @return array 列表数据
     */
    public function fetchAllBySplitCondition($tableId, $conditions = '', $offset = null, $limit = null)
    {
        $condition = $this->mergeSplitCondition($conditions);
        $db = Ibos::app()->db->createCommand();
        $list = $db->select('e.emailid,b.fromid,b.subject,b.sendtime,b.bodyid')
            ->from('{{' . $this->getTableName($tableId) . '}} e')
            ->rightJoin('{{' . EmailBody::model()->getTableName($tableId) . '}} b', 'e.`bodyid` = b.`bodyid`')
            ->where($condition)
            ->order('sendtime ASC')
            ->offset($offset)
            ->limit($limit)
            ->queryAll();
        return $list;
    }

    /**
     * 根据存档表id获取存档表名
     * @param integer $tableId 存档表id
     * @return string
     */
    public function getTableName($tableId = 0)
    {
        $tableId = intval($tableId);
        return $tableId > 0 ? "email_{$tableId}" : 'email';
    }

    /**
     * 获取指定存档表的状态
     * @param integer $tableId 存档表id
     * @return array
     */
    public function getTableStatus($tableId = 0)
    {
        return Database::getTableStatus($this->getTableName($tableId));
    }

    /**
     * 删除表
     * @param integer $tableId 存档表id
     * @param boolean $force 强制删除
     * @return boolean 删除成功与否
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
     * 私有方法，合并存档分表的查询条件，返回组合后的条件
     * @param string $conditions
     * @return string
     */
    private function mergeSplitCondition($conditions = '')
    {
        $conditions .= strpos($conditions, 'WHERE') ? ' AND' : '';
        //附加公共条件，待办邮件和未读邮件不能移动
        $conditions .= ' e.`ismark`=0 AND e.`isread`=1 AND b.`bodyid` IS NOT NULL';
        //附加子条件
        $addition = array();
        $addition[] = 'e.`fid` = 1 AND e.`isdel` = 0';  //收件箱
        $addition[] = 'e.`fid` = 1 AND b.`issend` = 1 AND b.`issenderdel` != 1'; //已发送
        $addition[] = 'b.`issend` = 1 AND b.`issenderdel` != 1 AND b.`towebmail`!=\'\''; //外发邮件
        //连接条件
        $conditions .= ' AND ((' . implode(') OR (', $addition) . '))';
        return $conditions;
    }

    /**
     * 根据查询条件获取邻近的邮件数据
     * @param string $condition
     * @param integer $archiveId
     * @return array
     */
    private function getSiblingsByCondition($condition, $order, $archiveId = 0)
    {
        $siblings = Ibos::app()->db->createCommand()
            ->select('e.emailid,eb.subject')
            ->from(sprintf('{{%s}} e', $this->getTableName($archiveId)))
            ->leftJoin(sprintf('{{%s}} eb', EmailBody::model()->getTableName($archiveId)), 'e.bodyid = eb.bodyid')
            ->where($condition)
            ->order($order)
            ->limit(1)
            ->queryRow();
        return $siblings ? $siblings : array();
    }

    /**
     * 被彻底删除的外部邮件id
     * @var array
     */
    protected $bodyIds = array();

    /**
     * 保存被彻底删除的外部邮件id
     * @param string $mainTable
     * @param array $emailIds
     */
    private function setDeleteBodyId($mainTable, $emailIds)
    {
        $this->bodyIds = Ibos::app()->db->createCommand()
            ->select('bodyid')
            ->from($mainTable)
            ->where('isweb=1 AND isdel=3')
            ->andwhere(array('in', 'emailid', $emailIds))
            ->queryColumn();
    }

    /**
     * 彻底删除外部邮件
     * @param type $mainTable
     * @param type $bodyTable
     * @param type $emailIds
     */
    private function deleteWebEmail($bodyTable)
    {
        if (!empty($this->bodyIds)) {
            Ibos::app()->db->createCommand()
                ->delete($bodyTable, array('in', 'bodyid', $this->bodyIds));
        }
    }

    /**
     * 根据邮件 ID 数组获取对应的邮件体 ID 数组
     * @param  array $emailIdList 邮件 ID 数组
     * @return array              邮件体 ID 数组
     */
    public function fetchBodyIdListByEmailIdList($emailIdList)
    {
        $bodyIdList = array();
        if (is_array($emailIdList)) {
            $emailIdList = implode(',', $emailIdList);
        }
        $emailList = $this->findAll(array('condition' => 'FIND_IN_SET( `emailid`, :emailIdList )', 'params' => array(':emailIdList' => $emailIdList)));
        foreach ($emailList as $email) {
            if (!in_array($email['bodyid'], $bodyIdList)) {
                $bodyIdList[] = $email['bodyid'];
            }
        }
        return $bodyIdList;
    }


    /**
     * 通用搜索
     *
     * @param $uid integer 用户id
     * @param $op string 操作
     * @param int $aid 存储表id（archiveId）
     * @return CDbCommand 返回 CDbCommand 对象
     */
    public function commonSearch($uid, $op, $aid = 0)
    {
        if ($op == 'folder') {
            $fid = Ibos::app()->session['fid'];
        }
        // 参数处理
        $uid = (int)$uid;
        $aid = (int)$aid;

        $emailTable = $this->getTableName($aid);
        $emailAlias = "e";
        $emailbodyTable = EmailBody::model()->getTableName($aid);
        $emailbodyAlias = "eb";

        $command = Ibos::app()->db->createCommand();
        $condition = "";

        $command = $command->select("*")
            ->from(sprintf("{{%s}} %s", $emailbodyTable, $emailbodyAlias))
            ->leftjoin(sprintf("{{%s}} %s", $emailTable, $emailAlias), "{$emailAlias}.`bodyid` = {$emailbodyAlias}.`bodyid`")
            ->group("{$emailbodyAlias}.bodyid")
            ->order("{$emailbodyAlias}.bodyid DESC");

        switch ($op) {
            case 'inbox':
                // 内部收件箱
                //$param['condition'] .= "e.toid ='{$uid}' AND e.fid ='1' AND e.isdel ='0' AND e.isweb = '0'";
                //$condition .= "  {$emailAlias}.`toid` = {$uid} ";
                $condition .= "{$emailAlias}.`toid` = {$uid} AND {$emailAlias}.`fid` = 1 AND {$emailAlias}.`isdel` = 0 AND {$emailAlias}.`isweb` = 0";
                break;
            case 'todo':
                // 代办邮件
                $condition .= "  {$emailAlias}.`toid` = {$uid} AND {$emailAlias}.`ismark` = 1 ";
                break;
            case 'del':
                // 已删除邮件,这个isdel我看到数据库删除的时候对应的值是3
                $condition .= "  {$emailAlias}.`toid` = {$uid} AND {$emailAlias}.`isdel` = 3 ";
                break;
            case "draft":
                // 草稿箱
                $condition .= "  {$emailbodyAlias}.`fromid` = {$uid} AND {$emailbodyAlias}.`issend` = 0 ";
                break;
            case "send":
                // 已发送
                $condition .= "  {$emailbodyAlias}.`fromid` = {$uid} AND {$emailbodyAlias}.`issend` = 1 ";
                break;
            case "web":
                //外部邮件
                $condition .= "{$emailAlias}.`isweb` = 1";
                break;
            case "folder":
                $condition .= "{$emailAlias}.`fid` = {$fid}";
                break;
            default:
                break;
        }

        return $command->andWhere($condition);
    }

    // 获取高级搜索需要的条件
    public function getAdvancedSearchCondition($search)
    {
        $condition = "";

        // 关键字
        //添加对keyword的转义，防止SQL错误
        $keyword = CHtml::encode(stripcslashes($search['keyword']));
        // 查询位置
        $pos = isset($search['pos']) ? $search['pos'] : 'all';
        // 文件夹
        $folder = isset($search['folder']) ? $search['folder'] : 0;
        $setAttach = isset($search['attachment']) && $search['attachment'] !== '-1';
        if ($folder == 'allbynoarchive') {//全部邮件（不含归档）
            $queryArchiveId = 0;
            $folder = 0;
        } elseif ($folder == 'all') {//全部邮件（包含归档）
            $ids = Ibos::app()->setting->get('setting/emailtableids');
            $queryArchiveId = $ids;
            $folder = 0;
        } elseif (strpos($folder, 'archive_') !== false) { //某一个归档
            $queryArchiveId = intval(preg_replace('/^archive_(\d+)/', '\1', $folder));
            //重置文件夹
            $folder = 0;
        } else { //某一个文件夹（不含归档）
            $queryArchiveId = 0;
            $folder = intval($folder);
        }
        //搜索的时候也应该转义然后搜索，不然找不到
        // StringUtil::ihtmlSpecialCharsUseReference( $keyword );
        // 搜索位置条件
        $allPos = ($pos == 'all');
        $posWhereJoin = $allPos ? ' OR ' : ' AND ';
        $posWhere = '';
        if ($pos == 'content' || !empty($pos)) {
            if (($pos == 'subject' || $allPos) && $keyword) {  //标题
                $posWhere .= $posWhereJoin . "eb.subject LIKE '%{$keyword}%'";
            }
            if ($pos == 'content' || $allPos) {  //邮件正文
                $posWhere .= $posWhereJoin . "eb.content LIKE '%{$keyword}%'";
            }
            if ($pos == 'attachment' || $allPos) {  //附件名
                $containAttach = isset($search['attachment']) && $search['attachment'] !== '0'; // 是否设置包含附件
                $kwBodyIds = $this->fetchAllBodyIdByKeywordFromAttach($keyword, $condition, $queryArchiveId);
                if (!$allPos && (!$containAttach || count($kwBodyIds) == 0)) { // 高级模式下，不包含附件或者没找到数据就直接返回
                    return array('condition' => "1=0", 'archiveId' => $queryArchiveId); // 找不到直接返回
                } else {
                    $posWhere .= $posWhereJoin . 'FIND_IN_SET(eb.bodyid,\'' . implode(',', $kwBodyIds) . '\')';
                }
            } //end 搜索附件名
            if ($allPos) {
                $condition .= ' AND  (' . preg_replace('/^' . $posWhereJoin . '/', '', $posWhere) . ')';
            } else {
                $condition .= $posWhere;
            }
        }
        // 文件夹条件
        if ($folder) {
            if ($folder == 1) {
                $condition .= " AND (e.fid = 1 AND e.isdel = 0)";
            } elseif ($folder == 3) {
                $condition .= " AND (eb.issend = 1 AND eb.issenderdel != 1 AND e.isweb=0)";
            } else {
                $condition .= " AND (e.fid = {$folder} AND e.isdel = 0)";
            }
        }
        // 时间范围条件
        if (isset($search['dateRange']) && $search['dateRange'] !== '-1') {
            $dateRange = intval($search['dateRange']);
            $endTime = TIMESTAMP;
            $startTime = strtotime("- {$dateRange}day", $endTime);
            $condition .= " AND (eb.sendtime BETWEEN {$startTime} AND {$endTime})";
        }
        // 邮件读取状态
        if (isset($search['readStatus']) && $search['readStatus'] !== '-1') {
            $readStatus = intval($search['readStatus']);
            $condition .= " AND e.isread = {$readStatus}";
        }
        // 设置附件
        if ($setAttach) {
            if ($search['attachment'] == '0') {
                $condition .= " AND eb.attachmentid = ''";
            } else if ($search['attachment'] == '1') {
                $condition .= " AND eb.attachmentid != ''";
            }
        }
        // 发件人与收件人
        if (isset($search['sender']) && !empty($search['sender'])) {
            $sender = StringUtil::getUid($search['sender']);
            $condition .= " AND eb.fromid = " . implode(',', $sender);
        }
        if (isset($search['recipient']) && !empty($search['recipient'])) {
            $recipient = StringUtil::getUid($search['recipient']);
            $condition .= " AND e.toid = " . implode(',', $recipient);
        }


        return $condition;
    }

    /**
     * 普通搜索，仅返回一个 CDbCommand 对象
     *
     * @param $uid integer 用户id
     * @param $op string 操作
     * @param $keyword string 搜索关键字
     * @param int $aid 存储表id（archiveId）
     * @return CDbCommand 返回 CDbCommand 对象
     */
    public function normalSearch($uid, $op, $keyword, $aid = 0, $fid = 0)
    {
        $command = $this->commonSearch($uid, $op, $aid, $fid)
            ->andWhere("`eb`.`subject` LIKE :keyword", array(":keyword" => '%' . $keyword . '%'));
        return $command;
    }

    /**
     * 高级搜索
     *
     * @param $uid int 用户id
     * @param $op string 操作
     * @param $search array 搜索参数
     * @param int $aid $aid 存储表id（archiveId）
     * @return CDbCommand 返回 CDbCommand 对象
     */
    public function advancedSearch($uid, $op, $search, $aid = 0)
    {
        $condition = $this->getAdvancedSearchCondition($search);
        $command = $this->commonSearch($uid, $op, $aid);
        $command->setWhere($command->getWhere() . $condition);
        return $command;
    }

    /**
     * 处理邮件搜索结果
     *
     * @param $emailData array 待处理邮件数据
     * @return array 处理成功后的邮件数据
     */
    public function handleSearchData($emailData)
    {
        if (is_array($emailData)) {
            foreach ($emailData as $k => $email) {
                $emailData[$k]["ismark"] = isset($email["ismark"]) ? $email["ismark"] : 0;
                $emailData[$k]["isdel"] = isset($email["isdel"]) ? $email["isdel"] : 0;
            }
        }
        return $emailData;
    }
}
