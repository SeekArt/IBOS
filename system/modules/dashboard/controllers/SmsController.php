<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Module;
use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Page;
use application\core\utils\String;
use application\modules\main\model\Setting;
use application\modules\message\model\NotifySms;
use application\modules\message\utils\Message;

class SmsController extends BaseController {

    public function actionSetup() {
        // 是否提交？
        $formSubmit = Env::submitCheck( 'smsSubmit' );
        if ( $formSubmit ) {
            if ( isset( $_POST['enabled'] ) ) {
                $enabled = 1;
            } else {
                $enabled = 0;
            }
            $interface = $_POST['interface'];
            $setup = $_POST['interface' . $interface];
            Setting::model()->updateSettingValueByKey( 'smsenabled', (int) $enabled );
            Setting::model()->updateSettingValueByKey( 'smsinterface', (int) $interface );
            Setting::model()->updateSettingValueByKey( 'smssetup', $setup );
            Cache::update( array( 'setting' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $data = array();
            $smsLeft = 0;
            $arr = Setting::model()->fetchSettingValueByKeys( 'smsenabled,smsinterface,smssetup' );
            $arr['smssetup'] = unserialize( $arr['smssetup'] );
            if ( is_array( $arr['smssetup'] ) ) {
                // 接口1：北程科技
                if ( $arr['smsinterface'] == '1' ) {
                    $accessKey = $arr['smssetup']['accesskey'];
                    $secretKey = $arr['smssetup']['secretkey'];
                    $url = "http://sms.bechtech.cn/Api/getLeft/data/json?accesskey={$accessKey}&secretkey={$secretKey}";
                    $return = File::fileSockOpen( $url );
                    if ( $return ) {
                        $return = json_decode( $return, true );
                        if ( isset( $return['result'] ) ) {
                            $smsLeft = $return['result'];
                        }
                    }
                }
            }
            $temp = Setting::model()->fetchSettingValueByKey( '' );
            $arr['setup'] = unserialize( $temp );
            $data['setup'] = $arr;
            $data['smsLeft'] = $smsLeft;
            $this->render( 'setup', $data );
        }
    }

    /**
     * 
     */
    public function actionManager() {
        $data = array();
        $type = Env::getRequest( 'type' );
        $inSearch = false;
        if ( $type == 'search' ) {
            $inSearch = true;
            $condition = '1';
            $keyword = Env::getRequest( 'keyword' );
            if ( !empty( $keyword ) ) {
                $keyword = String::filterCleanHtml( $keyword );
                $condition .= " AND content LIKE '%{$keyword}%'";
            }
            // 发送状态
            $searchType = Env::getRequest( 'searchtype' );
            if ( !empty( $searchType ) ) {
                $returnStatus = array();
                if ( String::findIn( $searchType, 1 ) ) {
                    $returnStatus[] = 1;
                }
                if ( String::findIn( $searchType, 0 ) ) {
                    $returnStatus[] = 0;
                }
                $condition .= sprintf( " AND return IN ('%s')", implode( ',', $returnStatus ) );
            }
            // 时间范围
            $begin = Env::getRequest( 'begin' );
            $end = Env::getRequest( 'end' );
            if ( !empty( $begin ) && !empty( $end ) ) {
                $condition .= sprintf( ' AND ctime BETWEEN %d AND %d', strtotime( $begin ), strtotime( $end ) );
            } else if ( !empty( $begin ) ) {
                $condition .= sprintf( ' AND ctime > %d', strtotime( $begin ) );
            } else if ( !empty( $end ) ) {
                $condition .= sprintf( ' AND ctime < %d', strtotime( $end ) );
            }
            // 发送人
            $sender = Env::getRequest( 'sender' );
            if ( !empty( $sender ) ) {
                $realSender = implode( ',', String::getId( $sender ) );
                $condition .= sprintf( ' AND uid = %d', intval( $realSender ) );
            }
            // 接收号码
            $recNumber = Env::getRequest( 'recnumber' );
            if ( !empty( $recNumber ) ) {
                $condition .= sprintf( ' AND mobile = %d', sprintf( '%d', $recNumber ) );
            }
            // 内容
            $content = Env::getRequest( 'content' );
            if ( !empty( $content ) && empty( $keyword ) ) {
                $content = String::filterCleanHtml( $content );
                $condition .= " AND content LIKE '%{$content}%'";
            }
            $type = 'manager';
        } else {
            $condition = '';
        }
        $count = NotifySms::model()->count( $condition );
        $pages = Page::create( $count, 20 );
        if ( $inSearch ) {
            $pages->params = array(
                'keyword' => $keyword,
                'searchtype' => $searchType,
                'begin' => $begin,
                'end' => $end,
                'sender' => $sender,
                'recnumber' => $recNumber,
                'content' => $content
            );
        }
		$data['list'] = NotifySms::model()->fetchAll( array( 
			'condition' => $condition,
			'offset' => $pages->getOffset(),
			'limit' => $pages->getLimit(),
			'order' => 'ctime DESC'
			) );
        $data['count'] = $count;
        $data['pages'] = $pages;
        $data['search'] = $inSearch;
        $this->render( 'manager', $data );
    }

    /**
     * 
     */
    public function actionAccess() {
        // 是否提交？
        $formSubmit = Env::submitCheck( 'smsSubmit' );
        if ( $formSubmit ) {
            $enabledModule = !empty( $_POST['enabled'] ) ? explode( ',', $_POST['enabled'] ) : array();
            Setting::model()->updateSettingValueByKey( 'smsmodule', $enabledModule );
            Cache::update( array( 'setting' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $data = array(
                'smsModule' => IBOS::app()->setting->get( 'setting/smsmodule' ),
                'enableModule' => Module::model()->fetchAllNotCoreModule()
            );
            $this->render( 'access', $data );
        }
    }

    /**
     * 
     */
    public function actionDel() {
        $id = Env::getRequest( 'id' );
        $id = String::filterStr( $id );
        NotifySms::model()->deleteAll( "FIND_IN_SET(id,'{$id}')" );
        $this->ajaxReturn( array( 'isSuccess' => true ) );
    }

    /**
     * 
     */
    public function actionExport() {
        $id = Env::getRequest( 'id' );
        $id = String::filterStr( $id );
        Message::exportSms( $id );
    }

}
