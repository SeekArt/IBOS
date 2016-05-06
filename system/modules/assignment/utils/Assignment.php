<?php

/**
 * 任务指派模块------ 任务指派工具类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 任务指派模块------  任务指派工具类
 * @package application.modules.assignment.util
 * @version $Id: Assignment.php 3297 2014-05-13 09:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment\utils;

use application\core\utils\File;
use application\core\utils\StringUtil;
use application\modules\assignment\model\AssignmentRemind;
use application\modules\dashboard\model\Stamp;
use application\modules\user\model\User;
use CHtml;

class Assignment {

    /**
     * 状态对应的css类名
     */
    const CLASS_UNREAD = 'unread'; // 未读
    const CLASS_ONGOING = 'ongoing'; // 进行中
    const CLASS_CANCEL = 'cancel'; // 已取消

    /**
     * 处理列表显示数据
     * @param array $data 任务二维数组
     * @param integer $uid 登陆用户id
     * @return array
     */

    public static function handleListData( $data, $uid ) {
        $reminds = AssignmentRemind::model()->fetchAllByUid( $uid );
        foreach ( $data as $k => $assignment ) {
            $data[$k] = self::handleShowData( $assignment );
            $aid = $assignment['assignmentid'];
            $data[$k]['remindtime'] = in_array( $aid, array_keys( $reminds ) ) ? $reminds[$aid] : 0;
            // 图章
            if ( $assignment['stamp'] != 0 ) {
                $path = Stamp::model()->fetchIconById( $assignment['stamp'] );
                $data[$k]['stampPath'] = File::fileName( Stamp::STAMP_PATH . $path );
            }
        }
        return $data;
    }

    /**
     * 处理“我指派的任务”数据，过滤掉指派人和负责人都是自己的数据，在指派栏不显示
     * @param array $designeeData
     * @return array
     */
    public static function handleDesigneeData( $designeeData ) {
        if ( is_array( $designeeData ) ) {
            foreach ( $designeeData as $k => $des ) {
                if ( $des['designeeuid'] == $des['chargeuid'] ) {
                    unset( $designeeData[$k] );
                }
            }
        }
        // 重新生成数组索引，确保前端输出的格式是数组
        return array_merge( $designeeData, array() );
    }

    /**
     * 处理单个显示数据
     * @param array $assignment 单个任务一维数组
     * @return array
     */
    public static function handleShowData( $assignment ) {
        $userArray = User::model()->fetchAllByUids( $assignment['designeeuid'] . ',' . $assignment['chargeuid'] );
        $assignment['designee'] = !empty( $userArray[$assignment['designeeuid']] ) ? $userArray[$assignment['designeeuid']] : array(); // 发起人
        $assignment['charge'] = !empty( $userArray[$assignment['chargeuid']] ) ? $userArray[$assignment['chargeuid']] : array(); // 负责人
        $assignment['st'] = date( 'm月d日 H:i', $assignment['starttime'] );
        $assignment['et'] = !$assignment['endtime'] ? '时间待定' : date( 'm月d日 H:i', $assignment['endtime'] );
        return $assignment;
    }

    /**
     * 连接条件语句
     * @param string $condition1 条件1
     * @param string $condition2 条件2
     * @return string
     */
    public static function joinCondition( $condition1, $condition2 ) {
        if ( empty( $condition1 ) ) {
            return $condition2;
        } else {
            return $condition1 . ' AND ' . $condition2;
        }
    }

    /**
     * 根据任务状态获得对应的css类，方便重用
     * @param integer $status
     * @return string
     */
    public static function getCssClassByStatus( $status ) {
        switch ( $status ) {
            case 0:
                $res = self::CLASS_UNREAD;
                break;
            case 1:
                $res = self::CLASS_ONGOING;
                break;
            case 4:
                $res = self::CLASS_CANCEL;
                break;
            default:
                $res = self::CLASS_UNREAD;
                break;
        }
        return $res;
    }

    /**
     * 处理添加、修改存入数据库前对数据处理
     * @return array
     */
    public static function handlePostData( $post ) {
        $chargeuid = StringUtil::getId( $post['chargeuid'] );
        $participantuid = StringUtil::getId( $post['participantuid'] );
        //添加对任务主题的xss安全过滤
        $data = array(
            'subject' => CHtml::encode( $post['subject'] ), // 任务主题
            'description' => StringUtil::filterStr( $post['description'] ), // 任务描述
            'chargeuid' => implode( ',', $chargeuid ), // 负责人
            'participantuid' => implode( ',', $participantuid ), // 参与人
            'attachmentid' => trim( $post['attachmentid'], ',' ), // 附件
            'starttime' => empty( $post['starttime'] ) ? TIMESTAMP : strtotime( $post['starttime'] ), // 开始时间
            'endtime' => strtotime( $post['endtime'] ) // 结束时间
        );
        return $data;
    }

}
