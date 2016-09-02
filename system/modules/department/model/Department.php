<?php

/**
 * 部门表数据层文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 部门表department对应数据层操作
 *
 * @package application.modules.department.model
 * @version $Id: Department.php 7629 2016-07-21 10:14:41Z php_lxy $
 * @author Ring <Ring@ibos.com.cn>
 *
 */

namespace application\modules\department\model;

use application\core\model\Model;
use application\core\utils as util;
use application\core\utils\IBOS;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\user\model\User as UserModel;

class Department extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{department}}';
	}

	public function deptid_find_in_set( $deptidX, $pre = '' ) {
		$preString = empty( $pre ) ? $pre : '`' . $pre . '`.';
		$deptidString = is_array( $deptidX ) ? implode( ',', $deptidX ) : $deptidX;
		return " FIND_IN_SET( {$preString}`deptid`, '{$deptidString}') ";
	}

	const DEPT_NUM_PER = 100; //每次从数据库里取的部门数目，默认100
	
	/**
	 * 根据单个或多个部门ID（用英文,号隔开）得到其所有父部门id，包括父部门的父部门
	 * @param string $deptid 部门Id
	 * @param boolean $connect 是否链接$deptid返回
	 * @return string
	 */
	public function queryDept( $deptid, $connect = false ) {
		$deptid = util\StringUtil::filterStr( $deptid );
		$splitArray = explode( ',', $deptid );
		$deptidStr = '';
		foreach ( $splitArray as $data ) {
			$deptidStr .= $this->getDeptParent( $data );
		}
		$result = util\StringUtil::filterStr( $deptidStr . ( $connect ? ',' . $deptid : '') );
		return $result;
	}

	/**
	 * 根据单个部门ID，从全局变量‘department’数据中得到其父部门id,包括父部门的父部门…[递归函数] (原名：deptparent)
	 * @static $depts
	 * @param integer $deptid
	 * @return string
	 */
	private function getDeptParent( $deptid ) {
		static $depts = NULL;
		if ( NULL === $depts ) {
			$depts = DepartmentUtil::loadDepartment();
		}
		$pid = isset( $depts[$deptid]['pid'] ) ? $depts[$deptid]['pid'] : 0;
		if ( $pid > 0 ) {
			$pidStr = $pid . ',' . $this->getDeptParent( $pid );
			return $pidStr;
		} else {
			return '';
		}
	}

	/**
	 * 返回deptids取得所有子分类id字符串,逗号分割
	 * @param mixed $deptidX 数组或者逗号分割的deptid
	 * @param boolean $connect 返回是否需要连接上原来部门id
	 * @return string 逗号分割的字符串，部门id
	 */
	public function fetchChildIdByDeptids( $deptidX, $connect = false ) {
		static $departArr = NULL;
		if ( NULL === $departArr ) {
			$departArr = DepartmentUtil::loadDepartment();
		}
		$deptidArray = is_array( $deptidX ) ? $deptidX : explode( ',', $deptidX );
		$childDeptidString = '';
		foreach ( $deptidArray as $deptid ) {
			$childDeptidString .= implode( ',', $this->fetchChildDeptByDeptid( $deptid, $departArr, false ) );
		}
		if ( true === $connect ) {
			$childDeptidString .= ',' . implode( ',', $deptidArray );
		}
		return util\StringUtil::filterStr( $childDeptidString );
	}

	/**
	 * 通过$deptid取得子类department
	 * @staticvar array $result
	 * @param integer $deptid 部门Id
	 * @param array $departArr 部门列表
	 * @param boolean $flag 默认为TRUE,如果不是，就只拿部门id
	 * @return array $result 子部门数组
	 * @author gzwwb
	 */
	public function fetchChildDeptByDeptid( $deptid, $departArr , $flag = true) {
		static $result = array();
		if ( !is_array( $departArr ) ) {//写入缓存的时候如果序列化的字符串出错了，会导致拿不到数组，这里做判断，如果出错，返回空，这么做是否合适有待考究，这里只是不让它报错
			return array();
		}
		foreach ( $departArr as $department ) {
			if ( !$flag ) {
				if ( $department['pid'] == $deptid ) {
					$result[] = $department['deptid'];
					array_merge( $result, $this->fetchChildDeptByDeptid( $department['deptid'], $departArr, FALSE ) );
				}
			}else {
				if ( $department['pid'] == $deptid ) {
					$result[] = $department;
					array_merge( $result, $this->fetchChildDeptByDeptid( $department['deptid'], $departArr ) );
				}
			}

		}
		return $result;
	}

	/**
	 * 根据deptid获取部门的主管ID
	 * @param integer $deptid
	 * @return int
	 */
	public function fetchManagerByDeptid( $deptid ) {
		$departArr = $this->findByPk( $deptid );
		return isset( $departArr->manager ) ? intval( $departArr->manager ) : 0;
	}

	// 以下 by banyan

	/**
	 * 根据部门ID查找部门名称，返回$glue分隔的部门名称字符串
	 * @param mixed $ids 部门ID数组或逗号分隔字符串
	 * @param string $glue 分隔符
	 * @param boolean $returnFirst 是否返回第一个
	 * @return string
	 */
	public function fetchDeptNameByDeptId( $id, $glue = ',', $returnFirst = false ) {
		$deptArr = DepartmentUtil::loadDepartment();
		$deptIds = is_array( $id ) ? $id : explode( ',', util\StringUtil::filterStr( $id ) );
		$name = array();
		if ( $returnFirst ) {
			if ( isset( $deptArr[$deptIds[0]] ) ) {
				$name[] = $deptArr[$deptIds[0]]['deptname'];
			}
		} else {
			foreach ( $deptIds as $deptId ) {
				$name[] = isset( $deptArr[$deptId] ) ? $deptArr[$deptId]['deptname'] : null;
			}
		}
		return implode( $glue, $name );
	}

	/**
	 * 根据用户uid获取用户所在部门的部门名称
	 * @param integer $uid 默认为0，即为获取当前登录用户的uid
	 * @return string
	 */
	public function fetchDeptNameByUid( $uid, $glue = ',', $returnFirst = false ) {
		$user = UserModel::model()->fetchByUid( $uid );
		$deptName = '';
		if ( !empty( $user ) && !empty( $user['alldeptid'] ) ) {
			$deptName = $this->fetchDeptNameByDeptId( $user['alldeptid'], $glue, $returnFirst );
		}
		return $deptName;
	}

	/**
	 * 判断某个部门是否属于分支机构
	 * @param integer $id 部门id
	 * @return integer
	 */
	public function getIsBranch( $id ) {
		$record = $this->findByPk( $id );
		return isset( $record->isbranch ) ? intval( $record->isbranch ) : 0;
	}

	/**
	 * 查看当前部门下是否还有子部门
	 * @param integer $id 部门id
	 * @return integer
	 */
	public function countChildByDeptId( $id ) {
		$count = $this->count( 'pid = :deptid', array( ':deptid' => $id ) );
		return $count;
	}

	/**
	 * 根据部门id，获取其所属分支的部门，若没有，返回除了“总公司”最大的部门
	 * 以前：如果没有设置分支，则返回空数组
	 * @param integer $deptid
	 * @return array 分支部门的数组
	 */
	public function getBranchParent( $deptid ) {
		static $depts = array();
		if ( empty( $depts ) ) {
			//获取所有部门信息，索引值对应部门id
			$depts = DepartmentUtil::loadDepartment();
		}
		if ( isset( $depts[$deptid] ) && $depts[$deptid]['isbranch'] == 1 ) {
			return $depts[$deptid];
		}
		$pid = isset( $depts[$deptid] ) ? $depts[$deptid]['pid'] : 0;
		if ( $pid > 0 ) {
			return $this->getBranchParent( $pid );
		} else {
			//父部门为0
			return array( 'deptid' => 0 );
		}
	}

	public function findAllDeptidByUidX( $uidX ) {
		$deptidArray = IBOS::app()->db->createCommand()
				->select( 'deptid' )
				->from( UserModel::model()->tableName() )
				->where( UserModel::model()->uid_find_in_set( $uidX ) )
				->queryColumn();
		$deptidRelatedArray = IBOS::app()->db->createCommand()
				->select( 'deptid' )
				->from( DepartmentRelated::model()->tableName() )
				->where( UserModel::model()->uid_find_in_set( $uidX ) )
				->queryColumn();
		return array_unique( array_merge( $deptidArray, $deptidRelatedArray ) );
	}

	public function findDeptmentIndexByDeptid( $deptMixed = NULL, $param = array() ) {
		if ( NULL === $deptMixed ) {
			$condition = 1;
		} else if ( empty( $deptMixed ) ) {
			return array();
		} else {
			$deptString = is_array( $deptMixed ) ? implode( ',', $deptMixed ) : $deptMixed;
			$condition = " FIND_IN_SET( `deptid`, '{$deptString}' )";
		}
		$query = IBOS::app()->db->createCommand()
				->select()
				->from( $this->tableName() )
				->where( $condition );
		if ( isset( $param['order'] ) ) {
			$query->order( $param['order'] );
		}

		$deptArray = $query->queryAll();
		$return = array();
		if ( !empty( $deptArray ) ) {
			foreach ( $deptArray as $dept ) {
				$return[$dept['deptid']] = $dept;
			}
		}
		return $return;
	}

	public function findDepartmentByDeptid( $deptMixed = NULL ) {
		if ( NULL === $deptMixed ) {
			$condition = 1;
		} else if ( empty( $deptMixed ) ) {
			return array();
		} else {
			$deptString = is_array( $deptMixed ) ? implode( ',', $deptMixed ) : $deptMixed;
			$condition = " FIND_IN_SET( `deptid`, '{$deptString}' )";
		}
		$deptArray = IBOS::app()->db->createCommand()
				->select()
				->from( $this->tableName() )
				->where( $condition )
				->queryAll();
		return $deptArray;
	}

	// 子查询语句
	private function deptid_not_in_binding( $type ) {
		return " `deptid` NOT IN ( SELECT `deptid` FROM {{department_binding}} WHERE `app` = '{$type}' ) ";
	}

	/**
	*查询未跟指定第三方关联的部门总数
	* @param  string $type
	* @return array  用户uid数组
	*/
	public function CountUnbind( $type ) {
		$list = IBOS::app()->db->createCommand()
				->select( 'count(deptid)' )
				->from( $this->tableName() )
				->where( $this->deptid_not_in_binding( $type ) )
				->queryScalar();
		return $list;
	}

	/**
 	* 根据部门的层级创建查询的条件
 	* @param type $level
 	*/
	private function createConditionByDeptLevel( $level = 0 ) {
		$sqlString = IBOS::app()->db->createCommand()
				->select( 'deptid' )
				->from( $this->tableName() )
				->where( " `pid` IN ( <string> )" )
				->getText();
		$sql = $sqlString;
		while ( $level ) {
			$sql = str_replace( '<string>', $sqlString, $sql );
			$level --;
		}
		return str_replace( '<string>', 0, $sql );
	}

	/**
 	* 得到部门树
 	* @param type $level
 	*/
	public function getPerDept( $level,$type ) {
		if ( '0' == $level) { // pid为0
			$deptidCondition = $this->createConditionByDeptLevel( $level );
			$return = IBOS::app()->db->createCommand()
				->select( 'deptname,deptid,pid,sort' )
				->from( $this->tableName() )
				->where( " `deptid` IN( {$deptidCondition} )" )
				->andWhere( $this->deptid_not_in_binding( $type ) )
				->order( 'deptid ASC' )
				->limit( self::DEPT_NUM_PER )
				->queryAll();
		} else {
			$deptidCondition = $this->createConditionByDeptLevel( $level );
			$deptids = IBOS::app()->db->createCommand()
		        ->select( 'deptid' )
		        ->from( '{{department}}' )
		        ->where( " `deptid` IN( {$deptidCondition} )" )
		        ->andWhere( $this->deptid_not_in_binding( $type ) )
		        ->order( 'deptid ASC' )
		        ->limit( self::DEPT_NUM_PER )
		        ->queryColumn();
		    $deptids = implode(',', $deptids);
			$return = IBOS::app()->db->createCommand()
				->select( 'd.deptname,d.deptid,b.bindvalue as pid,d.sort' )
				->from( '{{department}} d' )
				->leftjoin('{{department_binding}} b', 'd.pid = b.deptid')
				->where( "FIND_IN_SET(`d`.`deptid`, '{$deptids}')" )
				->limit( self::DEPT_NUM_PER )
				->queryAll();
		}
		
		return $return;
	}

	/**
 	* 得到部门
 	* @param string $deptid 部门id
 	* @param boolean $flag 部门pid不为0,就要从绑定表里拿pid
 	*/
	public function getDeptBydDepatid ( $deptid, $flag = TRUE ) {
		if( $flag ) {
			$deptArray = IBOS::app()->db->createCommand()
				->select('deptname, deptid, pid, sort')
				->from( $this->tableName() )
				->where( "`deptid` = '{$deptid}'" )
				->queryAll();
			return $deptArray;
		} else {
			$deptArray = IBOS::app()->db->createCommand()
				->select( 'd.deptname, d.deptid, b.bindvalue as pid, d.sort' )
				->from( '{{department}} d' )
				->leftjoin('{{department_binding}} b', 'd.pid = b.deptid')
				->where( "`d`.`deptid` = '{$deptid}'" )
				->queryAll();
			return $deptArray;
		}
		
	}

}
