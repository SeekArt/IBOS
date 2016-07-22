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
 * @version $Id: DefaultController.php 7310 2016-05-31 03:32:33Z tanghang $
 */

namespace application\modules\dashboard\controllers;

use application\core\model\Log;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Menu;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\components\UserIdentity;
use CHtml;

class DefaultController extends BaseController {

	/**
	 * 登陆处理
	 * @return void
	 */
	public function actionLogin() {
		$access = $this->getAccess();
		$defaultUrl = 'default/index';
		// 已登录即跳转
		if ( $access > 0 ) {
			$this->success( IBOS::lang( 'Login succeed' ), $this->createUrl( $defaultUrl ) );
		}
		// $referStr = Env::getRequest('refer');
		// $referArray = array_filter(explode('&', $referStr));
		// $refer = array_shift($referArray);
		// 显示登陆页面
		if ( !Env::submitCheck( 'formhash' ) ) {
			$data = array(
				'userName' => !empty( $this->user ) ? $this->user['username'] : '',
					// 'refer' => urlencode($refer)
			);
			$this->render( 'login', $data );
		} else {
			$userName = Env::getRequest( 'username' );
			$passWord = Env::getRequest( 'password' );
			if ( !$passWord || $passWord != CHtml::encode( $passWord ) ) {
				$this->error( IBOS::lang( 'Passwd illegal' ) );
			}
			// 开始验证
			// 登录类型
			if ( StringUtil::isMobile( $userName ) ) {
				$loginType = 4;
			} else if ( StringUtil::isEmail( $userName ) ) {
				$loginType = 2;
			} else {
				$loginType = 1;
			};
			//添加对userName的转义，防止SQL错误
			$userName = CHtml::encode( $userName );
			$identity = new UserIdentity( $userName, $passWord, $loginType );
			$result = $identity->authenticate( true );
			if ( $result > 0 ) {
				IBOS::app()->user->login( $identity );
				if ( IBOS::app()->user->uid != 1 ) {
					MainUtil::checkLicenseLimit( true );
				}
				$refer = IBOS::app()->user->getReturnUrl();
				if ( $refer == $this->createUrl( 'default/index' ) || $refer === '/' ) {
					$refer = $this->createUrl( 'index/index' );
				}
				$redirectUrl = $this->createUrl( $defaultUrl, array_filter( array( 'refer' => urldecode( $refer ) ) ) );
				$this->success( IBOS::lang( 'Login succeed' )
						, $redirectUrl );
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
				switch ( $result ) {
					case UserIdentity::USER_NO_ACCESS:
						$msg = IBOS::lang( 'Login failed, not admin' );
						break;
					case UserIdentity::USER_PASSWORD_INCORRECT:
						$msg = IBOS::lang( 'Passwd illegal' );
						break;
					default:
						$msg = IBOS::lang( 'Login failed' );
				}
				$this->error( $msg );
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
			foreach ( $routes as $routeName => $routeConfig ) {
				$data['routes'][$category][$routeName] = array(
					'url' => $this->createUrl( strtolower( $routeName ) ),
					'config' => $routeConfig,
				);
			}
		}
		$refer = Env::getRequest( 'refer' );
		if ( $refer == $this->createUrl( 'default/index' ) ) {
			$refer = $this->createUrl( 'index/index' );
		}
		$def = !empty( $refer ) ? $refer : $this->createUrl( 'index/index' );
		$data['def'] = $def;
		$data['cateConfig'] = $this->returnCateConfig();
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

	/**
	 * 设置侧栏配置
	 * @return type
	 */
	private function returnCateConfig() {
		return array(
			'index' => array(
				'lang' => 'Home page',
				'url' => $this->createUrl( 'index/index' ),
				'id' => 'index',
			),
			'binding' => array(
				'lang' => 'binding',
				'url' => $this->createUrl( 'cobinding/index' ),
				'id' => 'binding',
			),
			'global' => array(
				'lang' => 'Global',
				'url' => $this->createUrl( 'unit/index' ),
				'id' => 'global',
			),
			'organization' => array(
				'lang' => 'User',
				'url' => $this->createUrl( 'user/index' ),
				'id' => 'user',
			),
			'interface' => array(
				'lang' => 'Interface',
				'url' => $this->createUrl( 'nav/index' ),
				'id' => 'interface',
			),
			'module' => array(
				'lang' => 'Module',
				'url' => $this->createUrl( 'module/manager' ),
				'id' => 'module',
			),
			'manager' => array(
				'lang' => 'Manage',
				'url' => $this->createUrl( 'update/index' ),
				'id' => 'manage',
			),
			'service' => array(
				'lang' => 'Service',
				'url' => $this->createUrl( 'service/index' ),
				'id' => 'services',
			),
		);
	}

	/**
	 * 路由配置
	 * @var array
	 */
	private $_controllerMap = array(
		'index' => array(
			'index/index' => array(
				'lang' => 'Management center home page',
				'isShow' => true,
			), 'status/index' => array(
				'lang' => 'System state',
				'isShow' => false,
			),
		),
		'binding' => array(
			'cobinding/index' => array(
				'lang' => 'Co binding',
				'isShow' => true,
			), 'wxbinding/index' => array(
				'lang' => 'Weixin binding',
				'isShow' => true,
			), 'im/index' => array(
				'lang' => 'Action im',
				'isShow' => true,
			),
		),
		'global' => array(
			'unit/index' => array(
				'lang' => 'Unit management',
				'isShow' => true,
			), 'credit/setup' => array(
				'lang' => 'Integral set',
				'isShow' => true,
			), 'usergroup/index' => array(
				'lang' => 'User group',
				'isShow' => true,
			), 'optimize/cache' => array(
				'lang' => 'Performance optimization',
				'isShow' => true,
			), 'date/index' => array(
				'lang' => 'Date setup',
				'isShow' => true,
			), 'upload/index' => array(
				'lang' => 'Upload setting',
				'isShow' => true,
			), 'sms/manager' => array(
				'lang' => 'Sms setting',
				'isShow' => true,
			), 'syscode/index' => array(
				'lang' => 'System code setting',
				'isShow' => true,
			), 'email/setup' => array(
				'lang' => 'Email setting',
				'isShow' => true,
			), 'security/setup' => array(
				'lang' => 'Security setting',
				'isShow' => true,
			), 'sysstamp/index' => array(
				'lang' => 'System stamp',
				'isShow' => true,
			), 'approval/index' => array(
				'lang' => 'Approval process',
				'isShow' => true,
			), 'notify/setup' => array(
				'lang' => 'Notify setup',
				'isShow' => true,
			),
		),
		'organization' => array(
			'user/index' => array(
				'lang' => 'Department personnel management',
				'isShow' => true,
			), 'role/index' => array(
				'lang' => 'Role management',
				'isShow' => true,
			), 'position/index' => array(
				'lang' => 'Position management',
				'isShow' => true,
			), 'roleadmin/index' => array(
				'lang' => 'Admin managment',
				'isShow' => true,
			),
		),
		'interface' => array(
			'nav/index' => array(
				'lang' => 'Navigation setting',
				'isShow' => true,
			), 'quicknav/index' => array(
				'lang' => 'Quicknav setting',
				'isShow' => true,
			), 'login/index' => array(
				'lang' => 'Login page setting',
				'isShow' => true,
			), 'page/index' => array(
				'lang' => 'Login page setting',
				'isShow' => false,
			), 'background/index' => array(
				'lang' => 'System background setting',
				'isShow' => true,
			),
		),
		'module' => array(
			'module/manager' => array(
				'lang' => 'Module manager',
				'isShow' => true,
			),
			'permissions/setup' => array(
				'lang' => 'Permissions setup',
				'isShow' => true,
			),
		),
		'manager' => array(
			'update/index' => array(
				'lang' => 'Update cache',
				'isShow' => true,
			), 'announcement/setup' => array(
				'lang' => 'System announcement',
				'isShow' => true,
			),
			'database/backup' => array(
				'lang' => 'Database',
				'isShow' => true,
			), 'split/index' => array(
				'lang' => 'Table archive',
				'isShow' => false,
			), 'cron/index' => array(
				'lang' => 'Scheduled task',
				'isShow' => true,
			), 'fileperms/index' => array(
				'lang' => 'Check file permissions',
				'isShow' => false,
			), 'upgrade/index' => array(
				'lang' => 'Online upgrade',
				'isShow' => true,
			),
		),
		'service' => array(
			'service/index' => array(
				'lang' => 'Shop',
				'isShow' => true,
			),
		),
	);

}
