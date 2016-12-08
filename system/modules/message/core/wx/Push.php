<?php

/**
 * WxPush class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信推送处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @link http://qydev.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E7%B1%BB%E5%9E%8B%E5%8F%8A%E6%95%B0%E6%8D%AE%E6%A0%BC%E5%BC%8F 详细接口介绍
 * @version $Id$
 */

namespace application\modules\message\core\wx;

use CApplicationComponent;

class Push extends CApplicationComponent
{

    /**
     * 推送文字格式的消息
     * @param array $userIds 推送的微信用户ID
     * @param string $content 消息内容
     * @param integer $appId 应用ID
     * @param string $suiteid 套件id
     * @param integer $safe 是否保密消息
     * @param array $toparty 发送部门范围
     * @param array $totag 发送标签范围
     * @return boolean 推送成功与否
     */
    public function sendText($userIds, $content, $appId, $suiteid, $safe = 0, $toparty = array(), $totag = array())
    {
        $param = array(
            'msgtype' => 'text',
            'agentid' => $appId,
            'text' => array('content' => '{content}'),
            'safe' => $safe,
        );
        $this->handleSendScope($param, $userIds, $toparty, $totag);
        $encode = json_encode($param);
        $data = str_replace('{content}', $content, $encode);
        $res = WxApi::getInstance()->push($data, $suiteid);
        return $res;
    }

    /**
     * 发送图文格式的消息
     * @param array $userIds 推送的微信用户ID
     * @param array $items 图文消息内容项，格式详见 @see handleNewsItems
     * @param integer $appId 应用ID
     * @param string $suiteid 套件id
     * @param array $toparty 发送部门范围
     * @param array $totag 发送标签范围
     * @return boolean 推送成功与否
     */
    public function sendNews($userIds, $items, $appId, $suiteid, $toparty = array(), $totag = array())
    {
        $param = array();
        $this->handleSendScope($param, $userIds, $toparty, $totag);
        $content = $this->handleNewsItems($items);
        $article = <<<EOT
{
    "touser": "{$param['touser']}",
    "toparty": "{$param['toparty']}",
    "totag": "{$param['totag']}",
    "msgtype": "news",
    "agentid": "{$appId}",
    "news": {
        "articles":[
            {$content}
        ] 
    }
}                        
EOT;
        return WxApi::getInstance()->push($article, $suiteid);
    }

    /**
     * 发送图文格式的消息
     * @param array $userIds 推送的微信用户ID
     * @param string $medidaId 微信媒体资源ID
     * @param integer $appId 应用ID
     * @param string $suiteid 套件id
     * @param integer $safe 是否保密消息
     * @param array $toparty 发送部门范围
     * @param array $totag 发送标签范围
     * @return boolean 推送成功与否
     */
    public function sendVideo($userIds, $medidaId, $appId, $suiteid, $safe = 0, $toparty = array(), $totag = array())
    {
        $param = array(
            'msgtype' => 'video',
            'agentid' => $appId,
            'video' => array('media_id' => $medidaId),
            'safe' => $safe,
        );
        $this->handleSendScope($param, $userIds, $toparty, $totag);
        $data = json_encode($param);
        return WxApi::getInstance()->push($data, $suiteid);
    }

    /**
     * 发送音频格式的消息
     * @param array $userIds 推送的微信用户ID
     * @param string $medidaId 微信媒体资源ID
     * @param integer $appId 应用ID
     * @param string $suiteid 套件id
     * @param integer $safe 是否保密消息
     * @param array $toparty 发送部门范围
     * @param array $totag 发送标签范围
     * @return boolean 推送成功与否
     */
    public function sendVoice($userIds, $medidaId, $appId, $suiteid, $safe = 0, $toparty = array(), $totag = array())
    {
        $param = array(
            'msgtype' => 'voice',
            'agentid' => $appId,
            'voice' => array('media_id' => $medidaId),
            'safe' => $safe,
        );
        $this->handleSendScope($param, $userIds, $toparty, $totag);
        $data = json_encode($param);
        return WxApi::getInstance()->push($data, $suiteid);
    }

    /**
     * 发送图片格式的消息
     * @param array $userIds 推送的微信用户ID
     * @param string $medidaId 微信媒体资源ID
     * @param integer $appId 应用ID
     * @param string $suiteid 套件id
     * @param integer $safe 是否保密消息
     * @param array $toparty 发送部门范围
     * @param array $totag 发送标签范围
     * @return boolean 推送成功与否
     */
    public function sendImage($userIds, $medidaId, $appId, $suiteid, $safe = 0, $toparty = array(), $totag = array())
    {
        $param = array(
            'msgtype' => 'image',
            'agentid' => $appId,
            'image' => array('media_id' => $medidaId),
            'safe' => $safe,
        );
        $this->handleSendScope($param, $userIds, $toparty, $totag);
        $data = json_encode($param);
        return WxApi::getInstance()->push($data, $suiteid);
    }

    /**
     * 处理文件格式的类型描述
     * @param string $ext 文件后缀
     * @param integer $size 文件大小
     * @return string 符合微信端的格式则返回文件类型，否则返回空串
     */
    protected function handleFileType($ext, $size)
    {
        if ($ext == 'amr' && $size <= 2097152) {
            $type = 'voice';
        } else if ($ext == 'jpg' && $size <= 1048576) {
            $type = 'image';
        } else if ($ext == 'mp4' && $size <= 10485760) {
            $type = 'video';
        } else if ($size <= 10485760) {
            $type = 'file';
        } else {
            $type = '';
        }
        return $type;
    }

    /**
     * 处理图文格式的项目为字符串
     * @param array $items
     * @return string
     */
    protected function handleNewsItems($items)
    {
        $content = '';
        foreach ($items as $item) {
            $content .= <<<EOT
           {
               "title": "{$item['title']}",
               "description": "{$item['description']}",
               "url": "{$item['url']}",
               "picurl": "{$item['picurl']}",
           },
EOT;
        }
        return $content;
    }

    /**
     * 处理主动发送消息范围
     * @param array $param
     * @param mixed $userIds
     * @param array $toparty
     * @param array $totag
     */
    protected function handleSendScope(&$param, $userIds = array(), $toparty = array(), $totag = array())
    {
        $param['touser'] = $userIds == 'all' ? '@all' : implode('|', $userIds);
        if (!empty($toparty)) {
            $param['toparty'] = implode('|', $toparty);
        } else {
            $param['toparty'] = '';
        }
        if (!empty($totag)) {
            $param['totag'] = implode('|', $totag);
        } else {
            $param['totag'] = '';
        }
    }

}
