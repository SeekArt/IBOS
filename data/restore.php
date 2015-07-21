<?php

use application\core\utils\Database;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\extensions\SimpleUnzip;

/**
 * IBOS 数据备份恢复工具，与程序分离
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 * @author banyanCheung <banyan@ibos.com.cn>
 * 
 */
// 定义驱动引擎
define( 'ENGINE', 'LOCAL' );
// 根目录
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../' );
$defines = PATH_ROOT . '/system/defines.php';
defined( 'TIMESTAMP' ) or define( 'TIMESTAMP', time() );
$yii = PATH_ROOT . '/library/yii.php';
$config = PATH_ROOT . '/system/config/common.php';

require_once ( $defines );
require_once ( $yii );
Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );
Yii::createApplication( 'application\core\components\Application', $config );

$op = Env::getRequest( 'op' );
$msg = $url = '';
$type = 'message';
$success = 1;

if ( $op == 'restore' ) {
	$id = Env::getRequest( 'id' );
	$status = restore( $id );
	extract( $status );
	showMeassage( $msg, $url, $type, $success );
} else if ( $op == 'restorezip' ) {
	$id = Env::getRequest( 'id' );
	$status = restoreZip( $id );
	extract( $status );
	showMeassage( $msg, $url, $type, $success );
}

function showHeader() {
	ob_start();
	$charset = CHARSET;
	$staticUrl = 'static';
	print <<< EOT
	<!doctype html>
	<html lang="en">
		<head>
			<meta charset="{$charset}">
			<title>IBOS 数据恢复工具</title>
			<!-- load css -->
			<link rel="stylesheet" href="{$staticUrl}/css/bootstrap.css">
			<!-- IE8 fixed -->
			<!--[if lt IE 9]>
			<link rel="stylesheet" href="{$staticUrl}/css/iefix.css">
			<![endif]-->
		</head>
		<body>
			<script>
				function $(id) {
					return document.getElementById(id);
				}
				function showmessage(message) {
					document.getElementById('notice').innerHTML += message + '<br />';
				}
				function display(id) {
					var obj = $(id);
					if(obj.style.visibility) {
						obj.style.visibility = obj.style.visibility == 'visible' ? 'hidden' : 'visible';
					} else {
						obj.style.display = obj.style.display == '' ? 'none' : '';
					}
				}
				function redirect(url) {
					window.location = url;
					if($('confirmbtn')) {
						$('confirmbtn').disabled = !($('confirmbtn').disabled   &&   true);
					}
					if($('cancelbtn')) {
						$('cancelbtn').disabled = !($('cancelbtn').disabled   &&   true);
					}
				}
			</script>
			<div class="ct">
				<div class="ctb">
					<div>
						<h5>
							IBOS 数据恢复工具
							<span> &nbsp; 恢复当中有任何问题请访问技术支持站点 <a href="http://www.ibos.com.cn" target="_blank">http://www.ibos.com.cn</a></span>
						</h5>
EOT;
}

function showFooter( $quit = true ) {
	echo <<< EOT
						</div>
					</div>
				</div>
			</body>
	</html>
EOT;
	ob_flush();
	exit();
}

function showMeassage( $message, $urlForward = '', $type = 'message', $success = 0 ) {
	showHeader();
	if ( $type == 'message' ) {
		echo '<div class="status-tip status-tip-' . ($success ? 'success' : 'error') . '">' . $message . '</div>';
	} elseif ( $type == 'redirect' ) {
		echo "$message ...";
		echo "<br /><br /><br /><a href=\"{$urlForward}\">浏览器会自动跳转页面，无需人工干预。除非当您的浏览器长时间没有自动跳转时，请点击这里</a>";
		echo "<script>setTimeout(\"redirect('{$urlForward}');\", 1250);</script>";
	} elseif ( $type == 'confirm' ) {
		echo "$message";
		echo "<br /><br /><br /><button class=\"btn btn-primary\" id=\"confirmbtn\" onclick=\"redirect('{$urlForward}')\">确定</button><button class=\"btn\" id=\"cancelbtn\" onclick=\"redirect('restore.php')\">取消</button>";
	}
	showFooter();
}

/**
 * 恢复备份的sql文件
 * @param string $id 文件名
 * @return array
 */
