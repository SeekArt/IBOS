<?php

/**
 * IBOS本地文件 处理类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 本地文件处理类，基本上就是php文件函数的封装,实现IO接口
 * 
 * @package ext.enginedriver.local
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: file.php 3934 2014-08-11 09:32:26Z gzhzh $
 */

namespace application\core\engines\local;

use application\core\engines\FileOperationInterface;
use application\core\utils\Attach;
use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Image;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\extensions\ThinkImage\ThinkImage;
use application\modules\main\model\Attachment;
use application\modules\user\model\BgTemplate;
use application\modules\user\utils\User as UserUtil;

class LocalFile implements FileOperationInterface {

	public function __construct( $config = array() ) {
		
	}

	private static $_instance;

	/**
	 * 扫描文件夹时忽略的文件夹
	 * @var array 
	 */
	public $excludeFiles = array( '.svn', '.gitignore', '.', '..' );

	public static function getInstance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * 本地data文件夹文件的文件存在检测
	 * 对应云平台的storage目录
	 * @param string $file 文件名
	 * @return boolean
	 */
	public function fileExists( $file ) {
		return file_exists( $file );
	}

	/**
	 * 本地data文件夹的写操作
	 * 对应云平台的storage目录
	 * @param string $fileName
	 * @param mixed $content
	 * @return type
	 */
	public function createFile( $fileName, $content ) {
		return file_put_contents( $fileName, $content );
	}

	/**
	 * 本地data文件夹的删除操作
	 * 对应云平台的storage目录
	 * @param string $fileName
	 * @return boolean
	 */
	public function deleteFile( $fileName ) {
		return @unlink( $fileName );
	}

	/**
	 * 本地文件名读取操作
	 * @param string $fileName
	 * @return string
	 */
	public function fileName( $fileName, $suffix = false ) {
		$string = '';
		if ( true === $suffix ) {
			$string = '?' . VERHASH;
		}
		return $fileName . $string;
	}

	public function imageName( $fileName ) {
		return $this->fileName( $fileName );
	}

	/**
	 * 本地读取文件内容函数
	 * @param string $fileName
	 * @return string
	 */
	public function readFile( $fileName ) {
		if ( file_exists( $fileName ) )
			return file_get_contents( $fileName );
		return false;
	}

	public function copyFile( $source, $savepath, $deleteSrc = false ) {
		$dir = substr( $savepath, 0, strripos( $savepath, '/' ) );
		if ( !file_exists( $dir ) ) {
			mkdir( $dir, 0777, true );
		}
		if ( true === $deleteSrc ) {
			$this->deleteFile( $source );
		}
		return copy( $source, $savepath );
	}

	/**
	 * 本地环境文件大小：直接调用filesize
	 * @param string $file 文件名
	 * @return integer 文件大小字节数
	 */
	public function fileSize( $file ) {
		return sprintf( '%u', filesize( $file ) );
	}

	/**
	 * 本地环境图像尺寸获取：直接调用getimagesize
	 * @param string $image 图像地址
	 * @return mixed
	 */
	public function imageSize( $image ) {
		if ( filesize( $image ) ) {
			return getimagesize( $image );
		} else {
			return false;
		}
	}

	/**
	 * 本地环境获取临时目录：返回data/temp目录
	 * @return string
	 */
	public function getTempPath() {
		return sprintf( '%s', 'data/temp' );
	}

	/**
	 * 清空目录下文件
	 * @param string $dir 目录名
	 * @return void
	 */
	public function clearDir( $dir ) {
		$directory = @dir( $dir );
		if ( is_object( $directory ) ) {
			while ( $entry = $directory->read() ) {
				$file = $dir . DIRECTORY_SEPARATOR . $entry;
				if ( is_file( $file ) ) {
					@unlink( $file );
				}
			}
			$directory->close();
			@touch( $dir . '/index.htm' );
		}
	}

	/**
	 * 递归清空目录包括目录本身
	 * @param string $srcDir 目标文件夹路径
	 */
	public function clearDirs( $srcDir ) {
		$dir = @opendir( $srcDir );
		while ( $entry = @readdir( $dir ) ) {
			$file = $srcDir . DIRECTORY_SEPARATOR . $entry;
			if ( !in_array( $entry, $this->excludeFiles ) ) {
				if ( is_dir( $file ) ) {
					$this->clearDirs( $file . DIRECTORY_SEPARATOR );
				} else {
					@unlink( $file );
				}
			}
		}
		closedir( $dir );
		@rmdir( $srcDir );
	}

