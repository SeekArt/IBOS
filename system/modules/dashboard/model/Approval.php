<?php

/**
 * Approval表的数据层操作文件
 *
 * @author gzhzh <gzhzh@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  Approval表的数据层操作
 * 
 * @package application.modules.dashboard.model
 * @version $Id: Approval.php 575 2014-04-24 16:42:03Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\modules\article\model\ArticleCategory;
use application\modules\officialdoc\model\OfficialdocCategory;
use application\core\utils\module;

class Approval extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{approval}}';
	}

	/**
	 * 获得所有审批流程，按添加倒序
	 */
	public function fetchAllApproval() {
		return $this->fetchAll( array( 'order' => 'addtime DESC' ) );
	}

	/**
	 * 获取下一步骤审核人uid数组，若已经是最后一步，返回成功标识
	 * @param integer $id 审批流程id
	 * @param integer $step 步骤（1,2,3,4,5）
	 * @return array
	 */
	public function fetchNextApprovalUids( $id, $step ) {
		$ret = array( 'step' => '', 'uids' => array() );
		if ( empty( $id ) ) {
			return $ret;
		}
		$approval = $this->fetchByPk( $id );
		$nextStep = $step + 1;
		if ( !empty( $approval ) ) {
			// 大于审核等级的话返回可发布标识
			if ( $nextStep > $approval['level'] ) {
				$ret = array( 'step' => 'publish', 'uids' => array() );
			} else { // 否则返回下一步用户数组
				$nextLevelName = $this->getLevelNameByStep( $nextStep );
				$ret = array(
					'step' => $nextStep,
					'uids' => explode( ',', $approval[$nextLevelName] )
				);
			}
		}
		return $ret;
	}

	/**
	 * 获得步骤对应字段名
	 * @param integer $step 步骤（1,2,3,4,5）
	 * @return string
	 */
	public function getLevelNameByStep( $step ) {
		$levels = array(
			'1' => 'level1',
			'2' => 'level2',
			'3' => 'level3',
			'4' => 'level4',
			'5' => 'level5'
		);
		if ( in_array( $step, array_keys( $levels ) ) ) {
			return $levels[$step];
		} else {
			return $levels['1'];
		}
	}

	/**
	 * 根据主键ids获取所有步骤审核人uid数组
	 * @param mix $ids 审批流程id数组或逗号隔开的字符串
	 * @return array
	 */
	public function fetchApprovalUidsByIds( $ids ) {
		$ids = is_array( $ids ) ? implode( ',', $ids ) : $ids;
		$uidStr = '';
		$approvals = $this->fetchAll( "FIND_IN_SET(`id`, '{$ids}')" );
		foreach ( $approvals as $approval ) {
			for ( $i = 1; $i <= $approval['level']; $i++ ) {
				$uidStr .= $approval["level{$i}"] . ',';
			}
		}
		$uidArrTemp = explode( ',', $uidStr );
		$uidArr = array_unique( $uidArrTemp );
		return array_filter( $uidArr );
	}

	/**
	 * 删除审批流程，删除后更新指向该审批流程的所有分类
	 * @param integer $id 审批流程id
	 * @return boolean
	 */
	public function deleteApproval( $id ) {
		if ( empty( $id ) ) {
			return false;
		}
		$ret = $this->deleteByPk( $id );
		if ( $ret ) {
			if ( Module::getIsEnabled( 'article' ) ) {
			ArticleCategory::model()->updateAll( array( 'aid' => 0 ), "aid={$id}" );
			}
			OfficialdocCategory::model()->updateAll( array( 'aid' => 0 ), "aid={$id}" );
		}
		return $ret;
	}

	/**
	 * 根据审核ids获取每个审核流程的审核人uid数组，以审核id作为键值，该审核id下的所有审核者uid为键值返回
	 * @param mixed $ids 审核流程ids
	 * @return array
	 */
	public function fetchAllUidsByIds( $ids ) {
		$res = array();
		$ids = is_array( $ids ) ? implode( ',', $ids ) : $ids;
		$approvals = $this->fetchAll( "FIND_IN_SET(`id`, '{$ids}')" );
		foreach ( $approvals as $approval ) {
			$uids = array();
			for ( $i = 1; $i <= $approval['level']; $i++ ) {
				$uids = array_merge( $uids, explode( ',', $approval["level{$i}"] ) );
			}
			$aid = $approval['id'];
			$res[$aid] = array_filter( array_unique( $uids ) );
		}
		return $res;
	}

}
