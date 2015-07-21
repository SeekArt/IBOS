<?php

/**
 * 文件助手类文件.
 *
 * @author  banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 文件助手类,提供一套通用文件系统操作的助手方法
 *
 * @package application.core.utils
 * @version $Id: file.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

class File {

	/**
	 * 返回引擎上传接口
	 * @param string $fileArea 上传文件域
	 * @param string $module 模块名
	 * @return object
	 */
	public static function getUpload( $fileArea, $module = 'temp' ) {
		return IBOS::engine()->io()->upload( $fileArea, $module );
	}

	/**
	 * 获取附件目录
	 * @return string
	 */
	public static function getAttachUrl() {
		return rtrim( IBOS::app()->setting->get( 'setting/attachurl' ), DIRECTORY_SEPARATOR );
	}

	/**
	 * 下载文件接口
	 * @param array 附件数组
	 * @param array $downLoadInfo 下载额外信息
	 * @return void
	 */
	public static function download( $attach, $downloadInfo = array() ) {
		return IBOS::engine()->io()->file()->download( $attach, $downloadInfo );
	}

	/**
	 * io:storage 读取storage文件地址接口
	 * @param string $fileName 要读取的文件名
	 * @return string 
	 */
	public static function fileName( $fileName ) {
		return IBOS::engine()->io()->file()->fileName( $fileName );
	}

	/**
	 * 获取图片规格
	 * @param string $image 图片地址
	 * @return array 成功读取图片信息， false 读取失败
	 */
	public static function imageSize( $image ) {
		return IBOS::engine()->io()->file()->imageSize( $image );
	}

	/**
	 * 获取文件大小接口
	 * @param string $file 文件名
	 * @return integer 返回文件大小的字节数，如果出错返回 false 并生成一条 E_WARNING 级的错误。 
	 */
	public static function fileSize( $file ) {
		return IBOS::engine()->io()->file()->fileSize( $file );
	}

	/**
	 * io:storage 检查文件或目录是否存在
	 * @param string $file
	 * @return type
	 */
	public static function fileExists( $file ) {
		return IBOS::engine()->io()->file()->fileExists( $file );
	}

	/**
	 * io：storage 写入文件内容接口
	 * @param string $fileName 要写入的文件名
	 * @param string $content 写入的内容
	 * @return type
	 */
	public static function createFile( $fileName, $content ) {
		return IBOS::engine()->io()->file()->createFile( $fileName, $content );
	}

	/**
	 * io：storage 读取文件名接口
	 * @param string $fileName 文件名
	 * @return type
	 */
	public static function readFile( $fileName ) {
		return IBOS::engine()->io()->file()->readFile( $fileName );
	}

	/**
	 * io：storage 获取临时读写文件夹接口
	 * @return type
	 */
	public static function getTempPath() {
		return IBOS::engine()->io()->file()->getTempPath();
	}

	/**
	 * io:stirage 清空指定目录里文件 （不包含子目录）
	 * @param string $dir
	 * @return boolean
	 */
	public static function clearDir( $dir ) {
		return IBOS::engine()->io()->file()->clearDir( $dir );
	}

	/**
	 * io:storage 删除指定文件
	 * @param string $fileName
	 * @return boolean
	 */
	public static function deleteFile( $fileName ) {
		return IBOS::engine()->io()->file()->deleteFile( $fileName );
	}

	/**
	 * 递归清空目录包括目录本身
	 * @param string $srcDir 目标文件夹路径
	 */
	public static function clearDirs( $srcDir, $except = array() ) {
		return IBOS::engine()->io()->file()->clearDirs( $srcDir, $except );
	}

	/**
	 * 远程文件请求函数
	 * @param string $url 请求的地址
	 * @param integer $limit 请求的字符数
	 * @param string $post 要提交的参数
	 * @param string $cookie 设定HTTP请求中"Cookie: "部分的内容。
	 * @param string $ip
	 * @param integer $timeout 连接超时时间
	 * @param boolean $block If mode is 0, the given stream will be switched to non-blocking mode, 
	 * and if 1, it will be switched to blocking mode. 
	 * @param type $encodeType
	 * @param type $allowcurl
	 * @param type $position
	 * @return mixed 请求返回的内容
	 */
	public static function fileSockOpen( $url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = true, $encodeType = 'URLENCODE', $allowcurl = TRUE, $position = 0 ) {
		$return = '';
		$matches = parse_url( $url );
		$scheme = $matches['scheme'];
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'] . (isset( $matches['query'] ) ? '?' . $matches['query'] : '') : '/';
		$port = !empty( $matches['port'] ) ? $matches['port'] : 80;

		if ( function_exists( 'curl_init' ) && function_exists( 'curl_exec' ) && $allowcurl ) {
			$ch = curl_init();
			$ip && curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Host: " . $host ) );
			curl_setopt( $ch, CURLOPT_URL, $scheme . '://' . ($ip ? $ip : $host) . ':' . $port . $path );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			if ( $post ) {
				curl_setopt( $ch, CURLOPT_POST, 1 );
				if ( $encodeType == 'URLENCODE' ) {
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
				} else {
					parse_str( $post, $postArray );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $postArray );
				}
			}
			if ( $cookie ) {
				curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
			}
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
			curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
			$data = curl_exec( $ch );
			$status = curl_getinfo( $ch );
			$errno = curl_errno( $ch );
			curl_close( $ch );
			if ( $errno || $status['http_code'] != 200 ) {
				return;
			} else {
				return !$limit ? $data : substr( $data, 0, $limit );
			}
		}

		if ( $post ) {
			$out = "POST $path HTTP/1.0\r\n";
			$header = "Accept: */*\r\n";
			$header .= "Accept-Language: zh-cn\r\n";
			$boundary = $encodeType == 'URLENCODE' ? '' : '; boundary=' . trim( substr( trim( $post ), 2, strpos( trim( $post ), "\n" ) - 2 ) );
			$header .= $encodeType == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data$boundary\r\n";
			$header .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
			$header .= "Host: $host:$port\r\n";
			$header .= 'Content-Length: ' . strlen( $post ) . "\r\n";
			$header .= "Connection: Close\r\n";
			$header .= "Cache-Control: no-cache\r\n";
			$header .= "Cookie: $cookie\r\n\r\n";
			$out .= $header . $post;
		} else {
			$out = "GET $path HTTP/1.0\r\n";
			$header = "Accept: */*\r\n";
			$header .= "Accept-Language: zh-cn\r\n";
			$header .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
			$header .= "Host: $host:$port\r\n";
			$header .= "Connection: Close\r\n";
			$header .= "Cookie: $cookie\r\n\r\n";
			$out .= $header;
		}

		$fpflag = 0;
		if ( !$fp = @fsockopen( ($ip ? $ip : $host ), $port, $errno, $errstr, $timeout ) ) {
			$context = array(
				'http' => array(
					'method' => $post ? 'POST' : 'GET',
					'header' => $header,
					'content' => $post,
					'timeout' => $timeout,
				),
			);
			$context = stream_context_create( $context );
			$fp = @fopen( $scheme . '://' . ($ip ? $ip : $host) . ':' . $port . $path, 'b', false, $context );
			$fpflag = 1;
		}

		if ( !$fp ) {
			return '';
		} else {
			stream_set_blocking( $fp, $block );
			stream_set_timeout( $fp, $timeout );
			@fwrite( $fp, $out );
			$status = stream_get_meta_data( $fp );
			if ( !$status['timed_out'] ) {
				while ( !feof( $fp ) && !$fpflag ) {
					if ( ($header = @fgets( $fp )) && ($header == "\r\n" || $header == "\n") ) {
						break;
					}
				}

				if ( $position ) {
					fseek( $fp, $position, SEEK_CUR );
				}

				if ( $limit ) {
					$return = stream_get_contents( $fp, $limit );
				} else {
					$return = stream_get_contents( $fp );
				}
			}
			@fclose( $fp );
			return $return;
		}
	}

	/**
	 * 检查文件所在目录的读写权限
	 * @param mixed $fileList 文件路径，数组或字符串形式
	 * @return boolean
	 */
	public static function checkFolderPerm( $fileList ) {
		foreach ( $fileList as $file ) {
			if ( !file_exists( PATH_ROOT . '/' . $file ) ) {
				if ( !self::testDirWriteable( dirname( PATH_ROOT . '/' . $file ) ) ) {
					return false;
				}
			} else {
				if ( !is_writable( PATH_ROOT . '/' . $file ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 测试文件夹是否可写
	 * @param string $dir 文件夹路径
	 * @return boolean 是否可写
	 */
	public static function testDirWriteable( $dir ) {
		$writeable = false;
		if ( !is_dir( $dir ) ) {
			self::makeDirs( $dir, 0777 );
		}
		if ( is_dir( $dir ) ) {
			$fp = @fopen( "{$dir}/test.txt", 'w' );
			if ( $fp ) {
				@fclose( $fp );
				@unlink( "{$dir}/test.txt" );
				$writeable = true;
			} else {
				$writeable = false;
			}
		}
		return $writeable;
	}

	/**
	 * 创建单层文件夹
	 * @param string $dir 要创建的文件夹
	 * @param integer $mode 文件夹权限
	 * @param boolean $makeIndex 创建文件夹索引文件
	 * @return type
	 */
	public static function makeDir( $dir, $mode = 0777, $makeIndex = true ) {
		$res = true;
		if ( !is_dir( $dir ) ) {
			$res = @mkdir( $dir, $mode );
			if ( $makeIndex ) {
				@touch( $dir . '/index.html' );
			}
		}
		return $res;
	}

	/**
	 * 递归创建文件夹
	 * @param string $dir 要创建的文件夹路径
	 * @param integer $mode 文件夹权限
	 * @param boolean $makeIndex 创建文件夹索引文件
	 * @return boolean 成功与否
	 */
	public static function makeDirs( $dir, $mode = 0777, $makeIndex = true ) {
		if ( !is_dir( $dir ) ) {
			if ( !self::makeDirs( dirname( $dir ) ) ) {
				return false;
			}
			if ( !@mkdir( $dir, $mode ) ) {
				return false;
			}
			if ( $makeIndex ) {
				@touch( $dir . '/index.html' );
				@chmod( $dir . '/index.html', $mode );
			}
		}
		return true;
	}

	/**
	 * 复制一个文件到文件夹
	 * @param string $file 文件名
	 * @param string $copyToPath 指定文件夹
	 * @return boolean
	 */
	public static function copyToDir( $file, $copyToPath ) {
		if ( self::fileExists( $file ) ) {
			$name = basename( $file );
			if ( LOCAL ) {
				$state = @copy( $file, $copyToPath . $name );
			} else {
				$state = IBOS::engine()->io()->file()->moveFile( $file, $copyToPath . $name );
			}
			return $state;
		}
	}

	/**
	 * 复制文件夹到另一个文件夹
	 * @param string $srcDir 目标文件夹
	 * @param string $destDir 指定文件夹
	 * @return void
	 */
	public static function copyDir( $srcDir, $destDir ) {
		$dir = @opendir( $srcDir );
		while ( $entry = @readdir( $dir ) ) {
			$file = $srcDir . $entry;
			if ( $entry != '.' && $entry != '..' ) {
				if ( is_dir( $file ) ) {
					self::copyDir( $file . '/', $destDir . $entry . '/' );
				} else {
					self::makeDirs( dirname( $destDir . $entry ) );
					copy( $file, $destDir . $entry );
				}
			}
		}
		closedir( $dir );
	}

	/**
	 * 导出excel文件
	 * @param string $filename  导出的文件名
	 * @param string $data  导出的数据
	 */
	public static function exportCsv( $filename, $data ) {
		header( "Content-type:text/csv" );
		header( "Content-Disposition:attachment;filename=" . $filename );
		header( 'Cache-Control:must-revalidate,post-check=0,pre-check=0' );
		header( 'Expires:0' );
		header( 'Pragma:public' );
		echo $data;
	}

	public static function exportHtml( $filename, $data ) {
		header( "Cache-control: private" );
		header( "Content-type: text/html" );
		header( "Accept-Ranges: bytes" );
		header( "Accept-Length: " . strlen( $data ) );
		header( "Content-Disposition: attachment; filename=" . $filename . ".html" );
		echo "<meta charset=utf-8 />";
		echo $data;
	}

}
