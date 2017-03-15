<?php
/**
 * @namespace application\modules\vote\controllers
 * @filename DefaultController.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/19 9:26
 */

namespace application\modules\vote\controllers;


use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\message\model\NotifyMessage;
use application\modules\vote\model\Vote;
use application\modules\vote\utils\VoteFormUtil;
use application\modules\vote\utils\VoteUtil;

class DefaultController extends BaseController
{
    /**
     * 视图：渲染投票首页视图
     */
    public function actionIndex()
    {
        $type = Env::getRequest('type');
        $types = array(Vote::LIST_VOTE_UN_JOINED, Vote::LIST_START_RUNNING, Vote::LIST_MANAGE_RUNNING);
        if (!in_array($type, $types)) {
            $type = Vote::LIST_VOTE_UN_JOINED;
        }
        $data = array('type' => $type);
        $this->render("index", $data);
    }

    /**
     * 视图：显示投票内容
     */
    public function actionShow()
    {
        $this->render("show");
    }

    /**
     * API 接口：返回调查投票列表
     */
    public function actionFetchIndexList()
    {
        $type = Env::getRequest('type');
        $search = Env::getRequest('search');
        $draw = Env::getRequest('draw');
        $start = Env::getRequest('start');
        $length = Env::getRequest('length');

        list($list, $allListCount) = VoteUtil::fetchList($type, $search, $start, $length);
        $list = VoteUtil::handleList($list);
        return $this->ajaxBaseReturn(true, $list, '', array('draw' => $draw, 'recordsFiltered' => $allListCount));
    }

    /**
     * API 接口：返回投票的详细信息
     */
    public function actionShowVote()
    {
        $voteId = Env::getRequest('voteid');

        $voteData = VoteUtil::showVote($voteId);

        // 设置提醒消息为已读
        NotifyMessage::model()->setReadByUrl(Ibos::app()->user->uid, $this->createUrl('default/show', array('id' => $voteId)));
        return $this->ajaxBaseReturn(true, $voteData);
    }

    /**
     * API 接口：返回投票的用户参与情况信息
     *
     * @return bool|void
     */
    public function actionShowVoteUsers()
    {
        $voteId = Env::getRequest('voteid');

        $voteUsersData = VoteUtil::showVoteUsers($voteId);
        return $this->ajaxBaseReturn(true, $voteUsersData);
    }

    /**
     * API 接口：导出投票结果
     */
    public function actionExport()
    {
        $voteId = Env::getRequest('voteid');

        VoteUtil::exportVoteData($voteId);
    }

    /**
     * API 接口：发起投票
     * @throws \Exception
     */
    public function actionVote()
    {
        $uid = Ibos::app()->user->uid;
        $postData = Env::getRequest('vote');

        VoteFormUtil::getInstance()->vote($uid, $postData);
        return $this->ajaxBaseReturn(true, array(), Ibos::lang('vote success'));
    }

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     *
     * @param string $route
     * @return boolean true 不验证该路由
     */
    public function filterRoutes($route)
    {
        $whiteList = array(
            'vote/default/showvote',
            'vote/default/vote',
        );

        if (in_array($route, $whiteList)) {
            return true;
        }

        return false;
    }
}
