<?php

/**
 * 索引页
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 * @author banyanCheung <banyan@ibos.com.cn>
 */
if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
    die( 'require PHP > 5.3.0 !' );
}
// 程序根目录路径
define( 'PATH_ROOT', dirname( __FILE__ ) );

$defines = PATH_ROOT . '/system/defines.php';
$yii = PATH_ROOT . '/library/yii.php';
$config = PATH_ROOT . '/system/config/common.php';

require_once ( $defines );
require_once ( $yii );

Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );



// CORS 设置，有待讨论
header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, Authorization, ISCORS');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS, DELETE');

// 由于手机端发送的请求不带 $_SERVER 信息，所以只在有请求头部时设置 Origin
if(isset($_SERVER['HTTP_ORIGIN'])){
	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
}

if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	exit();
}

Yii::createApplication( 'application\core\components\Application', $config )->run();

exit;
