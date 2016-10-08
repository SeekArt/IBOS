<?php

namespace application\modules\email\controllers;

use application\core\controllers\Controller;
use application\core\utils\Ibos;
use application\modules\email\model\EmailFolder;
use application\modules\email\model\EmailWeb;
use application\modules\main\utils\Main as MainUtil;

class BaseController extends Controller {

	const INBOX_ID = 1; // 收件箱ID
	const DRAFT_ID = 2; // 草稿箱ID
	const SENT_ID = 3; // 已发送邮件箱 ID
	const TRASH_ID = 4; //垃圾箱 ID
	const DEFAULT_PAGE_SIZE = 10; //列表页默认显示条目

	/**
	 * 默认的页面属性
	 * @var array 
	 */

	private $_attributes = array(
		'uid' => 0,
		'fid' => 0,
		'webId' => 0,
		'archiveId' => 0,
		'allowWebMail' => false,
		'subOp' => '',
		'folders' => array( ),
		'webMails' => array( ),
	);

	/**
	 * 设置相对应属性值
	 * @param string $name 
	 * @param mixed $value
	 */
	public function __set( $name, $value ) {
		if ( isset( $this->_attributes[$name] ) ) {
			$this->_attributes[$name] = $value;
		} else {
			parent::__set( $name, $value );
		}
	}

	/**
	 * 获取对应属性值
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		if ( isset( $this->_attributes[$name] ) ) {
			return $this->_attributes[$name];
		} else {
			parent::__get( $name );
		}
	}

	/**
	 * 初始化用户ID，用户文件夹，外部邮箱及其列表等全局通用的数据
	 * @return void 
	 */
	public function init() {
		$this->uid = $uid = intval( Ibos::app()->user->uid );
		// 个人文件夹
		$this->folders = EmailFolder::model()->fetchAllUserFolderByUid( $uid );
		// 是否允许外部邮箱
		$this->allowWebMail = (bool) Ibos::app()->setting->get( 'setting/emailexternalmail' );
		// 个人外部邮箱列表
		$this->webMails = $this->allowWebMail ? EmailWeb::model()->fetchAllByUid( $uid ) : array( );
		parent::init();
	}

	/**
	 * 得到侧栏视图渲染结果
	 * @params string 当前动作
	 * @return string
	 */
	protected function getSidebar( $op = '' ) {
		$archiveTable = array( );
		// 获取存档表数据
		$settings = Ibos::app()->setting->get( 'setting' );
		// 存档表ID数组
		$archiveTable['ids'] = $settings['emailtableids'] ? $settings['emailtableids'] : array( );
		// 存档表信息数组
		$archiveTable['info'] = $settings['emailtable_info'] ? $settings['emailtable_info'] : array( );
		foreach ( $archiveTable['ids'] as $tableId ) {
			if ( $tableId != 0 && empty( $archiveTable['info'][$tableId]['displayname'] ) ) {
				$archiveTable['info'][$tableId]['displayname'] = Ibos::lang( 'Unnamed archive' ) . '(' . $tableId . ')';
			}
		}
		// sidebar所用的渲染视图数据
		$data = array(
			'op' => $op,
			'uid' => $this->uid,
			'lang' => Ibos::getLangSources(),
			'folders' => $this->folders,
			'allowWebMail' => $this->allowWebMail,
			'webEmails' => $this->webMails,
			'fid' => $this->fid,
			'webId' => $this->webId,
			'archiveId' => $this->archiveId,
			'hasArchive' => count( $archiveTable['ids'] ) > 1,
			'archiveTable' => $archiveTable
		);
		$sidebarAlias = 'application.modules.email.views.sidebar';
		$sidebarView = $this->renderPartial( $sidebarAlias, $data, true );
		return $sidebarView;
	}

	/**
	 * 设置列表页默认显示条数
	 * @param integer $size
	 */
	protected function setListPageSize( $size ) {
		$size = intval( $size );
		if ( $size > 0 && in_array( $size, array( 5, 10, 20 ) ) ) {
			MainUtil::setCookie( 'email_pagesize_' . $this->uid, $size, 0, 0 );
		}
	}

	/**
	 * 获取列表设置的条数
	 * @return integer
	 */
	protected function getListPageSize() {
		$pageSize = MainUtil::getCookie( 'email_pagesize_' . $this->uid, 0 );
		if ( is_null( $pageSize ) ) {
			$pageSize = self::DEFAULT_PAGE_SIZE;
		}
		return $pageSize;
	}

}