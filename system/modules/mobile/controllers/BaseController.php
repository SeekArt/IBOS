<?php

/**
 * 移动端模块基础控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端模块基础控制器,用以验证移动端操作和权限判定
 * 
 * @package application.modules.mobile.componets
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: BaseController.php 4239 2014-09-25 14:06:17Z Aeolus $
 */

namespace application\modules\mobile\controllers;

use application\core\controllers\Controller;
use application\core\utils\IBOS;
use application\modules\main\model\Session;
use application\modules\user\model\User;

class BaseController extends Controller {

    const TIMESTAMP = TIMESTAMP;

    /**
     * 移动端模块不适用全局layout
     * @var boolean 
     */
    public $layout = false;

    /**
     * 默认控制器
     * @var string 
     */
    protected $defaultController = 'mobile/default/index';

    /**
     * 手机端登录页
     * @var string 
     */
    private $_loginUrl = 'mobile/default/login';

    /**
     * session
     * @var array 
     */
    private $_session = array();

    /**
     * 当前登录的用户数组
     * @var array 
     */
    private $_user = array();

    /**
     * 权限标识
     * @var integer 
     */
    private $_access = 0;

    /**
     * 默认的页面属性
     * @var array 
     */
    private $_attributes = array( 'uid' => 0 ,'upuid'=>0);
    protected $_extraAttributes = array();

    /**
     * 设置相对应属性值
     * @param string $name 
     * @param mixed $value
     */
    public function __set( $name, $value ) {
        if ( isset( $this->_attributes[$name] ) ) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set( $name, $value );
        }
    }

    /**
     * 获取对应属性值
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        if ( isset( $this->_attributes[$name] ) ) {
            return $this->_attributes[$name];
        } else {
            parent::__get( $name );
        }
    }

    /**
     * 检测$_attributes数组里的值是否存在
     * @param string $name
     * @return boolean
     */
    public function __isset( $name ) {
        if ( isset( $this->_attributes[$name] ) ) {
            return true;
        } else {
            parent::__isset( $name );
        }
    }

    /**
     * 初始化所需标识与数组赋值
     * @see checkAccess
     * @final
     * @return void
     */
    public function init() {
        $this->_attributes = array_merge( $this->_attributes, $this->_extraAttributes );
        if ( isset( IBOS::app()->user->uid ) ) {
            $this->uid = intval( IBOS::app()->user->uid );
        } else {
            $this->uid = 0;
        }
        $user = User::model()->fetchByUid( $this->uid );
        $this->_user = $user;
        $this->checkAccess();
    }

    /**
     * 获取当前uid
     * @return integer
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * 获取当前用户资料
     * @return array
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * 要求手机登陆
     * @final
     * @return void
     */
    public final function userLogin() {
        IBOS::app()->user->loginUrl = array( $this->_loginUrl );
        IBOS::app()->user->loginRequired();
    }

    /**
     * 检查当前权限标识并视情况赋值
     * @return void
     */
    private function checkAccess() {
        if ( !isset( $this->_user['uid'] ) || ($this->_user['uid'] == 0 ) ) { 
            // 未登录
            $this->_access = 0;
        } else {
            $this->_session = Session::model()->findByAttributes( array( 'uid' => $this->_user['uid'] ) );
            $this->_access = 1;
        }
    }

    /**
     * 获取后台管理权限标识码
     * @return integer
     */
    protected function getAccess() {
        return $this->_access;
    }

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     * @param string $routes
     * @return boolean
     */
    public function filterRoutes( $routes ) {
        return true;
    }

}
