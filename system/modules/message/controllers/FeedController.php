<?php

namespace application\modules\message\controllers;

use application\core\utils as util;
use application\modules\department\model\Department;
use application\modules\message\model\Feed;
use application\modules\message\model\FeedDigg;
use application\modules\message\utils\Expression as ExpressionUtil;
use application\modules\position\model\Position;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\core as WbCore;
use application\modules\weibo\model\FeedTopic;
use application\modules\weibo\model\Follow;

class FeedController extends BaseController
{

    /**
     * 发布微博操作，用于AJAX
     * @return json 发布微博后的结果信息JSON数据
     */
    public function actionPostFeed()
    {
        if (util\Env::submitCheck('formhash')) {
            // 返回数据格式
            $return = array('isSuccess' => true, 'data' => '');
            // 用户发送内容
            $d['content'] = isset($_POST['content']) ? util\StringUtil::filterDangerTag($_POST['content']) : '';
            // 原始数据内容
            $d['body'] = $_POST['body'];
            $d['rowid'] = isset($_POST['rowid']) ? intval($_POST['rowid']) : 0;
            // 安全过滤
            foreach ($_POST as $key => $val) {
                $_POST[$key] = util\StringUtil::filterCleanHtml($_POST[$key]);
            }
            $uid = util\Ibos::app()->user->uid;
            $user = User::model()->fetchByUid($uid);
            // 可见
            if (isset($_POST['view'])) {
                $_POST['view'] = $d['view'] = intval($_POST['view']);
                if ($_POST['view'] == WbCore\WbConst::SELFDEPT_VIEW_SCOPE) {
                    $d['deptid'] = $user['deptid'];
                }
                if ($_POST['view'] == WbCore\WbConst::CUSTOM_VIEW_SCOPE) {
                    $scope = util\StringUtil::getId($_POST['viewid'], true);
                    if (isset($scope['u'])) {
                        $d['userid'] = implode(',', $scope['u']);
                    }
                    if (isset($scope['d'])) {
                        $d['deptid'] = implode(',', $scope['d']);
                    }
                    if (isset($scope['p'])) {
                        $d['positionid'] = implode(',', $scope['p']);
                    }
                    if (isset($scope['r'])) {
                        $d['roleid'] = implode(',', $scope['r']);
                    }
                }
            }
            // 应用分享到微博，原资源链接
            $d['source_url'] = isset($_POST['source_url']) ? urldecode($_POST['source_url']) : '';
            // 滤掉话题两端的空白
            $d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is", '#' . trim("\${1}") . '#', $d['body']);
            // 附件ID
            if (isset($_POST['attachid'])) {
                $d['attach_id'] = trim(util\StringUtil::filterCleanHtml($_POST['attachid']));
                if (!empty($d['attach_id'])) {
                    $d['attach_id'] = explode(',', $d['attach_id']);
                    array_map('intval', $d['attach_id']);
                }
            }
            // 发送动态的类型
            $type = util\StringUtil::filterCleanHtml($_POST['type']);
            $table = isset($_POST['table']) ? util\StringUtil::filterCleanHtml($_POST['table']) : 'feed';
            // 所属模块名称
            $module = isset($_POST['module']) ? util\StringUtil::filterCleanHtml($_POST['module']) : 'weibo';   // 当前动态产生所属的应用
            $data = Feed::model()->put(util\Ibos::app()->user->uid, $module, $type, $d, $d['rowid'], $table);
            if (!$data) {
                $return['isSuccess'] = false;
                $return['data'] = Feed::model()->getError('putFeed');
                $this->ajaxReturn($return);
            }
            if (!empty($d['attach_id'])) {
                util\Attach::updateAttach($d['attach_id']);
            }
            UserUtil::updateCreditByAction('addweibo', util\Ibos::app()->user->uid);
            // 微博来源设置
            $data['from'] = util\Env::getFromClient($data['from'], $data['module']);
            $lang = util\Ibos::getLangSources();
            $return['data'] = $this->renderPartial('feedlist', array('list' => array($data), 'lang' => $lang), true);
            // 动态ID
            $return['feedid'] = $data['feedid'];
            // 添加话题
            FeedTopic::model()->addTopic(html_entity_decode($d['body'], ENT_QUOTES, 'UTF-8'), $data['feedid'], $type);
            $this->ajaxReturn($return);
        }
    }

    /**
     * 赞过的人全部列表
     */
    public function actionAllDiggList()
    {
        $feedId = intval(util\Env::getRequest('feedid'));
        $result = FeedDigg::model()->fetchUserList($feedId, 5);
        $uids = util\Convert::getSubByKey($result, 'uid');
        $followStates = Follow::model()->getFollowStateByFids(util\Ibos::app()->user->uid, $uids);
        $this->renderPartial('alldigglist', array('list' => $result, 'followstates' => $followStates, 'feedid' => $feedId));
    }

    /**
     * 赞过的人简要列表 (4个)
     */
    public function actionSimpleDiggList()
    {
        $feedId = intval(util\Env::getRequest('feedid'));
        $count = FeedDigg::model()->countByAttributes(array('feedid' => $feedId));
        $res = array();
        if ($count) {
            $result = FeedDigg::model()->fetchUserList($feedId, 4);
            $res['count'] = $count;
            $res['data'] = $this->renderPartial('digglist', array('result' => $result, 'count' => $count, 'feedid' => $feedId), true);
            $res['isSuccess'] = true;
        } else {
            $res['isSuccess'] = false;
        }
        $this->ajaxReturn($res);
    }

