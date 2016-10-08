<?php

namespace application\modules\main\model;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use CException;

class AttachmentN {

	/**
	 * 实例
	 * @var mixed 
	 */
	private static $_instance;

	/**
	 * 模拟AR的model方法，实现单例
	 * @return type
	 */
	public static function model() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * 根据tableID获取对应的表名
	 * @param mixed $tableId 
	 * @return string 符合的表名
	 * @throws CException
	 */
	public function getTable( $tableId ) {
		if ( !is_numeric( $tableId ) ) {
			list($idType, $id) = explode( ':', $tableId );
			if ( $idType == 'aid' ) {
				$aid = StringUtil::iIntval( $id );
				$tableId = Ibos::app()->db->createCommand()
						->select( 'tableid' )
						->from( '{{attachment}}' )
						->where( "aid='{$aid}'" )
						->queryScalar();
			} elseif ( $idType == 'rid' ) {
				$rid = (string) $id;
				$tableId = StringUtil::iIntval( $rid{strlen( $rid ) - 1} );
			}
		}
		if ( $tableId >= 0 && $tableId < 10 ) {
			return sprintf( '{{attachment_%d}}', intval( $tableId ) );
		} elseif ( $tableId == 127 ) {
			return '{{attachment_unused}}';
		} else {
			throw new CException( 'Table attachment_' . $tableId . ' has not exists' );
		}
	}

	/**
	 * 获取一条附件分表的记录
	 * @param integer $tableId 分表ID
	 * @param integer $aid 附件ID
	 * @param boolean $isImage 是否图片格式
	 * @return array
	 */
	public function fetch( $tableId, $aid, $isImage = false ) {
		$isImage = $isImage === false ? '' : ' AND isimage = 1';
		$sqlText = sprintf( 'SELECT * FROM %s WHERE aid = %d %s', $this->getTable( $tableId ), $aid, $isImage );
		return !empty( $aid ) ? Ibos::app()->db->createCommand()->setText( $sqlText )->queryRow() : array();
	}

	/**
	 * 增加一条附件分表记录
	 * @param integer $tableId
	 * @param array $attrs
	 * @return integer 
	 */
	public function add( $tableId, $attrs, $returnId = false ) {
		$rs = Ibos::app()->db->createCommand()
				->insert( $this->getTable( $tableId ), $attrs );
		if ( $returnId ) {
			return Ibos::app()->db->getLastInsertID();
		} else {
			return $rs;
		}
	}

	/**
	 * 删除一条附件分表记录
	 * @param integer $tableId
	 * @param integer $aid
	 * @return integer
	 */
	public function deleteByPk( $tableId, $aid ) {
		return Ibos::app()->db->createCommand()
						->delete( $this->getTable( $tableId ), "aid = {$aid}" );
	}

	public function updateData( $tableid, $aid, $data ) {
		return Ibos::app()->db->createCommand()
						->update( $this->getTable( $tableid ), $data, "aid = {$aid}" );
	}

}
