<?php

/**
 * WxChatCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号聊天室应用回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\callback;

use application\core\utils\Ibos;
use application\modules\message\core\wx\Callback;
use application\modules\message\core\wx\Factory;
use application\modules\message\core\wx\Push;
use application\modules\message\model\Atme;
use application\modules\user\model\UserBinding;

class Chat extends Callback
{

    /**
     * 回调的处理方法
     * @return string
     */
    public function handle()
    {
        $factory = new Factory();
        $pushHandle = $factory->createHandle('Push');
        switch ($this->resType) {
            case self::RES_TEXT:
                $res = $this->retweetByText($pushHandle);
                break;
            case self::RES_IMAGE:
                $res = $this->retweetByImage($pushHandle);
                break;
            case self::RES_VOICE:
                $res = $this->retweetByVoice($pushHandle);
                break;
            case self::RES_VIDEO:
                $res = $this->retweetByVideo($pushHandle);
                break;
            case self::RES_EVENT:
                $res = $this->resText();
                break;
            default:
                $res = false;
                break;
        }
        if ($res) {
            return $this->resText();
        } else {
            return $this->resText('消息推送失败');
        }
    }

    /**
     * 文本消息转推
     * @param Push $handle 微信推送处理器
     * @return boolean
     */
    protected function retweetByText(Push $handle)
    {
        $atUids = Atme::model()->getUids($this->getMessage());
        if (!empty($atUids)) { // at人，单独发送
            $userIds = UserBinding::model()->fetchValuesByUids($atUids, 'wxqy');
            if (!empty($userIds)) {
                return $handle->sendText($userIds, Ibos::app()->user->realname . ':' . $this->getMessage(), $this->getAppId(), $this->getSuiteid());
            } else {
                return '';
            }
        } else {
            $res = $handle->sendText('all', Ibos::app()->user->realname . ':' . $this->getMessage(), $this->getAppId(), $this->getSuiteid());
            return $res;
        }
    }

    /**
     * 图片消息转推
     * @param Push $handle 微信推送处理器
     * @return boolean
     */
    protected function retweetByImage(Push $handle)
    {
        return $handle->sendImage('all', $this->getMediaId(), $this->getAppId(), $this->getSuiteid());
    }

    /**
     * 音频消息转推
     * @param Push $handle
     * @return boolean
     */
    protected function retweetByVoice(Push $handle)
    {
        return $handle->sendVoice('all', $this->getMediaId(), $this->getAppId(), $this->getSuiteid());
    }

    /**
     * 视频消息转推
     * @param Push $handle
     * @return boolean
     */
    protected function retweetByVideo(Push $handle)
    {
        return $handle->sendVideo('all', $this->getMediaId(), $this->getAppId(), $this->getSuiteid());
    }

}
