<?php

/**
 * 投票模块------投票数据表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------投票数据表操作类
 * @package application.modules.vote.model
 * @version $Id: Vote.php 164 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\model;

use application\core\model\Model;

class Vote extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{vote}}';
    }

    /**
     * 通过相关模块名称和相关id取得取得一行投票记录及这行记录的所有相关投票项
     * @param string $relatedModule  相关模块名称
     * @param integer $relatedId   相关模块id
     * @return array
     */
    public function fetchVote( $relatedModule, $relatedId ) {
        $result = array();
        $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
        $params = array(
            ':relatedmodule' => $relatedModule,
            ':relatedid' => $relatedId
        );
        $vote = $this->fetch( $condition, $params );
        if ( !empty( $vote ) ) {
            $voteid = $vote['voteid'];
            $voteItemList = VoteItem::model()->fetchAll( "voteid=:voteid", array( ':voteid' => $voteid ) );
            $result['voteItemList'] = $voteItemList;
            $result['vote'] = $vote;
            $result['vote']['type'] = $result['voteItemList'][0]['type'];
        }
        return $result;
    }

    /**
     * 查询当前投票参与人数
     * @param string $relatedModule  相关模块名称
     * @param integer $relatedId  相关id
     * @result integer 投票数
     */
    public function fetchUserVoteCount( $relatedModule, $relatedId ) {
        $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
        $params = array(
            ':relatedmodule' => $relatedModule,
            ':relatedid' => $relatedId
        );
        $vote = $this->fetch( $condition, $params );
        //取得所有投票项
        $voteid = $vote['voteid'];
        $voteItemList = VoteItem::model()->fetchAll( "voteid=:voteid", array( ':voteid' => $voteid ) );
        $uidArray = array();
        foreach ( $voteItemList as $voteItem ) {
            $itemid = $voteItem['itemid'];
            $ItemCountList = VoteItemCount::model()->fetchAll( "itemid=:itemid", array( ':itemid' => $itemid ) );
            if ( !empty( $ItemCountList ) ) {
                foreach ( $ItemCountList as $itemCount ) {
                    $uid = $itemCount['uid'];
                    $uidArray[] = $uid;
                }
            }
        }
        //移除数组中的重复的值
        $result = count( array_unique( $uidArray ) );
        return $result;
    }

    /**
     * 通过相关ids和模块名称删除评论
     * @param string $relatedids 相关Id
     * @param string $relatedModule 相关模块名
     * @return 影响的行数
     */
    public function deleteAllByRelationIdsAndModule( $relatedids, $relatedModule ) {
        $relatedidArr = explode( ',', $relatedids );
        foreach ( $relatedidArr as $relatedid ) {
            $vote = $this->fetch( array(
                'select' => array( 'voteid' ),
                'condition' => 'relatedid=:relatedid AND relatedmodule=:relatedmodule',
                'params' => array( ':relatedid' => $relatedid, ':relatedmodule' => $relatedModule )
                    ) );
            if ( !empty( $vote ) ) {
                $voteId = $vote['voteid'];
                //找出所有投票项Id
                $voteItemList = VoteItem::model()->fetchAll( 'voteid=:voteid', array( ':voteid' => $voteId ) );
                if ( !empty( $voteItemList ) ) {
                    $voteItemIds = '';
                    foreach ( $voteItemList as $voteitem ) {
                        $voteItemIds.=$voteitem['itemid'] . ',';
                    }
                    $voteitemids = trim( $voteItemIds, ',' );
                    VoteItemCount::model()->deleteAll( "itemid IN($voteitemids)" );
                    VoteItem::model()->deleteAll( "itemid IN($voteitemids)" );
                }
            }
        }
        return $this->deleteAll( "relatedmodule='$relatedModule' AND relatedid IN($relatedids)" );
    }

}
