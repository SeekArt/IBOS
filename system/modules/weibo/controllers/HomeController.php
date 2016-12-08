<?php

/**
 * 微博模块 主页控制器
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2014 IBOS Inc
 * @author banyan <banyan@ibos.com.cn>
 */
/**
 * @package application.modules.weibo.components
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\weibo\controllers;

use application\core\utils as util;
use application\core\utils\Ibos;
use application\modules\message\model\Feed;
use application\modules\message\model\FeedDigg;
use application\modules\message\model\UserData;
use application\modules\weibo\core as WbCore;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;

class HomeController extends BaseController
{

    /**
     * 微博首页
     */
    public function actionIndex()
    {
        $data = array();
        // 当前用户统计数据
        $data['userData'] = UserData::model()->getUserData($this->uid);
        // 活跃用户
        $data['activeUser'] = UserData::model()->fetchActiveUsers();
        // DEBUG::如果有关注分组需求，这里应获得我关注的人分组
        // 模块动态列表
        $data['movements'] = util\Ibos::app()->setting->get('setting/wbmovement');
        // 可用的动态模块列表
        $data['enableMovementModule'] = WbCommonUtil::getMovementModules();
        // 上传配置
        $data['uploadConfig'] = util\Attach::getUploadConfig();
        $this->setPageState('breadCrumbs', array(
            array('name' => util\Ibos::lang('Enterprise weibo')),
            array('name' => util\Ibos::lang('Index'), 'url' => $this->createUrl('home/index')),
            array('name' => util\Ibos::lang('List'))
        ));
        $var['type'] = isset($_GET['type']) ? util\StringUtil::filterCleanHtml($_GET['type']) : 'all';
        $var['feedtype'] = isset($_GET['feedtype']) ? util\StringUtil::filterCleanHtml($_GET['feedtype']) : 'all';
        $var['feedkey'] = isset($_GET['feedkey']) ? util\StringUtil::filterCleanHtml(urldecode($_GET['feedkey'])) : '';
        $var['loadNew'] = isset($_GET['page']) ? 0 : 1;
        $var['loadMore'] = isset($_GET['page']) ? 0 : 1;
        $var['loadId'] = 0;
        $var['nums'] = isset($_GET['page']) ? WbCore\WbConst::DEF_LIST_FEED_NUMS : 10;
        $var['enableImage'] = Ibos::app()->setting->get('setting/wbposttype/image');
        $this->render('index', array_merge($data, $var, $this->getData($var)));
    }

    /**
     * ajax加载更多微博
     */
    public function actionLoadMore()
    {
        // 获取GET与POST数据
        $data = $_GET + $_POST;
        // 查询是否有分页
        if (!empty($data['page']) || intval($data['loadcount']) == 2) {
            unset($data['loadId']);
            $data['nums'] = WbCore\WbConst::DEF_LIST_FEED_NUMS;
        } else {
            $return = array('status' => -1, 'msg' => util\Ibos::lang('Loading ID isnull'));
            $data['loadId'] = intval($data['loadId']);
            $data['nums'] = 5;
        }
        $content = $this->getData($data);
        // 查看是否有更多数据
        if (empty($content['html']) || (empty($data['loadId']) && intval($data['loadcount']) != 2)) {
            // 没有更多的
            $return = array('status' => 0, 'msg' => util\Ibos::lang('Weibo is not new'));
        } else {
            $return = array('status' => 1, 'msg' => util\Ibos::lang('Weibo success load'));
            $return['data'] = $content['html'];
            $return['loadId'] = $content['lastId'];
            $return['firstId'] = (empty($data['page']) && empty($data['loadId'])) ? $content['firstId'] : 0;
            $return['pageData'] = $content['pageData'];
        }
        $this->ajaxReturn($return);
    }

    /**
     * 显示最新微博
     * @return  array 最新微博信息、状态和提示
     */
    public function actionLoadNew()
    {
        $return = array('status' => -1, 'msg' => '');
        $_REQUEST['maxId'] = intval($_REQUEST['maxId']);
        if (empty($_REQUEST['maxId'])) {
            $this->ajaxReturn($return);
        }
        $content = $this->getData($_REQUEST);
        if (empty($content['html'])) { //没有最新的
            $return = array('status' => 0, 'msg' => util\Ibos::lang('Weibo is not new'));
        } else {
            $return = array('status' => 1, 'msg' => util\Ibos::lang('Weibo success load'));
            $return['html'] = $content['html'];
            $return['maxId'] = intval($content['firstId']);
            $return['count'] = intval($content['count']);
        }
        $this->ajaxReturn($return);
    }

    /**
     * 获取微博数据
     * @param array $var
     * @return array
     */
    protected function getData($var)
    {
        $data = array();
        $type = isset($var['new']) ? 'new' . $var['type'] : $var['type']; // 最新的微博与默认微博类型一一对应
        switch ($type) {
            case 'following':
                // 设定可查看的关注微博总数，可以提高大数据量下的查询效率
                $pages = util\Page::create(1000, WbCore\WbConst::DEF_LIST_FEED_NUMS);
                if (!empty($var['feedkey'])) {
                    $loadId = isset($var['loadId']) ? $var['loadId'] : 0;
                    $list = Feed::model()->searchFeed($var['feedkey'], 'following', $loadId, $var['nums'], $pages->getOffset());
                    $count = Feed::model()->countSearchFollowing($var['feedkey'], $loadId);
                } else {
                    $where = 'a.isdel = 0 AND ' . WbfeedUtil::getViewCondition($this->uid, 'a.');
                    if (isset($var['loadId']) && $var['loadId'] > 0) { //非第一次
                        $where .= " AND a.feedid < '" . intval($var['loadId']) . "'";
                    }
                    if (!empty($var['feedtype']) && $var['feedtype'] !== 'all') {
                        $where .= " AND a.type = '" . $var['feedtype'] . "'";
                    }
                    $list = Feed::model()->getFollowingFeed($where, $var['nums'], $pages->getOffset());
                    $count = Feed::model()->countFollowingFeed($where);
                }
                break;
            case 'all':
                $pages = util\Page::create(WbCore\WbConst::MAX_VIEW_FEED_NUMS, WbCore\WbConst::DEF_LIST_FEED_NUMS);
                if (!empty($var['feedkey'])) {
                    $loadId = isset($var['loadId']) ? $var['loadId'] : 0;
                    $list = Feed::model()->searchFeed($var['feedkey'], 'all', $loadId, $var['nums'], $pages->getOffset());
                    $count = Feed::model()->countSearchAll($var['feedkey'], $loadId);
                } else {
                    $where = 'isdel = 0 AND ' . WbfeedUtil::getViewCondition($this->uid);
                    if (isset($var['loadId']) && $var['loadId'] > 0) { //非第一次
                        $where .= " AND feedid < '" . intval($var['loadId']) . "'";
                    }
                    // 动态类型
                    if (!empty($var['feedtype']) && $var['feedtype'] !== 'all') {
                        $where .= " AND type = '" . util\StringUtil::filterCleanHtml($var['feedtype']) . "'";
                    }
                    $list = Feed::model()->getList($where, $var['nums'], $pages->getOffset());
                    $count = Feed::model()->count($where);
                }
                break;
            case 'movement':
                $pages = util\Page::create(WbCore\WbConst::MAX_VIEW_FEED_NUMS, WbCore\WbConst::DEF_LIST_FEED_NUMS);
                if (!empty($var['feedkey'])) {
                    $loadId = isset($var['loadId']) ? $var['loadId'] : 0;
                    $list = Feed::model()->searchFeed($var['feedkey'], 'movement', $loadId, $var['nums'], $pages->getOffset());
                    $count = Feed::model()->countSearchMovement($var['feedkey'], $loadId);
                } else {
                    $where = 'isdel = 0 AND ' . WbfeedUtil::getViewCondition($this->uid);
                    if (isset($var['loadId']) && $var['loadId'] > 0) { //非第一次
                        $where .= " AND feedid < '" . intval($var['loadId']) . "'";
                    }
                    // 动态类型
                    if (!empty($var['feedtype']) && $var['feedtype'] !== 'all') {
                        $where .= " AND module = '" . util\StringUtil::filterCleanHtml($var['feedtype']) . "'";
                    } else {
                        $where .= " AND module != 'weibo'";
                    }
                    $list = Feed::model()->getList($where, $var['nums'], $pages->getOffset());
                    $count = Feed::model()->count($where);
                }
                break;
            case 'newmovement':
                if ($var['maxId'] > 0) {
                    $where = sprintf('isdel = 0 AND %s AND feedid > %d', WbfeedUtil::getViewCondition($this->uid), intval($var['maxId']), $this->uid);
                    $list = Feed::model()->getList($where);
                    $count = Feed::model()->count($where);
                    $data['count'] = count($list);
                }
                break;
            case 'newfollowing':// 关注的人的最新微博
                $where = 'a.isdel = 0 AND ' . WbfeedUtil::getViewCondition($this->uid, 'a.');
                if ($var['maxId'] > 0) {
                    $where .= " AND a.feedid > '" . intval($var['maxId']) . "'";
                    $list = Feed::model()->getFollowingFeed($where);
                    $count = Feed::model()->countFollowingFeed($where);
                    $data['count'] = count($list);
                }
                break;
            case 'newall': // 所有人最新微博 -- 正在发生的
                if ($var['maxId'] > 0) {
                    $where = sprintf('isdel = 0 AND %s AND feedid > %d AND uid <> %d', WbfeedUtil::getViewCondition($this->uid), intval($var['maxId']), $this->uid);
                    $list = Feed::model()->getList($where);
                    $count = Feed::model()->count($where);
                    $data['count'] = count($list);
                }
                break;

            default:
                break;
        }
        $count = isset($count) ? $count : WbCore\WbConst::MAX_VIEW_FEED_NUMS;
        $pages = util\Page::create($count, WbCore\WbConst::DEF_LIST_FEED_NUMS);
        if (!isset($var['new'])) {
            $pages->route = 'home/index';
            // 替换url
            $currentUrl = (string) util\Ibos::app()->getRequest()->getUrl();
            $replaceUrl = str_replace('weibo/home/loadmore', 'weibo/home/index', $currentUrl);
            $data['pageData'] = $this->widget('application\core\widgets\Page', array('pages' => $pages, 'currentUrl' => $replaceUrl), true);
        }
        if (!empty($list)) {
            $data['firstId'] = $list[0]['feedid'];
            $data['lastId'] = $list[(count($list) - 1)]['feedid'];
            //赞功能
            $feedids = util\Convert::getSubByKey($list, 'feedid');
            $diggArr = FeedDigg::model()->checkIsDigg($feedids, $this->uid);
            foreach ($list as &$v) {
                // 这一步是赋值 来自XXX，手机端可根据具体哪种设备来赋值
                // 未来如果动态是来自于不同的模块，该信息也在这里处理
                // 默认是来自网页，即在微博主页发的
                switch ($v['module']) {
                    case 'mobile':

                        break;
                    default:
                        $v['from'] = util\Env::getFromClient($v['from'], $v['module']);
                        break;
                }
            }
            $data['html'] = $this->renderPartial('application.modules.message.views.feed.feedlist', array('list' => $list, 'diggArr' => $diggArr), true);
        } else {
            $data['html'] = '';
            $data['firstId'] = $data['lastId'] = 0;
        }
        return $data;
    }

}
