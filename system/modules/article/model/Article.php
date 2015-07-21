<?php

/**
 * 信息中心模块------ article表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author Ring <Ring@ibos.com.cn>
 */

/**
 * 信息中心模块------  article表的数据层操作类，继承ICModel
 * @package application.modules.article.model
 * @version $Id: Article.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\article\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\article\utils\Article as ArticleUtil;
use CDbCriteria;
use CPagination;

class Article extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{article}}';
	}

	/**
	 * 根据条件，查询出对应数据，返回一个数组，其中数组元素中的pages为翻页所需的数据，datas为列表所需的数据
	 * <pre>
	 * 		array( 'pages' => $pages, 'datas' => $datas );
	 * </pre>
	 * @param string $conditions 查询条件 default=''; 
	 * @param integer $pageSize default=null;每页显示的数据条数
	 * @return array 
	 */
	public function fetchAllAndPage( $conditions = '', $pageSize = null ) {
		$conditionArray = array( 'condition' => $conditions, 'order' => 'istop DESC,toptime ASC,addtime DESC' );
		$criteria = new CDbCriteria();
		foreach ( $conditionArray as $key => $value ) {
			$criteria->$key = $value;
		}
		$count = $this->count( $criteria );
		$pages = new CPagination( $count );
		$everyPage = is_null( $pageSize ) ? IBOS::app()->params['basePerPage'] : $pageSize;
		$pages->setPageSize( intval( $everyPage ) );
		$pages->applyLimit( $criteria );
		$datas = $this->fetchAll( $criteria );
		return array( 'pages' => $pages, 'datas' => $datas );
	}

	/**
	 * 根据articleid获取一个指定字段的所有值
	 * @param String $field 字段名
	 * @param integer $articleids 文章ids
	 * @return array
	 */
	public function fetchAllFieldValueByArticleids( $field, $articleids ) {
		$returnArray = array();
		$articleids = is_array( $articleids ) ? implode( ',', $articleids ) : $articleids;
		$rows = $this->fetchAll( array( 'select' => $field, 'condition' => "FIND_IN_SET(articleid,'{$articleids}')" ) );
		if ( !empty( $rows ) ) {
			foreach ( $rows as $row ) {
				$returnArray[] = $row[$field];
			}
		}
		return $returnArray;
	}

	/**
	 * 取消已过期的置顶
	 * @return boolean
	 */
	public function cancelTop() {
		$result = $this->updateAll( array(
			'istop' => 0,
			'toptime' => 0,
			'topendtime' => 0 ), 'istop = 1 AND topendtime<' . TIMESTAMP );
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
	public function updateTopStatus( $ids, $isTop, $topTime, $topEndTime ) {
		$condition = array( 'istop' => $isTop, 'toptime' => $topTime, 'topendtime' => $topEndTime );
		return $this->updateAll( $condition, "articleid IN ($ids)" );
	}

	/**
	 * 取消已过期高亮
	 * @return boolean
	 */
	public function updateIsOverHighLight() {
		$result = $this->updateAll( array( 'ishighlight' => 0, 'highlightstyle' => '',
			'highlightendtime' => '0' ), 'ishighlight = 1 AND highlightendtime<' . TIMESTAMP );

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
	public function updateHighlightStatus( $ids, $ishighlight, $highlightstyle, $highlightendtime ) {
		$condition = array( 'ishighlight' => $ishighlight, 'highlightstyle' => $highlightstyle, 'highlightendtime' => $highlightendtime );
		return $this->updateAll( $condition, "articleid IN ($ids)" );
	}

	/**
	 * 根据文章id，删除所有符合的数据
	 * @param string $ids 
	 * @return integer
	 */
	public function deleteAllByArticleIds( $ids ) {
		return $this->deleteAll( "articleid IN ($ids)" );
	}

	/**
	 * 根据文章ids更新所有符合条件的文章的状态和审批人
	 * @param string $ids 文章ids
	 * @param integer $approver 审批人uid
	 * @param string $status 状态，默认为1公开
	 * @return integer 被更新的行数
	 */
	public function updateAllStatusAndApproverByPks( $ids, $approver, $status = 1 ) {
		return $this->updateAll( array( 'status' => $status, 'approver' => $approver, 'uptime' => TIMESTAMP ), "articleid IN ($ids)" );
	}

	/**
	 * 根据文章ids，更新所有符合条件的分类
	 * @param string $ids
	 * @param integer $catid
	 * @return integer
	 */
	public function updateAllCatidByArticleIds( $ids, $catid ) {
		return $this->updateAll( array( 'catid' => $catid ), "articleid IN ($ids)" );
	}

	/**
	 * 更新文章点击数量
	 * @param integer $id 文章id
	 * @param integer $clickCount 点击数，默认为0
	 * @return integer 
	 */
	public function updateClickCount( $id, $clickCount = 0 ) {
		if ( empty( $clickCount ) ) {
			$record = parent::fetchByPk( $id );
			$clickCount = $record['clickcount'];
		}
		return parent::modify( $id, array( 'clickcount' => $clickCount + 1 ) );
	}

	/**
	 * 兼容Source接口
	 * @param integer $id 资源ID
	 * @return array
	 */
	public function getSourceInfo( $id ) {
		$info = $this->fetchByPk( $id );
		return $info;
	}

	/**
	 * 根据分类id获取某个uid的未审核文章id
	 * @param mixed $catid 分类id
	 * @param integer $uid 用户id
	 * @return array
	 */
	public function fetchUnApprovalArtIds( $catid, $uid ) {
		
		$backArtIds = ArticleBack::model()->fetchAllBackArtId();
		$backArtIdStr = implode( ',', $backArtIds );
		$backCondition = empty( $backArtIdStr ) ? '' : "AND `articleid` NOT IN({$backArtIdStr})";
		$catids = ArticleCategory::model()->fetchAllApprovalCatidByUid( $uid );
		if ( empty( $catid ) ) { // 所有数据,先获取uid所有要审核的分类
			$catidStr = implode( ',', $catids );
			$condition = "((FIND_IN_SET( `catid`, '{$catidStr}') {$backCondition} ) OR `author` = {$uid})"; // 作者或者在有审核权限的分类
		} else {
			$catidArr = is_array($catid) ? $catid : explode(',', $catid);
			$temp = array();
			foreach ($catidArr as $cid){
				if(  in_array( $cid, $catids ) ){
					$temp[] = $cid;
				}
			}
			$tempStr = implode(',', $temp);
			$catidStr = empty($tempStr) ? 0 : $tempStr;
			$allCatid = is_array($catid) ? explode(',', $catid) : $catid ;
			$condition =  "((`catid` IN({$catidStr}) {$backCondition} ) OR (`catid` IN({$allCatid}) AND `author` = {$uid}))"; // 是审核人，无限制，否则条件为作者
		}
		$record = $this->fetchAll( array(
			'select' => array( 'articleid' ),
			'condition' => "`status` = 2 AND " . $condition
				) );
		$artIds = Convert::getSubByKey( $record, 'articleid' );
		return $artIds;
	}
	
	/**
	 * 未读，待审核，草稿 统计数
	 * @param string $type
	 * @param integer $uid
	 * @param type $catid
	 * @param type $condition
	 */
	public function getArticleCount($type, $uid, $catid = 0, $condition = ''){
		$condition = ArticleUtil::joinListCondition($type, $uid, $catid, $condition);
		return $this->count($condition);
	}
	
}
