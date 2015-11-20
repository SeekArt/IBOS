<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS; 
use application\core\utils\Image;
use application\extensions\ThinkImage\ThinkImage;
use application\modules\dashboard\utils\Dashboard;
use application\modules\main\model\Setting;

class UploadController extends BaseController {

	const TTF_FONT_PATH = 'data/font/'; // 默认字体存放文件夹

	/**
	 * 上传与水印设置
	 * @return void
	 */

	public function actionIndex() {
		$operation = Env::getRequest( 'op' );
		switch ( $operation ) {
			case 'thumbpreview':// 缩略图预览
			case 'waterpreview':// 水印预览
				$temp = IBOS::engine()->io()->file()->getTempPath() . '/watermark_temp.jpg';
				if ( LOCAL ) {
					if ( is_file( $temp ) ) {
						@unlink( $temp );
					}
				}
				$quality = Env::getRequest( 'quality' );
				// 原图
				$source = PATH_ROOT . '/static/image/watermark_preview.jpg';
				if ( $operation == 'waterpreview' ) {
					$trans = Env::getRequest( 'trans',80 );
					$type = Env::getRequest( 'type' );
					$val = Env::getRequest( 'val' );
					$pos = Env::getRequest( 'pos',0 );
					// 图片水印
					if ( $type == 'image' ) {
                                                                                                                     //15-7-27 下午2:14 gzdzl
                                                                                                                     //图片水印时要判断文件是否存在
						//没有相应的图片文件，接下来的图片处理会发生错误
						if ( !file_exists( $val ) ) {
							//TODO 这里需要更加合理的提示
							die( '水印图片不存在' );
						}
                                                
						$sInfo = Image::getImageInfo( $source );
						$wInfo = Image::getImageInfo( $val );
						// 如果水印图片大过预览图, 压缩水印图片
						if ( $sInfo["width"] < $wInfo["width"] || $sInfo['height'] < $wInfo['height'] ) {
							$imgObj = new ThinkImage( THINKIMAGE_GD );
							$imgObj->open( $val )->thumb( 260, 77, 1 )->save( $val );
						}
						Image::water( $source, $val, $temp, $pos, $trans, $quality );
					} else {
						// 文字水印
						$hexColor = Env::getRequest( 'textcolor','#ffffff' );//默认是#ffffff(白色)，格式不做判断
						$size = intval( Env::getRequest( 'size', 16 ) ); //过滤无效输入
                        $size = ( $size > 0 && $size <= 48 ) ? $size : 16; //文字水印大小限制在1-48
						$fontPath = Env::getRequest( 'fontpath', 'msyh.ttf'); //字体默认是微软雅黑
						$rgb = Convert::hexColorToRGB( $hexColor );
                                                                                                                    //文字水印需要判断文字内容是否为空
						$val = (!empty( $val )) ? $val : 'IBOS'; //空时默认是IBOS
						Image::waterMarkString( $val, $size, $source, $temp, $pos, $quality, $rgb, self::TTF_FONT_PATH . $fontPath );
					}
					$image = $temp;
				}
				// 非本地环境，移动到 storage
				if ( !LOCAL ) {
					if ( IBOS::engine()->IO()->file()->createFile( $temp, file_get_contents( $image ) ) ) {
						$image = File::fileName( $temp );
					}
				}
				$data = array(
					'image' => $image,
					'sourceSize' => Convert::sizeCount( File::fileSize( $source ) ),
					'thumbSize' => Convert::sizeCount( File::fileSize( $image ) ),
					'ratio' => (sprintf( "%2.1f", File::fileSize( $image ) / File::fileSize( $source ) * 100 )) . '%',
                                                                                                 'time' => time()
				);
				$this->render( 'imagePreview', $data );
				exit();
				break;
			case 'upload': // 水印图片上传
				return $this->imgUpload( 'watermark', true );
				break;
		}
		$formSubmit = Env::submitCheck( 'uploadSubmit' );
		$uploadKeys = 'attachdir,attachurl,thumbquality,attachsize,filetype';
		$waterMarkkeys = 'watermarkminwidth,watermarkminheight,watermarktype,watermarkposition,' .
				'watermarktrans,watermarkquality,watermarkimg,watermarkstatus,watermarktext,watermarkfontpath';
		if ( $formSubmit ) {
			$keys = $uploadKeys . ',' . $waterMarkkeys;
			$keyField = explode( ',', $keys );
			$status = 0;
			foreach ( $_POST as $key => $value ) {
				if ( in_array( $key, $keyField ) ) {
					Setting::model()->updateSettingValueByKey( $key, $value );
				} 
				if(array_key_exists('watermarkstatus', $_POST)){
					$status = 1;
				}
				Setting::model()->updateSettingValueByKey( 'watermarkstatus', $status );
			}
			Cache::update( array( 'setting' ) );
			$this->success( IBOS::lang( 'Save succeed', 'message' ) );
		} else {
			$upload = Setting::model()->fetchSettingValueByKeys( $uploadKeys );
			$waterMark = Setting::model()->fetchSettingValueByKeys( $waterMarkkeys );
			$fontPath = Dashboard::getFontPathlist( self::TTF_FONT_PATH );
			//获取服务器上传最大限制
			$size = min( ini_get( 'upload_max_filesize' ), ini_get( 'post_max_size' ), ini_get( 'memory_limit' ) );
			$data = array(
				'size' => $size,
				'upload' => $upload,
				'waterMark' => $waterMark,
				'fontPath' => $fontPath
			);
			$this->render( 'index', $data );
		}
	}

}
