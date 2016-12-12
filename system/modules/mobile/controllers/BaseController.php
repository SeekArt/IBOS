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
 * @version $Id: BaseController.php 7964 2016-08-22 08:29:16Z php_lxy $
 */

namespace application\modules\mobile\controllers;

use application\core\controllers\Controller;
use application\core\utils\Ibos;
use application\modules\main\model\Session;
use application\modules\role\utils\Auth;
use application\modules\user\model\User;

class BaseController extends Controller
{

    const TIMESTAMP = TIMESTAMP;

    /**
     * 移动端模块不适用全局layout
     * @var boolean
     */
    public $layout = false;

    /**
     * @var string 当前 controller 对应的模块
     * 备注：如果需要获取评论列表，则需要设置正确的模块名。
     */
    protected $_module = '';

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
    private $_attributes = array('uid' => 0, 'upuid' => 0);
    protected $_extraAttributes = array();

    /**
     * 设置相对应属性值
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (isset($this->_attributes[$name])) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * 获取对应属性值
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            parent::__get($name);
        }
    }

    /**
     * 检测$_attributes数组里的值是否存在
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this->_attributes[$name])) {
            return true;
        } else {
            parent::__isset($name);
        }
    }

    /**
     * 初始化所需标识与数组赋值
     * @see checkAccess
     * @final
     * @return void
     */
    public function init()
    {
        $this->_attributes = array_merge($this->_attributes,
            $this->_extraAttributes);
        if (isset(Ibos::app()->user->uid)) {
            $this->uid = intval(Ibos::app()->user->uid);
        } else {
            $this->uid = 0;
        }
        $user = User::model()->fetchByUid($this->uid);
        $this->_user = $user;
        $this->checkAccess();
        $this->checkAccessForRoute(); // 检查路由权限
        $this->setCorrectModuleName($this->_module);

    }

    /**
     * 获取当前uid
     * @return integer
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * 获取当前用户资料
     * @return array
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 要求手机登陆
     * @final
     * @return void
     */
    public final function userLogin()
    {
        Ibos::app()->user->loginUrl = array($this->_loginUrl);
        Ibos::app()->user->loginRequired();
    }

    /**
     * 检查当前权限标识并视情况赋值
     * @return void
     */
    private function checkAccess()
    {
        if (!isset($this->_user['uid']) || ($this->_user['uid'] == 0)) {
            // 未登录
            $this->_access = 0;
        } else {
            $this->_session = Session::model()->findByAttributes(array('uid' => $this->_user['uid']));
            $this->_access = 1;
        }
    }

    /**
     * 获取后台管理权限标识码
     * @return integer
     */
    protected function getAccess()
    {
        return $this->_access;
    }

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     * @param string $routes
     * @return boolean
     */
    public function filterRoutes($routes)
    {
        return true;
    }

    /**
     * 设置正确的模块名
     * @param $moduleName
     * @return
     */
    public function setCorrectModuleName($moduleName)
    {
        if (empty($moduleName)) {
            return Ibos::app()->setting->set('correctModuleName',
                Ibos::getCurrentModuleName());
        }

        return Ibos::app()->setting->set('correctModuleName', $moduleName);
    }


    /**
     * 返回路由映射表。如果需要实现权限验证，
     * 备注：需要在这里建立路由映射。
     *
     * @return array
     */
    public function routeMap()
    {
        return array();
    }

    /**
     * 检查路由权限
     */
    public function checkAccessForRoute()
    {
        $route = Ibos::app()->getUrlManager()->parseUrl(Ibos::app()->getRequest());
        $routeMap = $this->routeMap();
        // 将路由映射表的键和值全部改为小写
        $route = strtolower($route);
        $routeMap = array_change_key_case($routeMap, CASE_LOWER);
//        $routeMap = array_map('strtolower', $routeMap);

        // 存在路由映射规则，才做权限检查
        if (!array_key_exists($route, $routeMap)) {
            return true;
        }

        $route = $routeMap[$route];
        // 只支持 string 和 array
        $type = strtolower(gettype($route));
        if (!in_array($type, array('string', 'array'))) {
            throw new \UnexpectedValueException('映射规则的值只允许是 string 或 array');
        }
        $rules = $route;
        if (is_string($route)) {
            $rules = array($route);
        }

        foreach ($rules as $rule) {
            $check = Ibos::app()->user->checkAccess($rule,
                Auth::getParams($rule));
            if (false === $check) {
                return $this->ajaxReturn(array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('Permission denied')
                ));
            }
        }

        return true;

    }


}
