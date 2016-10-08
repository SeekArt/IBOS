<?php

/**
 * session表对应的数据层操作
 * 
 * @package application.modules.main.model
 * @version $Id: Session.php 7262 2016-05-27 02:53:35Z tanghang $
 */

namespace application\modules\main\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

class Session extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{session}}';
	}

	/**
	 * 根据sid,ip,uid等条件查找session
	 * @param string $sid session id
	 * @param mixed $ip 是否有ip条件
	 * @param mixed $uid 是否有uid条件
	 * @return array 根据条件查找得出的session数组
	 */
	public function fetchBySid( $sid, $ip = false, $uid = false ) {
		if ( empty( $sid ) ) {
			return array();
		}
		$result = Ibos::app()->db->createCommand()
				->select()
				->from( $this->tableName() )
				->where( " `sid` = '{$sid}' " )
				->queryRow();
		$session = is_null( $result ) ? array() : $result;
		if ( !empty( $session ) ) {
			$ipConcat = "{$session['ip1']}.{$session['ip2']}.{$session['ip3']}.{$session['ip4']}";
		} else {
			$ipConcat = '';
		}
		if ( $session && $ip !== false && $ip != $ipConcat ) {
			$session = array();
		}
		if ( $session && $uid !== false && $uid != $session['uid'] ) {
			$session = array();
		}
		return $session;
	}

	/**
	 * 根据uid查找用户记录
	 * @param integer $uid 
	 * @return array
	 */
	public function fetchByUid( $uid ) {
		return $this->fetchByAttributes( array( 'uid' => $uid ) );
	}

	/**
	 * 根据给出条件删除session
	 * @param array $session session数组
	 */
	public function deleteBySession( $session ) {
		if ( !empty( $session ) && is_array( $session ) ) {
			$session = StringUtil::iaddSlashes( $session );
			$condition = "sid='{$session['sid']}'";
			$this->deleteAll( $condition );
		}
	}

}
