<?php

/**
 * 全局常量定义文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
defined( 'DEBUG' ) || define( 'DEBUG', false );

if ( DEBUG ) {
	error_reporting( E_ALL | E_STRICT );
}
//define( 'SAAS_STORAGE', 1 );
switch ( 1 ) {
	case function_exists( 'saeAutoLoader' ):
		defined( 'ENGINE' ) || define( 'ENGINE', 'SAE' );
		break;
	case defined( 'SAAS_STORAGE' ) && SAAS_STORAGE === 1:
		defined( 'ENGINE' ) || define( 'ENGINE', 'SAAS' );
		break;
	default:
		defined( 'ENGINE' ) || define( 'ENGINE', 'LOCAL' );
		break;
}


// 字符编码
define( 'CHARSET', 'utf-8' );
// 调试模式
defined( 'YII_DEBUG' ) or define( 'YII_DEBUG', DEBUG );
// 错误等级
defined('YII_TRACE_LEVEL') || define( 'YII_TRACE_LEVEL', DEBUG ? 3 : 0  );

require 'version.php';
