<?php

namespace application\core\cache\driver;

use application\core\components\Cache;
use application\core\utils\IBOS;

/**
 * 文件类型缓存类
 */
class File extends Cache {

	/**
	 * 架构函数
	 */
	public function __construct( $options = array() ) {
		if ( !empty( $options ) ) {
			$this->options = $options;
		}
		$this->options['temp'] = !empty( $options['temp'] ) ? $options['temp'] : IBOS::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'cache';
		$this->options['expire'] = isset( $options['expire'] ) ? $options['expire'] : 0;
		if ( substr( $this->options['temp'], -1 ) != '/' ) {
			$this->options['temp'] .= '/';
		}
		$this->initDir();
	}

	/**
	 * 初始化检查
	 * @return boolean
	 */
	private function initDir() {
		// 创建应用缓存目录
		if ( !is_dir( $this->options['temp'] ) ) {
			mkdir( $this->options['temp'] );
		}
	}

	/**
	 * 取得变量的存储文件名
	 * @param string $name 缓存变量名
	 * @return string
	 */
	private function filename( $name ) {
		$name = md5( $name );
		if ( $this->options['subdir'] ) {
			// 使用子目录
			$dir = '';
			for ( $i = 0; $i < $this->options['level']; $i++ ) {
				$dir .= $name{$i} . '/';
			}
			if ( !is_dir( $this->options['temp'] . $dir ) ) {
				mkdir( $this->options['temp'] . $dir, 0755, true );
			}
			$fileName = $dir . $this->options['prefix'] . $name . '.php';
		} else {
			$fileName = $this->options['prefix'] . $name . '.php';
		}
		return $this->options['temp'] . $fileName;
	}

	/**
	 * 读取缓存
	 * @param string $name 缓存变量名
	 * @return mixed
	 */
	public function get( $name ) {
		$filename = $this->filename( $name );
		if ( !is_file( $filename ) ) {
			return false;
		}
		$content = file_get_contents( $filename );
		if ( false !== $content ) {
			$expire = (int) substr( $content, 8, 12 );
			if ( $expire != 0 && time() > filemtime( $filename ) + $expire ) {
				//缓存过期删除缓存文件
				unlink( $filename );
				return false;
			}
			if ( $this->options['check'] ) {//开启数据校验
				$check = substr( $content, 20, 32 );
				$content = substr( $content, 52, -3 );
				if ( $check != md5( $content ) ) {//校验错误
					return false;
				}
			} else {
				$content = substr( $content, 20, -3 );
			}
			if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
				//启用数据压缩
				$content = gzuncompress( $content );
			}
			$content = unserialize( $content );
			return $content;
		} else {
			return false;
		}
	}

	/**
	 * 写入缓存
	 * @param string $name 缓存变量名
	 * @param mixed $value  存储数据
	 * @param int $expire  有效时间 0为永久
	 * @return boolean
	 */
	public function set( $name, $value, $expire = null ) {
		if ( is_null( $expire ) ) {
			$expire = $this->options['expire'];
		}
		$filename = $this->filename( $name );
		$data = serialize( $value );
		if ( $this->options['compress'] && function_exists( 'gzcompress' ) ) {
			//数据压缩
			$data = gzcompress( $data, 3 );
		}
		if ( $this->options['check'] ) {//开启数据校验
			$check = md5( $data );
		} else {
			$check = '';
		}
		$data = "<?php\n//" . sprintf( '%012d', $expire ) . $check . $data . "\n?>";
		$result = file_put_contents( $filename, $data );
		if ( $result ) {
			clearstatcache();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 删除缓存
	 * @param string $name 缓存变量名
	 * @return boolean
	 */
	public function rm( $name ) {
		$file = $this->filename( $name );
		return is_file( $file ) ? unlink( $file ) : false;
	}

	/**
	 * 清除缓存
	 * @param string $name 缓存变量名
	 * @return boolean
	 */
	public function clear() {
		$path = $this->options['temp'];
		$files = scandir( $path );
		if ( $files ) {
			foreach ( $files as $file ) {
				if ( $file != '.' && $file != '..' && is_dir( $path . $file ) ) {
					$glob = glob( $path . $file . '/*.*' );
					if ( is_array( $glob ) ) {
						array_map( 'unlink', glob( $path . $file . '/*.*' ) );
					}
				} elseif ( is_file( $path . $file ) ) {
					unlink( $path . $file );
				}
			}
			return true;
		}
		return false;
	}

}