function restore( $id ) {
	$path = PATH_ROOT;
	if ( strstr( $path, 'data' ) ) {
		$id = trim( str_replace( 'data', '', $id ), '/' );
	}
	$file = urldecode( $id );
	$fp = @fopen( $file, 'rb' );
	if ( $fp ) {
		$sqlDump = fgets( $fp, 256 );
		$identify = explode( ',', base64_decode( preg_replace( "/^# Identify:\s*(\w+).*/s", "\\1", $sqlDump ) ) );
		$dumpInfo = array(
			'method' => $identify[3],
			'volume' => intval( $identify[4] ),
			'tablepre' => $identify[5],
			'dbcharset' => $identify[6]
		);
		if ( $dumpInfo['method'] == 'multivol' ) {
			$sqlDump .= fread( $fp, filesize( $file ) );
		}
		fclose( $fp );
	} else {
		if ( Env::getRequest( 'autorestore', 'G' ) ) {
			return array( 'success' => 1, 'msg' => IBOS::lang( 'Database import multivol succeed', 'dashboard.default' ) );
		} else {
			return array( 'success' => 0, 'msg' => IBOS::lang( 'Database import file illegal', 'dashboard.default' ) );
		}
	}
	$command = IBOS::app()->db->createCommand();
	// 分卷导入
	if ( $dumpInfo['method'] == 'multivol' ) {
		$sqlQuery = String::splitSql( $sqlDump );
		unset( $sqlDump );
		$dbCharset = IBOS::app()->db->charset;
		$dbVersion = IBOS::app()->db->getServerVersion();
		foreach ( $sqlQuery as $sql ) {
			$sql = Database::syncTableStruct( trim( $sql ), $dbVersion > '4.1', $dbCharset );
			if ( $sql != '' ) {
				$command->setText( $sql )->execute();
			}
		}
		$delunzip = Env::getRequest( 'delunzip', 'G' );
		if ( $delunzip ) {
			@unlink( $file );
		}
		$pattern = "/-({$dumpInfo['volume']})(\..+)$/";
		$relacement = "-" . ($dumpInfo['volume'] + 1) . "\\2";
		$nextFile = preg_replace( $pattern, $relacement, $file );
		$nextFile = urlencode( $nextFile );
		$param = array(
			'op' => 'restore',
			'id' => $nextFile,
			'autorestore' => 'yes'
		);
		if ( $delunzip ) {
			$param['delunzip'] = 'yes';
		}
		$msg = IBOS::lang( 'Database import multivol redirect', 'dashboard.default', array( 'volume' => $dumpInfo['volume'] ) );
		$url = 'restore.php?' . http_build_query( $param );
		if ( $dumpInfo['volume'] == 1 ) {
			return array( 'type' => 'redirect', 'msg' => $msg, 'url' => $url );
		} elseif ( Env::getRequest( 'autorestore', 'G' ) ) {
			return array( 'type' => 'redirect', 'msg' => $msg, 'url' => $url );
		} else {
			return array( 'success' => 1, 'msg' => IBOS::lang( 'Database import succeed', 'dashboard.default' ) );
		}
	} else if ( $dumpInfo['method'] == 'shell' ) {
		// 加载系统生成配置文件
		$config = @include PATH_ROOT . './system/config/config.php';
		if ( empty( $config ) ) {
			throw new Exception( IBOS::Lang( 'Config not found', 'error' ) );
		} else {
			$db = $config['db'];
		}
		$query = $command->setText( "SHOW VARIABLES LIKE 'basedir'" )->queryRow();
		$mysqlBase = $query['Value'];
		$mysqlBin = $mysqlBase == '/' ? '' : addslashes( $mysqlBase ) . 'bin/';
		shell_exec( $mysqlBin . 'mysql -h"' . $db['host'] . ($db['port'] ? (is_numeric( $db['port'] ) ? ' -P' . $db['port'] : ' -S"' . $db['port'] . '"') : '') .
				'" -u"' . $db['username'] . '" -p"' . $db['password'] . '" "' . $db['dbname'] . '" < ' . $file );
		return array( 'success' => 1, 'msg' => IBOS::lang( 'Database import succeed', 'dashboard.default' ) );
	} else {
		return array( 'success' => 0, 'msg' => IBOS::lang( 'Database import file illegal', 'dashboard.default' ) );
	}
}

