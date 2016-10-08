<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\main\model\AttachmentN;
use application\modules\main\model\Setting;
use CHtml;

class UnitController extends BaseController {

	/**
	 * 单位管理
	 * @return mixed
	 */
	public function actionIndex() {
		$keys = array(
			'phone', 'fullname',
			'shortname', 'fax', 'zipcode',
			'address', 'adminemail', 'systemurl', 'corpcode'
		);
		// 是否提交
		if ( Env::submitCheck( 'unitSubmit' ) ) {
			$postData = array();

			//需要根据$_FILES['logo']['error']判断文件上传是否成功，0成功
			//当文件上传时需要判断是否有错误
			if ( isset( $_FILES['logo'] ) && !empty( $_FILES['logo']['name'] ) ) {
				if ( $_FILES['logo']['error'] != 0 ) {
					//TODO 这里只是简单的输出错误提示，应该要处理得更加合理
					die( '抱歉，设置失败，请您重试！' );
				}
				//应该还有判断是否为图片文件，通过文件扩展名（或是取得文件信息）
				$ext = strtolower( pathinfo( strip_tags( $_FILES['logo']['name'] ), PATHINFO_EXTENSION ) );
				if ( in_array( $ext, array( 'gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf' ) ) && !empty( $ext ) ) {
					$imginfo = getimagesize( $_FILES['logo']['tmp_name'] );
					if ( empty( $imginfo ) || ($ext == 'gif' && empty( $imginfo['bits'] )) ) {
						//TODO 这里只是简单的输出错误提示，应该要处理得更加合理
						die( '非法图像文件！' );
					}
				} else {
					//TODO 这里只是简单的输出错误提示，应该要处理得更加合理
					die( '不是有效的图片文件' );
				}
			}

			if ( !empty( $_FILES['logo']['name'] ) ) {
				!empty( $unit['logourl'] ) && File::deleteFile( $unit['logourl'] );
				$postData['logourl'] = $this->imgUpload( 'logo' );
				$postData['logourl'] = Ibos::engine()->io()->file()->thumbnail( $postData['logourl'], $postData['logourl'], 120, 40 );
			} else {
				if ( !empty( $_POST['logourl'] ) ) {
					$postData['logourl'] = $_POST['logourl'];
				} else {
					$postData['logourl'] = '';
				}
			}

			foreach ( $keys as $key ) {
				if ( isset( $_POST[$key] ) ) {
					$postData[$key] = CHtml::encode( $_POST[$key] );
				} else {
					$postData[$key] = '';
				}
			}
			Setting::model()->updateSettingValueByKey( 'unit', $postData );
			Cache::update( 'setting' );
			$this->success( Ibos::lang( 'Save succeed', 'message' ) );
		} else {

			$unit = StringUtil::utf8Unserialize( Setting::model()->fetchSettingValueByKey( 'unit' ) );
			foreach ( $keys as $key ) {
				if ( !isset( $unit[$key] ) ) {
					$unit[$key] = '';
				}
			}
			$license = Setting::model()->fetchSettingValueByKey( 'license' );
			$data = array( 'unit' => $unit, 'license' => $license );
			$this->render( 'index', $data );
		}
	}

}
