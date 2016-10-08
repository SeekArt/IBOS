<?php

/**
 * 文件柜模块------ 基类控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 文件柜模块------ 继承Controller
 * @package application.modules.file.controllers
 * @version $Id: BaseController.php 3297 2014-06-19 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\controllers;

use application\core\controllers\Controller;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File as FileUtil;
use application\core\utils\Ibos;
use application\modules\file\core\FileAttr;
use application\modules\file\core\FileCloud;
use application\modules\file\core\FileLocal;
use application\modules\file\core\FileOperationApi;
use application\modules\file\model\File;
use application\modules\file\utils\FileCheck;
use application\modules\file\utils\FileOffice;
use application\modules\main\components\CommonAttach;
use application\modules\main\utils\Main as MainUtil;
use CJSON;

class BaseController extends Controller {

	protected $condition = '';
	protected $uid;
	protected $cloudid; // 云盘id，0为本地
	protected $core; // 处理数据类(本地、云盘)
	protected $belongType = 0; // 所属类型，0个人，1公司
	protected $order = array(
		0 => "f.addtime DESC",
		1 => "f.addtime ASC",
		2 => "f.size ASC",
		3 => "f.size DESC",
		4 => "f.name ASC",
		5 => "f.name DESC"
	);
	public $orderIndex = 0;
	public $type;
	public $search = 0;

	public function init() {
		$this->uid = Ibos::app()->user->uid;
		$cloudOpen = Ibos::app()->setting->get( 'setting/filecloudopen' );
		if ( !$cloudOpen ) {
			$this->cloudid = 0;
		} else {
			$this->cloudid = Ibos::app()->setting->get( 'setting/filecloudid' );
		}
		parent::init();
	}

	/**
	 * 获取附件处理核心
	 * @return object
	 */
	private function getCore() {
		// if ( $this->cloudid == 0 ) {
		$core = new FileLocal();
		// } else {
		//    $core = new FileCloud( $this->cloudid );
		// }
		return $core;
	}

	/**
	 * 处理输出列表
	 * @param array $list
	 * @return array
	 */
	protected function handleList( $list ) {
		$core = $this->getCore();
		$attachDir = FileUtil::getAttachUrl() . '/';
		foreach ( $list as &$li ) {
			if ( $li['type'] == File::FILE && isset( $li['attachmentid'] ) ) { // 文件类型
				$attachs = Attach::getAttachData( $li['attachmentid'] );
				$attach = array_shift( $attachs );
				$li['fileurl'] = FileUtil::fileName( $attachDir . $attach['attachment'] );
				$li['iconbig'] = Attach::attachType( $li['filetype'], 'bigicon' );
				if ( in_array( Attach::attachType( $li['filetype'], 'id' ), range( 3, 6 ) ) ) {
					$li['officereadurl'] = $core->getOfficeReadUrl( Attach::getAttachStr( $li['attachmentid'] ) );
				}
				if ( in_array( Attach::attachType( $li['filetype'], 'id' ), range( 3, 5 ) ) ) {
					$li['officeediturl'] = $core->getOfficeEditUrl( Attach::getAttachStr( $li['attachmentid'] ) );
				}
			}
			if ( $li['type'] == File::FOLDER ) {
				$li['size'] = File::model()->countSizeByFid( $li['fid'] );
			}
			$li['formattedsize'] = Convert::sizeCount( $li['size'] );
			$li['formattedaddtime'] = date( 'Y/m/d', $li['addtime'] );
		}
		return $list;
	}

	/**
	 * 搜索
	 * @return void
	 */
	protected function search() {
		if ( Env::getRequest( 'search' ) == '1' ) {
			$this->search = 1;
			$conditionCookie = MainUtil::getCookie( 'condition' );
			if ( empty( $conditionCookie ) ) {
				MainUtil::setCookie( 'condition', $this->condition, 10 * 60 );
			}
			if ( Env::getRequest( 'normal_search' ) == '1' ) {
				$keyword = \CHtml::encode( Env::getRequest( 'keyword' ) );
				$this->condition = "f.name LIKE '%{$keyword}%'";
				MainUtil::setCookie( 'keyword', $keyword, 10 * 60 );
			} else {
				$this->condition = $conditionCookie;
			}
			//把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
			if ( $this->condition != MainUtil::getCookie( 'condition' ) ) {
				MainUtil::setCookie( 'condition', $this->condition, 10 * 60 );
			}
		}
	}

	/**
	 * 获取排序条件(默认"addtime DESC")
	 * @return string
	 */
	protected function getOrder() {
		$index = intval( Env::getRequest( 'orderIndex' ) );
		$this->orderIndex = in_array( $index, array_keys( $this->order ) ) ? $index : 0;
		$order = $this->order[$this->orderIndex];
		return $order;
	}

	/**
	 * 获取类型条件
	 * @return string
	 */
	protected function getTypeCondition() {
		$type = Env::getRequest( 'type' );
		$allowTypes = array( 'word', 'excel', 'ppt', 'text', 'image', 'package', 'audio', 'video', 'program' );
		if ( $type == 'mark' ) {
			$con = "fd.mark=1";
		} else if ( in_array( $type, $allowTypes ) ) {
			$allType = FileOffice::getAllType();
			$con = sprintf( "FIND_IN_SET(fd.filetype, '%s')", $allType[$type] );
		} else {
			$con = '1';
			$type = 'all';
		}
		$this->type = $type;
		return $con;
	}

	/**
	 * 获取文件属性对象
	 * @param integer $pid 父级id
	 * @return FileAttr
	 */
	protected function getFileAttr( $pid = null ) {
		$attr = array(
			'class' => 'application\modules\file\core\FileAttr',
			'uid' => $this->uid,
			'cloudid' => $this->cloudid,
			'belongType' => $this->belongType
		);
		if ( !is_null( $pid ) ) {
			$attr['pid'] = $pid;
		}
		return Ibos::createComponent( $attr );
	}

	/**
	 * 上传文件
	 */
	protected function upload() {
		$pid = intval( Env::getRequest( 'pid' ) );
		$attach = new CommonAttach( 'Filedata', 'file' );
		$return = CJSON::decode( $attach->upload() );
		$attachids = $return['aid'];
		$res = FileOperationApi::getInstance()->upload( $this->getFileAttr( $pid ), $this->getCore(), $attachids );
		if ( $res ) {
			$this->ajaxReturn( $return );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Upload failed' ) ) );
		}
	}

	/**
	 * 创建文件夹
	 */
	protected function mkDir() {
		$pid = intval( Env::getRequest( 'pid' ) );
		$dirname = Env::getRequest( 'name' );
		if ( FileCheck::getInstance()->isExist( $dirname, $pid, $this->uid, $this->cloudid, $this->belongType ) ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'The samename folder exist' ) ) );
		}
		$fid = FileOperationApi::getInstance()->mkDir( $this->getFileAttr( $pid ), $dirname );
		if ( $fid ) {
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Create succeed' ), 'fid' => $fid ) );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Create failed' ) ) );
		}
	}

	/**
	 * 创建办公文件
	 */
	protected function mkOffice() {
		$pid = intval( Env::getRequest( 'pid' ) );
		$type = strtolower( Env::getRequest( 'type' ) );
		$name = Env::getRequest( 'name' ) . '.' . $type;
		if ( !in_array( $type, array( 'doc', 'ppt', 'xls' ) ) ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'File type error' ) ) );
		}
		if ( FileCheck::getInstance()->isExist( $name, $pid, $this->uid, $this->cloudid, $this->belongType ) ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'The samename file exist' ) ) );
		}
		$res = FileOperationApi::getInstance()->mkOffice( $this->getFileAttr( $pid ), $this->getCore(), $name, $type, $this->module->getId() );
		if ( $res ) {
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Create succeed' ) ) );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Create failed' ) ) );
		}
	}

	/**
	 * 重命名（单文件操作）
	 */
	protected function rename() {
		$fid = intval( Env::getRequest( 'fid' ) );
		$newName = htmlspecialchars( strtolower( trim( Env::getRequest( 'name' ) ) ) );
		$file = File::model()->fetchByPk( $fid );
		if ( $file['uid'] != $this->uid ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Can not edit other\'s file' ) ) );
		}
		$sameNameFile = File::model()->fetchByNameWidthPid( $newName, $file['pid'], $this->uid );
		if ( !empty( $sameNameFile ) ) {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'The samename file exist' ) ) );
		}
		$res = FileOperationApi::getInstance()->rename( $fid, $newName );
		if ( $res ) {
			$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Operation succeed', 'message' ) ) );
		} else {
			$this->ajaxReturn( array( 'isSuccess' => false, 'msg' => Ibos::lang( 'Operation failure', 'message' ) ) );
		}
	}

	/**
	 * 下载（单/多文件操作）
	 */
	protected function download() {
		$fids = trim( Env::getRequest( 'fids' ), ',' );
		$downloadName = Env::getRequest( 'downloadName' );
		FileOperationApi::getInstance()->download( $this->getCore(), $fids, $downloadName );
	}

	/**
	 * 复制（多文件操作）
	 */
	protected function copy() {
		$sourceFids = trim( Env::getRequest( 'sourceFids' ), ',' ); // 操作的文件id
		$targetFid = intval( Env::getRequest( 'targetFid' ) ); // 目标文件夹
		FileOperationApi::getInstance()->copy( $this->getFileAttr(), $sourceFids, $targetFid );
		$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Operation succeed', 'message' ) ) );
	}

	/**
	 * 剪切（多文件操作）
	 */
	protected function cut() {
		$sourceFids = trim( Env::getRequest( 'sourceFids' ), ',' ); // 操作的文件id
		$targetFid = intval( Env::getRequest( 'targetFid' ) ); // 目标文件夹
		FileOperationApi::getInstance()->cut( $this->getFileAttr(), $sourceFids, $targetFid );
		$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Operation succeed', 'message' ) ) );
	}

	/**
	 * 删除至回收站
	 * @param array $deletes 要还原的数据
	 */
	protected function recycle() {
		$fids = Env::getRequest( 'fids' );
		FileOperationApi::getInstance()->recycle( $fids );
		$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Operation succeed', 'message' ) ) );
	}

	/**
	 * 标记
	 */
	protected function mark() {
		$fid = Env::getRequest( 'fid' );
		$mark = intval( Env::getRequest( 'mark' ) );
		FileOperationApi::getInstance()->mark( $fid, $mark );
		$this->ajaxReturn( array( 'isSuccess' => true, 'msg' => Ibos::lang( 'Operation succeed', 'message' ) ) );
	}

	/**
	 * 打开
	 */
	protected function open() {
		$fileurl = Env::getRequest( 'fileurl' );
		$openType = Env::getRequest( 'openType' );
		$core = $this->getCore();
		return $this->$openType( $fileurl, $core );
	}

}
