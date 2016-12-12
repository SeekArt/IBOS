<?php

/**
 * WxCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 * @link http://qydev.weixin.qq.com/wiki/index.php?title=%E8%A2%AB%E5%8A%A8%E5%93%8D%E5%BA%94%E6%B6%88%E6%81%AF 回调消息明文格式详细
 */

namespace application\modules\message\core\wx;

use application\core\utils\StringUtil;
use CApplicationComponent;

abstract class Callback extends CApplicationComponent
{

    const RES_TEXT = 'text';
    const RES_IMAGE = 'image';
    const RES_VOICE = 'voice';
    const RES_VIDEO = 'video';
    const RES_EVENT = 'event';

    /**
     * 微信企业号的企业ID
     * @var string
     */
    protected $corpId = '';

    /**
     * 微信企业号我们的套件ID
     * @var string
     */
    protected $suiteid = '';

    /**
     * 应用ID
     * @var integer
     */
    protected $appId = 0;

    /**
     * 回调处理的类型
     * @var string
     */
    protected $resType = '';

    /**
     *    事件类型
     * @var type
     */
    protected $eventType = '';

    /**
     * 微信用户ID
     * @var string
     */
    protected $userId = '';

    /**
     * 用户发送给微信端的信息
     * @var string
     */
    protected $message = '';

    /**
     * 媒体ID
     * @var string
     */
    protected $mediaId = '';

    /**
     * 设置微信企业号的企业ID
     * @param string $id
     */
    public function setCorpId($id)
    {
        $this->corpId = $id;
    }

    /**
     * 设置应用ID
     * @param integer $id
     */
    public function setAppId($id)
    {
        $this->appId = intval($id);
    }

    /**
     * 设置回复类型
     * @param string $type
     */
    public function setResType($type)
    {
        $this->resType = $type;
    }

    /**
     * 设置事件类型
     * @param type $type
     */
    public function setEventType($type)
    {
        $this->eventType = $type;
    }

    /**
     * 设置用户ID
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * 设置处理文本
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = StringUtil::filterCleanHtml($message);
    }

    /**
     * 返回微信企业号企业ID
     * @return string
     */
    public function getCorpId()
    {
        return $this->corpId;
    }

    public function getSuiteid()
    {
        return $this->suiteid;
    }

    /**
     * 获取应用ID
     * @return integer
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * 获取用户ID
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 获取处理文本
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     *
     * @return type
     */
    public function getResType()
    {
        return $this->resType;
    }

    /**
     * 获取事件类型
     * @return type
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * 设置媒体ID
     * @param string $mediaId
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * 获取媒体ID
     * @return string
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * 回调给微信端的文本消息
     * @param string $userId 微信用户ID
     * @param string $text 文本内容
     * @return string 替换组合后的XML格式字符串
     */
    protected function resText($text = '')
    {
        $time = TIMESTAMP;
        return <<<EOT
        <xml>
           <ToUserName><![CDATA[{$this->userId}]]></ToUserName>
           <FromUserName><![CDATA[{$this->corpId}]]></FromUserName> 
           <CreateTime>{$time}</CreateTime>
           <MsgType><![CDATA[text]]></MsgType>
           <Content><![CDATA[{$text}]]></Content>
        </xml>   
EOT;
    }

    /**
     * 回复给微信端的图文格式
     * @param string $userId
     * @param array $items
     * @return string 替换组合后的XML格式字符串
     */
    protected function resNews($items = array())
    {
        $itemStr = $this->handleResNewsItems($items);
        $count = count($items);
        $time = TIMESTAMP;
        return <<<EOT
        <xml>
            <ToUserName><![CDATA[{$this->userId}]]></ToUserName>
            <FromUserName><![CDATA[{$this->corpId}]]></FromUserName>
            <CreateTime>{$time}</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>{$count}</ArticleCount>
            <Articles>
                {$itemStr}
            </Articles>
        </xml>       
EOT;
    }

    /**
     * 处理回复文本里的图片格式项目为规定的字符串
     * @param array $items
     * @return string
     */
    private function handleResNewsItems($items)
    {
        $itemStr = '';
        foreach ($items as $item) {
            $itemStr .= <<<EOT
            <item>
                <Title><![CDATA[{$item['title']}]]></Title> 
                <Description><![CDATA[{$item['description']}]]></Description>
                <PicUrl><![CDATA[{$item['picurl']}]]></PicUrl>
                <Url><![CDATA[{$item['url']}]]></Url>
            </item>     
EOT;
        }
        return $itemStr;
    }

    abstract public function handle();

}
