<?php

namespace application\modules\weibo\controllers;

use application\core\utils as util;
use application\core\utils\String;
use application\modules\message\model\Feed;
use application\modules\message\model\FeedDigg;
use application\modules\message\model\UserData;
use application\modules\user\controllers\HomeBaseController;
use application\modules\user\model\User;
use application\modules\weibo\core as WbCore;
use application\modules\weibo\model\Follow;
use application\modules\weibo\utils\Common as WbCommonUtil;
use application\modules\weibo\utils\Feed as WbfeedUtil;

class PersonalController extends HomeBaseController {

    /**
     * 微博个人页
     */
    public function actionIndex() {
        $data = array(
            'movements' => util\IBOS::app()->setting->get( 'setting/wbmovement' ),
            'colleagues' => $this->getRelation( 'colleague' ),
            'assetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'user' ),
            'moduleAssetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'weibo' )
        );
        // 如果查看的不是自己，显示共同关注 与 我关注的人也关注TA 两个选项
        if ( !$this->getIsMe() ) {
            $data['bothfollow'] = $this->getRelation( 'bothfollow' );
            $data['secondfollow'] = $this->getRelation( 'secondfollow' );
        }
        // 模块动态列表
        $var['movements'] = util\IBOS::app()->setting->get( 'setting/wbmovement' );
        // 可用的动态模块列表
        $var['enableMovementModule'] = WbCommonUtil::getMovementModules();
        $var['type'] = isset( $_GET['type'] ) ? util\String::filterCleanHtml( $_GET['type'] ) : 'all';
        $var['feedtype'] = isset( $_GET['feedtype'] ) ? util\String::filterCleanHtml( $_GET['feedtype'] ) : 'all';
        $var['feedkey'] = isset( $_GET['feedkey'] ) ? util\String::filterCleanHtml( urldecode( $_GET['feedkey'] ) ) : '';
        $var['loadNew'] = isset( $_GET['page'] ) ? 0 : 1;
        $var['loadMore'] = isset( $_GET['page'] ) ? 0 : 1;
        $var['loadId'] = 0;
        $var['nums'] = isset( $_GET['page'] ) ? WbCore\WbCore\WbConst::DEF_LIST_FEED_NUMS : 10;
        $user = $this->getUser();
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => util\IBOS::lang( 'Enterprise weibo' ), 'url' => $this->createUrl( 'home/index' ) ),
            array( 'name' => $user['realname'] . util\IBOS::lang( 'sbs feed' ), 'url' => $this->createUrl( 'personal/index', array( 'uid' => $this->getUid() ) ) ),
            array( 'name' => util\IBOS::lang( 'List' ) )
        ) );
        $this->render( 'index', array_merge( $data, $var, $this->getData( $var ) ), false, array( 'user.default' ) );
    }

    /**
     * TODO::loadmore与下面的loadnew方法皆从WeiboHomeController复制而来，写的时候暂时没有什么好的
     * 分离方法，后期重构时要把这些改过来。 @banyan
     */
    public function actionLoadMore() {
        // 获取GET与POST数据
        $data = $_GET + $_POST;
        // 查询是否有分页
        if ( !empty( $data['page'] ) || intval( $data['loadcount'] ) == 2 ) {
            unset( $data['loadId'] );
            $data['nums'] = WbCore\WbCore\WbConst::DEF_LIST_FEED_NUMS;
        } else {
            $return = array( 'status' => -1, 'msg' => util\IBOS::lang( 'Loading ID isnull' ) );
            $data['loadId'] = intval( $data['loadId'] );
            $data['nums'] = 5;
        }
        $content = $this->getData( $data );
        // 查看是否有更多数据
        if ( empty( $content['html'] ) || (empty( $data['loadId'] ) && intval( $data['loadcount'] ) != 2) ) {
            // 没有更多的
            $return = array( 'status' => 0, 'msg' => util\IBOS::lang( 'Weibo is not new' ) );
        } else {
            $return = array( 'status' => 1, 'msg' => util\IBOS::lang( 'Weibo success load' ) );
            $return['data'] = $content['html'];
            $return['loadId'] = $content['lastId'];
            $return['firstId'] = ( empty( $data['page'] ) && empty( $data['loadId'] ) ) ? $content['firstId'] : 0;
            $return['pageData'] = $content['pageData'];
        }
        $this->ajaxReturn( $return );
    }

    /**
     * 显示最新微博
     * @return  array 最新微博信息、状态和提示
     */
    public function actionLoadNew() {
        $return = array( 'status' => -1, 'msg' => '' );
        $_REQUEST['maxId'] = intval( $_REQUEST['maxId'] );
        if ( empty( $_REQUEST['maxId'] ) ) {
            $this->ajaxReturn( $return );
        }
        $content = $this->getData( $_REQUEST );
        if ( empty( $content['html'] ) ) { //没有最新的
            $return = array( 'status' => 0, 'msg' => util\IBOS::lang( 'Weibo is not new' ) );
        } else {
            $return = array( 'status' => 1, 'msg' => util\IBOS::lang( 'Weibo success load' ) );
            $return['html'] = $content['html'];
            $return['maxId'] = intval( $content['firstId'] );
            $return['count'] = intval( $content['count'] );
        }
        $this->ajaxReturn( $return );
    }

    /**
     * ajax加载关注与被关注
     */
    public function actionLoadFollow() {
        $type = util\Env::getRequest( 'type' );
        $offset = intval( util\Env::getRequest( 'offset' ) );
        $count = Follow::model()->getFollowCount( array( $this->getUid() ) );
        $list = $this->getFollowData( $type, $offset, WbCore\WbCore\WbConst::DEF_LIST_FEED_NUMS );
        $res = array(
            'isSuccess' => true,
            'data' => $this->renderPartial( 'followlist', array( 'list' => $list ) ),
            'offset' => $offset + WbCore\WbCore\WbConst::DEF_LIST_FEED_NUMS,
            'more' => !!($count[$this->getUid()][$type] - $offset > 0) // 是否有更多
        );
        $this->ajaxReturn( $res );
    }

    /**
     * ajax获取人际关系
     */
    public function actionGetRelation() {
        $type = util\Env::getRequest( 'type' );
        $offset = util\Env::getRequest( 'offset' );
        $data = $this->getRelation( $type, $offset );
        $res = array(
            'isSuccess' => true,
            'data' => $this->renderPartial( 'relation', array( 'list' => $data['list'] ), true )
        );
        if ( !empty( $data['count'] ) ) {
            $res['offset'] = intval( $offset ) + 4;
        }
        $this->ajaxReturn( $res );
    }

    /**
     * 微博详细页
     */
    public function actionFeed() {
        $feedid = intval( util\Env::getRequest( 'feedid' ) );
        $feedInfo = Feed::model()->get( $feedid );
        if ( !$feedInfo ) {
            $this->error( util\IBOS::lang( 'Weibo not exists' ) );
        }
        if ( $feedInfo ['isdel'] == '1' ) {
            $this->error( util\IBOS::lang( 'No relate weibo' ) );
            exit();
        }
        if ( $feedInfo['from'] == '1' ) {
            $feedInfo['from'] = util\Env::getFromClient( 6, $feedInfo ['module'], '3G版' );
        } else {
            switch ( $feedInfo ['module'] ) {
                case 'mobile' :
                    break;
                default :
                    $feedInfo['from'] = util\Env::getFromClient( $feedInfo ['from'], $feedInfo ['module'] );
                    break;
            }
        }
        // 微博图片
        if ( $feedInfo['type'] === 'postimage' ) {
            $var = String::utf8Unserialize( $feedInfo['feeddata'] );
            $feedInfo['image_body'] = $var['body'];
            if ( !empty( $var['attach_id'] ) ) {
                $attach = util\Attach::getAttachData( $var['attach_id'] );
                $attachUrl = util\File::getAttachUrl();
                foreach ( $attach as $ak => $av ) {
                    $_attach = array(
                        'attach_id' => $av['aid'],
                        'attach_name' => $av['filename'],
                        'attach_url' => util\File::fileName( $attachUrl . '/' . $av['attachment'] ),
                        'extension' => util\String::getFileExt( $av['filename'] ),
                        'size' => $av['filesize']
                    );
                    $_attach['attach_small'] = WbCommonUtil::getThumbImageUrl( $av, WbCore\WbConst::ALBUM_DISPLAY_WIDTH, WbCore\WbConst::ALBUM_DISPLAY_HEIGHT );
                    $_attach['attach_middle'] = WbCommonUtil::getThumbImageUrl( $av, WbCore\WbConst::WEIBO_DISPLAY_WIDTH, WbCore\WbConst::WEIBO_DISPLAY_HEIGHT );
                    $feedInfo['attachInfo'][$ak] = $_attach;
                }
            }
        }
        // 赞功能
        $diggArr = FeedDigg::model()->checkIsDigg( $feedid, util\IBOS::app()->user->uid );
        $data = array(
            'diggArr' => $diggArr,
            'fd' => $feedInfo,
            'assetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'user' ),
            'moduleAssetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'weibo' ),
            'colleagues' => $this->getRelation( 'colleague' ),
        );
        // 如果查看的不是自己，显示共同关注 与 我关注的人也关注TA 两个选项
        if ( !$this->getIsMe() ) {
            $data['bothfollow'] = $this->getRelation( 'bothfollow' );
            $data['secondfollow'] = $this->getRelation( 'secondfollow' );
        }
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => util\IBOS::lang( 'Enterprise weibo' ), 'url' => $this->createUrl( 'home/index' ) ),
            array( 'name' => $feedInfo['user_info']['realname'] . util\IBOS::lang( 'sbs feed' ), 'url' => $this->createUrl( 'personal/index', array( 'uid' => $this->getUid() ) ) ),
            array( 'name' => util\IBOS::lang( 'Detail' ) )
        ) );
        $this->render( 'detail', $data, false, array( 'user.default' ) );
    }

    /**
     * 粉丝列表
     */
    public function actionFollower() {
        $user = $this->getUser();
        $count = Follow::model()->getFollowCount( array( $user['uid'] ) );
        $list = $this->getFollowData( 'follower', 0, WbCore\WbConst::DEF_LIST_FEED_NUMS );
        if ( $this->getIsMe() ) {
            UserData::model()->resetUserCount( $this->getUid(), 'new_folower_count', 0 );
        }
        $data = array(
            'count' => $count[$user['uid']],
            'list' => $list,
            'assetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'user' ),
            'moduleAssetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'weibo' ),
            'limit' => WbCore\WbConst::DEF_LIST_FEED_NUMS
        );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => util\IBOS::lang( 'Enterprise weibo' ), 'url' => $this->createUrl( 'home/index' ) ),
            array( 'name' => $user['realname'] . util\IBOS::lang( 'sbs fans' ), 'url' => $this->createUrl( 'personal/follower', array( 'uid' => $user['uid'] ) ) ),
            array( 'name' => util\IBOS::lang( 'List' ) )
        ) );
        $this->render( 'follower', $data, false, array( 'user.default' ) );
    }

    /**
     * 关注列表
     */
    public function actionFollowing() {
        $user = $this->getUser();
        $count = Follow::model()->getFollowCount( array( $user['uid'] ) );
        $list = $this->getFollowData( 'following', 0, WbCore\WbConst::DEF_LIST_FEED_NUMS );
        $data = array(
            'count' => $count[$user['uid']],
            'list' => $list,
            'assetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'user' ),
            'moduleAssetUrl' => util\IBOS::app()->assetManager->getAssetsUrl( 'weibo' ),
            'limit' => WbCore\WbConst::DEF_LIST_FEED_NUMS
        );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => util\IBOS::lang( 'Enterprise weibo' ), 'url' => $this->createUrl( 'home/index' ) ),
            array( 'name' => $user['realname'] . util\IBOS::lang( 'sbs follow' ), 'url' => $this->createUrl( 'personal/following', array( 'uid' => $user['uid'] ) ) ),
            array( 'name' => util\IBOS::lang( 'List' ) )
        ) );
        $this->render( 'following', $data, false, array( 'user.default' ) );
    }

    /**
     * 获取关注数据
     * @param string $type 是关注还是被关注 (following or follower)
     * @param integer $offset 分页偏移量
     * @param integer $limit 每页条数
     * @return array 一个符合列表输出的数组数据
     */
    protected function getFollowData( $type, $offset, $limit ) {
        if ( $type == 'follower' ) {
            $data = Follow::model()->getFollowerList( $this->getUid(), $offset, $limit );
        } else {
            $data = Follow::model()->getFollowingList( $this->getUid(), $offset, $limit );
        }
        if ( !empty( $data ) ) {
            $fids = util\Convert::getSubByKey( $data, 'fid' );
            $followStates = Follow::model()->getFollowStateByFids( util\IBOS::app()->user->uid, $fids );
            foreach ( $followStates as $uid => &$followState ) {
                $followState['user'] = User::model()->fetchByUid( $uid );
            }
            $list = &$followStates;
        } else {
            $list = array();
        }
        return $list;
    }

    /**
     * 获取查看人的人际关系 
     * @param string $type 人事关系类型
     * @param integer $offset 截取条数偏移量
     * @param integer $limit 截取条数
     * @return array
     */
    protected function getRelation( $type, $offset = 0, $limit = 4 ) {
        $data = array();
        switch ( $type ) {
            case 'colleague': // 部门同事
                $data = $this->getColleagues( $this->getUser(), false );
                $data = array_merge( $data, array() ); // trick:使之重新按数字排序
                break;
            case 'bothfollow': // 互相关注
                $data = Follow::model()->getBothFollow( $this->getUid(), util\IBOS::app()->user->uid );
                if ( !empty( $data ) ) {
                    $data = User::model()->fetchAllByUids( $data );
                }
                break;
            case 'secondfollow': // 第二关注(我关注的人也关注TA)
                $data = Follow::model()->getSecondFollow( util\IBOS::app()->user->uid, $this->getUid() );
                if ( !empty( $data ) ) {
                    $data = User::model()->fetchAllByUids( $data );
                }
                break;
            default :
                break;
        }
        return array(
            'count' => count( $data ),
            'list' => array_slice( $data, $offset, $limit )
        );
    }

    /**
     * 个人页获取微博数据
     * @param array $var
     * @return array
     */
    protected function getData( $var ) {
        $data = array();
        $type = isset( $var['new'] ) ? 'new' . $var['type'] : $var['type'];
        $where = 'isdel = 0 AND uid = ' . $this->getUid() . ($this->getIsMe() ? '' : ' AND ' . WbfeedUtil::getViewCondition( util\IBOS::app()->user->uid ));
        switch ( $type ) {
            case 'all': // 当前用户的全部微博
                $pages = util\Page::create( WbCore\WbConst::MAX_VIEW_FEED_NUMS, WbCore\WbConst::DEF_LIST_FEED_NUMS );
                if ( !empty( $var['feedkey'] ) ) {
                    $loadId = isset( $var['loadId'] ) ? $var['loadId'] : 0;
                    $list = Feed::model()->searchFeed( $var['feedkey'], 'all', $loadId, $var['nums'], $pages->getOffset(), '', $this->getUid() );
                    $count = Feed::model()->countSearchAll( $var['feedkey'], $loadId );
                } else {
                    if ( isset( $var['loadId'] ) && $var['loadId'] > 0 ) { // 非第一次
                        $where .=" AND feedid < '" . intval( $var['loadId'] ) . "'";
                    }
                    // 动态类型
                    if ( !empty( $var['feedtype'] ) && $var['feedtype'] !== 'all' ) {
                        $where .=" AND type = '" . util\String::filterCleanHtml( $var['feedtype'] ) . "'";
                    }
                    $list = Feed::model()->getList( $where, $var['nums'], $pages->getOffset() );
                    $count = Feed::model()->count( $where );
                }
                break;
            case 'movement':
                $pages = util\Page::create( WbCore\WbConst::MAX_VIEW_FEED_NUMS, WbCore\WbConst::DEF_LIST_FEED_NUMS );
                if ( !empty( $var['feedkey'] ) ) {
                    $loadId = isset( $var['loadId'] ) ? $var['loadId'] : 0;
                    $list = Feed::model()->searchFeed( $var['feedkey'], 'movement', $loadId, $var['nums'], $pages->getOffset(), '', $this->getUid() );
                    $count = Feed::model()->countSearchMovement( $var['feedkey'], $loadId );
                } else {
                    if ( isset( $var['loadId'] ) && $var['loadId'] > 0 ) { //非第一次
                        $where .=" AND feedid < '" . intval( $var['loadId'] ) . "'";
                    }
                    // 动态类型
                    if ( !empty( $var['feedtype'] ) && $var['feedtype'] !== 'all' ) {
                        $where .=" AND module = '" . util\String::filterCleanHtml( $var['feedtype'] ) . "'";
                    } else {
                        $where .=" AND module != 'weibo'";
                    }
                    $list = Feed::model()->getList( $where, $var['nums'], $pages->getOffset() );
                    $count = Feed::model()->count( $where );
                }
                break;
            case 'newmovement':
                if ( $var['maxId'] > 0 ) {
                    $where = sprintf( 'isdel = 0 AND %s AND feedid > %d AND uid = %d', WbfeedUtil::getViewCondition( util\IBOS::app()->user->uid ), intval( $var['maxId'] ), $this->uid );
                    $list = Feed::model()->getList( $where );
                    $count = Feed::model()->count( $where );
                    $data['count'] = count( $list );
                }
                break;
            case 'newall': // 当前用户最新微博
                if ( $var['maxId'] > 0 ) {
                    $where = sprintf( 'isdel = 0 %s AND feedid > %d AND uid = %d', ( $this->getIsMe() ? '' : ' AND ' . WbfeedUtil::getViewCondition( util\IBOS::app()->user->uid ) ), intval( $var['maxId'] ), $this->getUid() );
                    $list = Feed::model()->getList( $where );
                    $count = Feed::model()->count( $where );
                    $data['count'] = count( $list );
                }
                break;
            default:
                break;
        }
        $count = isset( $count ) ? $count : WbCore\WbConst::MAX_VIEW_FEED_NUMS;
        $pages = util\Page::create( $count, WbCore\WbConst::DEF_LIST_FEED_NUMS );
        if ( !isset( $var['new'] ) ) {
            $pages->route = 'personal/index';
            // 替换url
            $currentUrl = (string) util\IBOS::app()->getRequest()->getUrl();
            $replaceUrl = str_replace( 'weibo/personal/loadmore', 'weibo/personal/index', $currentUrl );
            $data['pageData'] = $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages, 'currentUrl' => $replaceUrl ), true );
        }
        if ( !empty( $list ) ) {
            $data['firstId'] = $list[0]['feedid'];
            $data['lastId'] = $list[(count( $list ) - 1)]['feedid'];
            //赞功能
            $feedids = util\Convert::getSubByKey( $list, 'feedid' );
            $diggArr = FeedDigg::model()->checkIsDigg( $feedids, $this->getUid() );
            foreach ( $list as &$v ) {
                // 这一步是赋值 来自XXX，手机端可根据具体哪种设备来赋值
                // 未来如果动态是来自于不同的模块，该信息也在这里处理
                // 默认是来自网页，即在微博主页发的
                switch ( $v['module'] ) {
                    case 'mobile':

                        break;
                    default:
                        $v['from'] = util\Env::getFromClient( $v['from'], $v['module'] );
                        break;
                }
            }
            $data['html'] = $this->renderPartial( 'application.modules.message.views.feed.feedlist', array( 'list' => $list, 'diggArr' => $diggArr ), true );
        } else {
            $data['html'] = '';
            $data['firstId'] = $data['lastId'] = 0;
        }
        return $data;
    }

}
