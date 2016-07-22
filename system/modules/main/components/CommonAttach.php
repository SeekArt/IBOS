<?php

/**
 * 通用上传处理
 * @package application.modules.main.components
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: CommonAttach.php 7549 2016-07-14 10:08:35Z tanghang $
 */

namespace application\modules\main\components;

use application\core\components\AttachCore;
use application\core\utils as util;
use application\core\utils\IBOS;
use application\modules\main\model as MainModel;
use CJSON;

class CommonAttach extends AttachCore {

	public function upload() {
		$uidTemp = intval( util\Env::getRequest( 'uid' ) );
		$uid = empty( $uidTemp ) ? IBOS::app()->user->uid : $uidTemp;
		$this->upload->save();
		$attach = $this->upload->getAttach();
		$attachment = $attach['type'] . '/' . $attach['attachment'];
		$data = array(
			'dateline' => TIMESTAMP,
			'filename' => $attach['name'],
			'filesize' => $attach['size'],
			'attachment' => $attachment,
			'isimage' => $attach['isimage'],
			'uid' => $uid
		);
		$aid = MainModel\Attachment::model()->add( array( 'uid' => $uid, 'tableid' => 127 ), true );
		$data['aid'] = $aid;
		MainModel\AttachmentUnused::model()->add( $data );
		$file['icon'] = util\Attach::attachType( $attach['ext'] );
		$file['aid'] = $aid;
		$file['name'] = $attach['name'];
		$file['url'] = util\File::fileName( util\File::getAttachUrl() . '/' . $attachment, false );
		$attach['aid'] = $aid;
		$this->upload->setAttach( $attach );
		if ( !empty( $file ) && is_array( $file ) ) {
			$this->isUpload = true;
			return CJSON::encode( $file );
		} else {
			$this->isUpload = false;
			return CJSON::encode( array( 'aid' => 0, 'url' => 0, 'name' => 0 ) );
		}
	}

	public function getUpload() {
		return $this->upload;
	}

	public function getIsUpoad() {
		return $this->isUpload;
	}

	/**
	 * 获取上传附件大小
	 * @return integer
	 */
	public function getAttachSize() {
		$attach = $this->upload->getAttach();
		$size = isset( $attach['size'] ) ? intval( $attach['size'] ) : 0;
		return $size;
	}

	/**
	 * 更新附件到附件表，从“未使用”表移除
	 * @param mixed $attachids
	 * @param string $related
	 */
	public function updateAttach( $attachids, $related = 0 ) {
		return util\Attach::updateAttach( $attachids, $related );
	}

}
