<?php

include 'saasindex.php';

use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\main\components\CommonAttach;

class Uploader {

	private $fileField; //文件域名
	private $file; //文件上传对象
	private $base64; //文件上传对象
	private $config; //配置信息
	private $oriName; //原始文件名
	private $fileName; //新文件名
	private $fullName; //完整文件名,即从当前配置目录开始的URL
	private $filePath; //完整文件名,即从当前配置目录开始的URL
	private $fileSize; //文件大小
	private $fileType; //文件类型
	private $stateInfo; //上传状态信息,

	/**
	 * 构造函数
	 * @param string $fileField 表单名称
	 * @param array $config 配置项
	 * @param bool $base64 是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
	 */

	public function __construct( $fileField, $config, $type = "upload" ) {

		$upload = new CommonAttach( $fileField );
		$upload->upload();
		if ( !$upload->getIsUpoad() ) {
			$this->stateInfo = '上传失败';
			$this->getController()->ajaxReturn( array( 'msg' => Ibos::lang( 'Save failed', 'message' ), 'isSuccess' => false ) );
		} else {
			$info = $upload->getUpload()->getAttach();
			$this->stateInfo = 'SUCCESS';
			$this->fullName = File::fileName( $info['target'] );
			$this->fileName = $info['attachname'];
			$this->oriName = $info['name'];
			$this->fileType = '.' . $info['ext'];
			$this->fileSize = $info['size'];
			$upload->updateAttach( $info['aid'] );
		}
	}

	/**
	 * 获取当前上传成功文件的各项信息
	 * @return array
	 */
	public function getFileInfo() {
		return array(
			"state" => $this->stateInfo,
			"url" => $this->fullName,
			"title" => $this->fileName,
			"original" => $this->oriName,
			"type" => $this->fileType,
			"size" => $this->fileSize
		);
	}

}
