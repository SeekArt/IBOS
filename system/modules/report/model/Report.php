<?php

/**
 * 工作总结与计划模块------report表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 工作总结与计划模块------report表操作类，继承ICModel
 * @package application.modules.report.model
 * @version $Id: Report.php 1951 2013-12-17 03:47:48Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use CDbCriteria;
use CPagination;

class Report extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{report}}';
    }

    /**
     * 取得列表内容，分页
     * @param string $condition 查询条件
     * @param integer $pageSize 分页大小
     * @return type
     */
    public function fetchAllByPage( $condition, $pageSize = 0 ) {
        $conditionArray = array( 'condition' => $condition, 'order' => 'addtime DESC' );
        $criteria = new CDbCriteria();
        foreach ( $conditionArray as $key => $value ) {
            $criteria->$key = $value;
        }
        $count = $this->count( $criteria );
        $pagination = new CPagination( $count );
        $everyPage = empty( $pageSize ) ? Ibos::app()->params['basePerPage'] : $pageSize;
        $pagination->setPageSize( intval( $everyPage ) );
        $pagination->applyLimit( $criteria );
        $reportList = $this->fetchAll( $criteria );
        return array( 'pagination' => $pagination, 'data' => $reportList );
    }

    /**
     * 根据汇报类型id获取所有总结id和附件id
     * @param mixed $typeids 汇报类型ids
     * @return array 返回一维数组，repid和aid都是逗号隔开的字符串
     */
    public function fetchRepidAndAidByTypeids( $typeids ) {
        $typeids = is_array( $typeids ) ? implode( ',', $typeids ) : trim( $typeids, ',' );
        $reports = $this->fetchAll( array(
            'select' => 'repid, attachmentid',
            'condition' => "typeid IN($typeids)"
                ) );
        $return = array();
        if ( !empty( $reports ) ) {
            $return['repids'] = implode( ',', Convert::getSubByKey( $reports, 'repid' ) );
            $attachmentidArr = Convert::getSubByKey( $reports, 'attachmentid' );
            $return['aids'] = implode( ',', array_filter( $attachmentidArr ) );
        }
        return $return;
    }

    /**
     * 根据总结计划id取得所有附件Id
     * @param mixed $repids 总结id
     * @return string 附件ids 逗号分割的字符串
     */
    public function fetchAllAidByRepids( $repids ) {
        $ids = is_array( $repids ) ? implode( ',', $repids ) : trim( $repids, ',' );
        $records = $this->fetchAll( array( 'select' => array( 'attachmentid' ), 'condition' => "repid IN($ids)" ) );
        $result = array();
        foreach ( $records as $record ) {
            if ( !empty( $record['attachmentid'] ) ) {
                $result[] = trim( $record['attachmentid'], ',' );
            }
        }
        return implode( ',', $result );
    }

    /**
     * 获取上一次总结与计划,用于下次添加总结计划页面找原计划
     * @param integer $uid 用户id
     * @param integer $typeid 汇报类型
     * @param integer $time 找哪个时间前的总结
     * @return array 返回上一次总结的一维数组
     */
    public function fetchLastRepByUidAndTypeid( $uid, $typeid, $time = TIMESTAMP ) {
        $lastRep = $this->fetch( array(
            'select' => 'repid',
            'condition' => 'uid = :uid AND typeid = :typeid AND addtime < :time',
            'params' => array( ':uid' => $uid, ':typeid' => $typeid, ':time' => $time ),
            'order' => 'addtime DESC'
                ) );
        return $lastRep;
    }

    /**
     * 通过这次的总结计划id找上一次的总结id
     * @param integer $repid 总结计划id
     * @param integer $uid 用户id
     * @param integer $typeid 汇报类型
     * @return array 返回上一次总结的一维数组
     */
    public function fetchLastRepByRepid( $repid, $uid, $typeid ) {
        $lastRep = $this->fetch( array(
            'select' => 'repid',
            'condition' => 'repid < :repid AND uid = :uid AND typeid = :typeid',
            'params' => array( ':repid' => $repid, ':uid' => $uid, ':typeid' => $typeid ),
            'order' => 'repid DESC',
            'limit' => 1
                ) );
        return $lastRep;
    }

    /**
     * 取得当前用户的总结计划点评总数
     */
    public function countCommentByUid( $uid ) {
        $sql = "SELECT count(repid) as sum FROM {{report}} WHERE uid=$uid AND isreview=1";
        $record = $this->getDbConnection()->createCommand( $sql )->queryAll();
        $sum = empty( $record[0]['sum'] ) ? 0 : $record[0]['sum'];
        return $sum;
    }

    /**
     * 增加阅读记录,数据存在或者uid等于作者，返回false,其他返回修改是否成功
     * @param array $report
     * @param integer $uid
     * @return boolean
     */
    public function addReaderuid( $report, $uid ) {
        $readeruid = $report['readeruid'];
        if ( $uid == $report['uid'] ) {
            return false;
        }
        $readerArr = explode( ',', trim( $readeruid, ',' ) );
        if ( in_array( $uid, $readerArr ) ) {
            return false;
        } else {
            $readeruid = empty( $readeruid ) ? $uid : $readeruid . ',' . $uid;
            return $this->modify( $report['repid'], array( 'readeruid' => $readeruid ) );
        }
    }

    /**
     * 检查总结计划id是否属于某个用户
     * @param integer $repid 总结报告id
     * @param integer $uid 用户id
     * @return boolean
     */