function restoreZip( $id ) {
	$path = PATH_ROOT;
	if ( strstr( $path, 'data' ) ) {
		$id = trim( str_replace( 'data', '', $id ), '/' );
	}
	if ( !file_exists( $id ) ) {
		return array( 'success' => 0, 'msg' => IBOS::lang( 'Database import file illegal', 'dashboard.default' ) );
	}
	$dataFileVol1 = trim( Env::getRequest( 'datafilevol1', 'G' ) );
	$multiVol = intval( Env::getRequest( 'multivol', 'G' ) );
	IBOS::import( 'ext.Zip', true );
	$unzip = new SimpleUnzip();
	$unzip->ReadFile( $id );

	if ( $unzip->Count() == 0 || $unzip->GetError( 0 ) != 0 || !preg_match( "/\.sql$/i", $importFile = $unzip->GetName( 0 ) ) ) {
		return array( 'success' => 0, 'msg' => IBOS::lang( 'Database import file illegal', 'dashboard.default' ) );
	}
	$identify = explode( ',', base64_decode( preg_replace( "/^# Identify:\s*(\w+).*/s", "\\1", substr( $unzip->GetData( 0 ), 0, 256 ) ) ) );
	$confirm = Env::getRequest( 'confirm', 'G' );
	$confirm = !is_null( $confirm ) ? 1 : 0;
	if ( !$confirm && $identify[1] != VERSION ) {
		return array(
			'type' => 'confirm',
			'msg' => IBOS::lang( 'Database import confirm', 'dashboard.default' ),
			'url' => 'restore.php?' . http_build_query( array( 'op' => 'restorezip', 'confirm' => 'yes', 'id' => $id ) )
		);
	}

	$sqlFileCount = 0;
	foreach ( $unzip->Entries as $entry ) {
		if ( preg_match( "/\.sql$/i", $entry->Name ) ) {
			$fp = fopen( 'backup/' . $entry->Name, 'w' );
			fwrite( $fp, $entry->Data );
			fclose( $fp );
			$sqlFileCount++;
		}
	}

	if ( !$sqlFileCount ) {
		return array( 'success' => 0, 'msg' => IBOS::lang( 'Database import file illegal', 'dashboard.default' ) );
	}
	if ( $multiVol ) {
		$multiVol++;
		$id = preg_replace( "/-(\d+)(\..+)$/", "-{$multiVol}\\2", $id );
		if ( file_exists( $multiVol ) ) {
			$param = array(
				'op' => 'restorezip',
				'multivol' => $multiVol,
				'datafilevol1' => $dataFileVol1,
				'delunzip' => $dataFileVol1,
				'confirm' => 'yes'
			);
			return array(
				'type' => 'confirm',
				'msg' => IBOS::lang( 'Database import multivol unzip redirect', 'dashboard.default', array( 'multivol' => $multiVol ) ),
				'url' => 'restore.php?' . http_build_query( $param )
			);
		} else {
			$param = array(
				'op' => 'restore',
				'id' => $dataFileVol1,
				'autorestore' => 'yes',
				'delunzip' => 'yes'
			);
			return array(
				'type' => 'confirm',
				'msg' => IBOS::lang( 'Database import multivol confirm', 'dashboard.default' ),
				'url' => 'restore.php?' . http_build_query( $param )
			);
		}
	}
	$info = '<b>' . basename( $id ) . '</b><br />' .
			IBOS::lang( 'Version' ) .
			': ' . $identify[1] . '<br />' . IBOS::lang( 'Type' ) .
			': ' . $identify[2] . '<br />' . IBOS::lang( 'Backup method' ) .
			': ' . ($identify[3] == 'multivol' ? IBOS::lang( 'DBMultivol' ) : IBOS::lang( 'DBShell' )) . '<br />';

	if ( $identify[3] == 'multivol' && $identify[4] == 1 && preg_match( "/-1(\..+)$/", $id ) ) {
		$dataFileVol1 = $id;
		$id = preg_replace( "/-1(\..+)$/", "-2\\1", $id );
		if ( file_exists( $id ) ) {
			$param = array(
				'op' => 'restorezip',
				'multivol' => 1,
				'datafilevol1' => 'backup/' . $importFile,
				'id' => $id,
				'confirm' => 'yes'
			);
			return array(
				'type' => 'redirect',
				'msg' => IBOS::lang( 'Database import multivol unzip redirect', 'dashboard.default', array( 'multivol' => 1 ) ),
				'url' => 'restore.php?' . http_build_query( $param )
			);
		}
	}
	$param = array(
		'op' => 'restore',
		'datafilevol1' => $dataFileVol1,
		'id' => 'backup/' . $importFile,
		'delunzip' => 'yes',
		'autorestore' => 'yes'
	);
	return array(
		'type' => 'confirm',
		'msg' => IBOS::lang( 'Database import unzip', 'dashboard.default', array( 'info' => $info ) ),
		'url' => 'restore.php?' . http_build_query( $param ),
	);
}
