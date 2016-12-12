<?php

/**
 * 通知模块------ doc_approval表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 通知模块 审批步骤记录------  doc_approval表的数据层操作类，继承ICModel
 * @package application.modules.officialdoc.model
 * @version $Id: OfficialdocApproval.php 2669 2014-04-26 08:58:29Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\officialdoc\model;

use application\core\model\Model;

class OfficialdocApproval extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{doc_approval}}';
    }

    /**
     * 获取某个uid的未审核通知
     * @return array
     */
//	public function fetchUnAuditedDocidByUid( $uid ) {
//		$docidArr = array();
//		$docApprovals = $this->fetchAll();
//		foreach ( $docApprovals as $docApproval ) {
//			$doc = Officialdoc::model()->fetchByPk( $docApproval['docid'] );
//			if ( !empty( $doc['catid'] ) ) {
//				$category = OfficialdocCategory::model()->fetchByPk( $doc['catid'] );
//				if ( !empty( $category['aid'] ) ) {
//					$approval = Approval::model()->fetchByPk( $category['aid'] );
//					if ( ($docApproval['step'] + 1) <= $approval['level'] ) { // 还没审核完，查找下一步审核的uid
//						$levelName = Approval::model()->getLevelNameByStep( $docApproval['step'] + 1 );
//						if ( in_array( $uid, explode( ',', $approval[$levelName] ) ) ) { // uid在下一步审核人中，该通知属于该uid的未审核通知
//							$docidArr[] = $docApproval['docid'];
//						}
//					}
//				}
//			}
//		}
//		return $docidArr;
//	}

    /**
     * 记录签收步骤
     * @param integer $docid 通知id
     * @param integer $uid 签收人uid
     */
    public function recordStep($docid, $uid)
    {
        $docApproval = $this->fetchLastStep($docid);
        if (empty($docApproval)) { // 第0步表示新的未审核通知
            $step = 0;
        } else {
            $step = $docApproval['step'] + 1;
        }
        return $this->add(array(
            'docid' => $docid,
            'uid' => $uid,
            'step' => $step
        ));
    }

    /**
     * 获取某篇通知最后一条审批步骤
     * @param integer $docId
     * @return array
     */
    public function fetchLastStep($docId)
    {
        $record = $this->fetch(array(
            'condition' => "docid={$docId}",
            'order' => 'step DESC'
        ));
        return $record;
    }

    /**
     * 根据通知ids删除审核记录
     * @param mix $docids
     */
    public function deleteByDocIds($docids)
    {
        $docids = is_array($docids) ? implode(',', $docids) : $docids;
        return $this->deleteAll("FIND_IN_SET(docid,'{$docids}')");
    }

    /**
     * 查询已走审核步骤的通知，并按通知id分组
     * @return array
     */
    public function fetchAllGroupByDocId()
    {
        $result = array();
        $records = $this->fetchAll("step > 0");
        if (!empty($records)) {
            foreach ($records as $record) {
                $docId = $record['docid'];
                $result[$docId][] = $record;
            }
        }
        return $result;
    }
}
