<?php

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\message\model\Comment;
use application\modules\message\model\Feed;
use application\modules\message\model\FeedDigg;
use application\modules\mobile\utils\Mobile;
use application\modules\user\utils\User as UserUtil;
use application\modules\weibo\core\WbConst;
use application\modules\weibo\model\Follow;
use application\modules\weibo\utils\Feed as WbfeedUtil;

class WeiboController extends BaseController
{

    /**
     * 微博首页
     */
    public function actionIndex()
    {
        $var['type'] = isset($_GET['type']) ? StringUtil::filterCleanHtml($_GET['type']) : 'all';
        $var['feedtype'] = isset($_GET['feedtype']) ? StringUtil::filterCleanHtml($_GET['feedtype']) : 'all';
        $var['feedkey'] = isset($_GET['feedkey']) ? StringUtil::filterCleanHtml(urldecode($_GET['feedkey'])) : '';
        $var['loadNew'] = isset($_GET['page']) ? 0 : 1;
        $var['loadMore'] = isset($_GET['page']) ? 0 : 1;
        $var['loadId'] = isset($_GET['loadid']) ? $_GET['loadid'] : 0;
        $var['nums'] = isset($_GET['page']) ? WbConst::DEF_LIST_FEED_NUMS : 10;
        $var['uid'] = isset($_GET['uid']) ? $_GET['uid'] : "";
        $data = $this->getData($var);
        $var['loadId'] = isset($data['lastId']) ? $data['lastId'] : 0;
        $this->ajaxReturn(array_merge($var, $data), Mobile::dataType());
    }

    /**
     * 发布微博操作，用于AJAX
     * @return json 发布微博后的结果信息JSON数据
     */
    public function actionAdd()
    {

        // 返回数据格式
        $return = array('isSuccess' => true, 'data' => '');
        // 用户发送内容
        $d['content'] = isset($_GET['content']) ? StringUtil::filterDangerTag($_GET['content']) : '';
        // 原始数据内容
        $d['body'] = Env::getRequest('body');
        $d['rowid'] = isset($_GET['rowid']) ? intval($_GET['rowid']) : 0;
        $d['from'] = Env::getRequest('from');
        // 安全过滤
        foreach ($_GET as $key => $val) {
            $_GET[$key] = StringUtil::filterCleanHtml($_GET[$key]);
        }
        // 可见 ,手机端不设可见范围
        if (isset($_GET['view'])) {
//			$_GET['view'] = $d['view'] = intval( $_GET['view'] );
//			if ( $_GET['view'] == WbConst::CUSTOM_VIEW_SCOPE ) {
//				$scope = StringUtil::getId( $_GET['viewid'], true );
//				if ( isset( $scope['u'] ) ) {
//					$d['userid'] = implode( ',', $scope['u'] );
//				}
//				if ( isset( $scope['d'] ) ) {
//					$d['deptid'] = implode( ',', $scope['d'] );
//				}
//				if ( isset( $scope['p'] ) ) {
//					$d['positionid'] = implode( ',', $scope['p'] );
//				}
//			}
        }
        // 应用分享到微博，原资源链接
        $d['source_url'] = isset($_GET['source_url']) ? urldecode($_GET['source_url']) : '';
        // 滤掉话题两端的空白
        $d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is", '#' . trim("\${1}") . '#', $d['body']);
        // 附件ID
        if (isset($_GET['attachid'])) {
            $d['attach_id'] = trim(StringUtil::filterCleanHtml($_GET['attachid']));
            if (!empty($d['attach_id'])) {
                $d['attach_id'] = explode(',', $d['attach_id']);
                array_map('intval', $d['attach_id']);
            }
        }
        // 发送动态的类型
        $type = StringUtil::filterCleanHtml(Env::getRequest('type'));
        $table = isset($_GET['table']) ? StringUtil::filterCleanHtml($_GET['table']) : 'feed';
        // 所属模块名称
        $module = isset($_GET['module']) ? StringUtil::filterCleanHtml($_GET['module']) : 'weibo';   // 当前动态产生所属的应用
        $data = Feed::model()->put(Ibos::app()->user->uid, $module, $type, $d, $d['rowid'], $table);
        if (!$data) {
            $return['isSuccess'] = false;
            $return['data'] = Feed::model()->getError('putFeed');
            $this->ajaxReturn($return);
        }
        UserUtil::updateCreditByAction('addweibo', Ibos::app()->user->uid);
        // 微博来源设置
        $data['from'] = Env::getFromClient($data['from'], $data['module']);
        //$lang = Ibos::getLangSources();
        $return['data'] = $data; // $this->renderPartial( 'feedlist', array( 'list' => array( $data ), 'lang' => $lang ), true );
        // 动态ID
        $return['feedid'] = $data['feedid'];
        // 添加话题
        //FeedTopic::model()->addTopic( html_entity_decode( $d['body'], ENT_QUOTES, 'UTF-8' ), $data['feedid'], $type );
        $this->ajaxReturn($return, Mobile::dataType());
    }

