<?php

/**
 * 岗位关联表数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 岗位关联表的数据层操作
 *
 * @package application.modules.position.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\position\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class PositionRelated extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{position_related}}';
    }

    public function countByPositionId($positionId)
    {
        return $this->count('`positionid` = :positionid', array(':positionid' => $positionId));
    }

    /**
     * 根据uid查找辅助岗位ID
     * @staticvar array $uids 用户数组缓存
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchAllPositionIdByUid($uid)
    {
        static $uids = array();
        if (!isset($uids[$uid])) {
            $posids = $this->fetchAll(array('select' => 'positionid', 'condition' => '`uid` = :uid', 'params' => array(':uid' => $uid)));
            $uids[$uid] = Convert::getSubByKey($posids, "positionid");
        }
        return $uids[$uid];
    }

    public function fetchAllPositionByUid($uid)
    {
        static $uidArr = array();

        if (!isset($uidArr[$uid])) {
            $positionArr = $this->fetchAll('uid = :uid', array(':uid' => $uid));
            $uidArr[$uid] = $positionArr;
        }

        return $uidArr[$uid];
    }

    public function findPositionidIndexByUidX($uidX = null)
    {
        if (null === $uidX) {
            $condition = 1;
        } else if (empty($uidX)) {
            return array();
        } else {
            $condition = User::model()->uid_find_in_set($uidX);
        }
        $related = Ibos::app()->db->createCommand()
            ->select('uid,positionid')
            ->from($this->tableName())
            ->where($condition)
            ->queryAll();
        $return = array();
        if (!empty($related)) {
            foreach ($related as $row) {
                $return[$row['uid']][] = $row['positionid'];
            }
        }
        return $return;
    }

    public function fetchUidListIndexByPositionid($positionidX = null, $returnDisabled = false, $related = true)
    {
        $positionString = is_array($positionidX) ? implode(',', $positionidX) : $positionidX;
        $condition = null === $positionidX ? 1 : " FIND_IN_SET( `positionid`, '{$positionString}' ) ";
        if (false === $returnDisabled) {
            $disabled = Ibos::app()->db->createCommand()
                ->select('uid')
                ->from(User::model()->tableName())
                ->where(" `status` = " . User::USER_STATUS_ABANDONED)
                ->queryColumn();
        } else {
            $disabled = array();
        }
        if (true === $related) {
            $PositionRelated = Ibos::app()->db->createCommand()
                ->select('uid,positionid')
                ->from($this->tableName())
                ->where($condition)
                ->queryAll();
        }
        $positionMain = Ibos::app()->db->createCommand()
            ->select('uid,positionid')
            ->from(User::model()->tableName())
            ->where($condition)
            ->andWhere(" `positionid` != 0 ")
            ->queryAll();
        $listRelated = $listMain = array();
        foreach ($PositionRelated as $row) {
            if (in_array($row['uid'], $disabled)) {
                continue;
            }
            $listRelated[$row['positionid']][] = $row['uid'];
        }
        foreach ($positionMain as $row) {
            if (empty($row['positionid']) || in_array($row['uid'], $disabled)) {
                continue;
            }
            $listMain[$row['positionid']][] = $row['uid'];
        }
        //这里没办法用数组合并，因为php会把数字键给重新排列，不管你是不是字符串的数字
        //$position = array_merge_recursive( $listMain, $listRelated );
        $return = array();
        foreach ($listMain as $positionid => $row) {
            if (isset($listRelated[$positionid])) {
                $return[$positionid] = array_unique(array_merge($row, $listRelated[$positionid]));
            } else {
                $return[$positionid] = $row;
            }
        }
        foreach ($listRelated as $positionid => $row) {
            if (isset($listMain[$positionid])) {
                $return[$positionid] = array_unique(array_merge($row, $listMain[$positionid]));
            } else {
                $return[$positionid] = $row;
            }
        }
        return $return;
    }

}
