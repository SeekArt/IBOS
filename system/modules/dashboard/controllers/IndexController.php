<?php

namespace application\modules\dashboard\controllers;

use application\core\model\Module;
use application\core\utils\Cache;
use application\core\utils\Convert;
use application\core\utils\Database;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\main\model\Attachment;
use application\modules\main\model\Setting;
use CHtml;

/**
 * 后台索引页文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */

/**
 * 后台索引控制器
 *
 * @package application.modules.dashboard.controllers
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: IndexController.php 6765 2016-04-06 03:27:45Z tanghang $
 */
class IndexController extends BaseController {

    const SECURITY_URL = 'http://www.ibos.com.cn/security.php';

    /**
     * 后台首页
     * @todo 附件统计
     * @todo 授权信息读取
     */
    public function actionIndex() {
        // 系统信息
        $systemInfo = Env::getSystemInfo();
        // 数据库大小
        $databaseSize = Database::getDatabaseSize();
        list($dataSize, $dataUnit) = explode( ' ', $databaseSize );
        // 系统关闭
        $appClosed = Setting::model()->fetchSettingValueByKey( 'appclosed' );
        // 新版本提示升级
        $newVersion = IBOS::app()->setting->get( 'newversion' );
        // 安全信息获取URL
        $getSecurityUrl = IBOS::app()->urlManager->createUrl( 'dashboard/index/getsecurity' );
        // 安装日期
        $mainModule = Module::model()->fetchByPk( 'main' );
        // 授权信息
        $authkey = IBOS::app()->setting->get( 'config/security/authkey' );
        $unit = Setting::model()->fetchSettingValueByKey( 'unit' );
        if ( isset( $_GET['attachsize'] ) ) {
            $attachSize = Attachment::model()->getTotalFilesize();
            $attachSize = is_numeric( $attachSize ) ? Convert::sizeCount( $attachSize ) : IBOS::lang( 'Unknow' );
        } else {
            $attachSize = '';
        }
        $data = array(
            'unit' => StringUtil::utf8Unserialize( $unit ),
            'sys' => $systemInfo,
            'dataSize' => $dataSize,
            'dataUnit' => $dataUnit,
            'appClosed' => $appClosed,
            'newVersion' => $newVersion,
            'getSecurityUrl' => $getSecurityUrl,
            'installDate' => $mainModule['installdate'],
            'authkey' => $authkey,
            'attachSize' => $attachSize
        );
        $this->render( 'index', $data );
    }

    /**
     * 切换系统开关状态
     * @return void
     */
    public function actionSwitchstatus() {
        if ( IBOS::app()->getRequest()->getIsAjaxRequest() ) {
            $val = Env::getRequest( 'val' );
            $result = Setting::model()->updateSettingValueByKey( 'appclosed', (int) $val );
            Cache::update( array( 'setting' ) );
            return $this->ajaxReturn( array( 'IsSuccess' => $result ), 'json' );
        }
    }

    /**
     * 获取远程服务器安全提示
     * @return void
     */
    public function actionGetSecurity() {
        if ( IBOS::app()->getRequest()->getIsAjaxRequest() ) {
            $return = File::fileSockOpen( self::SECURITY_URL, 0, 'charset=' . CHARSET );
            $this->ajaxReturn( $return, 'EVAL' );
        }
    }

    /**
     * 授权码写入文件
     */
    public function actionLicense() {
        if ( Env::submitCheck( 'formhash' ) ) {
            $licensekey = CHtml::encode( Env::getRequest( 'licensekey' ) );
            $filename = PATH_ROOT . '/data/licence.key';
            @file_put_contents( $filename, $licensekey );
            $license = IBOS::app()->licence;
            $license->init();
            $licenseInfo = $license->getLicence();
            $iboscloud = IBOS::app()->setting->get( 'setting/iboscloud' );
            $iboscloud['appid'] = isset( $licenseInfo['appid'] ) ? $licenseInfo['appid'] : '';
            $iboscloud['secret'] = isset( $licenseInfo['secret'] ) ? $licenseInfo['secret'] : '';
            Setting::model()->updateSettingValueByKey( 'iboscloud', serialize( $iboscloud ) );
            Cache::update( 'setting' );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        }
    }

}
