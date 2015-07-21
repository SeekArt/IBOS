<?php

use application\core\utils\Env;
use application\core\utils\String;
use application\modules\main\model\Setting;
use application\modules\message\core\BQQApi;
use application\modules\user\model\UserBinding;

// 根目录
define( 'PATH_ROOT', dirname( __FILE__ ) . '/../../' );
define( 'TIMESTAMP', time() );
define( 'YII_DEBUG', true );
$defines = PATH_ROOT . '/system/defines.php';
$yii = PATH_ROOT . '/library/yii.php';
$config = PATH_ROOT . '/system/config/common.php';

require_once ( $defines );
require_once ( $yii );
require_once '../login.php';
Yii::setPathOfAlias( 'application', PATH_ROOT . DIRECTORY_SEPARATOR . 'system' );
Yii::createApplication( 'application\core\components\Application', $config );
$value = Setting::model()->fetchSettingValueByKey( 'im' );
$im = unserialize( $value );
$imCfg = $im['qq'];
$cid = Env::getRequest( 'company_id' );
$openId = Env::getRequest( 'open_id' );
$hashskey = Env::getRequest( 'hashskey' );
$hashkey = Env::getRequest( 'hashkey' );
$returnurl = Env::getRequest( 'returnurl' );

if ( empty( $openId ) || empty( $hashskey ) || empty( $cid ) ) {
    exit( '参数错误' );
}

$uid = UserBinding::model()->fetchUidByValue( String::filterCleanHtml( $openId ), 'bqq' );
if ( $uid ) {
    $properties = array(
        'company_id' => $cid,
        'company_token' => $imCfg['token'],
        'app_id' => $imCfg['appid'],
        'client_ip' => Env::getClientIp()
    );
    $api = new BQQApi( $properties );
    $status = $api->getVerifyStatus( array( 'open_id' => $openId, 'hashskey' => $hashskey ) );
    if ( $status['ret'] == 0 ) {
        dologin( $uid, 'bqqsso' );
        if ( $returnurl == 'index' ) {
            header( 'Location: ../../index.php', true );
        } else {
            $url = parse_url( $returnurl );
            if ( isset( $url['scheme'] ) ) {
                header( 'Location:' . $returnurl, true );
            } else {
                header( 'Location:../../' . $returnurl, true );
            }
        }
    } else {
        Env::iExit( $status['msg'] );
    }
} else {
    Env::iExit( '该用户未绑定企业QQ' );
}
