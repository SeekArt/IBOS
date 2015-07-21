<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Log;
use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Page;
use application\modules\dashboard\controllers\BaseController;
use application\modules\dashboard\model\IpBanned;
use application\modules\main\model\Setting;

class SecurityController extends BaseController {

    public function actionSetup() {
        $formSubmit = Env::submitCheck( 'securitySubmit' );
        if ( $formSubmit ) {
            $fields = array(
                'expiration', 'minlength',
                'mixed', 'errorlimit', 'errorrepeat',
                'errortime', 'autologin', 'allowshare',
                'timeout'
            );
            $updateList = array();
            foreach ( $fields as $field ) {
                if ( !isset( $_POST[$field] ) ) {
                    $_POST[$field] = 0;
                }
                $updateList[$field] = $_POST[$field];
            }

            if ( intval( $updateList['timeout'] ) == 0 ) {
                $this->error( '请填写一个正确的大于0的超时时间值' );
            }
            Setting::model()->updateSettingValueByKey( 'account', $updateList );
            Cache::update( array( 'setting' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $data = array();
            $account = Setting::model()->fetchSettingValueByKey( 'account' );
            $data['account'] = unserialize( $account );
            $this->render( 'setup', $data );
        }
    }

    public function actionLog() {
        $formSubmit = Env::submitCheck( 'securitySubmit' );
        if ( $formSubmit ) {
            Cache::update( array( 'setting' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $data = array();
            $levels = array( 'admincp', 'banned', 'illegal', 'login' );
            $level = Env::getRequest( 'level' );
            $filterAct = Env::getRequest( 'filteract' );
            $timeScope = Env::getRequest( 'timescope' );
            if ( !in_array( $level, $levels ) ) {
                $level = 'admincp';
            }
            $conArr = array(
                'level' => $level
            );
            $condition = "level = '{$level}'";
            if ( !empty( $filterAct ) ) {
                $condition .= sprintf( " AND category = 'module.dashboard.%s'", $filterAct );
                $conArr['filteract'] = $filterAct;
            } else {
                $condition .= ' AND 1';
            }
            if ( !empty( $timeScope ) ) {
                $start = Env::getRequest( 'start' );
                $end = Env::getRequest( 'end' );
                $tableId = intval( $timeScope );
                $conArr['timescope'] = $tableId;
                if ( !empty( $start ) && !empty( $end ) ) {
                    $conArr['start'] = $start;
                    $conArr['end'] = $end;
                    $start = strtotime( $tableId . '-' . $start );
                    $end = strtotime( $tableId . '-' . $end );
                    $condition .= sprintf( ' AND `logtime` BETWEEN %d AND %d', $start, $end );
                } else if ( !empty( $start ) ) {
                    $conArr['start'] = $start;
                    $start = strtotime( $tableId . '-' . $start );
                    $condition .= sprintf( ' AND `logtime` > %d', $start );
                } else if ( !empty( $end ) ) {
                    $conArr['end'] = $end;
                    $end = strtotime( $tableId . '-' . $end );
                    $condition .= sprintf( ' AND `logtime` < %d', $end );
                }
            } else {
                $tableId = 0;
                $lastMonth = strtotime( 'last month' );
                $condition .= sprintf( ' AND `logtime` BETWEEN %d AND %d', $lastMonth, TIMESTAMP );
            }
            $count = Log::countByTableId( $tableId, $condition );
            $pages = Page::create( $count, 20 );
            $log = Log::fetchAllByList( $tableId, $condition, $pages->getLimit(), $pages->getOffset() );
            $data['log'] = $log;
            $data['pages'] = $pages;
            // 后台记录才有动作描述
            if ( $level == 'admincp' ) {
                $data['actions'] = IBOS::getLangSource( 'dashboard.actions' );
            }
            $data['filterAct'] = $filterAct;
            $data['level'] = $level;
            $data['archive'] = Log::getAllArchiveTableId();
            $data['con'] = $conArr;
            $this->render( 'log', $data );
        }
    }

    public function actionIp() {
        $formSubmit = Env::submitCheck( 'securitySubmit' );
        if ( $formSubmit ) {
            if ( $_POST['act'] == '' ) { // act为空，默认操作ip地址
                if ( isset( $_POST['ip'] ) ) {
                    // 新增处理
                    foreach ( $_POST['ip'] as $new ) {
                        if ( $new['ip1'] != '' && $new['ip2'] != '' && $new['ip3'] != '' && $new['ip4'] != '' ) {
                            $own = 0;
                            $ip = explode( '.', IBOS::app()->setting->get( 'clientip' ) );
                            for ( $i = 1; $i <= 4; $i++ ) {
                                if ( !is_numeric( $new['ip' . $i] ) || $new['ip' . $i] < 0 ) {
                                    $new['ip' . $i] = -1;
                                    $own++;
                                } elseif ( $new['ip' . $i] == $ip[$i - 1] ) {
                                    $own++;
                                }
                                $new['ip' . $i] = intval( $new['ip' . $i] );
                            }
                            if ( $own == 4 ) {
                                $this->error( IBOS::lang( 'Ipban illegal' ) );
                            }
                            $expiration = TIMESTAMP + $new['validitynew'] * 86400;
                            $new['admin'] = IBOS::app()->user->username;
                            $new['dateline'] = TIMESTAMP;
                            $new['expiration'] = $expiration;
                            IpBanned::model()->add( $new );
                        }
                    }
                }
                // 编辑处理
                if ( isset( $_POST['expiration'] ) ) {
                    $userName = IBOS::app()->user->username;
                    foreach ( $_POST['expiration'] as $id => $expiration ) {
                        IpBanned::model()->updateExpirationById( $id, strtotime( $expiration ), $userName );
                    }
                }
            } else if ( $_POST['act'] == 'del' ) { //删除选中
                if ( is_array( $_POST['id'] ) ) {
                    IpBanned::model()->deleteByPk( $_POST['id'] );
                }
            } else if ( $_POST['act'] == 'clear' ) { //清空
                $command = IBOS::app()->db->createCommand();
                $command->delete( '{{ipbanned}}' );
            }
            Cache::update( array( 'setting', 'ipbanned' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $data = array();
            $lists = IpBanned::model()->fetchAllOrderDateline();
            $list = array();
            foreach ( $lists as $banned ) {
                for ( $i = 1; $i <= 4; $i++ ) {
                    if ( $banned["ip{$i}"] == -1 ) {
                        $banned["ip{$i}"] = '*';
                    }
                }
                $banned['dateline'] = date( 'Y-m-d', $banned['dateline'] );
                $banned['expiration'] = date( 'Y-m-d', $banned['expiration'] );
                $displayIp = "{$banned['ip1']}.{$banned['ip2']}.{$banned['ip3']}.{$banned['ip4']}";
                $banned['display'] = $displayIp;
                $banned['scope'] = Convert::convertIp( $displayIp );
                $list[] = $banned;
            }
            $data['list'] = $list;
            $this->render( 'ip', $data );
        }
    }

}
