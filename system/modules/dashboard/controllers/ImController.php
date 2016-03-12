<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache as CacheUtil;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Org;
use application\core\utils\String;
use application\modules\dashboard\model\Cache;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\main\model\Setting;
use application\modules\message\core as MessageCore;
use application\modules\message\core\IMFactory;
use application\modules\message\utils\Message;
use application\modules\user\model\UserBinding;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;
use application\modules\user\utils\User;
use CJSON;

class ImController extends BaseController {

    public function actionIndex() {
        $type = Env::getRequest( 'type' );
        $allowType = array( 'rtx', 'qq' );
        if ( !in_array( $type, $allowType ) ) {
            $type = 'rtx';
        }
        $diff = array_diff( $allowType, array( $type ) );
        $value = Setting::model()->fetchSettingValueByKey( 'im' );
        $im = String::utf8Unserialize( $value );
        // 是否提交？
        $formSubmit = Env::submitCheck( 'imSubmit' );
        if ( $formSubmit ) {
            $type = $_POST['type'];
            // 暂时这样处理配置
            // 属于rtx的配置字段
            if ( $type == 'rtx' ) {
                $keys = array(
                    'open', 'server', 'appport', 'sdkport',
                    'push', 'sso', 'reverselanding', 'syncuser'
                );
            } else if ( $type == 'qq' ) {
                $keys = array(
                    'open', 'id', 'token', 'appid', 'appsecret',
                    'push', 'sso', 'syncuser', 'syncorg', 'showunread',
                    'refresh_token', 'time', 'expires_in'
                );
            }
            $updateList = array();
            foreach ( $keys as $key ) {
                if ( isset( $_POST[$key] ) ) {
                    $updateList[$key] = $_POST[$key];
                } else {
                    $updateList[$key] = 0;
                }
            }
            if ( $updateList['open'] == '1' ) {
                $this->checkImUnique( $diff );
                $correct = Message::getIsImBinding( $type, $updateList );
                if ( $correct !== true ) {
                    $updateList['open'] = 0;
                } else {
                    if ( $type == 'qq' ) {
                        $updateList['checkpass'] = 1;
                    }
                }
            } else {
                if ( $type == 'qq' ) {
                    $updateList['checkpass'] = 0;
                }
                $correct = true;
            }
            $im[$type] = $updateList;
            Setting::model()->updateSettingValueByKey( 'im', $im );
            CacheUtil::update( array( 'setting' ) );
            if ( $correct === true ) {
                $this->success( IBOS::lang( 'Save succeed', 'message' ) );
            } else {
                $updateList['open'] = 0;
                if ( is_array( $correct ) ) {
                    $msg = isset( $correct[MessageCore\IM::ERROR_INIT] ) ? implode( ',', $correct[MessageCore\IM::ERROR_INIT] ) : IBOS::lang( 'Unknown error', 'error' );
                } else {
                    $msg = IBOS::lang( 'Unknown error', 'error' );
                }
                $this->error( IBOS::lang( 'Binding error', '', array( '{err}' => $msg ) ) );
            }
        } else {
            $data = array(
                'type' => $type,
                'im' => $im[$type],
            );
            $this->render( $type, $data );
        }
    }

    /**
     * 同步OA组织架构到RTX
     */
    public function actionSyncRtx() {
        $pwd = Env::getRequest( 'pwd' );
        if ( Message::getIsImOpen( 'rtx' ) ) {
            $imCfg = IBOS::app()->setting->get( 'setting/im/rtx' );
            $factory = new MessageCore\IMFactory();
            $adapter = $factory->createAdapter( 'application\modules\message\core\IMRtx', $imCfg, array( 'pwd' => $pwd ) );
            $res = $adapter->syncOrg();
            if ( !$res ) {
                $msg = implode( ',', $adapter->getError( MessageCore\IM::ERROR_SYNC ) );
            } else {
                $msg = '';
            }
            $this->ajaxReturn( array( 'isSuccess' => !!$res, 'msg' => $msg ) );
        }
    }