    /**
     * 设置赞与被赞
     */
    public function actionSetDigg()
    {
        $uid = util\Ibos::app()->user->uid;
        $feedId = intval(util\Env::getRequest('feedid'));
        // 是否已赞
        $alreadyDigg = FeedDigg::model()->getIsExists($feedId, $uid);
        if ($alreadyDigg) {
            // 已赞就取消赞
            $result = FeedDigg::model()->delDigg($feedId, $uid);
            if ($result) {
                $feed = Feed::model()->get($feedId);
                $res['isSuccess'] = true;
                $res['count'] = intval($feed['diggcount']);
                $res['digg'] = 0;
            } else {
                $res['isSuccess'] = false;
                $res['msg'] = FeedDigg::model()->getError('delDigg');
            }
        } else {
            // 否则就加一个赞
            $result = FeedDigg::model()->addDigg($feedId, $uid);
            if ($result) {
                $user = User::model()->fetchByUid($uid);
                $feed = Feed::model()->get($feedId);
                $res['isSuccess'] = true;
                $res['count'] = intval($feed['diggcount']);
                $res['data'] = $this->renderPartial('digg', array('user' => $user), true);
                $res['digg'] = 1;
            } else {
                $res['isSuccess'] = false;
                $res['msg'] = FeedDigg::model()->getError('addDigg');
            }
        }
        $this->ajaxReturn($res);
    }

    /**
     * 前台删除一条动态/微博
     */
    public function actionRemoveFeed()
    {
        if (util\Env::submitCheck('formhash')) {
            // 删除失败
            $return = array('isSuccess' => false, 'data' => util\Ibos::lang('Del failed', 'message'));
            $feedId = intval($_POST['feedid']);
            $feed = Feed::model()->getFeedInfo($feedId);
            // 不存在时
            if (!$feed) {
                $this->ajaxReturn($return);
            }
            // 非作者时
            if ($feed['uid'] != util\Ibos::app()->user->uid) {
                // 没有管理权限不可以删除
                if (!util\Ibos::app()->user->isadministrator) {
                    $this->ajaxReturn($return);
                }
            }
            // 执行删除操作
            $return = Feed::model()->doEditFeed($feedId, 'delFeed', util\Ibos::app()->user->uid);
            // 删除失败或删除成功的消息
            $return['msg'] = ($return['isSuccess']) ? util\Ibos::lang('Del succeed', 'message') : util\Ibos::lang('Del failed', 'message');
            $this->ajaxReturn($return);
        }
    }

    /**
     * 转发微博操作，需要传入POST的值
     * @return json 分享/转发微博后的结果信息JSON数据
     */
    public function actionShareFeed()
    {
        if (util\Env::submitCheck('formhash')) {
            // 获取传入的值
            $post = $_POST;
            // 安全过滤
            foreach ($post as $key => $val) {
                $post[$key] = util\StringUtil::purify($val);
            }
            // 判断资源是否删除
            if (empty($post['curid'])) {
                $map['feedid'] = $post['sid'];
            } else {
                $map['feedid'] = $post['curid'];
            }
            $map['isdel'] = 0;
            $isExist = Feed::model()->countByAttributes($map);
            if ($isExist == 0) {
                $return['isSuccess'] = false;
                $return['data'] = '内容已被删除，转发失败';
                $this->ajaxReturn($return);
            }

            // 进行分享操作
            $return = Feed::model()->shareFeed($post, 'share');
            if ($return['isSuccess']) {
                $module = $post['module'];
                // 添加积分
                if ($module == 'weibo') {
                    UserUtil::updateCreditByAction('forwardweibo', util\Ibos::app()->user->uid);
                    //微博被转发
                    $suid = util\Ibos::app()->db->createCommand()
                        ->select('uid')
                        ->from('{{feed}}')
                        ->where(sprintf("feedid = %d AND isdel = 0", $map['feedid']))
                        ->queryScalar();
                    $suid && UserUtil::updateCreditByAction('forwardedweibo', $suid);
                }
                $lang = util\Ibos::getLangSources();
                $return['data'] = $this->renderPartial('feedlist', array('list' => array($return['data']), 'lang' => $lang), true);
            }
            $this->ajaxReturn($return);
        }
    }

    /**
     * 允许查看的人员列表
     */
    public function actionAllowedlist()
    {
        $feedId = intval(util\Env::getRequest('feedid'));
        $feed = Feed::model()->getFeedInfo($feedId);
        // 不存在时
        if (!$feed) {
            exit('该条动态不存在');
        }
        $list = array();
        // 仅自己可见
        if ($feed['view'] == '1') {
            $list['users'] = util\Ibos::lang('My self');
        } else if (!empty($feed['userid'])) {
            $list['users'] = User::model()->fetchRealnamesByUids($feed['userid']);
        }
        if (!empty($feed['deptid'])) {
            if ($feed['deptid'] == 'alldept' || $feed['view'] == '0') {
                $list['dept'] = util\Ibos::lang('All dept');
            } else {
                // 仅自己部门可见，取出自己的部门ID
                if ($feed['view'] == '2') {
                    $alldowndeptid = Department::model()->fetchChildIdByDeptids(util\Ibos::app()->user->alldeptid);
                    $deptIds = util\StringUtil::filterStr(util\Ibos::app()->user->alldeptid . ',' . $alldowndeptid);
                } else {
                    $deptIds = $feed['deptid'];
                }
                if (!empty($deptIds)) {
                    $list['dept'] = Department::model()->fetchDeptNameByDeptId($deptIds);
                } else {
                    $list['dept'] = '';
                }
            }
        }
        if (!empty($feed['positionid'])) {
            $list['pos'] = Position::model()->fetchPosNameByPosId($feed['positionid']);
        }
        $this->renderPartial('allowedlist', $list);
    }

    /**
     * 获取表情数据
     */
    public function actionGetexp()
    {
        $this->ajaxReturn(array('data' => ExpressionUtil::getAllExpression()));
    }

}
