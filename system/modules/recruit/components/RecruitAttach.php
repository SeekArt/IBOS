<?php

/**
 * 招聘模块头像和附件上传处理
 * @package application.modules.main.components
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: RecruitAttach.php 1294 2013-10-25 09:57:45Z gzhzh $
 */

namespace application\modules\recruit\components;

use application\core\components\Attach;
use application\core\utils\Attach as AttachUtil;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\StringUtil;
use application\modules\main\model\Attachment;
use application\modules\main\model\AttachmentUnused;
use CJSON;

class RecruitAttach extends Attach {

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
		$file['icon'] = AttachUtil::attachType( StringUtil::getFileExt( $attach['name'] ) );
        $file['aid'] = $aid;
        $file['name'] = $attach['name'];
        //获取兼容云平台的附件地址
        $file['url'] = File::fileName( File::getAttachUrl() . '/' . $attachment );

        if ( !empty( $file ) && is_array( $file ) ) {
            return CJSON::encode( $file );
        } else {
            return CJSON::encode( array( 'aid' => 0, 'url' => 0, 'name' => 0 ) );
        }
    }

}
