<?php

namespace application\modules\message\model;

use application\core\model\Model;

class Message extends Model
{

    const ONE_ON_ONE_CHAT = 1;   // 1对1聊天
    const MULTIPLAYER_CHAT = 2;   // 多人聊天
    const SYSTEM_NOTIFY = 3;   // 系统私信

    protected $_reversibleType = array();

    /**
     * 初始化方法，
     * @return void
     */
    public function init()
    {
        $this->_reversibleType = array(self::ONE_ON_ONE_CHAT, self::MULTIPLAYER_CHAT);
        parent::init();
    }

}