	/**
	 * 本地环境附件下载方法
	 * @param type $attach
	 * @param type $downloadInfo
	 */
	public function download( $attach, $downloadInfo = array() ) {
		$file = PATH_ROOT . '/' . File::getAttachUrl() . '/' . $attach['attachment'];
		if ( file_exists( $file ) ) {
			if ( IBOS::app()->browser->name == 'msie' || IBOS::app()->browser->getVersion() == '10.0' || IBOS::app()->browser->getVersion() == '11.0' ) {
				$usingIe = true;
			} else {
				$usingIe = false;
			}
			$typeArr = array(
				'1' => "application/octet-stream",
				'3' => "application/msword",
				'4' => "application/msexcel",
				'5' => "application/mspowerpoint",
				'7' => "application/octet-stream",
				'8' => "application/x-shockwave-flash",
				'10' => "application/pdf",
				'11' => "application/octet-stream",
				'18' => "application/x-shockwave-flash"
			);
			$attachType = Attach::Attachtype( StringUtil::getFileExt( $attach['filename'] ), 'id' );
			$content = false;
			// 额外参数处理
			if ( isset( $downloadInfo['directView'] ) ) {
				if ( !in_array( $attachType, array( '1', '7', '8', '10' ) ) ) {
					$content = true;
				}
				$contentType = $typeArr[$attachType];
			} else {
				if ( in_array( $attachType, array( '3', '4', '5' ) ) && $usingIe ) {
					$contentType = $typeArr[$attachType];
				} else {
					$content = 1;
					$contentType = "application/octet-stream";
				}
			}
			if ( ob_get_length() ) {
				ob_end_clean();
			}
			header( "Cache-control: private" );
			header( "Content-type: {$contentType}" );
			header( "Accept-Ranges: bytes" );
			header( "Content-Length: " . sprintf( "%u", $this->fileSize( $file ) ) );
			if ( $usingIe ) {
				$attach['filename'] = urlencode( $attach['filename'] );
			}
			if ( $content ) {
				header( "Content-Disposition: attachment; filename=\"" . $attach['filename'] . "\"" );
			} else {
				header( "Content-Disposition: filename=\"" . $attach['filename'] . "\"" );
			}
			Attachment::model()->updateDownload( $attach['aid'] );
			readfile( $file );
			exit();
		}
	}

	public function uploadFile( $destFileName, $srcFileName ) {
		$isUpload = move_uploaded_file( $destFileName, $srcFileName );
		if ( $isUpload ) {
			return $destFileName;
		} else {
			return false;
		}
	}

	public function createAvatar( $srcPath, $params ) {
		$avatarPath = 'data/avatar/';
		// 三种尺寸的地址
		$avatarBig = UserUtil::getAvatar( $params['uid'], 'big' );
		$avatarMiddle = UserUtil::getAvatar( $params['uid'], 'middle' );
		$avatarSmall = UserUtil::getAvatar( $params['uid'], 'small' );
		// 如果是本地环境，先确定文件路径要存在
		File::makeDirs( $avatarPath . dirname( $avatarBig ) );
		// 先创建空白文件
		$this->createFile( $avatarPath . $avatarBig, '' );
		$this->createFile( $avatarPath . $avatarMiddle, '' );
		$this->createFile( $avatarPath . $avatarSmall, '' );
		// 加载类库
		IBOS::import( 'ext.ThinkImage.ThinkImage', true );
		$imgObj = new ThinkImage( THINKIMAGE_GD );
		//裁剪原图
		$imgObj->open( $srcPath )->crop( $params['w'], $params['h'], $params['x'], $params['y'] )->save( $srcPath );
		//生成缩略图
		$imgObj->open( $srcPath )->thumb( 180, 180, 1 )->save( $avatarPath . $avatarBig );
		$imgObj->open( $srcPath )->thumb( 60, 60, 1 )->save( $avatarPath . $avatarMiddle );
		$imgObj->open( $srcPath )->thumb( 30, 30, 1 )->save( $avatarPath . $avatarSmall );
		return array(
			'avatar_big' => $avatarPath . $avatarBig,
			'avatar_middle' => $avatarPath . $avatarMiddle,
			'avatar_small' => $avatarPath . $avatarSmall,
		);
	}

