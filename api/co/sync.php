<?php

use application\core\utils\Env;
use application\core\utils\Cache;
use application\modules\dashboard\model\Syscache;
use application\modules\main\model\Setting;
use application\modules\user\model\User;
use application\modules\department\model\Department;
use application\modules\user\model\UserBinding;
use application\modules\dashboard\utils\CoSync;
use application\modules\dashboard\model\Cache as CacheModel;

// 程序根目录路径
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../../' );
$defines = PATH_ROOT . '/system/defines.php';
define( 'YII_DEBUG', true );
define( 'TIMESTAMP', time() );
define( 'CALLBACK', true );
$yii = PATH_ROOT . '/library/yii.php';
$mainConfig = require_once PATH_ROOT . '/system/config/common.php';
require_once ( $defines );
require_once ( $yii );
require_once ( '../login.php' );
Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );
Yii::createApplication( 'application\core\components\Application', $mainConfig );
// 接收的参数
$signature = rawurldecode( Env::getRequest( 'signature' ) );
$timestamp = Env::getRequest( 'timestamp' );
$aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
if ( strcmp( $signature, sha1( $aeskey . $timestamp ) ) != 0 ) {
    Env::iExit( 'error:sign error' );
}

// 接收信息处理
$result = trim( file_get_contents( "php://input" ), " \t\n\r" );
// 解析
if ( !empty( $result ) ) {
    $msg = CJSON::decode( $result, true );
    switch ( $msg['op'] ) {
        case 'getuser':
            $res = getUserList();
            break;
        case 'getdept':
            $res = getDepartmentList();
            break;
        case 'getuserallinfo':
            $res = getUserListAllInfo();
            break;
        case 'getbinding':
            $res = getBindingList();
            break;
        case 'set':
            $res = setBinding( $msg['data'] );
            break;
        case 'unbind':
            $res = setUnbind();
            break;
        case 'creatuser' :
            $res = setCreat( $msg['data'] );
            break;
        case 'creatdepartment':
            $res = setCreatDapartment( $msg['data'] );
            break;
        case 'verifywebsite':
            $res = verifyWebSite();
            break;
        default:
            $res = array( 'isSuccess' => false, 'msg' => '未知操作' );
            break;
    }
    Env::iExit( CJSON::encode( $res ) );
}

/**
 * 获取用户id以及用户真实姓名
 * @return array
 */
function getUserList() {
    User::model()->setSelect( 'uid,realname' );
    $users = User::model()->findUserByUid();
    return array(
        'isSuccess' => true,
        'data' => $users
    );
}

/**
 * 获取用户列表所有信息， 同步用户时调用
 * @return type
 */
function getUserListAllInfo() {
    $users = User::model()->fetchAllByUids( NULL, false );
    return array(
        'isSuccess' => true,
        'data' => $users
    );
}

/**
 * 获取部门列表数据,同步部门时调用
 * @return array
 */
function getDepartmentList() {
    $departments = array();
    $cache = Syscache::model()->fetchAllCache( 'department' );
    if ( !empty( $cache['department'] ) ) {
        $departments = $cache['department'];
    }
    return array(
        'isSuccess' => true,
        'data' => $departments
    );
}

/**
 * 获取绑定用户数组
 * @return array
 */
function getBindingList() {
    $bindings = UserBinding::model()->fetchAllByApp( 'co' );
    $users = array();
    if ( !empty( $bindings ) ) {
        foreach ( $bindings as $row ) {
            $user = User::model()->findByPk( $row['uid'] );
            if ( !empty( $user ) ) {
                $users[] = array(
                    'uid' => $row['uid'],
                    'bindvalue' => $row['bindvalue'],
                    'realname' => $user->realname,
                );
            }
        }
    }
    return array(
        'isSuccess' => true,
        'data' => $users
    );
}

/**
 * 设置绑定用户列表
 * @param array $list
 * @return array
 */
function setBinding( $list ) {
    //UserBinding::model()->deleteAllByAttributes( array( 'app' => 'co' ) );
    $count = 0;
    foreach ( $list as $row ) {
        //判断是否已经绑定,此处做了容错处理
        $data = array( 'uid' => $row['uid'], 'bindvalue' => $row['guid'], 'app' => 'co' );
        $checkbinding = UserBinding::model()->find( sprintf( "`uid` = '%s' AND `app` = 'co'", $row['uid'] ) );
        if ( empty( $checkbinding ) ) {
            $res = UserBinding::model()->add( $data );
        } else {
            $res = UserBinding::model()->modify( $checkbinding['id'], $data );
        }
        $res and $count++;
    }
    // 设置绑定标识
    if ( $count > 0 ) {
        Setting::model()->updateSettingValueByKey( 'cobinding', '1' );
    }
    return array( 'isSuccess' => true, 'data' => true );
}

/**
 * 解除绑定
 * @return
 */
function setUnbind() {
    UserBinding::model()->deleteAllByAttributes( array( 'app' => 'co' ) );
    Setting::model()->updateSettingValueByKey( 'cobinding', '0' );
    Setting::model()->updateSettingValueByKey( 'coinfo', '' );
    Setting::model()->updateSettingValueByKey( 'autosync', serialize( array( 'status' => 0, 'lastsynctime' => 0 ) ) );
    CacheModel::model()->deleteAll( "FIND_IN_SET( cachekey, 'cocreatelist,coremovelist,iboscreatelist,ibosremovelist,successinfo' )" );
    return array( 'isSuccess' => true );
}

/**
 * 创建并绑定用户
 * @param array $param
 * @return array
 * update by Sam 2015-08-24 <gzxgs@ibos.com.cn>
 */
function setCreat( $data ) {
    if ( !empty( $data ) ) {
        CoSync::CreateUser( $data ); //直接调用工具类执行创建用户，暂时不用理会返回信息
        //以下的那些错误或者成功的用户信息，其实目前并没有用到
        //Cache::model()->deleteAll( "FIND_IN_SET(cachekey,'cousers,couserfail,cousersuccess')" );
        //Cache::model()->add( array( 'cachekey' => 'cousers', 'cachevalue' => serialize( $return['data']['users'] ) ) );
        //Cache::model()->add( array( 'cachekey' => 'couserfail', 'cachevalue' => serialize( $return['data']['error'] ) ) );
        //Cache::model()->add( array( 'cachekey' => 'cousersuccess', 'cachevalue' => serialize( $return['data']['success'] ) ) ); // 成功同步的用户
    }
}

/**
 * 创建部门
 * @param type $coDepartmentList 添加部门列表
 * @author Sam
 * @time 2015-08-24 <gzxgs@ibos.com.cn>
 */
function setCreatDapartment( $coDepartmentList ) {
    //删除IBOS所有部门,推倒所有部门重新建立
    Department::model()->deleteAll();
    foreach ( $coDepartmentList as $department ) {
        Department::model()->add( $department );
    }
    //更新部门缓存,避免缓存问题
    Cache::update( array( 'department' ) );
}

/**
 * 验证当前项目环境是否可被外部访问
 * 告诉外部该接口的调用方式后，让对方调用这个接口
 * 如果能返回正确的数据则表示当前环境可被对方访问
 * @return string json 数据
 */
function verifyWebSite() {
    $result = array(
        'isSuccess' => TRUE,
        'msg' => '当前 IBOS 可被正常访问！',
    );
    Env::iExit( json_encode( $result ) );
}
