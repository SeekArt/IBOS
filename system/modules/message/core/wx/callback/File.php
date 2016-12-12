<?php

/**
 * WxFileCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号企业网盘应用回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\callback;

use application\core\utils\Api;
use application\core\utils\Attach;
use application\core\utils\File as FileUtil;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\file\core\FileCloud;
use application\modules\message\core\wx\Callback;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\Factory;
use application\modules\message\core\wx\WxApi;

class File extends Callback
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
     * 查询文件并推送下载
     * @return string
     */
    protected function handleByText()
    {
        $uid = Ibos::app()->user->uid;
        $condition = "f.name LIKE '%" . $this->getMessage() . "%' AND f.uid = {$uid} AND belong = 0 AND f.type = 0 AND f.isdel=0";
        $lists = Ibos::app()->db->createCommand()
            ->select('*')
            ->from('{{file}} f')
            ->leftJoin('{{file_detail}} fd', 'f.fid = fd.fid')
            ->where($condition)
            ->limit(9)
            ->queryAll();
        if (empty($lists)) {
            return $this->resText('抱歉，无法找到该文件：' . $this->getMessage());
        }
        return $this->resByList($lists);
    }

    /**
     * 找到多个文件的处理
     * @param array $lists 文件列表数组
     * @return string
     */
    private function resByList($lists)
    {
        $hostinfo = WxApi::getInstance()->getHostInfo();
        $items[0] = array(
            'title' => "为你找到以下文件，请输入ID或点击链接后下载",
            'description' => '',
            'picurl' => 'http://app.ibos.cn/img/banner/file.png',
            'url' => ''
        );
        foreach ($lists as $row) {
            $fileType = StringUtil::getFileExt($row['name']);
            $icon = Attach::attachType($fileType, 'bigicon');
            if ($row['cloudid'] > 0) {
                $param = array('param' => 'cloud/' . $row['cloudid'] . '-' . $row['attachmentid']);
            } else {
                $param = array('param' => 'local/' . $row['attachmentid']);
            }
            $param['userid'] = $this->getUserId();
            $param['appid'] = $this->getAppId();
            $param['type'] = 'attach';
            $route = Api::getInstance()->buildUrl($hostinfo . '/api/wxqy/callback.php', $param);
            $item = array(
                'title' => $row['name'] . " 【ID：{$row['attachmentid']}】",
                'description' => $row['remark'],
                'picurl' => $hostinfo . '/' . $icon,
                'url' => WxApi::getInstance()->createOauthUrl($route, $param['appid']),
            );
            $items[] = $item;
        }
        return $this->resNews($items);
    }
}
