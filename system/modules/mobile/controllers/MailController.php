<?php

/**
 * 移动端邮箱控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端控制器文件
 * 
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: MailController.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\email\model\Email;
use application\modules\email\model\EmailBody;
use application\modules\email\model\EmailFolder;
use application\modules\email\utils\Email as EmailUtil;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;

class MailController extends BaseController {

	/**
	 * 默认页,获取主页面各项数据统计
	 * @return void 
	 */
	public function actionIndex() {
		$type = isset( $_GET["type"] ) ? $_GET["type"] : "";
		$keyword = isset( $_GET["search"] ) ? $_GET["search"] : "";
		$lastid = isset( $_GET["lastid"] ) ? intval( $_GET["lastid"] ) : 0;

		$uid = IBOS::app()->user->uid;
		$list = $this->page( $type, $uid, $lastid, $keyword );
		$this->ajaxReturn( array( 'data' => $list['list'], 'lastid' => $list['minid'] ), Mobile::dataType() );
	}

	public function page( $type, $uid, $lastid, $keyword = '' ) {
		$param = Email::model()->getListParam( $type, $uid );
		$condition = $param['condition'];
		if ( !empty( $keyword ) ) {
			$condition .= " AND (eb.subject LIKE '%{$keyword}%' OR eb.content LIKE '%{$keyword}%') ";
		}
		$where = $lastid ? $condition . " and e.emailid < {$lastid}" : $condition;
		$group = isset( $param['group'] ) ? $param['group'] : '';
		$list = IBOS::app()->db->createCommand()
				->select( "*" )
				->from( "{{email}} e" )
				->leftJoin( "{{email_body}} eb", "e.bodyid = eb.bodyid " )
				->where( $where )
				->order( "e.emailid desc" )
				->group( $group )
				->limit( 10 )
				->queryAll();
		$ids = array();
		foreach ( $list as &$value ) {
			$ids[] = $value['emailid'];
			if ( !empty( $value['fromid'] ) ) {
				$value['fromuser'] = User::model()->fetchRealnameByUid( $value['fromid'] );
			} else {
				$value['fromuser'] = $value['fromwebmail'];
			}
		}
		$minid = !empty( $ids ) ? min( $ids ) : '';
		$email = IBOS::app()->db->createCommand()
				->select( "*" )
				->from( "{{email}} e" )
				->join( "{{email_body}} eb", "e.bodyid = eb.bodyid " )
				->where( sprintf( "%s and e.emailid < %d", $where, intval( $minid ) ) )
				->queryRow();
		$data = array(
			'list' => $list,
			'email' => $email,
			'minid' => empty( $email ) ? '' : $minid // 没有更多了返回空
		);
		return $data;
	}

