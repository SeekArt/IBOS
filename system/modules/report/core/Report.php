<?php

/**
 * 工作总结与计划模块------工作总结与计划组件类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 *  工作总结与计划模块------工作总结与计划组件类
 * @package application.modules.report.core
 * @version $Id: ICReport.php 66 2013-09-13 08:40:50Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\report\core;

use application\core\utils\Convert;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Stamp;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class Report {

    /**
     * 处理总结计划列表输出数据
     * @param array $reports 总结计划二维数组
     * @return array 处理过后的总结计划二维数组
     */
    public static function handelListData( $reports ) {
        $return = array();
        foreach ( $reports as $report ) {
            $report['cutSubject'] = StringUtil::cutStr( strip_tags( $report['subject'] ), 60 );
            $report['user'] = User::model()->fetchByUid( $report['uid'] );
            // 阅读次数
            $readeruid = $report['readeruid'];
            $report['readercount'] = empty( $readeruid ) ? 0 : count( explode( ',', trim( $readeruid, ',' ) ) );
            $report['content'] = StringUtil::cutStr( strip_tags( $report['content'] ), 255 );
            $report['addtime'] = Convert::formatDate( $report['addtime'], 'u' );
            // 图章
            if ( $report['stamp'] != 0 ) {
                $path = Stamp::model()->fetchIconById( $report['stamp'] );
				$report['stampPath'] = $path;
            }
            $return[] = $report;
        }
        return $return;
    }

    /**
     * 处理总结汇报的添加数据，目的是为了补充默认值
     * @param array $data 要添加的总结报告数组
     * @return array 返回填充默认值后的总结报告数组
     */
    public static function handleSaveData( $data ) {
        $fieldDefault = array(
            'uid' => 0,
            'begindate' => 0,
            'enddate' => 0,
            'addtime' => TIMESTAMP,
            'typeid' => 0,
            'subject' => '',
            'content' => '',
            'attachmentid' => '',
            'toid' => '',
            'readeruid' => '',
            'status' => 0,
            'remark' => '',
            'stamp' => 0,
            'lastcommenttime' => 0,
            'comment' => '',
            'commentline' => 0,
            'replyer' => 0,
            'reminddate' => 0,
            'commentcount' => 0
        );
        foreach ( $data as $field => $val ) {
            if ( array_key_exists( $field, $fieldDefault ) ) {
                $fieldDefault[$field] = $val;
            }
        }
        return $fieldDefault;
    }

    /**
     * 处理总结或者计划标题
     * @param array $reportType 汇报类型数组
     * @param integer $begin 总结/计划区间开始时间 时间戳
     * @param integer $end	 总结/计划区间结束时间 时间戳
     * @param string $connection 显示的文字，0为总结，1为计划， 其他为为自定义标题
     * @return string 返回标题字符串
     */
    public static function handleShowSubject( $reportType, $begin, $end, $connection = 0 ) {
        if ( $reportType['intervaltype'] == 5 ) { // 如果是自定义类型
            $connectTitle = $reportType['typename'];
        } else {
            $interval = ReportType::handleShowInterval( $reportType['intervaltype'] );
            $connectTitle = $connection == 0 ? $interval . '报' : $interval . '计划';
        }
        $subject = date( 'm.d', $begin ) . ' - ' . date( 'm.d', $end ) . ' ' . $connectTitle;
        return $subject;
    }

    /**
     * 判断用户是否有阅读某篇总结的权限
     * @param array $report 要阅读的总结
     * @param integer $uid 阅读人
     * @return boolean 返回是否有权限
     */
    public static function checkPermission( $report, $uid ) {
        // 如果总结所属的uid在他的下属uid里，或者这篇总结的汇报对象是他，有权限
        $toid = explode( ',', $report['toid'] );
        if ( $report['uid'] == $uid || in_array( $uid, $toid ) || UserUtil::checkIsSub( $uid, $report['uid'] ) ) {
            return true;
        } else {
            return false;
        }
    }

}
