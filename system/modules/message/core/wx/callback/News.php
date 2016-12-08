<?php

/**
 * WxNewsCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号信息中心应用回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\callback;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\model\Article;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\message\core\wx\Callback;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\WxApi;

class News extends Callback
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

    protected function handleByText()
    {
        $condition = ArticleUtil::joinListCondition('normal_search', Ibos::app()->user->uid, 0, " subject LIKE '%{$this->message}%' ");
        $criteria = array(
            'condition' => $condition,
            'order' => 'istop DESC,toptime ASC,addtime DESC',
            'limit' => 9,
        );
        $lists = Article::model()->fetchAll($criteria);
        $hostinfo = WxApi::getInstance()->getHostInfo();
        if (!empty($lists)) {
            $items = array();
            $items[0] = array(
                'title' => "公司新闻(" . count($lists) . ")",
                'description' => '',
                'picurl' => 'http://app.ibos.cn/img/banner/news.png',
                'url' => ''
            );
            foreach ($lists as $key => $row) {
                $key++;
                $picUrl = 'http://app.ibos.cn/img/sort/' . $key . '.png';
                $route = 'http://app.ibos.cn?host=' . urlencode($hostinfo) . '/#/news/detail/' . $row['articleid'];
                $item = array(
                    'title' => $row['subject'],
                    'description' => StringUtil::cutStr(strip_tags($row['content']), 30),
                    'picurl' => $picUrl,
                    'url' => WxApi::getInstance()->createOauthUrl($route, $this->appId),
                );
                $items[] = $item;
            }
            return $this->resNews($items);
        } else {
            return $this->resText('抱歉，没有搜索到相关的新闻，请更换关键字再试');
        }
    }

}
