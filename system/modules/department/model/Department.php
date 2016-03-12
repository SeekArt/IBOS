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
 * @version $Id: Department.php 5160 2015-06-16 08:39:42Z tanghang $
 * @author Ring <Ring@ibos.com.cn>
 * 
 */

namespace application\modules\department\model;

use application\core\model\Model;
use application\core\utils as util;
use application\core\utils\Cache as CacheUtil;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\user\model as UserModel;

class Department extends Model {

    public function init() {
        $this->cacheLife = 0;
        parent::init();
    }

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{department}}';
	}

    public function afterSave() {
        CacheUtil::update( 'Department' );
        CacheUtil::load( 'Department' );
        parent::afterSave();
    }

	/**
	 * 根据单个或多个部门ID（用英文,号隔开）得到其所有父部门id，包括父部门的父部门
	 * @param string $deptid 部门Id
	 * @param boolean $connect 是否链接$deptid返回
	 * @return string 
	 */
	public function queryDept( $deptid, $connect = false ) {
		$deptid = util\String::filterStr( $deptid );
		$splitArray = explode( ',', $deptid );
		$deptidStr = '';
		foreach ( $splitArray as $data ) {
			$deptidStr .= $this->getDeptParent( $data );
		}
		$result = util\String::filterStr( $deptidStr . ( $connect ? ',' . $deptid : '') );
		return $result;
	}

	/**
	 * 根据单个部门ID，从全局变量‘department’数据中得到其父部门id,包括父部门的父部门…[递归函数] (原名：deptparent)
	 * @static $depts
	 * @param integer $deptid 
	 * @return string 
	 */
	private function getDeptParent( $deptid ) {
		static $depts = array();
		if ( empty( $depts ) ) {
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
	 * @param integer $deptids 逗号分割的deptid，也有可能是单个的部门
	 * @param boolean $connect 返回是否需要连接上原来部门id
	 * @return string 逗号分割的字符串，部门id
	 */
	public function fetchChildIdByDeptids( $deptids, $connect = false ) {
		$departArr = DepartmentUtil::loadDepartment();
		$deptidArr = explode( ',', $deptids );
		$childDepartment = array();
		$childDeptIds = '';
		foreach ( $deptidArr as $deptid ) {
			$childDepartment = array_merge( $childDepartment, $this->fetchChildDeptByDeptid( $deptid, $departArr ) );
		}
		foreach ( $childDepartment as $department ) {
			$childDeptIds.=$department['deptid'] . ',';
		}
		if ( $connect ) {
			$childDeptIds = $deptids . ',' . $childDeptIds;
		}
		return util\String::filterStr( $childDeptIds );
	}

	/**
	 * 通过$deptid取得子类department
	 * @staticvar array $result
	 * @param integer $deptid 部门Id
	 * @param array $departArr 部门列表
	 * @return array $result 子部门数组
	 * @author gzwwb
	 */
	public function fetchChildDeptByDeptid( $deptid, $departArr ) {
		static $result = array();
		if ( !is_array( $departArr ) ) {//写入缓存的时候如果序列化的字符串出错了，会导致拿不到数组，这里做判断，如果出错，返回空，这么做是否合适有待考究，这里只是不让它报错
			return array();
		}
		foreach ( $departArr as $department ) {
			if ( $department['pid'] == $deptid ) {
				$result[] = $department;
				array_merge( $result, $this->fetchChildDeptByDeptid( $department['deptid'], $departArr ) );
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
		$departArr = $this->fetchByPk( $deptid );
		return isset( $departArr['manager'] ) ? intval( $departArr['manager'] ) : 0;
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
		$deptIds = is_array( $id ) ? $id : explode( ',', util\String::filterStr( $id ) );
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
		$user = UserModel\User::model()->fetchByUid( $uid );
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
		$record = $this->fetchByPk( $id );
		return isset( $record['isbranch'] ) ? intval( $record['isbranch'] ) : 0;
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
			return $depts[$deptid];
		}
	}

}
