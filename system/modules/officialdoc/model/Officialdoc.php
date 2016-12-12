<?php

/**
 * 通知模块------ doc表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 通知模块------  doc表的数据层操作类，继承ICModel
 * @package application.modules.officialDoc.model
 * @version $Id: Officialdoc.php 642 2013-06-20 09:49:19Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\department\model\Department;
use application\modules\officialdoc\utils\Officialdoc as OfficialdocUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use CDbCriteria;
use CPagination;

class Officialdoc extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{doc}}';
    }

    /**
     * 根据条件，查询出对应数据，返回一个数组，其中数组元素中的pages为翻页所需的数据，datas为列表所需的数据
     * <pre>
     *        array( 'pages' => $pages, 'datas' => $datas );
     * </pre>
     * @param string $conditions 查询条件 default='';
     * @param integer $pageSize default=null;每页显示的数据条数
     * @return array
     */
    public function fetchAllAndPage($conditions = '', $pageSize = null)
    {
        $conditionArray = array('condition' => $conditions, 'order' => 'istop DESC, addtime DESC');
        $criteria = new CDbCriteria();
        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }
        $count = $this->count($criteria);
        $pages = new CPagination($count);
        $everyPage = is_null($pageSize) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pages->setPageSize(intval($everyPage));
        $pages->applyLimit($criteria);
        $datas = $this->fetchAll($criteria);
        return array('pages' => $pages, 'datas' => $datas);
    }

    /**
     * 取消已过期高亮
     * @return boolean
     */
    public function updateIsOverHighLight()
    {
        $result = $this->updateAll(array('ishighlight' => 0, 'highlightstyle' => '',
            'highlightendtime' => '0'), 'ishighlight = 1 AND highlightendtime<' . TIMESTAMP);
        return $result;
    }

    /**
     * 设置/取消高亮
     * @param string $ids 要设置或取消的id
     * @param integer $ishighlight 状态
     * @param string $highlightstyle 高亮样式
     * @param integer $highlightendtime 高亮结束时间
     * @return boolean
     */
    public function updateHighlightStatus($ids, $ishighlight, $highlightstyle, $highlightendtime)
    {
        $attributes = array('ishighlight' => $ishighlight, 'highlightstyle' => $highlightstyle, 'highlightendtime' => $highlightendtime);
        return $this->updateAll($attributes, "docid IN ($ids)");
    }

    /**
     * 根据通知id，删除所有符合的数据
     * @param string $ids
     * @return integer
     */
    public function deleteAllByDocIds($ids)
    {
        return $this->deleteAll("docid IN ($ids)");
    }

    /**
     * 根据通知ids更新所有符合条件的文章的状态
     * @param string $ids 审核的id
     * @param string $status 状态
     * @param integer $approver 审核人
     * @return type
     */
    public function updateAllStatusByDocids($ids, $status, $approver)
    {
        return $this->updateAll(array('status' => $status, 'approver' => $approver, 'uptime' => TIMESTAMP), "docid IN ($ids)");
    }

    /**
     * 取消已过期的置顶
     * @return boolean
     */
    public function cancelTop()
    {
        $result = $this->updateAll(array('istop' => 0, 'toptime' => 0,
            'topendtime' => 0), 'istop = 1 AND topendtime<' . TIMESTAMP);
        return $result;
    }

    /**
     * 设置/取消置顶
     * @param string $ids 要设置或取消的id
     * @param integer $isTop 状态
     * @param integer $topTime 置顶时间
     * @param integer $topEndTime 置顶结束时间
     * @return boolean
     */
    public function updateTopStatus($ids, $isTop, $topTime, $topEndTime)
    {
        $condition = array('istop' => $isTop, 'toptime' => $topTime, 'topendtime' => $topEndTime);
        return $this->updateAll($condition, "docid IN ($ids)");
    }

    /**
     * 根据通知ids，更新所有符合条件的分类
     * @param string $ids
     * @param integer $catid
     * @return integer
     */
    public function updateAllCatidByDocids($ids, $catid)
    {
        return $this->updateAll(array('catid' => $catid), "docid IN ($ids)");
    }

    /**
     * 更新通知点击数量
     * @param integer $id 文章id
     * @param integer $clickCount 点击数，默认为0
     * @return integer
     */
    public function updateClickCount($id, $clickCount = 0)
    {
        if (empty($clickCount)) {
            $record = $this->fetch(array('select' => 'clickcount', 'condition' => "docid = '$id'"));
            $clickCount = $record['clickcount'];
        }
        return $this->modify($id, array('clickcount' => $clickCount + 1));
    }

    /**
     * 取得当前用户没有签收的通知数量
     */
    public function countNoSignByUid($uid)
    {
        return OfficialdocUtil::getNoSignNumByUid($uid);
    }

    /**
     * 根据docid获取一个指定字段的所有值
     * @param String $field 字段名
     * @param integer $docids 文章ids
     * @return array
     */
    public function fetchAidsByDocids($docids)
    {
        $rows = $this->fetchAll(array('select' => 'attachmentid', 'condition' => "FIND_IN_SET(docid,'$docids')"));
        $res = Convert::getSubByKey($rows, 'attachmentid');
        return $res;
    }

    /**
     * 获取某篇通知所有需审核的人uid集合
     * @param integer $docId 通知id
     * @return array
     */
    public function fetchAllUidsByDocId($docId)
    {
        $doc = $this->fetchByPk($docId);
        if (empty($doc)) {
            return null;
        }
        //发布范围uid
        if ($doc['deptid'] == 'alldept' || (empty($doc['deptid']) && empty($doc['positionid']) && empty($doc['uid']) && empty($doc['roleid']))) {
            $uids = User::model()->fetchUidA(false);
        } else {
            //需要签收的用户都要考虑辅助岗位，辅助部门和辅助角色的问题
            $uids = array();
            if (!empty($doc['deptid'])) {
                $deptids = Department::model()->fetchChildIdByDeptids($doc['deptid'], true);
                $uids = array_merge($uids, User::model()->fetchAllUidByDeptids($deptids, false, true));
            }
            if (!empty($doc['positionid'])) {
                $uids = array_merge($uids, User::model()->fetchAllUidByPositionIds($doc['positionid'], false, true));
            }
            if (!empty($doc['uid'])) {
                $uids = array_merge($uids, explode(',', $doc['uid']));
            }
            if (!empty($doc['roleid'])) {
                $uids = array_merge($uids, User::model()->fetchAllUidByRoleids($doc['roleid'], false, true));
            }
        }

        return array_unique($uids);
    }

    /**
     * 根据分类id获取某个uid的未审核文章id
     * @param integer $catid 分类id
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchUnApprovalDocIds($catid, $uid)
    {
        $backDocIds = OfficialdocBack::model()->fetchAllBackDocId();
        $backDocIdStr = implode(',', $backDocIds);
        $backCondition = empty($backDocIdStr) ? '' : "AND `docid` NOT IN({$backDocIdStr})";
        $catids = OfficialdocCategory::model()->fetchAllApprovalCatidByUid($uid);
        if (empty($catid)) { // 所有数据,先获取uid所有要审核的分类
            $catidStr = implode(',', $catids);
            $condition = "((FIND_IN_SET( `catid`, '{$catidStr}' ) {$backCondition}) OR `author` = {$uid})"; // 作者或者在有审核权限的分类
        } else {
            $catidArr = is_array($catid) ? $catid : explode(',', $catid);
            $temp = array();
            foreach ($catidArr as $cid) {
                if (in_array($cid, $catids)) {
                    $temp[] = $cid;
                }
            }
            $tempStr = implode(',', $temp);
            $catidStr = empty($tempStr) ? 0 : $tempStr;
            $allCatid = is_array($catid) ? explode(',', $catid) : $catid;
            $condition = "((`catid` IN({$catidStr}) {$backCondition} ) OR (`catid` IN({$allCatid}) AND `author` = {$uid}))"; // 是审核人，无限制，否则条件为作者
        }
        $record = $this->fetchAll(array(
            'select' => array('docid'),
            'condition' => "`status` = 2 AND " . $condition
        ));
        $docIds = Convert::getSubByKey($record, 'docid');
        return $docIds;
    }

    /**
     * 未签收，带审核，草稿 统计数
     * @param string $type
     * @param integer $uid
     * @param integer $catid
     * @param string $condition
     * @return string
     */
    public function getOfficialdocCount($type, $uid, $catid = 0, $condition = '')
    {
        $condition = OfficialdocUtil::joinListCondition($type, $uid, $catid, $condition);
        return $this->count($condition);
    }

}
