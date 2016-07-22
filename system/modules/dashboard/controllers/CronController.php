<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\main\model\Cron;

class CronController extends BaseController {

    public function actionIndex() {
        $op = Env::getRequest( 'op' );
        $id = intval( Env::getRequest( 'id' ) );
        if ( Env::submitCheck( 'formhash' ) ) {
            // 编辑页面提交
            if ( $op == 'edit' ) {
                $dayNew = $_POST['weekdaynew'] != -1 ? -1 : $_POST['daynew'];
                if ( strpos( $_POST['minutenew'], ',' ) !== false ) {
                    $minuteNew = explode( ',', $_POST['minutenew'] );
                    foreach ( $minuteNew as $key => $val ) {
                        $minuteNew[$key] = $val = intval( $val );
                        if ( $val < 0 || $val > 59 ) {
                            unset( $minuteNew[$key] );
                        }
                    }
                    $minuteNew = array_slice( array_unique( $minuteNew ), 0, 12 );
                    $minuteNew = implode( "\t", $minuteNew );
                }
                elseif ( strpos( $_POST['minutenew'], '*/' ) !== FALSE ) {
                    $minuteNew = $_POST['minutenew'];
                }
                else {
                    $minuteNew = intval( $_POST['minutenew'] );
                    $minuteNew = $minuteNew >= 0 && $minuteNew < 60 ? $minuteNew : '';
                }
                $cronfile = $this->getRealCronFile( $_POST['type'], $_POST['filenamenew'], $_POST['module'] );
                if ( preg_match( "/[\\\\\/\:\*\?\"\<\>\|]+/", $_POST['filenamenew'] ) ) {
                    $this->error( IBOS::lang( 'Crons filename illegal' ) );
                } elseif ( !is_readable( $cronfile ) ) {
                    $this->error( IBOS::lang( 'Crons filename invalid', '', array( '{cronfile}' => $cronfile ) ) );
                } elseif ( $_POST['weekdaynew'] == -1 && $dayNew == -1 && $_POST['hournew'] == -1 && $minuteNew === '' ) {
                    $this->error( IBOS::lang( 'Crons time invalid' ) );
                }
                $data = array(
                    'weekday' => $_POST['weekdaynew'],
                    'day' => $dayNew,
                    'hour' => $_POST['hournew'],
                    'minute' => $minuteNew,
                    'filename' => trim( $_POST['filenamenew'] )
                );
                $id && Cron::model()->modify( $id, $data );
                IBOS::app()->cron->run( $id );
            } else {
                if ( $op == 'delete' ) {
                    if ( !empty( $_POST['delete'] ) ) {
                        $ids = StringUtil::iImplode( $_POST['delete'] );
                        Cron::model()->deleteAll( sprintf( "cronid IN (%s) AND type='user'", $ids ) );
                    }
                } else {
                    // 列表页面提交
                    if ( isset( $_POST['namenew'] ) && !empty( $_POST['namenew'] ) ) {
                        foreach ( $_POST['namenew'] as $id => $name ) {
                            $newCron = array(
                                'name' => \CHtml::encode( $_POST['namenew'][$id] ),
                                'available' => isset( $_POST['availablenew'][$id] ) ? 1 : 0
                            );
                            if ( isset( $_POST['availablenew'][$id] ) && empty( $_POST['availablenew'][$id] ) ) {
                                $newCron['nextrun'] = '0';
                            }
                            Cron::model()->modify( $id, $newCron );
                        }
                    }
                    if ( !empty( $_POST['newname'] ) ) {
                        $data = array(
                            'name' => StringUtil::ihtmlSpecialChars( $_POST['newname'] ),
                            'type' => 'user',
                            'available' => '0',
                            'weekday' => '-1',
                            'day' => '-1',
                            'hour' => '-1',
                            'minute' => '',
                            'nextrun' => TIMESTAMP,
                        );
                        Cron::model()->add( $data );
                    }
                    $list = Cron::model()->fetchAll( array( 'select' => 'cronid,filename,type,module' ) );
                    foreach ( $list as $cron ) {
                        $cronFile = $this->getRealCronFile( $cron['type'], $cron['filename'], $cron['module'] );
                        if ( !file_exists( $cronFile ) ) {
                            Cron::model()->modify( $cron['cronid'], array( 'available' => 0, 'nextrun' => 0 ) );
                        }
                    }
                    Cache::update( 'setting' );
                }
            }
            $this->success( IBOS::lang( 'Crons succeed' ), $this->createUrl( 'cron/index' ) );
        } else {
            if ( $op && in_array( $op, array( 'edit', 'run' ) ) ) {
                $cron = Cron::model()->fetchByPk( $id );
                if ( !$cron ) {
                    $this->error( 'Cron not found' );
                }
                $cron['filename'] = str_replace( array( '..', '/', '\\' ), array( '', '', '' ), $cron['filename'] );
                if ( $op == 'edit' ) {
                    $this->render( 'edit', array( 'cron' => $cron ) );
                } else if ( $op == 'run' ) {
                    $file = $this->getRealCronFile( $cron['type'], $cron['filename'], $cron['module'] );
                    if ( !file_exists( $file ) ) {
                        $this->error( IBOS::lang( 'Crons run invalid', '', array( '{cronfile}' => $file ) ) );
                    } else {
                        IBOS::app()->cron->run( $cron['cronid'] );
                        $this->success( IBOS::lang( 'Crons run succeed' ), $this->createUrl( 'cron/index' ) );
                    }
                }
            } else {
                $list = Cron::model()->fetchAll( array( 'order' => 'type desc' ) );
                $this->handleCronList( $list );
                $this->render( 'index', array( 'list' => $list ) );
            }
        }
    }