//	public function searchPage( $type, $uid, $lastid, $condition ) {
//		$where = " e.isdel = 0 ";
//		if ( $type == 'inbox' ) {
//			$where .= " and e.toid = {$uid} and e.isweb = 0 and e.isdel = 0";
//		} elseif ( $type == "send" ) {
//			$where .= " and eb.fromid = {$uid} and e.isweb = 0 and e.isdel = 0";
//		}
//
//		$conditions = $lastid ? $where . " and e.emailid < {$lastid}" : $where;
//		$list = IBOS::app()->db->createCommand()
//				->select( "*" )
//				->from( "{{email}} e" )
//				->join( "{{email_body}} eb", "e.bodyid = eb.bodyid " )
//				->where( $condition['condition'] . "AND {$conditions}" )
//				->order( "e.emailid desc" )
//				->limit( 2 )
//				->queryAll();
//		$ids = array();
//		foreach ( $list as &$value ) {
//			$ids[] = $value['emailid'];
//			if ( !empty( $value['fromid'] ) ) {
//				$value['fromuser'] = User::model()->fetchRealnameByUid( $value['fromid'] );
//			} else {
//				$value['fromuser'] = $value['fromwebmail'];
//			}
//		}
//		$minid = min( $ids );
//		$email = IBOS::app()->db->createCommand()
//				->select( "*" )
//				->from( "{{email}} e" )
//				->join( "{{email_body}} eb", "e.bodyid = eb.bodyid " )
//				->where( $where . " and e.emailid < {$minid}" )
//				->queryRow();
//		$data = array(
//			'list' => $list,
//			'email' => $email,
//			'minid' => $minid
//		);
//		return $data;
//	}
//	private function search( $kw ) {
//		$search['keyword'] = $kw;
//		$type = isset( $_GET["type"] ) ? $_GET["type"] : "";
//		$uid = IBOS::app()->user->uid;
//		$lastid = isset( $_GET["lastid"] ) ? intval( $_GET["lastid"] ) : 0;
//		$condition = array();
//		$condition = EmailUtil::mergeSearchCondition( $search, IBOS::app()->user->uid );
//		$condition['condition'] .= "AND ( e.isweb = 0)";
////		$conditionStr = base64_encode( serialize( $condition ) );
//
//		if ( empty( $condition ) ) {
//			$this->error( IBOS::lang( 'Request tainting', 'error' ), $this->createUrl( 'list/index' ) );
//		}
//		$datas = $this->searchPage( $type, $uid, $lastid, $condition );
//		var_dump( $datas['list'] );
//		die;
//		$emailData = Email::model()->fetchAllByArchiveIds( '*', $condition['condition'], $condition['archiveId'], array( 'e', 'eb' ), null, null, SORT_DESC, 'emailid' );
//		
//		
//		$count = count( $emailData );
//		$pages = Page::create( $count, 10, false );
//		$pages->params = array( 'condition' => $conditionStr );
//		$list = array_slice( $emailData, $pages->getOffset(), $pages->getLimit(), false );
//		foreach ( $list as $index => &$mail ) {
//			$mail['fromuser'] = $mail['fromid'] ? User::model()->fetchRealnameByUid( $mail['fromid'] ) : "";
//		}
//		$return = array(
//			'datas' => $list,
//			'pages' => array(
//				'pageCount' => $pages->getPageCount(),
//				'page' => $pages->getCurrentPage(),
//				'pageSize' => $pages->getPageSize()
//			)
//		);
//		$this->ajaxReturn( $datas, Mobile::dataType() );
//	}

	public function actionCategory() {
		$uid = IBOS::app()->user->uid;
		$myFolders = EmailFolder::model()->fetchAllUserFolderByUid( $uid );
		// 获取未读邮件数目
		$notReadCount = Email::model()->countUnreadByListParam( 'inbox', $uid );
		//$notReadCount = Email::model()->countNotReadByToid( $uid, 'web' );
		$return = array(
			'folders' => $myFolders,
			'notread' => $notReadCount
		);
		$this->ajaxReturn( $return, Mobile::dataType() );
	}

	public function actionShow() {
		$id = isset( $_GET["id"] ) ? $_GET['id'] : 0;

		$email = Email::model()->fetchById( $id, 0 );
		//处理附件
		if ( !empty( $email ) ) {
			if ( !empty( $email['attachmentid'] ) ) {
				$email["attach"] = Attach::getAttach( $email["attachmentid"], TRUE, FALSE, FALSE, FALSE, TRUE );
				$attachmentArr = explode( ",", $email['attachmentid'] );
			}
		}
		Email::model()->setRead( $id, IBOS::app()->user->uid );

		$this->ajaxReturn( $email, Mobile::dataType() );
	}

	public function actionDraftShow() {
		$id = isset( $_GET["bodyid"] ) ? $_GET['bodyid'] : 0;
		$emailBody = EmailBody::model()->fetchByPk( $id );

		$this->ajaxReturn( $emailBody, Mobile::dataType() );
	}

	public function actionEdit() {
		$bodyData["subject"] = Env::getRequest( 'subject' );
		$bodyData["content"] = Env::getRequest( 'content' );
		$bodyData["toids"] = Env::getRequest( 'toids' );
		$bodyData["copytoids"] = Env::getRequest( 'ccids' );
		$bodyData["secrettoids"] = Env::getRequest( 'mcids' );
		$bodyData["isneedreceipt"] = 0;
		$bodyData['issend'] = 1;
		$bodyData["fromid"] = IBOS::app()->user->uid;
		$bodyData["sendtime"] = time();

		$bodyId = EmailBody::model()->add( $bodyData, true );
		Email::model()->send( $bodyId, $bodyData );
		$emailBody = EmailBody::model()->fetch( "bodyid = {$bodyId}" );
		$this->ajaxReturn( $emailBody, Mobile::dataType() );
	}

	public function actionDel() {
		$ids = Env::getRequest( 'emailid' );
		$id = StringUtil::filterStr( $ids );
		$status = false;
		if ( !empty( $id ) ) {
			$condition = 'toid = ' . intval( IBOS::app()->user->uid ) . ' AND FIND_IN_SET(emailid,"' . $id . '")';
			$status = Email::model()->setField( 'isdel', 1, $condition );
		}
		$errorMsg = !$status ? IBOS::lang( 'Operation failure', 'message' ) : '';
		$this->ajaxReturn( array( 'isSuccess' => !!$status, 'errorMsg' => $errorMsg ), Mobile::dataType() );
	}

	public function actionMark() {
		$id = Env::getRequest( 'emailid' );
		$status = false;
		if ( !empty( $id ) ) {
			$condition = 'toid = ' . $this->uid . ' AND FIND_IN_SET(emailid,"' . $id . '")';
			$markFlag = Env::getRequest( 'ismark' );
			$ismark = strcasecmp( $markFlag, 'true' ) == 0 ? 1 : 0;
			$status = Email::model()->setField( 'ismark', $ismark, $condition );
		}
		$errorMsg = !$status ? IBOS::lang( 'Operation failure', 'message' ) : '';
		$this->ajaxReturn( array( 'isSuccess' => !!$status, 'errorMsg' => $errorMsg ), Mobile::dataType() );
	}

	// 彻底删除邮件
	public function actionDelete() {
		$ids = Env::getRequest( 'emailid' );
		$id = StringUtil::filterStr( $ids );
		$status = false;
		if ( !empty( $id ) ) {
			$status = Email::model()->completelyDelete( explode( ',', $id ), IBOS::app()->user->uid );
		}
		$this->ajaxReturn( array( 'isSuccess' => !!$status ), Mobile::dataType() );
	}

	// 恢复邮件
	public function actionRecovery() {
		$ids = Env::getRequest( 'emailid' );
		$id = StringUtil::filterStr( $ids );
		$status = false;
		if ( !empty( $id ) ) {
			$condition = 'toid = ' . $this->uid . ' AND FIND_IN_SET(emailid,"' . $id . '")';
			$status = Email::model()->setField( 'isdel', 0, $condition );
		}
		$errorMsg = !$status ? IBOS::lang( 'Operation failure', 'message' ) : '';
		$this->ajaxReturn( array( 'isSuccess' => !!$status, 'errorMsg' => $errorMsg ), Mobile::dataType() );
	}

}
