<?php

/**
 * 主模块初始化行为文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 主模块初始化,执行主要操作如初始化环境变量，初始化链接，升级，license,session,缓存及
 * 系统配置等重要操作
 * @package application.module.main.components
 * @version $Id: InitMainModule.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\main\behaviors;

use application\core\utils\Convert;
use application\core\utils\DateTime;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\core\utils\String;
use application\core\utils\Upgrade;
use application\modules\dashboard\model\Syscache;
use application\modules\main\model\Setting;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\components\UserIdentity;
use application\modules\user\model\User;
use application\modules\user\model\UserStatus;
use CBehavior;
use CException;
use CJSON;

class InitMainModule extends CBehavior {

	/**
	 * 允许未登录用户访问的URL
	 * @var array
	 */
	protected $allowedGuestUserRoutes = array(
		'user/default/login',
		'user/default/reset',
		'user/default/logout',
		'user/default/ajaxlogin',
		'user/default/checklogin',
		'user/default/wxcode',
		'dashboard/default/login',
		'dashboard/default/logout',
		'mobile/api',
		'mobile/default/login',
		'mobile/default/logout',
		'main/default/getCert',
		'main/default/unsupportedBrowser',
        'main/default/update',
        'assets/mobile/index', // 资产管理移动端盘点
        'assets/mobile/login', //资产管理移动端登陆
	);

	/**
	 * 覆盖父类方法，附加初始化核心行为到父类的组件
	 * @param mixed $owner
	 */
	public function attach( $owner ) {
		// 处理运行环境
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleInitEnvironment' ) );
		// 处理请求提交
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleInitInput' ) );
		// 加载系统缓存
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleLoadSysCache' ) );
		if ( !defined( "IN_DEBUG" ) ) {
			// 处理用户连接
			$owner->attachEventHandler( 'onInitModule', array( $this, 'handleBeginRequest' ) );
		}
		// 初始化session组件
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleInitSession' ) );
		// 配置系统设定
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleSystemConfigure' ) );
		// 处理计划任务
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleInitCron' ) );
		// 处理组织架构数据
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleInitOrg' ) );
		// 检查更新升级
		$owner->attachEventHandler( 'onInitModule', array( $this, 'handleCheckUpgrade' ) );
	}

	/**
	 * 开始处理请求，处理客户请求与是否需要登录
	 * @param mixed $event
	 */
	public function handleBeginRequest( $event ) {
		// 创建一个可访问的url列表
		// 这些url也要考虑到程序在升级的过程中可用的情况
		$allowedGuestUserUrls = array();
		foreach ( $this->allowedGuestUserRoutes as $allowedGuestUserRoute ) {
			$allowedGuestUserUrls[] = IBOS::app()->createUrl( $allowedGuestUserRoute );
		}
		// 获取当前url请求,判断是否在可访问url列表内
		$requestedUrl = IBOS::app()->getRequest()->getUrl();
		$isUrlAllowedToGuests = false;
		//不能用in_array代替了下面的判断是因为还有URL后参数是可变的
		foreach ( $allowedGuestUserUrls as $url ) {
			if ( strpos( $requestedUrl, $url ) === 0 ) {
				$isUrlAllowedToGuests = true;
				break;
			}
		}
		// 兼容swfupload上传
		$uid = Env::getRequest( 'uid' );
		$swfHash = Env::getRequest( 'hash' );
		if ( $uid && $swfHash ) {
            defined('IN_SWFHASH') or define('IN_SWFHASH', true);
			$authKey = IBOS::app()->setting->get( 'config/security/authkey' );
			if ( (empty( $uid )) || $swfHash != md5( substr( md5( $authKey ), 8 ) . $uid ) ) {
				exit();
			}
			$curUser = User::model()->fetchByUid( $uid );
			// 开始登录
			// 登录类型
			if ( String::isMobile( $curUser['username'] ) ) {
				$loginType = 4;
			} else if ( String::isEmail( $curUser['username'] ) ) {
				$loginType = 2;
			} else {
				$loginType = 1;
			};
			$identity = new UserIdentity( $curUser['username'], $curUser['password'], $loginType );
			$identity->setId( $uid );
			$identity->setPersistentStates( $curUser );
			// 是否允许多个账户同时登录
			$account = IBOS::app()->setting->get( 'setting/account' );
			$user = IBOS::app()->user;
			if ( $account['allowshare'] != 1 ) {
				$user->setStateKeyPrefix( IBOS::app()->setting->get( 'sid' ) );
			}
			$user->login( $identity );
		} else if ( IBOS::app()->user->isGuest ) {
            defined('IN_SWFHASH') or define('IN_SWFHASH', false);
			// 未登录即跳转
			if ( !$isUrlAllowedToGuests ) {
				if ( IN_DASHBOARD ) {
					IBOS::app()->request->redirect( IBOS::app()->createUrl( 'dashboard/default/login', array( 'refer' => $requestedUrl ) ) );
				} else {
					IBOS::app()->user->loginRequired();
				}
			}
		}
	}

	/**
	 * 处理初始化运行环境
	 * @param mixed $event
	 * @throws CException 配置文件丢失
	 */
	public function handleInitEnvironment( $event ) {
		// ---- 性能检测 ,部署模式时可移除以减少组件加载 ----
		IBOS::app()->performance->startClock();
		IBOS::app()->performance->startMemoryUsageMarker();
		// -------------------------------------------
		// 可访问的静态资源文件夹
        // 添加defined判断，防止URL请求错误
        defined('STATICURL') or define('STATICURL', IBOS::app()->assetManager->getBaseUrl());
		// 是否用手机访问
        defined('IN_MOBILE') or define('IN_MOBILE', Env::checkInMobile());
        defined('IN_DASHBOARD') or define('IN_DASHBOARD', Env::checkInDashboard());
        defined('TIMESTAMP') or define('TIMESTAMP', time());
        defined('IN_APP') or define('IN_APP', Env::checkInApp());
		$this->setTimezone();
		// 设置运行内存
		if ( function_exists( 'ini_get' ) ) {
			$memorylimit = @ini_get( 'memory_limit' );
			if ( $memorylimit && Convert::ConvertBytes( $memorylimit ) < 33554432 && function_exists( 'ini_set' ) ) {
				ini_set( 'memory_limit', '128m' );
			}
		}
		// setting 组件里要用到的全局变量，这里先赋予给一个数组,里面的全为初始值
		$global = array(
			'timestamp' => TIMESTAMP,
			'version' => VERSION,
			'clientip' => Env::getClientIp(),
			'referer' => '',
			'charset' => CHARSET,
			'authkey' => '',
			'newversion' => 0,
			'config' => array(),
			'setting' => array(),
			'user' => array(),
			'cookie' => array(),
			'session' => array(),
			'lunar' => DateTime::getlunarCalendar(), // 农历显示
			'title' => MainUtil::getIncentiveWord(),
			'staticurl' => STATICURL
		);
		// 脚本与路径变量
		$global['phpself'] = Env::getScriptUrl();
		$sitePath = substr( $global['phpself'], 0, strrpos( $global['phpself'], '/' ) );
		$global['isHTTPS'] = Env::isHttps();
		$global['siteurl'] = Env::getSiteUrl( $global['isHTTPS'], $sitePath );
		$url = parse_url( $global['siteurl'] );
		$global['siteroot'] = isset( $url['path'] ) ? $url['path'] : '';
		$global['siteport'] = empty( $_SERVER['SERVER_PORT'] ) || $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? '' : ':' . $_SERVER['SERVER_PORT'];
		// 加载系统生成配置文件
		$config = @include PATH_ROOT . '/system/config/config.php';
		if ( empty( $config ) ) {
			throw new CException( IBOS::Lang( 'Config not found', 'error' ) );
		} else {
			$global['config'] = $config;
		}
		// 注册给setting 组件,实现全局调用
		IBOS::app()->setting->copyFrom( $global );
	}

	/**
	 * 检查请求处理
	 * @param mixed $event
	 * @throws CException 过滤全局变量错误
	 */
	public function handleInitInput( $event ) {
		// 过滤全局数组
		if ( isset( $_GET['GLOBALS'] ) || isset( $_POST['GLOBALS'] ) ||
				isset( $_COOKIE['GLOBALS'] ) || isset( $_FILES['GLOBALS'] ) ) {
			throw new CException( IBOS::lang( 'Parameters error', 'error' ) );
		}
		$global = IBOS::app()->setting->toArray();
		$config = $global['config'];
		// 如果是ibos生成的cookie,把它重新赋值到全局设置里
		$preLength = strlen( $global['config']['cookie']['cookiepre'] );
		foreach ( $_COOKIE as $key => $value ) {
			if ( substr( $key, 0, $preLength ) == $config['cookie']['cookiepre'] ) {
				$global['cookie'][substr( $key, $preLength )] = $value;
			}
		}
		// 初始化session id
		$global['sid'] = $global['cookie']['sid'] = isset( $global['cookie']['sid'] ) ?
				String::ihtmlSpecialChars( $global['cookie']['sid'] ) : '';

		if ( empty( $global['cookie']['saltkey'] ) ) {
			$global['cookie']['saltkey'] = String::random( 8 );
			MainUtil::setCookie( 'saltkey', $global['cookie']['saltkey'], 86400 * 30, 1, 1 );
		}
		// 生成身份验证码
		$global['authkey'] = md5( $global['config']['security']['authkey'] . $global['cookie']['saltkey'] );
		$global['aeskey'] = Setting::model()->fetchSettingValueByKey( 'aeskey' );
		IBOS::app()->setting->copyFrom( $global );
	}

	/**
	 * 初始化session组件
	 * @param mixed $event
	 */
	public function handleInitSession( $event ) {
		$global = IBOS::app()->setting->toArray();
		IBOS::app()->session->load( $global['cookie']['sid'], $global['clientip'], IBOS::app()->user->isGuest ? 0 : IBOS::app()->user->uid  );
		$global['sid'] = IBOS::app()->session->sid;
		$global['session'] = IBOS::app()->session->var;

		if ( !empty( $global['sid'] ) && $global['sid'] != $global['cookie']['sid'] ) {
			MainUtil::setCookie( 'sid', $global['sid'], 86400 );
		}
		IBOS::app()->setting->copyFrom( $global );
		$isNewSession = IBOS::app()->session->isNew;
		// 如果是未登录的用户，检查是否被ban IP
		if ( $isNewSession ) {
			if ( Env::ipBanned( $global['clientip'] ) ) {
                //当访问的客户在禁止ip列表时，程序运行到这里会出错
                //error方法没有定义
                //解决是要调用正确的处理函数或是新定义一个error函数
				//IBOS::error( IBOS::lang( 'User banned', 'message' ) );
                //直接返回403，禁止访问
                //TODO 显示更加友好的提示信息
                header("HTTP/1.1 403 Forbidden");
                header("status: 403 Forbidden");
                exit('<h1>Forbidden<h1>');
			}
		}
		// 如果已登录用户，检查是否需要更新最后活动时间
		if ( !IBOS::app()->user->isGuest && ( $isNewSession || ( IBOS::app()->session->getKey( 'lastactivity' ) + 600) < TIMESTAMP) ) {
			IBOS::app()->session->setKey( 'lastactivity', TIMESTAMP );
			if ( $isNewSession ) {
				UserStatus::model()->updateByPk( IBOS::app()->user->uid, array( 'lastip' => $global['clientip'], 'lastvisit' => TIMESTAMP ) );
			}
		}
	}

	/**
	 * 处理定时任务
	 * @param mixed $event
	 */
	public function handleInitCron( $event ) {
		$cronNextRunTime = IBOS::app()->setting->get( 'cache/cronnextrun' );
		$enableCronRun = $cronNextRunTime && $cronNextRunTime <= TIMESTAMP;
		if ( $enableCronRun ) {
			IBOS::app()->cron->run();
		}
	}

	/**
	 * 初始化组织架构静态js,无则生成
	 * @param mixed $event
	 */
	public function handleInitOrg( $event ) {
		if ( !File::fileExists( 'data/org.js' ) ) {
			Org::update();
		}
	}

	/**
	 * 加载系统设置
	 * @param mixed $event
	 */
	public function handleLoadSysCache( $event ) {
		$caches = Syscache::model()->fetchAll();
		foreach ( $caches as $cache ) {
			$value = $cache['type'] == '1' ? unserialize( $cache['value'] ) : $cache['value'];
			if ( $cache['name'] == 'setting' ) {
				IBOS::app()->setting->set( 'setting', $value );
			} else {
				IBOS::app()->setting->set( 'cache/' . $cache['name'], $value );
			}
		}
	}

	/**
	 * 检查系统更新升级
	 * @param mixed $event
	 */
	public function handleCheckUpgrade( $event ) {
		// 只有超级管理员才有权限看到
		if ( !IBOS::app()->user->isGuest && IBOS::app()->user->isadministrator ) {
			$upgrade = IBOS::app()->setting->get( 'setting/upgrade' );
			if ( !empty( $upgrade ) ) {
				IBOS::app()->setting->set( 'newversion', 1 );
			}
			$cookie = IBOS::app()->setting->get( 'cookie' );
			$needUpgrade = isset( $cookie['checkupgrade'] );
			if ( $needUpgrade ) {
				$checkReturn = Upgrade::checkUpgrade();
				IBOS::app()->setting->set( 'newversion', $checkReturn ? 1 : 0  );
				MainUtil::setCookie( 'checkupgrade', 1, 7200 );
			}
		}
	}

    /**
     * 检查主授权
     * @param mixed $event
     * @author Ring 
     */
    public function handleCheckLicence($event) {
        IBOS::app()->licence->checkMainLicence();
    }

	/**
	 * 应用系统设置
	 * @param mixed $event
	 */
	public function handleSystemConfigure( $event ) {
		$global = IBOS::app()->setting->toArray();
		// 处理timezone
		$timeOffset = $global['setting']['timeoffset'];
		$this->setTimezone( $timeOffset );
		// todo::检查系统设置里ip过滤是否启用，若启用，检查当前ip是否合法 @banyan
		// 处理身份标识
		if ( !IBOS::app()->user->isGuest ) {
            defined('FORMHASH') or define('FORMHASH', Env::formHash());
		} else {
            defined('FORMHASH') or define('FORMHASH', '');
		}
        defined('VERHASH') or define('VERHASH', $global['setting']['verhash']);
		// 程序关闭处理
		if ( $global['setting']['appclosed'] ) {
			$route = IBOS::app()->getUrlManager()->parseUrl( IBOS::app()->getRequest() );
			if ( !empty( $route ) ) {
				list($module,, ) = explode( '/', $route );
			} else {
				$module = '';
			}
			if ( !IBOS::app()->user->isGuest && IBOS::app()->user->isadministrator ) {
				// 如果是管理组，忽略
			} elseif ( in_array( $module, array( 'dashboard', 'user' ) ) ) {
				// 如果是以上模块，无需作处理
			} elseif ( defined( 'IN_SWFHASH' ) && IN_SWFHASH ) {
				// 如果正在进行swfupload上传验证，也无需作处理
			} else {
                $msg = IBOS::lang('System closed', 'message');
                if (IBOS::app()->getRequest()->getIsAjaxRequest()) {
                    Env::iExit(CJSON::encode(array('isSuccess' => false, 'msg' => $msg)));
                } else {
                    Env::iExit($msg);
				}
			}
		}
    }

	/**
	 * 设置时区
	 * @param boolean $timeOffset
	 */
	private function setTimezone( $timeOffset = 0 ) {
		if ( function_exists( 'date_default_timezone_set' ) ) {
			@date_default_timezone_set( 'Etc/GMT' . ($timeOffset > 0 ? '-' : '+') . (abs( $timeOffset )) );
		}
	}

	/**
	 * 获取脚本路径，用多个判断适配最佳
	 * @return string 
	 * @throws CException
	 */
	private function getScriptUrl() {
		$phpSelf = '';
		$scriptName = basename( $_SERVER['SCRIPT_FILENAME'] );
		if ( basename( $_SERVER['SCRIPT_NAME'] ) === $scriptName ) {
			$phpSelf = $_SERVER['SCRIPT_NAME'];
		} else if ( basename( $_SERVER['PHP_SELF'] ) === $scriptName ) {
			$phpSelf = $_SERVER['PHP_SELF'];
		} else if ( isset( $_SERVER['ORIG_SCRIPT_NAME'] ) && basename( $_SERVER['ORIG_SCRIPT_NAME'] ) === $scriptName ) {
			$phpSelf = $_SERVER['ORIG_SCRIPT_NAME'];
		} else if ( ($pos = strpos( $_SERVER['PHP_SELF'], '/' . $scriptName )) !== false ) {
			$phpSelf = substr( $_SERVER['SCRIPT_NAME'], 0, $pos ) . '/' . $scriptName;
		} else if ( isset( $_SERVER['DOCUMENT_ROOT'] ) && strpos( $_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT'] ) === 0 ) {
			$phpSelf = str_replace( '\\', '/', str_replace( $_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME'] ) );
			$phpSelf[0] != '/' && $phpSelf = '/' . $phpSelf;
		} else {
			throw new CException( IBOS::lang( 'Request tainting', 'error' ) );
		}
		return $phpSelf;
	}

}
