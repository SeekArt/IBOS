<?php

/**
 * 后台默认控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 后台模块默认控制器类
 * 
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: DefaultController.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\dashboard\controllers;

use application\core\model\Log;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\dashboard\model\Menu;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\components\UserIdentity;

class DefaultController extends BaseController {

	/**
	 * 控制器-动作映射数组，用于生成url
	 * @var array 
	 */
	private $_controllerMap = array(
		'index' => array( 'index/index', 'status/index' ),
		'weixin' => array( 'wxBinding/index', 'wxSync/index', 'wxCenter/index' ),
		'co' => array( 'Cobinding/index' ),
		'global' => array(
			'unit/index', 'date/index', 'sysCode/index',
			'userGroup/index', 'credit/setup', 'optimize/cache',
			'upload/index', 'security/setup', 'sms/manager',
			'im/index', 'email/setup', 'sysStamp/index',
			'approval/index', 'notify/setup'
		),
		'organization' => array( 'user/index', 'role/index', 'position/index' ),
		'interface' => array( 'nav/index', 'login/index', 'page/index', 'quicknav/index', 'background/index' ),
		'module' => array( 'module/manager', 'permissions/setup' ),
		'manager' => array(
			'update/index', 'announcement/setup', 'task/index',
			'database/backup', 'upgrade/index', 'fileperms/index',
			'cron/index', 'split/index'
		),
		'service' => array( 'service/index' )
	);

	/**
	 * 登陆处理
	 * @return void 
	 */
	public function actionLogin() {
		$access = $this->getAccess();
		$defaultUrl = $this->createUrl( 'default/index' );
		// 已登录即跳转
		if ( $access > 0 ) {
			$this->success( IBOS::lang( 'Login succeed' ), $defaultUrl );
		}
		$refer = Env::getRequest( 'refer' );
		// 显示登陆页面
		if ( !Env::submitCheck( 'formhash' ) ) {
			$data = array(
				'userName' => !empty( $this->user ) ? $this->user['username'] : '',
				'refer' => $refer
			);
			$this->render( 'login', $data );
		} else {
			$userName = Env::getRequest( 'username' );
			$passWord = Env::getRequest( 'password' );
			if ( !$passWord || $passWord != addslashes( $passWord ) ) {
				$this->error( IBOS::lang( 'Passwd illegal' ) );
			}
			// 开始验证
			// 登录类型
			if ( String::isMobile( $userName ) ) {
				$loginType = 4;
			} else if ( String::isEmail( $userName ) ) {
				$loginType = 2;
			} else {
				$loginType = 1;
			};
                                                          //15-7-27 下午2:03 gzdzl 添加对userName的转义，防止SQL错误
			$userName = addslashes( $userName );
			$identity = new UserIdentity( $userName, $passWord, $loginType );
			$result = $identity->authenticate( true );
			if ( $result > 0 ) {
				IBOS::app()->user->login( $identity );
				if ( IBOS::app()->user->uid != 1 ) {
					MainUtil::checkLicenseLimit( true );
				}

				$this->success( IBOS::lang( 'Login succeed' ), $defaultUrl . (!empty( $refer ) ? '&refer=' . urlencode( $refer ) : '') );
			} else {
				// 记录登录错误日志
				// 加密密码字符串
				$passWord = preg_replace( "/^(.{" . round( strlen( $passWord ) / 4 ) .
						"})(.+?)(.{" . round( strlen( $passWord ) / 6 ) . "})$/s", "\\1***\\3", $passWord );
				$log = array(
					'user' => $userName,
					'password' => $passWord,
					'ip' => IBOS::app()->setting->get( 'clientip' )
				);
				Log::write( $log, 'illegal', 'module.dashboard.login' );
				$this->error( IBOS::lang( 'Login failed' ) );
			}
		}
	}

	/**
	 * 外层框架主页
	 * @return void 
	 */
	public function actionIndex() {
		// 视图变量
		$data = array();
		$data['moduleMenu'] = Menu::model()->fetchAllRootMenu();
		// 控制器连接生成
		foreach ( $this->getControllerMap() as $category => $routes ) {
			while ( list($index, $route) = each( $routes ) ) {
				list($controller, ) = explode( '/', $route );
				$data[$category][$controller] = $this->createUrl( strtolower( $route ) );
			}
		}
		$refer = Env::getRequest( 'refer' );
		if ( !empty( $refer ) ) {
			$def = $refer;
		} else {
			$def = $data['index']['index'];
		}
		$data['def'] = $def;
		$this->render( 'index', $data );
	}

	/**
	 * 查询后台操作
	 * @return void
	 */
	public function actionSearch() {
		if ( Env::submitCheck( 'formhash' ) ) {
			$data = array();
			$keywords = trim( $_POST['keyword'] );
			$kws = array_map( 'trim', explode( ' ', $keywords ) );
			$keywords = implode( ' ', $kws );
			if ( $keywords ) {
				$searchIndex = IBOS::getLangSource( 'dashboard.searchIndex' );
				$result = $html = array();
				// 查找关键字所在的项目
				foreach ( $searchIndex as $skey => $items ) {
					foreach ( $kws as $kw ) {
						foreach ( $items['text'] as $k => $text ) {
							if ( strpos( strtolower( $text ), strtolower( $kw ) ) !== false ) {
								$result[$skey][] = $k;
							}
						}
					}
				}
				// 处理好引号给前台用以高亮显示关键字
				$data['kws'] = array_map( (function($item) {
					return sprintf( '"%s"', $item );
				} ), $kws );
				if ( $result ) {
					$totalCount = 0;
					$item = IBOS::lang( 'Item' );
					foreach ( $result as $skey => $tkeys ) {
						// 具体项目的链接
						$tmp = array();
						foreach ( $searchIndex[$skey]['index'] as $title => $url ) {
							$tmp[] = '<a href="' . $url . '" target="_self">' . $title . '</a>';
						}
						$links = implode( ' &raquo; ', $tmp );
						$texts = array();
						$tkeys = array_unique( $tkeys );
						foreach ( $tkeys as $tkey ) {
							$texts[] = '<li><span data-class="highlight">' . $searchIndex[$skey]['text'][$tkey] . '</span></li>';
						}
						$texts = implode( '', array_unique( $texts ) );
						$totalCount += $count = count( $tkeys );
						$html[] = <<<EOT
								<div class="ctb">
									<h2 class="st">{$count} {$item}</h2>
									<div>
										<strong>{$links}</strong>
										<ul class="tipsblock">{$texts}</ul>
									</div>
								</div>
EOT;
					}
					if ( $totalCount ) {
						$data['total'] = $totalCount;
						$data['html'] = $html;
					} else {
						$data['msg'] = IBOS::lang( 'Search result noexists' );
					}
				} else {
					$data['msg'] = IBOS::lang( 'Search result noexists' );
				}
			} else {
				$data['msg'] = IBOS::lang( 'Search keyword noexists' );
			}
			$this->render( 'search', $data );
		}
	}

	/**
	 * getter方法,获取控制器映射数组
	 * @return array
	 */
	protected function getControllerMap() {
		return $this->_controllerMap;
	}

	/**
	 * 登出操作
	 * @return void 
	 */
	public function actionLogout() {
		IBOS::app()->user->logout();
		$this->showMessage( IBOS::lang( 'Logout succeed' ), IBOS::app()->urlManager->createUrl( $this->loginUrl ) );
	}

}
