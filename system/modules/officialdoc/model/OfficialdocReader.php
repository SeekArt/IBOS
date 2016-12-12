<?php

/**
 * 通知模块------ officialDoc_reader表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 通知模块------  officialDoc_reader表的数据层操作类，继承ICModel
 * @package application.modules.officialDoc.model
 * @version $Id: OfficialdocReader.php 117 2013-06-07 09:29:09Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\modules\user\model\User;

class OfficialdocReader extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{doc_reader}}';
    }

    /**
     * 判断用户是否已经读过这篇文章
     * @param int $docid 文章id
     * @param int $uid 用户id
     * @return boolean 返回是否已读
     */
    public function checkIsRead($docid, $uid)
    {
        $result = false;
        $readerInfo = $this->fetch('docid=:docid AND uid=:uid', array(':docid' => $docid, ':uid' => $uid));
        if (!empty($readerInfo)) {
            $result = true;
        }
        return $result;
    }

    /**
     * 获取某个用户所有已读通知id
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchReadArtIdsByUid($uid)
    {
        $record = $this->fetchAll("uid = {$uid}");
        $readDocIds = Convert::getSubByKey($record, 'docid');
        return $readDocIds;
    }

    /**
     * 获取某个用户所有已签收通知id
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchSignArtIdsByUid($uid)
    {
        $record = $this->fetchAll("uid = {$uid} AND issign = 1");
        $signedDocIds = Convert::getSubByKey($record, 'docid');
        return $signedDocIds;
    }

    /**
     * 添加阅读者信息
     * @param integer $docid
     * @param integer $uid
     * @return type
     */
    public function addReader($docid, $uid)
    {
        if ($this->checkIsRead($docid, $uid) == false) {
            $reader = array(
                'docid' => $docid,
                'uid' => $uid,
                'readername' => User::model()->fetchRealnameByUid($uid),
                'addtime' => TIMESTAMP,
            );
            return $this->add($reader);
        }
    }

    /**
     * 通过uid取得所有已签收的docids
     * <pre>
     *        array(1=>15,2=>25...)
     * </pre>
     * @param type $uid
     * @return array
     */
    public function fetchDocidsByUid($uid)
    {
        $result = array();
        $readerList = $this->fetchAll(sprintf("uid=%d AND issign='1'", intval($uid)));
        if (!empty($readerList)) {
            foreach ($readerList as $reader) {
                $result[$reader['readerid']] = $reader['docid'];
            }
        }
        return $result;
    }

    /**
     *
     * @param integer $docid
     * @param integer $uid
     * @return type 修改成功或失败
     */

    /**
     * 通过docid修改签收状态和签收时间
     * @param integer $docid
     * @param integer $uid
     * @param boolean $isMobile 是否是手机端签收的
     * @return integer number of rows affected by the execution
     */
    public function updateSignByDocid($docid, $uid, $isMobile = false)
    {
        $attributes = array(
            'issign' => 1,
            'signtime' => TIMESTAMP,
            'frommobile' => $isMobile ? 1 : 0,
        );
        $condition = 'docid=:docid AND uid=:uid';
        $params = array(':docid' => $docid, ':uid' => $uid);
        return $this->updateAll($attributes, $condition, $params);
    }

    /**
     * 通过通知id和uid查找某个uid对于某篇通知的签收信息
     * @param integer $docid 通知id
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchSignInfo($docid, $uid)
    {
        $record = $this->fetch(array(
            'condition' => 'docid=:docid AND uid=:uid',
            'params' => array(':docid' => $docid, ':uid' => $uid)
        ));
        return $record;
    }

    /**
     * 通过docid取得签收情况0为未签收，1为已签收
     * @param integer $docid
     * @param integer $uid
     * @return int
     */
    public function fetchSignByDocid($docid, $uid)
    {
        $record = $this->fetchSignInfo($docid, $uid);
        if (!empty($record)) {
            return $record['issign'];
        }
        return 0;
    }

    /**
     * 获得某篇通知已签收的数据
     * @param integer $docId 通知id
     * @return array
     */
    public function fetchSignedByDocId($docId)
    {
        $ret = $this->fetchAll(array(
            'condition' => 'docid=:docid AND issign = :issign',
            'params' => array(':docid' => $docId, ':issign' => 1)
        ));
        return $ret;
    }

    /**
     * 获得某篇通知已签收的uid集合
     * @param integer $docId 通知id
     * @return array
     */
    public function fetchSignedUidsByDocId($docId)
    {
        $ret = $this->fetchSignedByDocId($docId);
        $signedUids = Convert::getSubByKey($ret, 'uid');
        return $signedUids;
    }

    /**
     * 根据docid删除阅读记录
     * @param integer $docid
     */
    public function deleteReaderByDocIds($docids)
    {
        return $this->deleteAll("FIND_IN_SET(docid,'$docids')");
    }

}