    /**
     * 同步RTX组织架构到OA
     */
    public function actionSyncOa() {
        if ( Env::submitCheck( 'formhash' ) ) {
            if ( Message::getIsImOpen( 'rtx' ) ) {
                $file = File::getUpload( $_FILES['xml'] );
                if ( $file->save() ) {
                    $attach = $file->getAttach();
                    $origpwd = Env::getRequest( 'pwd' );
                    $content = file_get_contents( $attach['target'] );
                    $content = Convert::iIconv( $content, 'gb2312', CHARSET );
                    $content = str_replace( '<?xml version="1.0" encoding="GB2312"?>', '<?xml version="1.0" encoding="UTF-8"?>', $content );
                    CacheUtil::set( 'syncoa', $content );
                    Cache::model()->add( array( 'cachekey' => 'initpwd', 'cachevalue' => $origpwd ) );
                    unlink( $attach['target'] );
                    $this->showMessage( '开始处理过程，请勿关闭窗口...', $this->createUrl( 'im/syncoa', array( 'op' => 'dept', 'start' => 0 ) ), array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                }
            }
        } else {
            $file = CacheUtil::get( 'syncoa' );
            if ( $file ) {
                $start = Env::getRequest( 'start' );
                $end = (int) ($start + 20);
                $op = Env::getRequest( 'op' );
                if ( $op == 'dept' ) {
                    $end = (int) ($start + 10);
                    $xml = simplexml_load_string( $file );
                    $depts = (array) $xml->Database->RTX_Dept;
                    $deptRelates = array();
                    $count = count( $depts['Item'] );
                    $datas = array_slice( $depts['Item'], $start, 10 );
                    foreach ( $datas as $dept ) {
                        $dept = (array) $dept;
                        $id = (string) $dept['@attributes']['DeptID'];
                        $name = (string) $dept['@attributes']['DeptName'];
                        $data = array( 'deptname' => $name );
                        $newId = Department::model()->add( $data, true );
                        Department::model()->updateByPk( $newId, array( 'sort' => $newId ) );
                        $deptRelates[$id] = $newId;
                    }
                    $cache = Cache::model()->fetchByPk( 'deptrelate' );
                    if ( $cache ) {
                        $cache = String::utf8Unserialize( $cache['cachevalue'] );
                        $cache = $cache + $deptRelates;
                        Cache::model()->updateByPk( 'deptrelate', array( 'cachevalue' => serialize( $cache ) ) );
                    } else {
                        Cache::model()->add( array( 'cachekey' => 'deptrelate', 'cachevalue' => serialize( $deptRelates ) ) );
                    }
                    if ( $end > $count ) {
                        $this->showMessage( '开始处理部门关联...请稍后', $this->createUrl( 'im/syncoa', array( 'op' => 'deptrelated', 'start' => 0 ) ), array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                    } else {
                        $this->showMessage( '正在处理部门，请稍后...', $this->createUrl( 'im/syncoa', array( 'op' => 'dept', 'start' => $end ) ), array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                    }
                } else if ( $op == 'deptrelated' ) {
                    $xml = simplexml_load_string( $file );
                    $depts = (array) $xml->Database->RTX_Dept;
                    $count = count( $depts['Item'] );
                    $cache = Cache::model()->fetchByPk( 'deptrelate' );
                    $deptRelates = String::utf8Unserialize( $cache['cachevalue'] );
                    $datas = array_slice( $depts['Item'], $start, 20 );
                    foreach ( $datas as $dept ) {
                        $dept = (array) $dept;
                        $id = (string) $dept['@attributes']['DeptID'];
                        $pid = (string) $dept['@attributes']['PDeptID'];
                        if ( $pid != 0 && isset( $deptRelates[$pid] ) ) {
                            Department::model()->updateByPk( $deptRelates[$id], array( 'pid' => $deptRelates[$pid] ) );
                        }
                    }
                    if ( $end > $count ) {
                        $this->showMessage( '开始处理用户与部门关联...请稍后', $this->createUrl( 'im/syncoa', array( 'op' => 'userrelated' ) ), array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                    } else {
                        $this->showMessage( '正在处理部门关联，请稍后...', $this->createUrl( 'im/syncoa', array( 'op' => 'deptrelated', 'start' => $end ) ), array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                    }
                } else if ( $op == 'userrelated' ) {
                    $xml = simplexml_load_string( $file );
                    $related = (array) $xml->Database->RTX_DeptUser;
                    $rec = Cache::model()->fetchByPk( 'deptrelate' );
                    $deptRelates = String::utf8Unserialize( $rec['cachevalue'] );
                    $userRelates = $userDeptRelates = array();
                    $ip = IBOS::app()->setting->get( 'clientip' );
                    foreach ( $related['Item'] as $dr ) {
                        $dr = (array) $dr;
                        $id = (string) $dr['@attributes']['DeptID'];
                        $userId = (string) $dr['@attributes']['UserID'];
                        if ( isset( $userRelates[$userId] ) ) {
                            $userDeptRelates[$userId][] = isset( $deptRelates[$id] ) ? $deptRelates[$id] : 0;
                        } else {
                            $userRelates[$userId] = isset( $deptRelates[$id] ) ? $deptRelates[$id] : 0;
                        }
                    }
                    Cache::model()->add( array( 'cachekey' => 'userrelate', 'cachevalue' => serialize( $userRelates ) ) );
                    Cache::model()->add( array( 'cachekey' => 'userdeptrelate', 'cachevalue' => serialize( $userDeptRelates ) ) );
                    $this->showMessage( '开始处理用户关联...请稍后', $this->createUrl( 'im/syncoa', array( 'op' => 'user', 'start' => 0 ) ), array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                } else if ( $op == 'user' ) {
                    $xml = simplexml_load_string( $file );
                    $users = (array) $xml->Database->Sys_User;
                    $count = count( $users['Item'] );
                    $newUser = array();
                    $datas = array_slice( $users['Item'], $start, 20 );
                    $rec = Cache::model()->fetchByPk( 'userrelate' );
                    $userDeptRec = Cache::model()->fetchByPk( 'userdeptrelate' );
                    $origpwd = Cache::model()->fetchByPk( 'initpwd' );
                    $userRelates = String::utf8Unserialize( $rec['cachevalue'] );
                    $userDeptRelates = String::utf8Unserialize( $userDeptRec['cachevalue'] );
                    $ip = IBOS::app()->setting->get( 'clientip' );
                    foreach ( $datas as $user ) {
                        $user = (array) $user;
                        $salt = String::random( 6 );
                        $username = (string) $user['@attributes']['UserName'];
                        if ( User::model()->userNameExists( $username ) ) {
                            continue;
                        }
                        $data = array(
                            'username' => $username,
                            'email' => (string) $user['@attributes']['Email'],
                            'mobile' => String::cutStr( (string) $user['@attributes']['Mobile'], 11, '' ),
                            'realname' => (string) $user['@attributes']['Name'],
                            'deptid' => $userRelates[$user['@attributes']['ID']],
                            'salt' => $salt,
                            'createtime' => TIMESTAMP,
                            'guid' => String::createGuid(),
                            'password' => md5( md5( $origpwd['cachevalue'] ) . $salt ),
                        );
                        $newId = User::model()->add( $data, true, true );
                        UserCount::model()->add( array( 'uid' => $newId ) );
                        UserStatus::model()->add(
                                array(
                                    'uid' => $newId,
                                    'regip' => $ip,
                                    'lastip' => $ip
                                )
                        );
                        UserProfile::model()->add( array( 'uid' => $newId ) );
                        if ( isset( $userDeptRelates[$user['@attributes']['ID']] ) ) {
                            foreach ( $userDeptRelates[$user['@attributes']['ID']] as $rdept ) {
                                DepartmentRelated::model()->add( array( 'deptid' => $rdept, 'uid' => $newId ) );
                            }
                        }
                        $newUser[] = $newId;
                    }
                    $cache = Cache::model()->fetchByPk( 'newuser' );
                    if ( $cache ) {
                        $cache = String::utf8Unserialize( $cache['cachevalue'] );
                        $cache = $cache + $newUser;
                        Cache::model()->updateByPk( 'newuser', array( 'cachevalue' => serialize( $cache ) ) );
                    } else {
                        Cache::model()->add( array( 'cachekey' => 'newuser', 'cachevalue' => serialize( $newUser ) ) );
                    }
                    if ( $end > $count ) {
                        CacheUtil::rm( 'syncoa' );
                        Cache::model()->deleteAll( "FIND_IN_SET(cachekey,'newuser,userrelate,deptrelate,initpwd,userdeptrelate')" );
                        User::model()->fetchAllByUids( $cache );
                        CacheUtil::update();
                        Org::update();
                        $this->showMessage( '导入RTX完成,您可以关闭此窗口了', '', array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                    } else {
                        $this->showMessage( '还在处理用户，请稍后...', $this->createUrl( 'im/syncoa', array( 'op' => 'user', 'start' => $end ) ), array( 'messageType' => 'info', 'timeout' => 1 ), 0 );
                    }
                }
            } else {
                $this->renderPartial( 'syncoa' );
            }
        }
    }

    /**
     * 企业QQ绑定用户
     */
    public function actionBindingUser() {
        if ( Env::submitCheck( 'formhash' ) ) {
            $map = filter_input( INPUT_POST, 'map', FILTER_SANITIZE_STRING );
            if ( !empty( $map ) ) {
                UserBinding::model()->deleteAllByAttributes( array( 'app' => 'bqq' ) );
                $maps = explode( ',', $map );
                foreach ( $maps as $relation ) {
                    list($uid, $openId) = explode( '=', $relation );
                    UserBinding::model()->add( array( 'uid' => $uid, 'bindvalue' => $openId, 'app' => 'bqq' ) );
                }
                $this->ajaxReturn( array( 'isSuccess' => true ) );
            }
            $this->ajaxReturn( array( 'isSuccess' => false ) );
        } else {
            if ( Message::getIsImOpen( 'qq' ) ) {
                $imCfg = IBOS::app()->setting->get( 'setting/im/qq' );
                $factory = new IMFactory();
                $adapter = $factory->createAdapter( 'application\modules\message\core\IMQq', $imCfg );
                $api = $adapter->getApi();
                $rs = $api->getUserList( array( 'timestamp' => 0 ) );
                $bqqUsers = array();
                if ( !is_array( $rs ) ) {
                    $rsArr = CJSON::decode( $rs, true );
                    if ( isset( $rsArr['ret'] ) && $rsArr['ret'] == '0' ) {
                        $bqqUsers = $rsArr['data']['items'];
                    }
                }
                $data = array(
                    'ibosUsers' => User::loadUser(),
                    'binds' => UserBinding::model()->fetchAllSortByPk( 'uid', "app = 'bqq'" ),
                    'bqqUsers' => $bqqUsers
                );
                $this->renderPartial( 'qqbinding', $data );
            }
        }
    }

    /**
     * 检查IM启用唯一性
     * @param array $arr
     */
    private function checkImUnique( $arr ) {
        foreach ( $arr as $type ) {
            if ( Message::getIsImOpen( $type ) ) {
                $this->error( IBOS::lang( 'Binding unique error' ) );
            }
        }
    }

}
