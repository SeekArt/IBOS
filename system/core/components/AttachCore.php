<?php

/**
 *
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *
 * @package application.core.components
 * @version $Id: AttachCore.php 7509 2016-07-08 08:35:42Z tanghang $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\components;

use application\core\utils\File;
use application\core\utils\IBOS;
use CException;

abstract class AttachCore {

	/**
	 * 上传对象
	 * @var object
	 */
	protected $upload;
	protected $isUpload = true;

	/**
	 * 初始化上传域
	 * @param string $fileArea
	 */
	public function __construct( $fileArea = 'Filedata', $module = 'temp' ) {
		$file = $_FILES[$fileArea];
		if ( $file['error'] ) {
			throw new CException( IBOS::lang( 'File is too big', 'error' ) );
		} else {
			$upload = File::getUpload( $file, $module );
			$this->upload = $upload;
		}
	}

	abstract public function upload();

	abstract public function updateAttach( $attachids, $related = 0 );
}
