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
 * @version $Id$
 */

namespace application\modules\dashboard\controllers;

use application\core\controllers\Controller;
use application\core\model\Log;
use application\core\utils\Convert;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\main\components\CommonAttach;
use application\modules\main\utils\Main as MainUtil;
use application\modules\role\model\Role;
use application\modules\user\model\User;

class BaseController extends Controller
{

    protected $attahInfo = null;

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
    private $_adminType = false;

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
    public function getAssetUrl($module = '')
    {
        return parent::getAssetUrl('dashboard');
    }

    /**
     * 初始化后台管理所需标识与数组赋值
     * @return void
     */
    public function init()
    {
        $this->initBase();
        $this->checkAccess();
    }

    /**
     * 记录后台所有操作日志
     * @return void
     */
    public function beforeAction($action)
    {
        if (!Ibos::app()->user->isGuest) {
            $param = Convert::implodeArray(
                array('GET' => $_GET, 'POST' => $_POST), array('username', 'password', 'formhash')
            );
            $controller = $action->getController()->id;
            $action = $action->getId();
            $log = array(
                'user' => Ibos::app()->user->username,
                'ip' => Ibos::app()->setting->get('clientip'),
                'action' => $action,
                'param' => $param
            );
            $category = $controller . '.' . $action;
            Log::write($log, 'admincp', sprintf('module.dashboard.%s', $category));
        }
        return true;
    }

    /**
     * 跳转到后台登陆页
     * @final
     * @return void
     */
    protected function userLogin()
    {
        Ibos::app()->user->loginUrl = array($this->loginUrl);
        Ibos::app()->user->loginRequired();
    }

    /**
     * 初始化后台管理所需标识
     */
    public function initBase()
    {
        $this->useConfig = true;
        $this->errorParam = array('autoJump' => false, 'jumpLinksOptions' => array('首页' => $this->createUrl('index/index'),));
        $this->user = Ibos::app()->user->isGuest ? array() : User::model()->fetchByUid(Ibos::app()->user->uid);
        $this->_adminType = $this->checkAdministrator($this->user);
        $this->_sessionLimit = (int)(TIMESTAMP - $this->_sessionLife);
        $this->_cookieLimit = (int)(TIMESTAMP - $this->_cookieLife);
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
     * 后台图片上传
     */
    protected function imgUpload($fileArea, $inajax = false)
    {
        $upload = new CommonAttach($fileArea, 'dashboard');
        $upload->upload();
        if ($upload->getIsUpoad()) {
            $info = $upload->getUpload()->getAttach();
            $tableArray = $upload->updateAttach($info['aid']);
            $info['tableid'] = $tableArray[$info['aid']]['tableid'];
            $this->attahInfo = $info;
            $file = File::imageName($info['target']);
            if (!$inajax) {
                return $info['target'];
            } else {
                $this->ajaxReturn(array('url' => $file, 'path' => $info['target']));
            }
        } else {
            return false;
        }
    }

    protected function getAttachInfo()
    {
        return $this->attahInfo;
    }

    /**
     * 检查是否是管理员：1是超级管理员，2是普通管理员，0非管理员，-1未登录
     * @param array $user 当前登录用户数组
     * @return boolean
     */
    private function checkAdministrator(array $user)
    {
        if (!empty($user)) {
            $alreadyLogin = ((int)$user['uid'] > 0);
            $inAdminIdentity = ($user['isadministrator'] == 1);
            if ($alreadyLogin) {
                if ($inAdminIdentity) {
                    return 1;
                } else {
                    $allroleidS = $user['allroleid'];
                    $roleid = Ibos::app()->db->createCommand()
                        ->select('roleid')
                        ->from(Role::model()->tableName())
                        ->where(sprintf(" FIND_IN_SET( `roleid`, '%s' ) AND `roletype` = '%s' ", $allroleidS, Role::ADMIN_TYPE))
                        ->queryRow();
                    return !empty($roleid) ? 2 : 0;
                }
            }
            return -1;
        }
        return -1;
    }

    /**
     * 检查当前权限标识并视情况赋值
     * @return void
     */
    private function checkAccess()
    {
        if (isset($this->user['uid']) && ($this->user['uid'] == 0)) {
            // 未登录
            $this->_access = 0;
        } else {
            if ($this->_adminType > 0) {
                $lastactivity = MainUtil::getCookie('lastactivity');
                // 是否长时间无操作？
                $frozenTime = intval(TIMESTAMP - $lastactivity);
                if ($frozenTime < $this->_cookieLife) {
                    $this->_access = 1;
                    MainUtil::setCookie('lastactivity', TIMESTAMP);
                } else {
                    $this->_access = -1;
                }
                if ($this->_adminType == '1') {
                    $this->isFilterRoute = false;
                }
            } else {
                $this->_access = -1;
            }
        }
        if ($this->_access == 1) {
            Ibos::app()->session->update();
        } else {
            // 获取当前url请求,判断是否在可访问url列表内
            $requestUrl = Ibos::app()->getRequest()->getUrl();
            $loginUrl = Ibos::app()->getUrlManager()->createUrl($this->loginUrl);
            if (strpos($requestUrl, $loginUrl) !== 0) {
                return $this->userLogin();
            }
        }
    }

}