    public function actionShare()
    {
        // 判断资源是否删除
        if (empty($_GET['curid'])) {
            $map['feedid'] = Env::getRequest('sid');
        } else {
            $map['feedid'] = Env::getRequest('curid');
        }
        $map['isdel'] = 0;
        $isExist = Feed::model()->countByAttributes($map);
        if ($isExist == 0) {
            $return['isSuccess'] = false;
            $return['data'] = '内容已被删除，转发失败';
            $this->ajaxReturn($return);
        }

        // 进行分享操作
        $return = Feed::model()->shareFeed($_GET, 'share');
        if ($return['isSuccess']) {
            $module = $_GET['module'];
            // 添加积分
            if ($module == 'weibo') {
                UserUtil::updateCreditByAction('forwardweibo', Ibos::app()->user->uid);
                //微博被转发
                $suid = Ibos::app()->db->createCommand()
                    ->select('uid')
                    ->from('{{feed}}')
                    ->where(sprintf("feedid = %d AND isdel = 0", $map['feedid']))
                    ->queryScalar();
                $suid && UserUtil::updateCreditByAction('forwardedweibo', $suid);
            }
        }
        $this->ajaxReturn($return, Mobile::dataType());
    }

    /**
     * 微博详细页
     */
    public function actionFeed()
    {
        $feedid = intval(Env::getRequest('feedid'));
        $feedInfo = Feed::model()->get($feedid);
        if (!$feedInfo) {
            $this->error(Ibos::lang('Weibo not exists'));
        }
        if ($feedInfo ['isdel'] == '1') {
            $this->error(Ibos::lang('No relate weibo'));
            exit();
        }
        if ($feedInfo['from'] == '1') {
            $feedInfo['from'] = Env::getFromClient(6, $feedInfo ['module'], '3G版');
        } else {
            switch ($feedInfo ['module']) {
                case 'mobile' :
                    break;
                default :
                    $feedInfo['from'] = Env::getFromClient($feedInfo ['from'], $feedInfo ['module']);
                    break;
            }
        }
        // 微博图片
        if (isset($v["attach_id"])) {
            if (is_array($v["attach_id"])) {
                $attachid = $v["attach_id"];
            } else {
                $attachid = explode(",", $v["attach_id"]);
            }
            $_tmp = Attach::getAttachData($$attachid);
            $v["attach_url"] = File::getAttachUrl() . '/' . $_tmp[$$attachid[0]]["attachment"]; //?TODO:: 可能多个附件
        }
        // 赞功能
        $diggArr = FeedDigg::model()->checkIsDigg($feedid, Ibos::app()->user->uid);
        $data = array(
            'diggArr' => $diggArr,
            'fd' => $feedInfo,
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('user'),
            'moduleAssetUrl' => Ibos::app()->assetManager->getAssetsUrl('weibo'),
        );
        $this->ajaxReturn($data, 'JSONP');
    }

