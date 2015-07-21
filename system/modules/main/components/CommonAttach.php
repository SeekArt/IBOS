<?php

/**
 * 通用上传处理
 * @package application.modules.main.components
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: CommonAttach.php 4064 2014-09-03 09:13:16Z zhangrong $
 */

namespace application\modules\main\components;

use application\core\components\Attach;
use application\core\utils as util;
use application\modules\main\model as MainModel;
use CJSON;

class CommonAttach extends Attach {

    public function upload() {
        $uid = intval( util\Env::getRequest( 'uid' ) );
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
        $file['icon'] = util\Attach::attachType( util\String::getFileExt( $attach['name'] ) );
        $file['aid'] = $aid;
        $file['name'] = $attach['name'];
//		$file['url'] = $attachment;
        $file['url'] = util\File::fileName( util\File::getAttachUrl() . '/' . $attachment );
        if ( !empty( $file ) && is_array( $file ) ) {
            return CJSON::encode( $file );
        } else {
            return CJSON::encode( array( 'aid' => 0, 'url' => 0, 'name' => 0 ) );
        }
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

}