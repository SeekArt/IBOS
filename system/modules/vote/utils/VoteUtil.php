<?php

/**
 * 投票模块------投票工具类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------投票工具类
 * @package application.modules.vote.utils
 * @version $Id: VoteUtil.php 140 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\utils;

use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\vote\model\Vote;
use application\modules\vote\model\VoteItem;
use application\modules\vote\model\VoteItemCount;

class VoteUtil {

    /**
     * 对投票源数据进行相应页面显示处理
     * @param array $data 处理前投票数据
     * @return array 处理后投票数据
     */
    public static function processVoteData( $data ) {
        //如果有投票项记录，设置各自投票项票数和总票数所占的百分比
        if ( !empty( $data ) ) {
            $data['voteItemList'] = self::getPercentage( $data['voteItemList'] );
            //得到投票剩余结束时间
            $data['vote']['remainTime'] = self::getRemainTime( $data['vote']['starttime'], $data['vote']['endtime'] );
        }
        return $data;
    }

    /**
     * 判断用户是否投过票
     * @param string $relatedModule 关联模块名称
     * @param integer $relatedId  关联模块id
     * @param integer $uid  访问当前投票用户UID,如果不填，则默认Ibos::app()->user->uid
     * @return boolean true为已投，false为没投过票
     */
    public static function checkVote( $relatedModule, $relatedId, $uid = 0 ) {
        $result = false;
        $uid = empty( $uid ) ? Ibos::app()->user->uid : $uid;
        $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
        $params = array(
            ':relatedmodule' => $relatedModule,
            ':relatedid' => $relatedId
        );
        $vote = Vote::model()->fetch( $condition, $params );
        if ( !empty( $vote ) ) {
            //取出所有投票项
            $voteid = $vote['voteid'];
            $voteItemList = VoteItem::model()->fetchAll( "voteid=:voteid", array( ':voteid' => $voteid ) );
            //判断所有投票项下是否有用户记录
            foreach ( $voteItemList as $voteItem ) {
                $itemid = $voteItem['itemid'];
                //取出所有投票用户信息
                $itemCountList = VoteItemCount::model()->fetchAll( "itemid=:itemid", array( ':itemid' => $itemid ) );
                if ( !empty( $itemCountList ) && count( $itemCountList ) > 0 ) {
                    foreach ( $itemCountList as $itemCount ) {
                        if ( $itemCount['uid'] == $uid ) {
                            $result = true;
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 设置各自投票项票数和总票数所占的百分比以及颜色样式
     * @param array $voteItemList 未经过处理的投票项集合
     * @return array $voteItemList 处理后的投票项集合
     */
    private static function getPercentage( $voteItemList ) {
        //得到所有票数
        $numberCount = 0;
        foreach ( $voteItemList as $index => $voteItem ) {
            $voteItemList[$index]['picpath'] = File::fileName( $voteItem['picpath'] );
            $numberCount += $voteItem['number'];
        }
        //计算各自投票项所占百分比
        $length = count( $voteItemList );
        if ( $numberCount == 0 ) {
            //如果票数为0，说明没人投票，设置所有投票项百分比为0%
            for ( $i = 0; $i < $length; $i++ ) {
                $voteItemList[$i]['percentage'] = '0%';
                $voteItemList[$i]['color_style'] = '';
            }
        } else {
            //如果票数不为0，计算各自的百分比
            $percentageCount = 0;
            $count = 0;
            $colors = array( '#91CE31', '#EE8C0C', '#E26F50', '#3497DB' );
            $colorLength = count( $colors );
            for ( $i = 0; $i < $length; $i++ ) {
                $percentage = round( $voteItemList[$i]['number'] / $numberCount * 100 );
                $voteItemList[$i]['percentage'] = $percentage;
                $percentageCount = $percentageCount + $voteItemList[$i]['percentage'];
                //设置投票项的颜色样式
                $voteItemList[$i]['color_style'] = $colors[$count];
                $count++;
                if ( $count >= $colorLength ) {
                    $count = 0;
                }
            }
            if ( $percentageCount != 100 ) {
                $voteItemList[0]['percentage'] = $voteItemList[0]['percentage'] + 1;
            }
            for ( $i = 0; $i < $length; $i++ ) {
                $voteItemList[$i]['percentage'] = $voteItemList[$i]['percentage'] . '%';
            }
        }
        return $voteItemList;
    }

    /**
     * 设置投票结束时间
     * @param integer $startTime 投票开始时间戳
     * @param integer $dayNumber 天数
     * @return integer 结束投票时间戳
     */
    public static function setEndTime( $startTime, $dayNumber ) {
        return $startTime + $dayNumber * 24 * 60 * 60;
    }

    /**
     * 取得剩余结束时间
     * @code array('day'=>5,'hour'=>17,'minute'=>43,'second'=>31)
     * @param integer $startTime  开始时间戳
     * @param integer $endTime  结束时间戳
     * @return mixed 有剩余时间则返回数组如上示例数组,0代表无结束时间，-1代表已经过了结束时间
     */
    public static function getRemainTime( $startTime, $endTime ) {
        $remainTime = $endTime - time();
        if ( $endTime == 0 ) {
            return 0;
        } else if ( $endTime > $startTime && $remainTime > 0 ) {
            $minuteCount = floor( $remainTime / 60 );
            $dayNumber = floor( $minuteCount / (60 * 24) );
            $remainHour = floor( ($minuteCount - $dayNumber * 24 * 60) / 60 );
            $remainMinute = floor( ($minuteCount - $dayNumber * 24 * 60) % 60 );
            $remainSecond = round( ($remainTime / 60 - $minuteCount) * 60 );
            $remainTime = array(
                'day' => $dayNumber,
                'hour' => $remainHour,
                'minute' => $remainMinute,
                'second' => $remainSecond
            );
            return $remainTime;
        } else if ( $endTime > $startTime && $remainTime <= 0 ) {
            return -1;
        }
    }

    public static function processDateTime( $dateTime ) {
        $resultTime = 0;
        if ( $dateTime == 'One week' ) {
            $resultTime = time() + 7 * 24 * 60 * 60;
        } else if ( $dateTime == 'One month' ) {
            $resultTime = time() + 30 * 24 * 60 * 60;
        } else if ( $dateTime == 'half of a year' ) {
            $resultTime = time() + 6 * 30 * 24 * 60 * 60;
        } else if ( $dateTime == 'One year' ) {
            $resultTime = time() + 365 * 24 * 60 * 60;
        }
        return $resultTime;
    }

    /**
     * 取得投票结束时间
     * @param type $endtime
     * @param type $selectEndIime
     */
    public static function getEndTime( $endtime, $selectEndIime ) {
        $result = '';
        $selectEndTime = trim( $selectEndIime );
        if ( isset( $endtime ) && $selectEndTime == 'Custom' ) {
            $result = strtotime( $endtime ) + 24 * 60 * 60 - 1;
        } else if ( $selectEndTime !== 'Custom' ) {
            $result = self::processDateTime( $selectEndTime );
        }
        return $result;
    }

}