    private function getRealCronFile( $type, $fileName, $module = '' ) {
        if ( $type == 'user' ) {
            $cronFile = './system/extensions/cron/' . $fileName;
        } else {
            $cronFile = sprintf( './system/modules/%s/cron/%s', $module, $fileName );
        }
        return $cronFile;
    }

    /**
     * 
     * @param type $list
     */
    private function handleCronList( &$list ) {
        foreach ( $list as &$cron ) {
            $cron['disabled'] = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? true : false;
            if ( $cron['day'] > 0 && $cron['day'] < 32 ) {
                $cron['time'] = IBOS::lang( 'Per mensem' ) . $cron['day'] . IBOS::lang( 'Cron day' );
            } elseif ( $cron['weekday'] >= 0 && $cron['weekday'] < 7 ) {
                $cron['time'] = IBOS::lang( 'Weekly' ) . IBOS::lang( 'Cron week day ' . $cron['weekday'] );
            } elseif ( $cron['hour'] >= 0 && $cron['hour'] < 24 ) {
                $cron['time'] = IBOS::lang( 'Cron perday' );
            } else {
                $cron['time'] = IBOS::lang( 'Per hour' );
            }
            
            $cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf( '%02d', $cron['hour'] ) . IBOS::lang( 'Cron hour' ) : '';
            if ( strpos( $cron['minute'], '*/' ) !== FALSE ) {
                $cron['time'] = IBOS::lang( 'Every few minutes', '', array( '{minutes}' => str_replace( '*/', '', $cron['minute'] ) ) );
            } elseif ( !in_array( $cron['minute'], array( -1, '' ) ) ) {
                foreach ( $cron['minute'] = explode( "\t", $cron['minute'] ) as $k => $v ) {
                    $cron['minute'][$k] = sprintf( '%02d', $v );
                }
                $cron['minute'] = implode( ',', $cron['minute'] );
                $cron['time'] .= $cron['minute'] . IBOS::lang( 'Cron minute' );
            } else {
                $cron['time'] .= '00' . IBOS::lang( 'Cron minute' );
            }

            $cron['lastrun'] = $cron['lastrun'] ? Convert::formatDate( $cron['lastrun'], IBOS::app()->setting->get( 'setting/dateformat' ) . "<\b\\r />" . IBOS::app()->setting->get( 'setting/timeformat' ) ) : '<b>N/A</b>';
//			$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_G['setting']['timeoffset'] * 3600 < TIMESTAMP ? 'style="color: #ff0000"' : '';
            $cron['nextrun'] = $cron['nextrun'] ? Convert::formatDate( $cron['nextrun'], IBOS::app()->setting->get( 'setting/dateformat' ) . "<\b\\r />" . IBOS::app()->setting->get( 'setting/timeformat' ) ) : '<b>N/A</b>';
        }
    }

}
