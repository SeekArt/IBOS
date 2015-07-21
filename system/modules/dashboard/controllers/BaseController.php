<?php

/**
 * 后台模块基础控制器文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 后台模块基础控制器,用以验证操作是否超时。后台所有的控制器都必须继承于此控制器
 * 
 * @package application.modules.dashboard.componets
 * @author banyan <banyan@ibos.com.cn>
 * @version $Id: BaseController.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\dashboard\controllers;

use application\core\controllers\Controller;
use application\core\model\Log;
use application\core\utils\Convert;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\main\utils\Main as MainUtil;
use application\modules\user\model\User;

class BaseController extends Controller {

    /**
     * @var boolean 
     */
    public $layout = 'application.modules.dashboard.views.layouts.dashboard';

    /**
     * 当前登录后台的用户数组
     * @var array 
     */
    protected $user = array();

    /**
     * 后台用户登录路由
     * @var string 
     */
    protected $loginUrl = 'dashboard/default/login';

    /**
     * 是否有管理权限标识
     * @var boolean 
     */
    private $_isAdministrator = false;

    /**
     * session生命周期,默认20分钟
     * @var integer 
     */
    private $_sessionLife = 1200;

    /**
     * 当前时间减去声明周期后的偏移值
     * @var integer 
     */
    private $_sessionLimit = 0;

    /**
     * cookie生命周期,默认20分钟
     * @var integer 
     */
    private $_cookieLife = 1200;

    /**
     * 当前时间减去声明周期后的偏移值
     * @var integer 
     */
    private $_cookieLimit = 0;

    /**
     * 权限标识
     * @var integer 
     */
    private $_access = 0;

    /**
     * 为其他模块继承于该控制器提供一个预置方法
     * @param string $module
     * @return type
     */
    public function getAssetUrl( $module = '' ) {
        return parent::getAssetUrl( 'dashboard' );
    }

    /**
     * 初始化后台管理所需标识与数组赋值
     * @return void
     */
    public function init() {
        $this->user = IBOS::app()->user->isGuest ? array() : User::model()->fetchByUid( IBOS::app()->user->uid );
        $this->_isAdministrator = $this->checkAdministrator( $this->user );
        $this->_sessionLimit = (int) ( TIMESTAMP - $this->_sessionLife );
        $this->_cookieLimit = (int) ( TIMESTAMP - $this->_cookieLife );
        $this->checkAccess();
    }

    /**
     * 记录后台所有操作日志
     * @return void
     */
    public function beforeAction( $action ) {
        if ( !IBOS::app()->user->isGuest ) {
            $param = Convert::implodeArray(
                            array( 'GET' => $_GET, 'POST' => $_POST ), array( 'username', 'password', 'formhash' )
            );
            $action = $action->getId();
            $log = array(
                'user' => IBOS::app()->user->username,
                'ip' => IBOS::app()->setting->get( 'clientip' ),
                'action' => $action,
                'param' => $param
            );
            Log::write( $log, 'admincp', sprintf( 'module.dashboard.%s', $action ) );
        }
        return true;
    }

    /**
     * 跳转到后台登陆页
     * @final
     * @return void
     */
    protected function userLogin() {
        IBOS::app()->user->loginUrl = array( $this->loginUrl );
        IBOS::app()->user->loginRequired();
    }

    /**
     * 获取后台管理权限标识码
     * @return integer
     */
    protected function getAccess() {
        return $this->_access;
    }

    /**
     * 后台图片上传
     */
    protected function imgUpload( $fileArea, $inajax = false ) {
        $_FILES[$fileArea]['name'] = String::iaddSlashes( urldecode( $_FILES[$fileArea]['name'] ) );
        $file = $_FILES[$fileArea];
        $upload = File::getUpload( $file, 'dashboard' );
        if ( $upload->save() ) {
            $info = $upload->getAttach();
            $file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
            if ( !$inajax ) {
                return $file;
            } else {
                $this->ajaxReturn( array( 'url' => $file ) );
            }
        } else {
            return false;
        }
    }

    /**
     * 检查是否是管理员
     * @param array $user 当前登录用户数组
     * @return boolean
     */
    private function checkAdministrator( array $user ) {
        if ( !empty( $user ) ) {
            $alreadyLogin = ((int) $user['uid'] > 0 );
            $inAdminIdentity = ( $user['isadministrator'] == 1 );
            if ( $alreadyLogin && $inAdminIdentity ) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查当前权限标识并视情况赋值
     * @return void
     */
    private function checkAccess() {
        if ( isset( $this->user['uid'] ) && ($this->user['uid'] == 0 ) ) {
            // 未登录
            $this->_access = 0;
        } else {
            if ( $this->_isAdministrator ) {
                $lastactivity = MainUtil::getCookie( 'lastactivity' );
                // 是否长时间无操作？
                $frozenTime = intval( TIMESTAMP - $lastactivity );
                if ( $frozenTime < $this->_cookieLife ) {
                    $this->_access = 1;
                    MainUtil::setCookie( 'lastactivity', TIMESTAMP );
                } else {
                    $this->_access = -1;
                }
            } else {
                $this->_access = -1;
            }
        }
        if ( $this->_access == 1 ) {
            IBOS::app()->session->update();
        } else {
            // 获取当前url请求,判断是否在可访问url列表内
            $requestUrl = IBOS::app()->getRequest()->getUrl();
            $loginUrl = IBOS::app()->getUrlManager()->createUrl( $this->loginUrl );
            if ( strpos( $requestUrl, $loginUrl ) !== 0 ) {
                $this->userLogin();
            }
        }
    }

}