//	public function checkRepIsBelongUid($repid, $uid){
//		$isBelong = false;
//		$count = $this->count("repid = {$repid} AND uid = {$uid}");
//		if( $count > 0 ){
//			$isBelong = true;
//		}
//		return $isBelong;
//	}

    /**
     * 增加阅读记录,数据存在或者uid等于作者，返回false,其他返回修改是否成功
     * @param array $report
     * @param integer $uid
     * @return boolean
     */
    public function addReaderuidByPk( $report, $uid ) {
        //assert( '$uid>0' );
        $readeruid = $report['readeruid'];
        if ( $uid == $report['uid'] ) {
            return false;
        }
        $readerArr = explode( ',', trim( $readeruid, ',' ) );
        if ( in_array( $uid, $readerArr ) ) {
            return false;
        } else {
            $readeruid = empty( $readeruid ) ? $uid : $readeruid . ',' . $uid;
            return $this->modify( $report['repid'], array( 'readeruid' => $readeruid ) );
        }
    }

    /**
     * 根据一条总结计划获取上一条和下一条同类型的总结计划
     * @param array $report 参照总结计划
     * @return array 返回上一条和下一条总结的数组
     */
    public function fetchPreAndNextRep( $report ) {
        $preRep = $this->fetch( array(
            'select' => 'repid, subject',
            'condition' => 'repid < :repid AND uid = :uid',
            'params' => array( ':repid' => $report['repid'], ':uid' => $report['uid'] ),
            'order' => 'repid DESC'
                ) );
        $nextRep = $this->fetch( array(
            'select' => 'repid, subject',
            'condition' => 'repid > :repid AND uid = :uid',
            'params' => array( ':repid' => $report['repid'], ':uid' => $report['uid'] ),
            'order' => 'repid ASC'
                ) );
        $preAndNextRep = array( 'preRep' => '', 'nextRep' => '' );
        if ( !empty( $preRep ) ) {
            $preAndNextRep['preRep'] = $preRep;
        }
        if ( !empty( $nextRep ) ) {
            $preAndNextRep['nextRep'] = $nextRep;
        }
        return $preAndNextRep;
    }

    /**
     * 通过uid获取所有总结，用于首页的个人和评阅公用
     * @param mix $uids 用户id，数组或者逗号隔开的字符串
     * @param integer $limit 获取多少条
     * @return array
     */
    public function fetchAllRepByUids( $uids, $limit = 4 ) {
        $ids = is_array( $uids ) ? implode( ',', $uids ) : trim( $uids, ',' );
        $reports = $this->fetchAll( array(
            'select' => 'repid, uid, subject, stamp',
            'condition' => "FIND_IN_SET(`uid`, '{$ids}')",
            'order' => 'addtime DESC',
            'limit' => $limit
                ) );
        return $reports;
    }

    /**
     * 根据条件获取未评阅总结报告，暂时只用到sidebar的未评阅数量
     * @param string $joinCondition 外加条件
     * @return array 返回符合条件的未评阅的总结的二维数组，没有返回空数组
     */
    public function fetchUnreviewReps( $joinCondition = '' ) {
        $condition = "isreview = 0";
        if ( !empty( $joinCondition ) ) {
            $condition .= ' AND ' . $joinCondition;
        }
        $unreviewReps = $this->fetchAll( $condition );
        return $unreviewReps;
    }

    /**
     * 根据总结id取得所属uid
     * @param integer $repId 总结id
     * @return integer
     */
    public function fetchUidByRepId( $repId ) {
        $report = $this->fetchByPk( $repId );
        if ( !empty( $report ) ) {
            return $report['uid'];
        }
    }

    /**
     * 统计指定用户指定日期范围内的总结数
     * @param mixed $uid 单个用户ID或数组
     * @param integer $start 开始范围
     * @param integer $end 结束范围
     * @param integer $typeid 总结类型id
     * @return integer
     */
    public function countReportTotalByUid( $uid, $start, $end, $typeid ) {
        $uid = is_array( $uid ) ? implode( ',', $uid ) : $uid;
        return $this->getDbConnection()->createCommand()
                        ->select( 'count(repid)' )
                        ->from( $this->tableName() )
                        ->where( sprintf( "uid IN ('%s') AND begindate < %d AND enddate > %d AND typeid = %d", $uid, $end, $start, $typeid ) )
                        ->queryScalar();
    }

    /**
     * 统计用户被评阅总数
     * @param integer $uid
     * @return integer
     */
    public function countReviewTotalByUid( $uid, $start, $end, $typeid ) {
        return $this->getDbConnection()->createCommand()
                        ->select( 'count(repid)' )
                        ->from( $this->tableName() )
                        ->where( sprintf( "isreview = 1 AND uid = %d AND begindate < %d AND enddate > %d AND typeid = %d", $uid, $end, $start, $typeid ) )
                        ->queryScalar();
    }

    /**
     * 统计指定用户的未评阅数
     * @param mixed $uid 用户ID
     * @param integer $start 开始时间戳
     * @param integer $end 结束时间戳
     * @return integer
     */
    public function countUnReviewByUids( $uid, $start, $end, $typeid ) {
        is_array( $uid ) && $uid = implode( ',', $uid );
        return $this->getDbConnection()->createCommand()
                        ->select( 'count(repid)' )
                        ->from( $this->tableName() )
                        ->where( sprintf( "isreview = 0 AND uid IN ('%s') AND begindate < %d AND enddate > %d AND typeid = %d", $uid, $end, $start, $typeid ) )
                        ->queryScalar();
    }

    /**
     * 获取指定总结ID范围内的总结添加时间
     * @param mixed $repIds 总结ID
     * @return array
     */
    public function fetchAddTimeByRepId( $repIds ) {
        is_array( $repIds ) && $repIds = implode( ',', $repIds );
        $criteria = array(
            'select' => 'repid,addtime',
            'condition' => sprintf( "FIND_IN_SET(repid,'%s')", $repIds )
        );
        return $this->fetchAllSortByPk( 'repid', $criteria );
    }

    /**
     * 获取总结的开始结束时间
     * @param  mixed $repIds 总结 ID
     * @return array         
     */
    public function fetchBETimeById( $repIds ) {
        is_array( $repIds ) && $repIds = implode( ',', $repIds );
        $criteria = array(
            'select' => 'repid,begindate,enddate',
            'condition' => sprintf( "FIND_IN_SET(repid,'%s')", $repIds )
        );
        return $this->fetchAllSortByPk( 'repid', $criteria );
    }

}
