<?php

namespace application\modules\report\utils;

use application\core\utils\IBOS;
use application\modules\dashboard\model\Stamp;
use application\modules\message\utils\MessageApi;
use application\modules\report\model\Report;
use application\modules\user\model\User;

class ReportApi extends MessageApi {

    private $_indexTab = array( 'reportPersonal', 'reportAppraise' );

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting() {
        $uid = IBOS::app()->user->uid;
        $subUidArr = User::model()->fetchSubUidByUid( $uid );
        $subReports = Report::model()->fetchAll( "FIND_IN_SET({$uid}, `toid`)" );
        if ( count( $subUidArr ) > 0 || !empty( $subReports ) ) {
            return array(
                'name' => 'report/report',
                'title' => '工作总结',
                'style' => 'in-report',
                'tab' => array(
                    array(
                        'name' => 'reportPersonal',
                        'title' => '个人',
                        'icon' => 'o-rp-personal'
                    ),
                    array(
                        'name' => 'reportAppraise',
                        'title' => '评阅',
                        'icon' => 'o-rp-appraise'
                    )
                )
            );
        } else {
            return array(
                'name' => 'report/report',
                'title' => '工作总结',
                'style' => 'in-report',
                'tab' => array(
                    array(
                        'name' => 'reportPersonal',
                        'title' => '个人',
                        'icon' => 'o-rp-personal'
                    )
                )
            );
        }
    }

    /**
     * 渲染首页视图
     * @return type
     */
    public function renderIndex() {
        $return = array();
        $viewAlias = 'application.modules.report.views.indexapi.report';
        $uid = IBOS::app()->user->uid;
        // 自己的总结计划
        $reports = Report::model()->fetchAllRepByUids( $uid );
        if ( !empty( $reports ) ) {
            $reports = $this->handleIconUrl( $reports );
        }
        // 下属或者是某篇总结的汇报对象的总结计划
        $subUidArr = User::model()->fetchSubUidByUid( $uid );
        $subUidStr = implode( ',', $subUidArr );
        $subReports = Report::model()->fetchAll( "FIND_IN_SET(`uid`, '{$subUidStr}') OR FIND_IN_SET({$uid}, `toid`)" );
        if ( !empty( $subReports ) ) {
            $subReports = $this->handleIconUrl( $subReports, true );
        }
        $data = array(
            'reports' => $reports,
            'subReports' => $subReports,
            'lang' => IBOS::getLangSource( 'report.default' ),
            'assetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'report' )
        );
        foreach ( $this->_indexTab as $tab ) {
            $data['tab'] = $tab;
            if ( $tab == 'reportPersonal' ) {
                $return[$tab] = IBOS::app()->getController()->renderPartial( $viewAlias, $data, true );
            } else if ( $tab == 'reportAppraise' && ( count( $subUidArr ) > 0 || !empty( $subReports ) ) ) {
                $return[$tab] = IBOS::app()->getController()->renderPartial( $viewAlias, $data, true );
            }
        }
        return $return;
    }

    /**
     * 获取最新总结
     * @return integer
     */
    public function loadNew() {
        $uid = IBOS::app()->user->uid;
        //获取所有直属下属id
        $uidArr = User::model()->fetchSubUidByUid( $uid );
        if ( !empty( $uidArr ) ) {
            $uidStr = implode( ',', $uidArr );
            $sql = "SELECT COUNT(repid) AS number FROM {{report}} WHERE FIND_IN_SET( `uid`, '{$uidStr}' ) AND isreview = 0";
            $record = Report::model()->getDbConnection()->createCommand( $sql )->queryAll();
            return intval( $record[0]['number'] );
        } else {
            return 0;
        }
    }

    /**
     * 处理图章icon输出路径
     * @param array $reports 总结报告二维数组
     * @param boolean $returnUserInfo 是否要获得用户信息，用于评阅
     * @return 返回处理过图章icon后的数组
     */
    private function handleIconUrl( $reports, $returnUserInfo = false ) {
        foreach ( $reports as $k => $report ) {
            if ( $returnUserInfo ) {
                $reports[$k]['userInfo'] = User::model()->fetchByUid( $report['uid'] );
            }
            if ( $report['stamp'] != 0 ) {
                $stamp = Stamp::model()->fetchIconById( $report['stamp'] );
				$reports[$k]['iconUrl'] = $stamp;
            } else {
                $reports[$k]['iconUrl'] = '';
            }
        }
        return $reports;
    }

}
