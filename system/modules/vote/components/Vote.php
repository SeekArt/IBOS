<?php

/**
 * 投票模块------投票组件类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------投票组件类
 * @package application.modules.vote.components
 * @version $Id: ICVote.php 164 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\components;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\vote\model\Vote as VoteModel;
use application\modules\vote\model\VoteItem;
use application\modules\vote\model\VoteItemCount;
use application\modules\vote\utils\RequestValidator;
use application\modules\vote\utils\VoteUtil;

abstract class Vote
{

    /**
     * 用户点击投票的操作
     * @param string $relatedModule 相关模块名称
     * @param integer $relatedId 相关id
     * @param mixed $voteItemids 投票项id
     * @return integer 默认返回0，代表添加失败；-1代表已投过票；投票成功则返回最近的投票数据
     */
    public function clickVote($relatedModule, $relatedId, $voteItemids)
    {
        $result = 0;
        //判断用户是否已经投过票
        if (!VoteUtil::checkVote($relatedModule, $relatedId)) {
            $affectedRow = VoteItem::model()->updateNumber($voteItemids);
            if ($affectedRow) {
                $voteitemidArray = explode(',', rtrim($voteItemids, ','));
                foreach ($voteitemidArray as $voteitemid) {
                    $voteItemCount = new VoteItemCount();
                    $voteItemCount->itemid = $voteitemid;
                    $voteItemCount->uid = Ibos::app()->user->uid;
                    $voteItemCount->save();
                }
                $voteData = VoteModel::model()->fetchVote($relatedModule, $relatedId);
                $result = VoteUtil::processVoteData($voteData);
            }
        } else {
            $result = -1;
        }
        return $result;
    }

    /**
     * 取得投票状态
     * @param string $relatedModule 相关模块名称
     * @param integer $relatedId 相关id
     * @param Object|string $voteData 投票对象，默认为空
     * @return int 状态  0：无效 、1：有效、2：结束
     */
    public function getStatus($relatedModule, $relatedId, $voteData = '')
    {
        if (empty($voteData)) {
            $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
            $params = array(
                ':relatedmodule' => $relatedModule,
                ':relatedid' => $relatedId
            );
            $vote = VoteModel::model()->fetch($condition, $params);
        } else {
            $vote = $voteData;
        }
        if ($vote['status'] == 0) {
            return $vote['status'];
        } else if ($vote['status'] == 2) {
            return $vote['status'];
        } else {
            $remainTime = VoteUtil::getRemainTime($vote['starttime'], $vote['endtime']);
            if ($remainTime == 0) {
                return 1;
            } else if ($remainTime == -1) {
                //修改状态为已结束
                $affectedRow = VoteModel::model()->updateByPk($vote['voteid'], array('status' => 2));
                if ($affectedRow) {
                    return 2;
                }
            } else if (is_array($remainTime)) {
                return 1;
            }
        }

        return -1;
    }


    /**
     * 取得投票局部视图文件
     *
     * @param integer $view
     * @return string
     */
    public static function getView($view)
    {
        $basePath = 'application.modules.vote.views.default.';
        $uploadConfig = Attach::getUploadConfig();

        $relatedModule = Ibos::getCurrentModuleName();
        $relatedId = Env::getRequest($relatedModule . 'id');
        $voteModel = VoteModel::model()->fetchVoteByModule($relatedModule, $relatedId);


        $output = '';
        if ($view == 'view') {
            if (empty($voteModel)) {
                $voteId = 0;
            } else {
                $voteId = $voteModel['voteid'];
            }
            $assetUrl = Ibos::app()->assetManager->getAssetsUrl('vote');
            $output = sprintf('<script>Ibos.app.s({voteid: "%d", origin: "%s"})</script><script src="%s/js/vote_default_show.js"></script>', $voteId, $relatedModule, $assetUrl);
        } elseif ($view == 'topicsform') {
            // add or edit topics
            $vote = array();
            $topics = array();

            if (!empty($voteModel)) {
                $voteId = $voteModel['voteid'];
                list($vote, $topics) = VoteModel::model()->fetchVoteDetail($voteId);
            }
            $selectView = $basePath . $view;
            $output = Ibos::app()->controller->renderPartial($selectView, array(
                'uploadConfig' => $uploadConfig,
                'vote' => $vote,
                'topics' => $topics,
            ), true);
        }

        return $output;
    }

    /**
     * 第三方模块添加一条投票
     *
     * @param array $voteData 投票数据
     * @param string $moduleName 关联模块名
     * @param integer $moduleId 关联模块 id
     * @return bool 成功 true，失败 false
     * @throws \Exception
     */
    public static function add($voteData, $moduleName, $moduleId)
    {
        RequestValidator::getInstance()->initAddVote($voteData);
        VoteUtil::add($voteData, $moduleName, $moduleId);

        return true;
    }

    /**
     * 第三方模块更新一条投票
     *
     * @param array $voteData 投票数据
     * @param string $moduleName 关联模块名
     * @param integer $moduleId 关联模块 id
     * @return bool 成功 true，失败 false
     */
    public static function update($voteData, $moduleName, $moduleId)
    {
        $voteModel = VoteModel::model()->fetchVoteByModule($moduleName, $moduleId);
        if (empty($voteModel)) {
            throw new \InvalidArgumentException();
        }

        $voteData['voteid'] = $voteModel['voteid'];
        VoteUtil::updateVote($voteData, $moduleName, $moduleId);

        return true;
    }

}