    /**
     * 获取评论列表
     */
    public function actionGetCommentList()
    {
        $module = StringUtil::filterCleanHtml($_REQUEST['module']);
        $table = StringUtil::filterCleanHtml($_REQUEST['table']);
        $rowid = intval($_REQUEST['feedid']);
        $moduleuid = intval($_REQUEST['moduleuid']);
        $properties = array(
            'module' => $module,
            'table' => $table,
            'attributes' => array(
                'rowid' => $rowid,
                'limit' => 10,
                'moduleuid' => $moduleuid
            )
        );
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this, 'application\modules\weibo\core\WeiboComment', $properties);
        $list = $widget->getCommentList();
        foreach ($list as &$v) {
            unset($v["user_info"]);
            unset($v["sourceInfo"]);
        }
        $this->ajaxReturn($list, Mobile::dataType());
    }

    /**
     * 赞
     */

    /**
     * 设置赞与被赞
     */
    public function actionDigg()
    {
        $uid = Ibos::app()->user->uid;
        $feedId = intval(Env::getRequest('feedId'));
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
                $feed = Feed::model()->get($feedId);
                $res['isSuccess'] = true;
                $res['count'] = intval($feed['diggcount']);
                $res['digg'] = 1;
            } else {
                $res['isSuccess'] = false;
                $res['msg'] = FeedDigg::model()->getError('addDigg');
            }
        }
        $this->ajaxReturn($res, Mobile::dataType());
    }

    /**
     * 赞过的人列表 (100个)
     */
    public function actionDiggList()
    {
        $feedId = intval(Env::getRequest('feedid'));
        $count = FeedDigg::model()->countByAttributes(array('feedid' => $feedId));
        $res = array();
        if ($count) {
            $result = FeedDigg::model()->fetchUserList($feedId, 100);
            $res['count'] = $count;
            $res['data'] = $result;
            $res['isSuccess'] = true;
            $this->ajaxReturn($res, Mobile::dataType());
        } else {
            $this->ajaxReturn(array('count' => 0, 'isSuccess' => true), Mobile::dataType());
        }
    }

    /**
     * 粉丝列表
     */
    public function actionFollower()
    {
        $uid = $_REQUEST["uid"];
        $count = Follow::model()->getFollowCount(array($uid));
        $list = $this->getFollowData('follower', $uid, 0, WbConst::DEF_LIST_FEED_NUMS);

        $data = array(
            'count' => $count[$uid],
            'list' => $list
        );
        $this->ajaxReturn($data, Mobile::dataType());
    }

    /**
     * 关注列表
     */
    public function actionFollowing()
    {
        $uid = $_REQUEST["uid"];
        $count = Follow::model()->getFollowCount(array($uid));
        $list = $this->getFollowData('following', $uid, 0, 100);
        $data = array(
            'count' => $count[$uid],
            'list' => $list
        );
        $this->ajaxReturn($data, Mobile::dataType());
    }

    /**
     * 获取关注数据
     * @param string $type 是关注还是被关注 (following or follower)
     * @param integer $offset 分页偏移量
     * @param integer $limit 每页条数
     * @return array 一个符合列表输出的数组数据
     */
    protected function getFollowData($type, $uid, $offset, $limit)
    {
        if ($type == 'follower') {
            $data = Follow::model()->getFollowerList($uid, $offset, $limit);
        } else {
            $data = Follow::model()->getFollowingList($uid, $offset, $limit);
        }
        if (!empty($data)) {
            $fids = Convert::getSubByKey($data, 'fid');
            $list = Follow::model()->getFollowStateByFids(Ibos::app()->user->uid, $fids);
        } else {
            $list = array();
        }
        return $list;
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
                $pages = Page::create(1000, WbConst::DEF_LIST_FEED_NUMS);
                if (!empty($var['feedkey'])) {
                    $list = Feed::model()->searchFeed($var['feedkey'], 'following', $var['loadId'], $var['nums'], $pages->getOffset());
                } else {
                    $where = 'a.isdel = 0 AND ' . WbfeedUtil::getViewCondition($this->uid, 'a.');
                    if (isset($var['loadId']) && $var['loadId'] > 0) { //非第一次
                        $where .= " AND a.feedid < '" . intval($var['loadId']) . "'";
                    }
                    if (!empty($var['feedtype']) && $var['feedtype'] !== 'all') {
                        $where .= " AND a.type = '" . $var['feedtype'] . "'";
                    }
                    $list = Feed::model()->getFollowingFeed($where, $var['nums'], $pages->getOffset());
                }
                break;
            case 'all':
                $where = 'isdel = 0  AND ' . WbfeedUtil::getViewCondition($this->uid);
                $where .= empty($var['uid']) ? "" : 'AND uid = ' . $var['uid'];
                $pages = Page::create(WbConst::MAX_VIEW_FEED_NUMS, WbConst::DEF_LIST_FEED_NUMS);
                if (!empty($var['feedkey'])) {
                    $loadId = isset($var['loadId']) ? $var['loadId'] : 0;
                    $list = Feed::model()->searchFeed($var['feedkey'], 'all', $loadId, $var['nums'], $pages->getOffset(), '', $var['uid']);
                } else {
                    if (isset($var['loadId']) && $var['loadId'] > 0) { //非第一次
                        $where .= " AND feedid < '" . intval($var['loadId']) . "'";
                    }
                    // 动态类型
                    if (!empty($var['feedtype']) && $var['feedtype'] !== 'all') {
                        $where .= " AND type = '" . StringUtil::filterCleanHtml($var['feedtype']) . "'";
                    }
                    $list = Feed::model()->getList($where, $var['nums'], $pages->getOffset());
                }
                break;
            case 'movement':
                $pages = Page::create(WbConst::MAX_VIEW_FEED_NUMS, WbConst::DEF_LIST_FEED_NUMS);
                if (!empty($var['feedkey'])) {
                    $list = Feed::model()->searchFeed($var['feedkey'], 'movement', $var['loadId'], $var['nums'], $pages->getOffset());
                } else {
                    $where = 'isdel = 0 AND ' . WbfeedUtil::getViewCondition($this->uid);
                    if (isset($var['loadId']) && $var['loadId'] > 0) { //非第一次
                        $where .= " AND feedid < '" . intval($var['loadId']) . "'";
                    }
                    // 动态类型
                    if (!empty($var['feedtype']) && $var['feedtype'] !== 'all') {
                        $where .= " AND module = '" . StringUtil::filterCleanHtml($var['feedtype']) . "'";
                    } else {
                        $where .= " AND module != 'weibo'";
                    }
                    $list = Feed::model()->getList($where, $var['nums'], $pages->getOffset());
                }
                break;
            case 'newmovement':
                if ($var['maxId'] > 0) {
                    $where = sprintf('isdel = 0 AND %s AND feedid > %d', WbfeedUtil::getViewCondition($this->uid), intval($var['maxId']), $this->uid);
                    $list = Feed::model()->getList($where);
                    $data['count'] = count($list);
                }
                break;
            case 'newfollowing':// 关注的人的最新微博
                $where = 'a.isdel = 0 AND ' . WbfeedUtil::getViewCondition($this->uid, 'a.');
                if ($var['maxId'] > 0) {
                    $where .= " AND a.feedid > '" . intval($var['maxId']) . "'";
                    $list = Feed::model()->getFollowingFeed($where);
                    $data['count'] = count($list);
                }
                break;
            case 'newall': // 所有人最新微博 -- 正在发生的
                if ($var['maxId'] > 0) {
                    $where = sprintf('isdel = 0 AND %s AND feedid > %d AND uid <> %d', WbfeedUtil::getViewCondition($this->uid), intval($var['maxId']), $this->uid);
                    $list = Feed::model()->getList($where);
                    $data['count'] = count($list);
                }
                break;

            default:
                break;
        }
        if (!isset($var['new'])) {
            $pages->route = 'home/index';
            //$data['pageData'] = $this->widget( 'IWPage', array( 'pages' => $pages ), true );
        }
        if (!empty($list)) {
            $data['firstId'] = $list[0]['feedid'];
            $data['lastId'] = $list[(count($list) - 1)]['feedid'];
            //赞功能
            $feedids = Convert::getSubByKey($list, 'feedid');
            $diggArr = FeedDigg::model()->checkIsDigg($feedids, $this->uid);
            foreach ($list as &$v) {
                // 这一步是赋值 来自XXX，手机端可根据具体哪种设备来赋值
                // 未来如果动态是来自于不同的模块，该信息也在这里处理
                // 默认是来自网页，即在微博主页发的
                switch ($v['module']) {
                    case 'mobile':

                        break;
                    default:
                        $v['from'] = Env::getFromClient($v['from'], $v['module']);
                        break;
                }
                if (isset($v["attach_id"])) {

                    if (is_array($v["attach_id"])) {
                        $attachid = $v["attach_id"];
                    } else {
                        $attachid = explode(",", $v["attach_id"]);
                    }
                    $_tmp = Attach::getAttachData($attachid);
                    $v["attach_url"] = File::getAttachUrl() . '/' . $_tmp[$attachid[0]]["attachment"]; //?TODO:: 可能多个附件
                }
                if (isset($v["api_source"]["attach"][0]["attach_url"])) {
                    $v["api_source"]["attach_url"] = $v["api_source"]["attach"][0]["attach_url"];
                    unset($v["api_source"]["attach"]);
                    unset($v["api_source"]["source_body"]);
                }

                unset($v["user_info"]);
                unset($v["body"]);
                unset($v["sourceInfo"]);
                unset($v["api_source"]["source_user_info"]);
                unset($v["api_source"]["avatar_big"]);
                unset($v["api_source"]["avatar_middle"]);
                unset($v["api_source"]["avatar_small"]);
                unset($v["api_source"]["source_url"]);
                unset($v["feeddata"]);
            }
            //$data['html'] = $this->renderPartial( 'application.modules.message.views.feed.feedlist', , true );
            $data['list'] = $list;
            $data['diggArr'] = $diggArr;
        } else {
            $data['list'] = array();
            $data['firstId'] = $data['lastId'] = 0;
        }
        return $data;
    }

    /**
     * 增加一条评论
     * @return type
     */
    public function actionAddComment()
    {
        // 返回结果集默认值
        $return = array('isSuccess' => false);
        // 获取接收数据
        $data = $_GET;
        // 安全过滤
        foreach ($data as $key => $val) {
            $data[$key] = StringUtil::filterCleanHtml($data[$key]);
        }
        $data['uid'] = Ibos::app()->user->uid;
        // 评论所属与评论内容
        $data['content'] = StringUtil::filterDangerTag($data['content']);
        // 判断资源是否被删除
        if ($data['table'] == 'feed') {
            $table = 'application\modules\message\model\Feed';
        } else {
            $table = 'application\modules\\' . $data['module'] . '\\model\\' . ucfirst($data['table']);
        }
        $pk = $table::model()->getTableSchema()->primaryKey;
        $sourceInfo = $table::model()->fetch(array('condition' => "`{$pk}` = {$data['rowid']}"));
        if (!$sourceInfo) {
            $return['isSuccess'] = false;
            $this->ajaxReturn($return, Mobile::dataType());
        }
        $data['ctime'] = TIMESTAMP;
        $data['cid'] = Comment::model()->addComment($data);
        if ($data['cid']) {
            $return['isSuccess'] = true;
        }
        $this->ajaxReturn($return, Mobile::dataType());
    }

}
