<?php

/**
 * 信息中心模块------ 分类控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 信息中心模块------  分类控制器类，继承ArticleBaseController
 * @package application.modules.comment.controllers
 * @version $Id: CategoryController.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\controllers;

use application\core\utils\IBOS;
use application\core\utils\Env;
use application\modules\article\core\ArticleCategory;
use application\modules\article\model\ArticleCategory as CateModel;
use application\modules\dashboard\model\Approval;

class CategoryController extends BaseController {

	/**
	 * 分类对象
	 * @var object
	 * @access private
	 */
	private $_category = null;

	/**
	 * 初始化当前分类对象
	 * @return void
	 */
	public function init() {
		if ( $this->_category === null ) {
			$this->_category = new ArticleCategory( 'application\modules\article\model\ArticleCategory' );
		}
	}

	/**
	 * 默认动作
	 */
	public function actionIndex() {
		if ( IBOS::app()->request->getIsAjaxRequest() ) {
			$data = CateModel::model()->fetchAll( array( 'order' => 'sort ASC' ) );
			$this->ajaxReturn( $this->_category->getAjaxCategory( $data ), 'json' );
		}
	}

	/**
	 * 新建
	 */
	public function actionAdd() {
		$pid = Env::getRequest( 'pid' );
		$name = trim( Env::getRequest( 'name' ) );
		$aid = intval( Env::getRequest( 'aid' ) );
		// 查询出最大的sort
		$cond = array( 'select' => 'sort', 'order' => "`sort` DESC" );
		$sortRecord = CateModel::model()->fetch( $cond );
		if ( empty( $sortRecord ) ) {
			$sortId = 0;
		} else {
			$sortId = $sortRecord['sort'];
		}
		// 排序号默认在最大的基础上加1，方便上移下移操作
		$newSortId = $sortId + 1;
		$ret = CateModel::model()->add(
				array(
			'sort' => $newSortId,
			'pid' => $pid,
			'name' => $name,
			'aid' => $aid
				), true );
		$url = $this->createUrl( 'default/index&catid=' . $ret );
		$this->ajaxReturn( array( 'IsSuccess' => !!$ret, 'id' => $ret, 'url' => $url, 'aid' => $aid ), 'json' );
	}

	/**
	 * 编辑
	 */
	public function actionEdit() {
		$op = Env::getRequest( 'op' );
		$option = empty( $op ) ? 'default' : $op;
		if ( $option == 'default' ) {
			$pid = intval( Env::getRequest( 'pid' ) );
			$name = trim( Env::getRequest( 'name' ) );
			$catid = intval( Env::getRequest( 'catid' ) );
			$aid = intval( Env::getRequest( 'aid' ) );
			if ( $pid == $catid ) {
				$this->error( IBOS::lang( 'Parent and current can not be the same' ) );
			}
			$ret = CateModel::model()->modify( $catid, array( 'pid' => $pid, 'name' => $name, 'aid' => $aid ) );
			$this->ajaxReturn( array( 'IsSuccess' => !!$ret, 'aid' => $aid ), 'json' );
		} else {
			$this->$option();
		}
	}

	/**
	 * 删除
	 */
	public function actionDel() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$catid = Env::getRequest( 'catid' );
			// 判断顶级分类少于一个不给删除
			$category = CateModel::model()->fetchByPk( $catid );
			$supCategoryNum = CateModel::model()->countByAttributes( array( 'pid' => 0 ) );
			if ( !empty( $category ) && $category['pid'] == 0 && $supCategoryNum == 1 ) {
				$this->ajaxReturn( array( 'IsSuccess' => false, 'msg' => IBOS::lang( 'Leave at least a Category' ) ), 'json' );
			}
			$ret = $this->_category->delete( $catid );
			if ( $ret == -1 ) {
				$this->ajaxReturn( array( 'IsSuccess' => false, 'msg' => IBOS::lang( 'Contents under this classification only be deleted when no content' ) ), 'json' );
			}
			$this->ajaxReturn( array( 'IsSuccess' => !!$ret ), 'json' );
		}
	}

	/**
	 * 移动
	 */
	protected function move() {
		if ( IBOS::app()->request->isAjaxRequest ) {
			$moveType = Env::getRequest( 'type' );
			$pid = Env::getRequest( 'pid' );
			$catid = Env::getRequest( 'catid' );
			$ret = $this->_category->move( $moveType, $catid, $pid );
			$this->ajaxReturn( array( 'IsSuccess' => !!$ret ), 'json' );
		}
	}

	/**
	 * 获得所有审批流程
	 */
	protected function getApproval() {
		$approvals = Approval::model()->fetchAllApproval();
		$this->ajaxReturn( array( 'approvals' => $approvals ) );
	}

	/**
	 * 获取某个分类的审批流程
	 */
	protected function getCurApproval() {
		$catid = Env::getRequest( 'catid' );
		$category = CateModel::model()->fetchByPk( $catid );
		$approval = Approval::model()->fetchByPk( $category['aid'] );
		$this->ajaxReturn( array( 'approval' => $approval ) );
	}

}
