<?php

namespace application\modules\email\controllers;

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\email\model\Email;
use application\modules\email\model\EmailBody;
use application\modules\message\model\Notify;

class ApiController extends BaseController {

	public function filterRoutes( $routes ) {
		return true;
	}

	/**
	 * 初始化文件夹ID与存档ID
	 * @return void 
	 */
	public function init() {
		parent::init();
		// 文件夹ID
		$this->fid = intval( Env::getRequest( 'fid' ) );
		// 分类存档ID
		$this->archiveId = intval( Env::getRequest( 'archiveid' ) );
	}

	/**
	 * 获取左侧sidebar统计
	 * @return void 
	 */
	public function actionGetCount() {
		$uid = $this->uid;
		$data = array();
		// 收件箱
		$data['inboxcount'] = Email::model()->countUnreadByListParam( 'inbox', $uid );
		// 未读待办邮件
		$data['todocount'] = Email::model()->countUnreadByListParam( 'todo', $uid );
		// 垃圾箱
		$data['delcount'] = Email::model()->countUnreadByListParam( 'del', $uid );
		$this->ajaxReturn( $data );
	}

	/**
	 * 设置指定人员的所有邮件为已读
	 * @return void 
	 */
	public function actionSetAllRead() {
		$uid = $this->uid;
		Email::model()->setAllRead( $uid );
		$this->ajaxReturn( array( 'isSuccess' => true ) );
	}

	/**
	 * 撤回操作
	 */
	public function actionRecall() {
		$ids = Env::getRequest( 'emailids' );
		$id = String::filterStr( $ids );
		$status = false;
		if ( !empty( $id ) ) {
			$status = Email::model()->recall( $id, $this->uid );
		}
		$errorMsg = !$status ? IBOS::lang( 'Operation failure', 'message' ) : '';
		$this->ajaxReturn( array( 'isSuccess' => !!$status, 'errorMsg' => $errorMsg ) );
	}

	/**
	 * 删除草稿
	 * @return void 
	 */
	public function actionDelDraft() {
		$ids = Env::getRequest( 'emailids' );
		$id = String::filterStr( $ids );
		$status = false;
		if ( !empty( $id ) ) {
			$status = EmailBody::model()->delBody( $id, $this->archiveId );
		}
		$errorMsg = !$status ? IBOS::lang( 'Operation failure', 'message' ) : '';
		$this->ajaxReturn( array( 'isSuccess' => !!$status, 'errorMsg' => $errorMsg ) );
	}

	/**
	 * 彻底删除
	 * @return void
	 */
	public function actionCpDel() {
		$ids = Env::getRequest( 'emailids' );
		$id = String::filterStr( $ids );
		$status = false;
		if ( !empty( $id ) ) {
			$status = Email::model()->completelyDelete( explode( ',', $id ), $this->uid, $this->archiveId );
		}
		$errorMsg = !$status ? IBOS::lang( 'Operation failure', 'message' ) : '';
		$this->ajaxReturn( array( 'isSuccess' => !!$status, 'errorMsg' => $errorMsg ) );
	}

	/**
	 * 标记动作
	 */
	public function actionMark() {
		$op = Env::getRequest( 'op' );
		$opList = array(
			'todo', 'read', 'unread', 'sendreceipt',
			'cancelreceipt', 'del', 'restore',
			'batchdel', 'move'
		);
		if ( !in_array( $op, $opList ) ) {
			exit();
		}
		$ids = Env::getRequest( 'emailids' );
		$id = String::filterStr( $ids );
		$extends = array();
		$condition = 'toid = ' . $this->uid . ' AND FIND_IN_SET(emailid,"' . $id . '")';
		// 采用表驱动法，优化switch条件语法
		$valueDriver = array(
			'read' => array( 'isread', 1 ),
			'unread' => array( 'isread', 0 ),
			'sendreceipt' => array( 'isreceipt', 1 ),
			'cancelreceipt' => array( 'isreceipt', 2 ),
			'restore' => array( 'isdel', 0 )
		);
		switch ( $op ) {
			case 'del':
			case 'batchdel':
				// 删除邮件要获取删除后跳转的ID
				if ( $op == 'del' ) {
					$next = Email::model()->fetchNext( $id, $this->uid, $this->fid, $this->archiveId );
					if ( !empty( $next ) ) {
						$extends['url'] = $this->createUrl( 'content/show', array( 'id' => $next['emailid'], 'archiveid' => $this->archiveId ) );
					} else {
						$extends['url'] = $this->createUrl( 'list/index' );
					}
				}
				$status = Email::model()->setField( 'isdel', 3, $condition );
				break;
			case 'move':
				$fid = intval( Env::getRequest( 'fid' ) );
				$status = Email::model()->updateAll( array( 'fid' => $fid, 'isdel' => 0 ), $condition );
				break;
			case 'todo':
				$markFlag = Env::getRequest( 'ismark' );
				$ismark = strcasecmp( $markFlag, 'true' ) == 0 ? 1 : 0;
				$status = Email::model()->setField( 'ismark', $ismark, $condition );
				break;
			case 'sendreceipt': // 发送回执
				$fromInfo = IBOS::app()->db->createCommand()
						->select( 'eb.bodyid,eb.subject,eb.fromid' )
						->from( '{{email_body}} eb' )
						->leftJoin( '{{email}} e', 'e.bodyid = eb.bodyid' )
						->where( 'e.emailid = ' . intval( $id ) )
						->queryRow();
				if ( $fromInfo ) {
					$config = array(
						'{reader}' => IBOS::app()->user->realname,
						'{url}' => IBOS::app()->urlManager->createUrl( 'email/content/show', array( 'id' => $fromInfo['bodyid'] ) ),
						'{title}' => $fromInfo['subject'],
						'id' => $fromInfo['bodyid'],
					);
					Notify::model()->sendNotify( $fromInfo['fromid'], 'email_receive_message', $config );
				}
			default:
				if ( isset( $valueDriver[$op] ) ) {
					list($key, $value) = $valueDriver[$op];
					$status = Email::model()->setField( $key, $value, $condition );
				} else {
					$status = false;
				}
				break;
		}
		$errorMsg = !$status ? IBOS::lang( 'Operation failure', 'message' ) : '';
		$this->ajaxReturn( array_merge( array( 'isSuccess' => !!$status, 'errorMsg' => $errorMsg ), $extends ) );
	}

}
