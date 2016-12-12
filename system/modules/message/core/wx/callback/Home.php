<?php

/**
 * WxHomeCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号个人门户应用回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\callback;

use application\core\utils\Attach;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\main\model\Attachment;
use application\modules\main\model\AttachmentN;
use application\modules\message\core\wx\Callback;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\WxApi;
use application\modules\message\model\Feed;

class Home extends Callback
{

    /**
     * 回调的处理方法
     * @return string
     */
    public function handle()
    {
        switch ($this->resType) {
            case self::RES_TEXT:
                $res = $this->handleByText();
                break;
            case self::RES_IMAGE:
                $res = $this->handleByImage();
                break;
            case self::RES_EVENT:
                $res = $this->resText();
                break;
            default:
                $res = $this->resText(Code::UNSUPPORTED_RES_TYPE);
                break;
        }
        return $res;
    }

    /**
     * 插入文字到企业微博
     * @return string
     */
    protected function handleByText()
    {
        $data = array(
            'from' => 6,
            'body' => $this->getMessage()
        );
        Feed::model()->put(Ibos::app()->user->uid, 'weibo', 'post', $data);
        return $this->resText('你已经成功发送该消息到企业微博');
    }

    /**
     * 插入图片到企业微博
     * @return string
     */
    protected function handleByImage()
    {
        $suffix = '.jpg';
        $uid = Ibos::app()->user->uid;
        $file = $this->saveToLocal($suffix);
        if (is_file($file)) {
            $aid = $this->saveAttach($uid, $file, $suffix);
            $data = array(
                'from' => 6,
                'body' => '我分享了一张图片',
                'attach_id' => array($aid)
            );
            Feed::model()->put($uid, 'weibo', 'postimage', $data);
            return $this->resText('你已经成功发送该图片到企业微博');
        } else {
            return $this->resText('发送图片失败');
        }
    }

    /**
     * 保存图片为本地附件
     * @param integer $uid
     * @param string $file
     * @param string $suffix
     * @return integer
     */
    private function saveAttach($uid, $file, $suffix)
    {
        $tableId = Attach::getTableId($uid);
        $attach = array('uid' => $uid, 'tableid' => $tableId);
        $aid = Attachment::model()->add($attach, true);
        $attachdata = array(
            'aid' => $aid,
            'uid' => $uid,
            'dateline' => TIMESTAMP,
            'filename' => $this->getMediaId() . $suffix,
            'filesize' => filesize($file),
            'attachment' => 'weibo/' . date('Ym') . '/' . date('d') . '/' . $this->getMediaId() . $suffix,
            'isimage' => 1,
        );
        AttachmentN::model()->add($tableId, $attachdata);
        return $aid;
    }

    /**
     * 保存的文件名后缀
     * @param string $suffix
     * @return string
     */
    private function saveToLocal($suffix)
    {
        $path = PATH_ROOT . './data/attachment/weibo/' . date('Ym') . '/' . date('d') . '/';
        File::makeDirs($path);
        $file = $path . $this->getMediaId() . $suffix;
        File::createFile($file, '');
        file_put_contents($file, WxApi::getInstance()->getMediaContent($this->getMediaId()));
        return $file;
    }

}
