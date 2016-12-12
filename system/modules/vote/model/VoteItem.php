<?php

/**
 * 投票模块------投票项数据表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------投票项数据表操作类
 * @package application.modules.vote.model
 * @version $Id: VoteItem.php 164 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\model;

use application\core\model\Model;
use application\core\utils\StringUtil;

class VoteItem extends Model
{

    /**
     * 文字投票
     */
    const TYPE_TEXT = 1;

    /**
     * 图片投票
     */
    const TYPE_IMG = 2;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{vote_item}}';
    }

    /**
     * 通过投票项id或者投票项id数组修改该投票项票数+1
     * @param mixed $itemids 字符串可以为单个投票项id，也可以是多个投票项id样式为1,2,3,4,5 逗号分割,用数组则为如下样式array(1,2,3,4,5)
     * @return integer 影响的行数，没影响则返回0
     */
    public function updateNumber($itemids)
    {
        $result = 0;
        //如果是单个id
        if (is_numeric($itemids)) {
            $voteItem = $this->findByPk($itemids);
            $result = $this->updateByPk($voteItem['itemid'], array('number' => $voteItem['number'] + 1));
            //如果是数组
        } else if (is_array($itemids)) {
            foreach ($itemids as $itemid) {
                $voteItem = $this->findByPk($itemid);
                $result = $this->updateByPk($itemid, array('number' => $voteItem['number'] + 1));
            }
        } else {
            //逗号分割的字符串
            $itemids = explode(',', rtrim($itemids, ','));
            foreach ($itemids as $itemid) {
                $voteItem = $this->findByPk($itemid);
                $result = $this->updateByPk($itemid, array('number' => $voteItem['number'] + 1));
            }
        }
        return $result;
    }

    /**
     * 添加一条记录
     *
     * @param integer $voteId
     * @param integer $topicId
     * @param string $content
     * @param int $number
     * @param string $picPath
     * @return bool|mixed
     */
    public function addRecord($voteId, $topicId, $content, $number = 0, $picPath = '')
    {
        $voteItem = new self();
        $voteItem->voteid = filter_var($voteId, FILTER_SANITIZE_NUMBER_INT);
        $voteItem->topicid = filter_var($topicId, FILTER_SANITIZE_NUMBER_INT);
        $voteItem->content = StringUtil::filterCleanHtml($content);
        $voteItem->number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
        $voteItem->picpath = StringUtil::filterCleanHtml($picPath);


        if ($voteItem->save()) {
            return $voteItem->itemid;
        }

        return false;
    }

    /**
     * 获取所有题目项目
     *
     * @param $topicId
     * @return array
     */
    public function fetchItems($topicId)
    {
        $topicId = filter_var($topicId, FILTER_SANITIZE_NUMBER_INT);

        $criteria = new \CDbCriteria();
        $criteria->addCondition('topicid = :topicid');
        $criteria->params[':topicid'] = $topicId;
        $criteria->order = 'topicid ASC';
        return $this->fetchAll($criteria);
    }


    /**
     * 判断投票项目是否存在
     *
     * @param integer $voteId
     * @param integer $topicId
     * @param integer $itemId
     * @return bool
     */
    public function isExists($voteId, $topicId, $itemId)
    {
        return $this->exists('voteid = :voteid AND topicid = :topicid AND itemid = :itemid', array(
            ':voteid' => $voteId,
            ':topicid' => $topicId,
            ':itemid' => $itemId,
        ));
    }


    /**
     * 通过 voteId 删除所有投票项目
     *
     * @param integer $voteId
     * @return bool
     * @throws \CDbException
     */
    public function delAll($voteId)
    {
        $voteId = filter_var($voteId, FILTER_VALIDATE_INT);

        $voteItems = $this->findAll('voteid = :voteid', array(':voteid' => $voteId));
        foreach ($voteItems as $item) {
            $item->delete();
        }

        return true;
    }

    /**
     * 投票项 += 1
     *
     * @param $itemId
     * @return bool
     */
    public function incrementVoteNum($itemId)
    {
        $voteItem = $this->findByPk($itemId);

        if (empty($voteItem)) {
            return false;
        }

        $voteItem->number += 1;
        return $voteItem->save();
    }

    /**
     * 增加投票项
     * @param array $data 表单提交数据
     * @param integer $voteId
     * @param string $type
     */
    public function addVoteItem($data, $voteId, $type) {
        foreach ($data['voteItem'] as $key => $value) {
            $voteItem = array(
                'voteid' => $voteId,
                'type' => $type,
                'content' => $value
            );
            if ($type == 1 && !empty($value)) { // 文字投票，去掉内容为空的条目
                $this->add($voteItem);
            } else if ($type == 2) { // 图片投票，只添加有图片或者有内容的条目
                if (!empty($data['picpath'][$key]) || !empty($value)) {
                    $voteItem['picpath'] = $data['picpath'][$key];
                    $this->add($voteItem);
                }
            }
        }
    }
}
