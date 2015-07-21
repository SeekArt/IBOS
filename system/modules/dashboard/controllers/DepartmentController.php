<?php

/**
 * 组织架构模块部门控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 组织架构模块部门控制器类,提供增删查改功能
 * 
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: DepartmentController.php 4064 2014-09-03 09:13:16Z zhangrong $
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache as CacheUtil;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\core\utils\String;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\main\model\Setting;
use application\modules\user\model\User;

class DepartmentController extends OrganizationBaseController {

	/**
	 * 下拉选择框字符串格式
	 * @var string 
	 */
	public $selectFormat = "<option value='\$deptid' \$selected>\$spacer\$deptname</option>";

	/**
	 * 增加操作
	 * @return void
	 */
	public function actionAdd() {
		if ( Env::submitCheck( 'addsubmit' ) ) {
			$this->dealWithBranch();
			$this->dealWithSpecialParams();
			$data = Department::model()->create();
			$data['isbranch'] = isset( $_POST['isbranch'] ) ? 1 : 0;
			$newId = Department::model()->add( $data, true );
			Department::model()->modify( $newId, array( 'sort' => $newId ) );
			CacheUtil::update( 'department' );
			$newId && Org::update();
			$this->success( IBOS::lang( 'Save succeed', 'message' ), $this->createUrl( 'user/index' ) );
		} else {
			$dept = DepartmentUtil::loadDepartment();
			$param = array(
				'tree' => String::getTree( $dept, $this->selectFormat ),
			);
			$this->render( 'add', $param );
		}
	}

	/**
	 * 编辑操作
	 * @return void
	 */
	public function actionEdit() {
		if ( Env::getRequest( 'op' ) == 'structure' ) { // 排序
			$pid = Env::getRequest( 'pid' );
			$deptid = Env::getRequest( 'id' );
			$index = Env::getRequest( 'index' ); // 排序后位置,0表示第一位，1表示第二位...
			$status = $this->setStructure( $index, $deptid, $pid );
			$this->ajaxReturn( array( 'isSuccess' => $status ), 'json' );
		}
		if ( Env::getRequest( 'op' ) == 'get' ) {
			return $this->get();
		}
		$deptId = Env::getRequest( 'deptid' );
		// 总部
		if ( $deptId == 0 ) {
			//不再组织架构这里单独处理总公司，只保留全局设置的
		} else {
			$this->dealWithBranch();
			$this->dealWithSpecialParams();
			$data = Department::model()->create();
			$data['isbranch'] = isset( $_POST['isbranch'] ) ? 1 : 0;
			$editStatus = Department::model()->modify( $data['deptid'], $data );
			CacheUtil::update( 'department' );
			$editStatus && Org::update();
		}
		$this->success( IBOS::lang( 'Update succeed', 'message' ), $this->createUrl( 'user/index' ) );
	}

	/**
	 * 删除操作
	 * @return void 
	 */
	public function actionDel() {
		if ( IBOS::app()->request->getIsAjaxRequest() ) {
			$delId = Env::getRequest( 'id' );
			if ( Department::model()->countChildByDeptId( $delId ) ) {
				$delStatus = false;
				$msg = IBOS::lang( 'Remove the child department first' );
			} else {
				$delStatus = Department::model()->remove( $delId );
				// 删除辅助部门关联
				DepartmentRelated::model()->deleteAll( 'deptid = :deptid', array( ':deptid' => $delId ) );
				$relatedIds = User::model()->fetchAllUidByDeptid( $delId );
				// 更新用户部门信息
				if ( !empty( $relatedIds ) ) {
					User::model()->updateByUids( $relatedIds, array( 'deptid' => 0 ) );
				}
				CacheUtil::update( 'department' );
				$delStatus && Org::update();
				$msg = IBOS::lang( 'Operation succeed', 'message' );
			}
			$this->ajaxReturn( array( 'isSuccess' => !!$delStatus, 'msg' => $msg ), 'json' );
		}
	}

	/**
	 * 获取部门编辑数据
	 * @return void 
	 */
	protected function get() {
		$id = Env::getRequest( 'id' );
		if ( $id == 0 ) { // 总公司
			$this->render( 'editHeadDept' );
		} else {
			$result = Department::model()->fetchByPk( $id );
			$result['manager'] = String::wrapId( array( $result['manager'] ) );
			$result['leader'] = String::wrapId( array( $result['leader'] ) );
			$result['subleader'] = String::wrapId( array( $result['subleader'] ) );
			$depts = DepartmentUtil::loadDepartment();
			$param = array(
				'department' => $result,
				'tree' => String::getTree( $depts, $this->selectFormat, $result['pid'] ),
			);
			$this->render( 'edit', $param );
		}
	}

	/**
	 * 改变前后排序结果
	 * @param integer $index 目标位置
	 * @param integer $deptid 当前部门ID
	 * @param integer $pid 目标PID
	 * @return boolean 
	 */
	protected function setStructure( $index, $deptid, $pid ) {
		$depts = Department::model()->fetchAll( array( 'condition' => "`pid`={$pid} AND `deptid`!={$deptid}", 'order' => "`sort` ASC" ) ); // 把移动到的父级原有的部门找出来
		foreach ( $depts as $k => $dept ) {
			$newSort = $k;
			if ( $newSort >= $index ) {
				$newSort = $k + 1; // 比新插入的部门后，排序加1
			}
			Department::model()->modify( $dept['deptid'], array( 'sort' => $newSort + 1 ) ); // 排序从1开始的，所以+1
		}
		Department::model()->modify( $deptid, array( 'sort' => $index + 1, 'pid' => $pid ) );
		CacheUtil::update( 'department' );
		Org::update();
		return true;
	}

	/**
	 * 处理分支判断
	 * @return void 
	 */
	protected function dealWithBranch() {
		$isBranch = Env::getRequest( 'isbranch' );
		$pid = Env::getRequest( 'pid' );
		if ( $isBranch ) {
			// 如果有部门要设置分支机构，其上级只能为顶级或分支机构
			if ( $pid == 0 || Department::model()->getIsBranch( $pid ) ) {
				// do nothing
			} else {
				$this->error( IBOS::lang( 'Incorrect branch setting' ) );
			}
		}
	}

	/**
	 * 特别参数再处理
	 * @return void
	 */
	protected function dealWithSpecialParams() {
		$_POST['manager'] = implode( ',', String::getUid( $_POST['manager'] ) );
		$_POST['leader'] = implode( ',', String::getUid( $_POST['leader'] ) );
		$_POST['subleader'] = implode( ',', String::getUid( $_POST['subleader'] ) );
	}

}
