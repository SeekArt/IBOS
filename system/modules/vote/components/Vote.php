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
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\vote\components\VotePlugManager;
use application\modules\vote\model\Vote as VoteModel;
use application\modules\vote\model\VoteItem;
use application\modules\vote\model\VoteItemCount;
use application\modules\vote\utils\VoteUtil;

abstract class Vote {

    /**
     * 用户点击投票的操作
     * @param string $relatedModule 相关模块名称
     * @param integer $relatedId  相关id
     * @param mixed $voteItemids  投票项id
     * @return 默认返回0，代表添加失败；-1代表已投过票；投票成功则返回最近的投票数据
     */
    public function clickVote( $relatedModule, $relatedId, $voteItemids ) {
        $result = 0;
        //判断用户是否已经投过票
        if ( !VoteUtil::checkVote( $relatedModule, $relatedId ) ) {
            $affectedRow = VoteItem::model()->updateNumber( $voteItemids );
            if ( $affectedRow ) {
                $voteitemidArray = explode( ',', rtrim( $voteItemids, ',' ) );
                foreach ( $voteitemidArray as $voteitemid ) {
                    $voteItemCount = new VoteItemCount();
                    $voteItemCount->itemid = $voteitemid;
                    $voteItemCount->uid = IBOS::app()->user->uid;
                    $voteItemCount->save();
                }
                $voteData = VoteModel::model()->fetchVote( $relatedModule, $relatedId );
                $result = VoteUtil::processVoteData( $voteData );
            }
        } else {
            $result = -1;
        }
        return $result;
    }

    /**
     * 取得投票状态
     * @param string $relatedModule 相关模块名称
     * @param integer $relatedId  相关id
     * @param Object $voteData 投票对象，默认为空
     * @return integer  状态  0：结束 、1：有效、2：无效
     */
    public function getStatus( $relatedModule, $relatedId, $voteData = '' ) {
        if ( empty( $voteData ) ) {
            $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
            $params = array(
                ':relatedmodule' => $relatedModule,
                ':relatedid' => $relatedId
            );
            $vote = VoteModel::model()->fetch( $condition, $params );
        } else {
            $vote = $voteData;
        }
        if ( $vote['status'] == 0 ) {
            return $vote['status'];
        } else if ( $vote['status'] == 2 ) {
            return $vote['status'];
        } else {
            $remainTime = VoteUtil::getRemainTime( $vote['starttime'], $vote['endtime'] );
            if ( $remainTime == 0 ) {
                return 1;
            } else if ( $remainTime == -1 ) {
                //修改状态为已结束
                $affectedRow = VoteModel::model()->updateByPk( $vote['voteid'], array( 'status' => 2 ) );
                if ( $affectedRow ) {
                    return 2;
                }
            } else if ( is_array( $remainTime ) ) {
                return 1;
            }
        }
    }

    /**
     * 取得投票局部视图文件
     * @param integer $view 
     * @return string
     */
    public static function getView( $view ) {
        $currentController = IBOS::app()->getController();
        $basePath = 'application.modules.vote.views.default.';

        $relatedModule = IBOS::getCurrentModuleName();
        $relatedId = Env::getRequest( $relatedModule . 'id' );

        if ( $view == 'articleView' ) {
            $voteData = VoteModel::model()->fetchVote( $relatedModule, $relatedId );
            $votes = VoteUtil::processVoteData( $voteData );
            if ( !empty( $votes ) ) {
                $voteItemList = $votes['voteItemList'];
                $voteType = $voteItemList[0]['type'];
                if ( $voteType == 1 ) {
                    $view = 'articleTextView';
                } else if ( $voteType == 2 ) {
                    $view = 'articleImageView';
                }
                $selectView = $basePath . $view;
                //取得参与人数
                $votePeopleNumber = VoteModel::model()->fetchUserVoteCount( $relatedModule, $relatedId );
                //判断用户是否已投票
                $userHasVote = VoteUtil::checkVote( $relatedModule, $relatedId );
                //取得当前投票状态
                $mothedName = 'get' . ucfirst( $relatedModule ) . 'Vote';
                $voteStatus = VotePlugManager::$mothedName()->getStatus( $relatedModule, $relatedId, $votes['vote'] );
                $votes['vote']['subject'] = String::cutStr( $votes['vote']['subject'], 60 );
                $data = array(
                    'voteData' => $votes,
                    'votePeopleNumber' => $votePeopleNumber,
                    'userHasVote' => $userHasVote,
                    'voteStatus' => $voteStatus,
                    'attachUrl' => IBOS::app()->setting->get( 'setting/attachurl' )
                );
                if ( $voteStatus == 2 ) {
                    $partView = null;
                } else {
                    $partView = $currentController->renderPartial( $selectView, $data, true );
                }
            } else {
                $partView = null;
            }
        } else if ( $view == 'articleAdd' ) {
            $selectView = $basePath . $view;
            $partView = $currentController->renderPartial( $selectView, array( 'uploadConfig' => Attach::getUploadConfig() ), true );
        } else if ( $view == 'articleEdit' ) {
            $selectView = $basePath . $view;
            $voteData = VoteModel::model()->fetchVote( $relatedModule, $relatedId );
            if ( !empty( $voteData ) && isset( $voteData['voteItemList'] ) ) {
                foreach ( $voteData['voteItemList'] as $k => $voteItem ) {
                    $voteData['voteItemList'][$k]['thumburl'] = File::fileName( $voteItem['picpath'] );
                }
            }
            $data = array( 'voteData' => $voteData, 'uploadConfig' => Attach::getUploadConfig() );
            $partView = $currentController->renderPartial( $selectView, $data, true );
        }
        return $partView;
    }

}