	public function createBg( $srcPath, $params ) {
		$bgPath = 'data/home/';
		// 三种尺    寸的地址
		$bgBig = UserUtil::getBg( $params['uid'], 'big' );
//		$bgMiddle = UserUtil::getBg( $params['uid'], 'middle' );
		$bgSmall = UserUtil::getBg( $params['uid'], 'small' );
		File::makeDirs( $bgPath . dirname( $bgBig ) );
		// 先创建空白文件
		$this->createFile( $bgPath . $bgBig, ' ' );
//		$this->createFile( $bgPath . $bgMiddle, ' ' );
		$this->createFile( $bgPath . $bgSmall, ' ' );
		// 加载类库
		$imgObj = new ThinkImage( THINKIMAGE_GD );
		//裁剪原图(系统的背景图不需要裁剪)
		if ( !isset( $params['noCrop'] ) ) {
			$imgObj->open( $srcPath )->crop( $params['w'], $params['h'], $params['x'], $params['y'], 1000, 300 )->save( $srcPath );
		}
		//生成缩略图
		$imgObj->open( $srcPath )->thumb( 1000, 300, 1 )->save( $bgPath . $bgBig );
		//$imgObj->open( $srcPath )->thumb( 520, 156, 1 )->save( $bgPath . $bgMiddle );
		$imgObj->open( $srcPath )->thumb( 400, 120, 1 )->save( $bgPath . $bgSmall );
		$host = $this->getImgHost( $bgPath );
		// 设置为公用模板
		if ( isset( $params['commonSet'] ) && $params['commonSet'] ) {
			$this->setCommonBg( $bgPath, $bgBig );
		}
		return array(
			'bg_big' => $host . $bgBig,
			'bg_middle' => '',
			'bg_small' => $host . $bgSmall,
		);
	}

	/**
	 * 设置公用模板
	 * @param string $src 图片路径
	 * @return boolean
	 */
	private function setCommonBg( $bgPath, $bgBig ) {
		$host = $this->getImgHost( $bgPath );
		$data = array(
			'desc' => '',
			'status' => 0,
			'system' => 0,
			'image' => $host . $bgBig,
			'image_path' => $bgPath . $bgBig,
		);
		$addRes = BgTemplate::model()->add( $data );
		return $addRes;
	}

	public function getImgHost( $path ) {
		return $path;
	}

	public function getHost( $path ) {
		return $path;
	}

	public function thumbnail( $fromFileName, $toFileName, $thumbWidth = 96, $thumbHeight = 96 ) {
		Image::thumb( $fromFileName, $toFileName, $thumbWidth, $thumbHeight );
		return $toFileName;
	}

	public function getOrgJs( $typeArray ) {
		$string = '';
		foreach ( $typeArray as $type ) {
			$js = $this->fileExists( 'data/org/' . $type . '.js' );
			if ( !$js ) {
				Org::update( $type );
			}
			$string .= '<script src = "data/org/' . $type . '.js?' . VERHASH . '" ></script>';
		}
		return $string;
	}

	public function setOrgJs( $type, $value ) {
		Cache::set( $type . '_js', 1 ); //本地的话，直接引用js，所以只要设置这个cache存在就好了
		return $this->createFile( 'data/org/' . $type . '.js', $value );
	}

	//tp的文字水印不支持透明度
	public function waterString( $text, $size, $from, $to, $position, $alpha, $quality, $color, $fontPath ) {
		$rgb = Convert::hexColorToRGB( $color );
		Image::waterMarkString( $text, $size, $from, $to, $position, $quality, $rgb, $fontPath );
	}

	//tp的图片水印
	public function waterPic( $from, $pic, $to, $position, $alpha, $quality, $imgHeight, $imgWidth ) {
		// 加载类库
		$imgObj = new ThinkImage( THINKIMAGE_GD );
		$imgObj->open( $pic )->thumb( $imgWidth, $imgHeight, 1 )->save( $pic );
		Image::water( $from, $pic, $to, $position, $alpha, $quality );
	}

}
