<?php

/**
 * WxCalendarCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号日程待办应用回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\callback;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Tasks;
use application\modules\message\core\wx\Callback;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\WxApi;

class Calendar extends Callback
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
     * 插入最新的待办并返回最近的待办列表
     * @return string
     */
    protected function handleByText()
    {
        $uid = Ibos::app()->user->uid;
        $sort = Ibos::app()->db->createCommand()
            ->select('MAX(sort) as sortid')
            ->from('{{tasks}}')
            ->where('uid=' . $uid)
            ->queryScalar();
        $id = TIMESTAMP . StringUtil::random(3);
        $data = array(
            'id' => $id,
            'text' => $this->getMessage(),
            'addtime' => TIMESTAMP,
            'uid' => $uid,
            'sort' => intval($sort),
            'pid' => '',
        );
        Tasks::model()->add($data);
        return $this->resRecentTasks();
    }

    /**
     * 返回最近的待办列表
     * @return string
     */
    public function resRecentTasks()
    {
        $uid = Ibos::app()->user->uid;
        $criteria = array(
            'condition' => "`pid` = '' AND `uid` = {$uid} AND `complete` = 0",
            'order' => '`sort` ASC,`addtime` DESC',
            'limit' => 9,
        );
        $lists = Tasks::model()->fetchAll($criteria);
        $items = array();
        $items[0] = array(
            'title' => "最近的待办(" . count($lists) . ")，点击即可完成",
            'description' => '',
            'picurl' => 'http://app.ibos.cn/img/banner/calendar.png',
            'url' => ''
        );
        foreach ($lists as $key => $row) {
            $key++;
            $item = array(
                'title' => $row['text'],
                'description' => '',
                'picurl' => 'http://app.ibos.cn/img/sort/' . $key . '.png',
                'url' => WxApi::getInstance()->createOauthUrl(WxApi::getInstance()->getHostInfo() . '/api/wxqy/callback.php?type=todo&param=' . $row['id'], $this->appId)
            );
            $items[] = $item;
        }
        return $this->resNews($items);
    }

}
