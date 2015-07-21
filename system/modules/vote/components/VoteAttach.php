<?php

/**
 * 通用上传处理
 * @package application.modules.main.components
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @version $Id: VoteAttach.php 1924 2013-12-13 11:46:57Z gzhzh $
 */

namespace application\modules\vote\components;

use application\core\components\Attach;
use application\core\utils\Env;
use application\core\utils\File;
use application\modules\main\model\Attachment;
use application\modules\main\model\AttachmentUnused;
use CJSON;

class VoteAttach extends Attach {

    public function upload() {
        $uid = intval( Env::getRequest( 'uid' ) );
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
        $aid = Attachment::model()->add( array( 'uid' => $uid, 'tableid' => 127 ), true );
        $data['aid'] = $aid;
        AttachmentUnused::model()->add( $data );
//        $file['icon'] = AttachUtil::attachType( AttachUtil::getFileExt( $attach['name'] ) );
        $file['aid'] = $aid;
        $file['name'] = $attach['name'];
        $attachmentPath = File::getAttachUrl() . '/' . $attachment;
        $file['url'] = $attachmentPath;
        $file['thumburl'] = File::fileName( File::getAttachUrl() . '/' . $attachment );
//        if ( Yii::app()->setting->get( 'setting/votethumbenable' ) ) {
//            list($thumbWidth, $thumbHeight) = explode( ',', Yii::app()->setting->get( 'setting/votethumbwh' ) );
//            $imageInfo = ImageUtil::getImageInfo( $attachmentPath );
//            if ( $imageInfo['width'] > $thumbWidth || $imageInfo['height'] > $thumbHeight ) {
//                $sourceFileName = explode( '/', $attachment );
//				$sourceFileName[count( $sourceFileName ) - 1] = 'thumb_' . $sourceFileName[count( $sourceFileName ) - 1];
//				$thumbName = implode( '/', $sourceFileName );
//				if ( LOCAL ) {
//					ImageUtil::thumb( $attachment, $thumbName, $thumbWidth, $thumbHeight );
//				} else {
//					$tempFile = File::getTempPath() . 'tmp.' . $attach['ext'];
//					$orgImgname = Ibos::engine()->IO()->file()->fetchTemp( File::fileName( $attachment ), $attach['ext'] );
//					ImageUtil::thumb( $orgImgname, $tempFile, $thumbWidth, $thumbHeight );
//					File::createFile( $thumbName, file_get_contents( $tempFile ) );
//				}
//                $file['thumburl'] = $thumbName;
//            }
//        }
        if ( !empty( $file ) && is_array( $file ) ) {
            return CJSON::encode( $file );
        } else {
            return CJSON::encode( array( 'aid' => 0, 'url' => 0, 'name' => 0 ) );
        }
    }

}
