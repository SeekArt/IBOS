<?php

namespace application\modules\message\utils;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;

class MessageApi
{

    public function getCommentSourceDesc($sourceUser, $sourceType, $sourceUrl, $data)
    {
        $user = User::model()->fetchByUid($data['uid']);
        $params = array(
            '{sourceUser}' => $sourceUser,
            '{sourceType}' => $sourceType,
            '{user}' => $user['realname'],
            '{commentContent}' => StringUtil::cutStr($data['sourceContent'], 30),
            '{sourceUrl}' => $sourceUrl
        );
        return Ibos::lang('Comment source desc', 'message.default', $params);
    }

}
