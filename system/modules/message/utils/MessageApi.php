<?php

namespace application\modules\message\utils;

use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\user\model\User;

class MessageApi {

    public function getCommentSourceDesc( $sourceUser, $sourceType, $sourceUrl, $data ) {
        $user = User::model()->fetchByUid( $data['uid'] );
        $params = array(
            '{sourceUser}' => $sourceUser,
            '{sourceType}' => $sourceType,
            '{user}' => $user['realname'],
            '{commentContent}' => String::cutStr( $data['sourceContent'], 30 ),
            '{sourceUrl}' => $sourceUrl
        );
        return IBOS::lang( 'Comment source desc', 'message.default', $params );
    }

}
