<?php

/**
 * 企业微博后台控制器
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author banyan <banyan@ibos.com.cn>
 */
/**
 * @package application.modules.weibo.controllers
 * @version $Id$
 * @author banyan <banyan@ibos.com.cn>
 */

namespace application\modules\weibo\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;
use application\modules\message\model\Comment;
use application\modules\message\model\Feed;
use application\modules\weibo\utils\Common as WbCommonUtil;

class DashboardController extends BaseController
{

    /**
     * 微博设置
     * @return void
     */
    public function actionSetup()
    {
        if (Env::submitCheck('formhash')) {
            // 发布频率必须至少为5秒
            $_POST['wbpostfrequency'] = intval($_POST['wbpostfrequency']) > 5 ? $_POST['wbpostfrequency'] : 5;
            // 微博字数至少为140字
            $_POST['wbnums'] = intval($_POST['wbnums']) >= 140 ? $_POST['wbnums'] : 140;
            $wbwatermark = isset($_POST['wbwatermark']) ? 1 : 0;
            $wbwcenabled = isset($_POST['wbwcenabled']) ? 1 : 0;
            $postType = array(
                'image' => 0,
                'topic' => 0,
                'praise' => 0,
            );
            if (isset($_POST['wbposttype'])) {
                foreach ($postType as $key => &$val) {
                    if (isset($_POST['wbposttype'][$key])) {
                        $val = 1;
                    }
                }
            }
            if (isset($_POST['wbmovements'])) {
                // do nothing
            } else {
                $_POST['wbmovements'] = array();
            }
            $data = array(
                'wbnums' => $_POST['wbnums'],
                'wbpostfrequency' => $_POST['wbpostfrequency'],
                'wbposttype' => $postType,
                'wbwatermark' => $wbwatermark,
                'wbwcenabled' => $wbwcenabled,
                'wbmovement' => $_POST['wbmovements']
            );
            foreach ($data as $key => $value) {
                Setting::model()->updateSettingValueByKey($key, $value);
            }
            Cache::update('setting');
            $this->success(Ibos::lang('Operation succeed', 'message'));
        } else {
            $data = array(
                'config' => WbCommonUtil::getSetting(),
                'movementModule' => WbCommonUtil::getMovementModules(),
                'moduleAssetUrl' => Ibos::app()->assetManager->getAssetsUrl('weibo'),
            );
            $this->render('setup', $data);
        }
    }

    /**
     * 微博管理
     */
    public function actionManage()
    {
        $op = Env::getRequest('op');
        if (Env::submitCheck('formhash')) {
            if (!in_array($op, array('delFeed', 'deleteFeed', 'feedRecover'))) {
                exit();
            }
            $ids = Env::getRequest('ids');
            foreach (explode(',', $ids) as $id) {
                Feed::model()->doEditFeed($id, $op);
            }
            $this->ajaxReturn(array('isSuccess' => true));
        } else {
            if (!in_array($op, array('list', 'recycle'))) {
                $op = 'list';
            }
            $map = '';
            if ($op == 'list') {
                $map = 'isdel = 0';
            } else {
                $map = 'isdel = 1';
            }
            if (Env::getRequest('search')) {
                $key = StringUtil::filterCleanHtml(Env::getRequest('search'));
                $count = Feed::model()->countSearchFeeds($key);
                $inSearch = true;
            } else {
                $count = Feed::model()->count($map);
                $inSearch = false;
            }
            $pages = Page::create($count);
            if ($inSearch) {
                $list = Feed::model()->searchFeeds($key, null, $pages->getLimit(), $pages->getOffset());
            } else {
                $list = Feed::model()->getList($map, $pages->getLimit(), $pages->getOffset());
            }
            $data = array(
                'op' => $op,
                'list' => $list,
                'pages' => $pages,
                'moduleAssetUrl' => Ibos::app()->assetManager->getAssetsUrl('weibo'),
            );
            $this->render('manage', $data);
        }
    }

    /**
     * 微博评论管理
     */
    public function actionComment()
    {
        $op = Env::getRequest('op');
        if (Env::submitCheck('formhash')) {
            if (!in_array($op, array('delComment', 'deleteComment', 'commentRecover'))) {
                exit();
            }
            $ids = Env::getRequest('ids');
            foreach (explode(',', $ids) as $id) {
                Comment::model()->doEditComment($id, $op);
            }
            $this->ajaxReturn(array('isSuccess' => true));
        } else {
            if (!in_array($op, array('list', 'recycle'))) {
                $op = 'list';
            }
            $map = '';
            if ($op == 'list') {
                $map = 'isdel = 0';
            } else {
                $map = 'isdel = 1';
            }
            if (Env::getRequest('search')) {
                $key = StringUtil::filterCleanHtml(Env::getRequest('search'));
                $map .= " AND content LIKE '%{$key}%'";
            }
            $count = Comment::model()->count($map);
            $pages = Page::create($count);
            $list = Comment::model()->getCommentList($map, 'cid DESC', $pages->getLimit(), $pages->getOffset());
            $data = array(
                'op' => $op,
                'list' => $list,
                'pages' => $pages,
                'moduleAssetUrl' => Ibos::app()->assetManager->getAssetsUrl('weibo'),
            );
            $this->render('comment', $data);
        }
    }

}
