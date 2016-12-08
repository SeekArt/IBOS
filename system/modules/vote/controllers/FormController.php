<?php
/**
 *
 * @namespace application\modules\vote\controllers
 * @filename DefaultController.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/19 9:19
 */

namespace application\modules\vote\controllers;


use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\vote\utils\MessageUtil;
use application\modules\vote\utils\VoteUtil;
use application\modules\vote\VoteModule;

class FormController extends BaseController
{

    /**
     * 视图：显示发起调查表单
     *
     * @return mixed|string
     */
    public function actionShow()
    {
        return $this->render('form');
    }

    /**
     * 视图：显示编辑调查表单
     *
     * @return mixed|string
     */
    public function actionEdit()
    {
        $voteId = Env::getRequest('voteid');
        $voteFormData = VoteUtil::fetchVoteFormData($voteId);

        return $this->render('form', $voteFormData);
    }


    /**
     * API 接口：添加或更新一条投票记录
     */
    public function actionAddOrUpdate()
    {
        $postData = Env::getRequest('vote');
        if (isset($postData['voteid']) && !empty($postData['voteid'])) {
            // 更新投票
            $newVoteId = VoteUtil::updateVote($postData, VoteModule::MODULE_NAME, 0);
            if ($newVoteId === false) {
                return $this->ajaxBaseReturn(false, array(), Ibos::lang('update vote failed'));
            }
            // 发送更新投票提醒消息
            MessageUtil::getInstance()->sendUpdateVoteNotify($newVoteId, $postData['subject'], $postData['publishscope']);
            return $this->ajaxBaseReturn(true, array('voteid' => $newVoteId), Ibos::lang('update vote success'));
        } else {
            // 添加投票
            $voteId = VoteUtil::addVote($postData, VoteModule::MODULE_NAME, 0);
            // 发送添加投票提醒消息
            MessageUtil::getInstance()->sendAddVoteNotify($voteId, $postData['subject'], $postData['publishscope']);
            return $this->ajaxBaseReturn(true, array('voteid' => $voteId), Ibos::lang('Add vote success'));
        }
    }


    /**
     * API 接口：更新投票截至时间
     */
    public function actionUpdateEndTime()
    {
        $postData = Env::getRequest('vote');

        VoteUtil::updateEndTime($postData);
        return $this->ajaxBaseReturn(true, array(), Ibos::lang('update endtime success'));
    }

    /**
     * API 接口：删除一条投票记录
     */
    public function actionDel()
    {
        $postData = Env::getRequest('vote');
        $delStatus = VoteUtil::delVotes($postData);

        if ($delStatus === false) {
            return $this->ajaxBaseReturn(false, array(), Ibos::lang('delete vote failed'));
        }
        return $this->ajaxBaseReturn(true, array(), Ibos::lang('delete vote success'));
    }

}