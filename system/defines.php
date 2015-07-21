<?php

/**
 * 全局常量定义文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
defined( 'DEBUG' ) or define( 'DEBUG', false );

if ( DEBUG ) {
    error_reporting( E_ALL | E_STRICT );
}
// 自动识别SAE环境
if ( function_exists( 'saeAutoLoader' ) ) {// 自动识别SAE环境
    defined( 'ENGINE' ) or define( 'ENGINE', 'SAE' );
} else {
    defined( 'ENGINE' ) or define( 'ENGINE', 'LOCAL' );            // 引擎 默认为本地引擎 
}
// 是否本地环境
define( 'LOCAL', strtolower( ENGINE ) === 'local' ? true : false  );
// 字符编码
define( 'CHARSET', 'utf-8' );
// 调试模式
defined( 'YII_DEBUG' ) or define( 'YII_DEBUG', DEBUG );
// 错误等级
define( 'YII_TRACE_LEVEL', DEBUG ? 3 : 0  );

require 'version.php';
