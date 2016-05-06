<?php

/**
 * 主模块函数库文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 主模块函数库类
 * @package application.modules.main.utils
 * @version $Id: Main.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\main\utils;

use application\core\model\Module;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\user\model\User;

class Main {

	/**
	 * 设置cookie
	 * @param string $var 变量名
	 * @param string $value 变量值
	 * @param integer $life 生命期
	 * @param integer $prefix 前缀
	 * @param boolean $httpOnly 安全属性
	 */
	public static function setCookie( $var, $value = '', $life = 0, $prefix = 1, $httpOnly = false ) {
		$global = IBOS::app()->setting->toArray();
		// 写入全局设置组件
		IBOS::app()->setting->set( 'cookie/' . $var, $value );
		$config = $global['config']['cookie'];
		// 写入全局cookie数组
		$var = ($prefix ? $config['cookiepre'] : '') . $var;
		$_COOKIE[$var] = $value;
		// 值为空或生命期为-1，视作取消一个cookie
		if ( $value == '' || $life < 0 ) {
			$value = '';
			$life = -1;
		}
		if ( IN_MOBILE ) {
			$httpOnly = false;
		}
		$life = $life > 0 ? $global['timestamp'] + $life : ($life < 0 ? $global['timestamp'] - 31536000 : 0);
		$path = $config['cookiepath'];

		if ( !isset( $_SERVER['SERVER_PORT'] ) ) {
			$secure = 0;
		} else {
			$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
		}
		@setcookie( $var, $value, $life, $path, $config['cookiedomain'], $secure, $httpOnly );
	}

	/**
	 * 取得cookie
	 * @param string $var
	 * @param integer $prefix
	 * @return mixed
	 */
	public static function getCookie( $var, $prefix = 1 ) {
		$global = IBOS::app()->setting->toArray();
		$config = $global['config']['cookie'];
		$var = ($prefix ? $config['cookiepre'] : '') . $var;
		if ( array_key_exists( $var, $_COOKIE ) ) {
			return $_COOKIE[$var];
		} else {
			return null;
		}
	}

	/**
	 * 清除所有cookie
	 * @return void 
	 */
	public static function clearCookies() {
		$global = IBOS::app()->setting->toArray();
		foreach ( $global['cookie'] as $key => &$value ) {
			self::setCookie( $key );
			$value = '';
		}
		IBOS::app()->setting->copyFrom( $global );
	}

	/**
	 * 获取随机名言
	 * @return string
	 */
	public static function getIncentiveWord() {
        $useIncentiveword = IBOS::app()->params->incentiveword;
        if (true === $useIncentiveword){
		$words = IBOS::getLangSource( 'incentiveword' );
		$luckyOne = array_rand( $words );
		$source = $words[$luckyOne];
		return IBOS::lang( 'Custom title', 'main.default' ) . $source[array_rand( $source )];
        }else{
            $title = ' ';
            $unit = Setting::model()->fetchSettingValueByKey('unit');
            if (!empty($unit)){
                $unitArray = StringUtil::utf8Unserialize($unit);
                if (isset($unitArray['shortname'])){
                    $title = $unitArray['shortname'] . '- IBOS协同办公平台';
                }
            }
            return $title;
        }
	}

	/**
	 * api执行方法
	 * @param string $method 要执行的方法名
	 * @param array $moduleArr 模块名称的一维数组
	 * @return array
	 */
//	public static function execApiMethod( $method, $moduleArr ) {
//		$data = array();
//		$paramNum = func_num_args();
//		if ( $paramNum > 2 ) {
//			$params = func_get_args();
//			$args = array_slice( $params, 2, count( $params ) );
//		} else {
//			$args = array();
//		}
//		$enableModule = Module::model()->fetchAllEnabledModule();
//		foreach ( $moduleArr as $module ) {
//			if ( array_key_exists( $module, $enableModule ) ) {
//				$class = 'application\modules\\' . $module . '\\utils\\' . ucfirst( $module ) . 'Api';
//				if ( class_exists( $class ) ) {
//					$api = new $class;
//					if ( $args ) {
//						$data[$module] = call_user_func_array( array( $api, $method ), $args );
//					} else {
//						$data[$module] = $api->$method();
//					}
//				}
//			}
//		}
//		return $data;
//	}

	/**
	 * api执行方法
	 * @param string $method 要执行的方法名
	 * @param array $widgetArr widget数组
	 * @return array
	 */
	public static function execLoadSetting( $method, $widgetArr ) {
		$data = array();
		foreach ( $widgetArr as $widget ) {
			$info = explode( '/', $widget );
			if ( count( $info ) == 2 ) {
				$module = $info[0];
				$file = $info[1];
				$class = 'application\modules\\' . $module . '\\utils\\' . ucfirst( $file ) . 'Api';
				if ( class_exists( $class ) ) {
					$api = new $class;
                    $close = false;
                    if (method_exists($api, 'close')):
                        $close = $api->close();
                    endif;
                    if (true === $close):
                        continue;
                    endif;
					$data[$widget] = $api->$method();
				}
			}
		}
		return $data;
	}


    /**
     * 为JS提供全局的一些模块参数
     * @return array
     */
	public static function getModuleParamsForJs() {
		$modules = Module::model()->fetchAllEnabledModule();
		$params = array();
		foreach ( $modules as $moduleName => $module ) {
			$params[$moduleName]['assetUrl'] = IBOS::app()->assetManager->getAssetsUrl( $module['module'] );
		}
		return $params;
	}

}
