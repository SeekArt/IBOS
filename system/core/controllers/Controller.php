<?php

/**
 * 继承到CController的 IBOS controller文件.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
/**
 * 全局控制器必须继承自CController
 *
 * @package application.core.controllers
 * @version $Id: controller.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\controllers;

use application\core\utils\Ibos;
use application\modules\main\utils\Main as MainUtil;
use application\modules\role\model\AuthItem;
use application\modules\role\utils\Auth;
use CController;

class Controller extends CController
{

    /**
     * 是否强制过滤路由，用以设置超级管理员的权限
     *
     * @var type
     */
    public $isFilterRoute = true;

    /**
     * true：使用模块里的权限配置去验证权限，不在配置里的不做权限判断
     * false：使用过滤列表去验证权限，不在过滤列表里的不做权限判断
     *
     * @var type
     */
    public $useConfig = false;

    /**
     * 设置错误跳转页面的参数
     */
    public $errorParam = array('autoJump' => true, 'timeout' => 3);

    /**
     * 布局类型
     *
     * @var string
     */
    public $layout = '';

    /**
     * 默认不进行权限验证的模块
     *
     * @var type
     */
    private $_notAuthModule = array('main', 'user', 'message', 'weibo');

    /**
     * 当前模块可访问的静态资源文件路径
     *
     * @var string
     */
    private $_assetUrl = '';

    public function __construct($id, $module = null)
    {
        Ibos::app()->setting->set('module', $module->getId());
        parent::__construct($id, $module);
    }

    /**
     * 检测是否需要更改密码
     */
    public function init()
    {
        parent::init();
        if (!Ibos::app()->user->isGuest && Ibos::app()->user->isNeedReset && !Ibos::app()->request->isAjaxRequest) {
            Ibos::app()->request->redirect(Ibos::app()->createUrl('user/default/reset'));
        }
    }

    /**
     * 错误异常处理
     *
     * @return void
     */
    public function actionError()
    {
        $error = Ibos::app()->errorHandler->error;
        if ($error) {
            $isAjaxRequest = Ibos::app()->request->getIsAjaxRequest();
            $this->error($error['message'], '', array(), $isAjaxRequest);
        }
    }

    /**
     * 覆盖父类渲染视图方法，在视图变量处增加静态资源路径，合并语言包文件方法
     *
     * @param string $view @see CController::render
     * @param array $data @see CController::render
     * @return @see CController::render
     */
    public function render($view, $data = null, $return = false, $langSources = array())
    {
        if (is_null($data)) {
            $data = array();
        }
        Ibos::app()->setting->set('pageTitle', $this->getPageTitle());
        Ibos::app()->setting->set('breadCrumbs', $this->getPageState('breadCrumbs', array()));
        $this->setPageState('breadCrumbs', null);
        !isset($data['assetUrl']) && $data['assetUrl'] = $this->getAssetUrl();
        $data['lang'] = Ibos::getLangSources($langSources);
        $data['language'] = Ibos::app()->getLanguage();
        return parent::render($view, $data, $return);
    }

    public function ajaxReturn($data, $type = '')
    {
        return Ibos::app()->response->ajaxReturn($data, $type);
    }

    public function ajaxBaseReturn($isSuccess, array $data, $msg = '', array $extraArgs = array(), $type = '')
    {
        return Ibos::app()->response->ajaxBaseReturn($isSuccess, $data, $msg, $extraArgs, $type);
    }


    /**
     * 操作错误跳转的快捷方法
     *
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param array $params 输出页面配置数组
     * <pre>
     *    $params = array(
     *        // 操作信息类型【success | error | info】 默认为success
     *        'messageType' => 'success',
     *        // 是否自动跳转 默认为true
     *        'autoJump' => true,
     *        // 等待自动跳转时间，只有在autoJump为true时才有效
     *        'timeout' => 3,
     *        // 供给选择的跳转链接地址，最多三个。只有在autoJump=false时才有效
     *        'jumpLinksOptions' => array( '地址名1' => 'url1','地址名2' => 'url2' )
     *        // 额外js代码
     *        'script' = 'function ddd(){}',
     *    );
     * </pre>
     * @param boolean $ajax 是否为Ajax方式
     * @return void
     */
    public function error($message = '', $jumpUrl = '', $params = array(), $ajax = false)
    {
        $this->showMessage($message, $jumpUrl, $params, 0, $ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     *
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param array $params 输出页面配置数组
     * <pre>
     *    $params = array(
     *        // 操作信息类型【success | error | info】 默认为success
     *        'messageType' => 'success',
     *        // 是否自动跳转 默认为true
     *        'autoJump' => true,
     *        // 等待自动跳转时间，只有在autoJump为true时才有效
     *        'timeout' => 3,
     *        // 供给选择的跳转链接地址，最多三个。只有在autoJump=false时才有效
     *        'jumpLinksOptions' => array( '地址名1' => 'url1','地址名2' => 'url2' )
     *        // 额外js代码
     *        'script' = 'function ddd(){}',
     *    );
     * </pre>
     * @param boolean $ajax 是否为Ajax方式
     * @return void
     */
    public function success($message = '', $jumpUrl = '', $params = array(), $ajax = false)
    {
        $this->showMessage($message, $jumpUrl, $params, 1, $ajax);
    }

    /**
     * 输出信息
     *
     * @param string $message 要输出的信息
     * @param string $jumpUrl 页面跳转地址
     * @param array $params 输出页面配置数组
     * <pre>
     *    $params = array(
     *        // 操作信息类型【success | error | info】 默认为success
     *        'messageType' => 'success',
     *        // 是否自动跳转 默认为true
     *        'autoJump' => true,
     *        // 等待自动跳转时间，只有在autoJump为true时才有效
     *        'timeout' => 3,
     *        // 供给选择的跳转链接地址，最多三个。只有在autoJump=false时才有效
     *        'jumpLinksOptions' => array( '地址名1' => 'url1','地址名2' => 'url2' )
     *        // 额外js代码
     *        'script' = 'function ddd(){}',
     *    );
     * </pre>
     * @param integer $status 快捷处理信息状态，1为成功，0为错误，目前只提供了这两种方式
     * @param boolean $ajax 是否为Ajax方式
     * @return void
     */
    public function showMessage($message, $jumpUrl = '', $params = array(), $status = 1, $ajax = false)
    {

        // AJAX提交方式的处理
        if ($ajax === true || Ibos::app()->request->getIsAjaxRequest()) {
            $data = is_array($ajax) ? $ajax : array();
            $data['msg'] = $message;
            $data['isSuccess'] = $status;
            $data['url'] = $jumpUrl;
            $this->ajaxReturn($data);
        }
        $params['message'] = $message;
        // autoJump : 是否自动跳转
        $params['autoJump'] = isset($params['autoJump']) ? $params['autoJump'] : true;
        // jumpLinksOptions : 不自动跳转的情况下，供选择跳转的url
        if (!$params['autoJump']) {
            $params['jumpLinksOptions'] = isset($params['jumpLinksOptions']) && is_array($params['jumpLinksOptions']) ?
                $params['jumpLinksOptions'] : array();
        } else {
            $params['jumpLinksOptions'] = array();
        }
        // 跳转url
        if (!empty($jumpUrl)) {
            $params['jumpUrl'] = $jumpUrl;
        } else {
            $params['jumpUrl'] = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
        }
        // timeout ：自动跳转超时时间
        if (!isset($params['timeout'])) {
            if ($status) {
                // 成功操作后默认停留1秒
                $params['timeout'] = 1;
            } else {
                // 发生错误时候默认停留3秒
                $params['timeout'] = 5;
            }
        }
        // 提示标题
        $params['msgTitle'] = $status ? Ibos::lang('Operation successful', 'message') :
            Ibos::lang('Operation failure', 'message');
        // 如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if (isset($params['closeWin'])) {
            $params['jumpUrl'] = 'javascript:window.close();';
        }
        // 自带脚本执行
        $params['script'] = isset($params['script']) ? trim($params['script']) : null;
        // 消息类型
        if (!isset($params['messageType'])) {
            $params['messageType'] = $status ? 'success' : 'error';
        }
        if ($status) {
            MainUtil::setCookie('globalRemind', urlencode($params['message']), 30);
            MainUtil::setCookie('globalRemindType', $params['messageType'], 30);
            $this->redirect($params['jumpUrl']);
        } else {
            //判断jumpUrl是否为空，如果是空的话在错误提示页面会不停地重复跳转
            //默认跳转回到网站首页
            if (empty($params['jumpUrl'])) {
                $params['jumpUrl'] = '/';
            }
            // 渲染视图
            $viewPath = $basePath = Ibos::app()->getViewPath();
            $viewFile = $this->resolveViewFile('showMessage', $viewPath, $basePath);
            $output = $this->renderFile($viewFile, $params, true);
            echo $output;
        }
        exit();
    }

    /**
     * 获取控制器所属模块的静态资源发布文件夹
     *
     * @param String $module 模块名
     * @return String 文件夹路径
     */
    public function getAssetUrl($module = '')
    {
        if (empty($this->_assetUrl)) {
            if (empty($module)) {
                $module = Ibos::getCurrentModuleName();
            }
            $this->_assetUrl = Ibos::app()->assetManager->getAssetsUrl($module);
        }
        return $this->_assetUrl;
    }

    /**
     * 设置title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        Ibos::app()->setting->set('title', $title);
    }

    /**
     * 强制执行 验证模块方法，给出模块在notAuthModule数组里的都不进行后续权限验证
     *
     * @param string $module
     * @final 子类不应该重写这个方法
     * @return boolean
     */
    public final function filterNotAuthModule($module)
    {
        return in_array($module, $this->_notAuthModule);
    }

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     *
     * @param string $routes
     * @return boolean true 不验证该路由
     */
    public function filterRoutes($routes)
    {
        return false;
    }

    /**
     * 当前用户是否有访问某个路由的权限
     *
     * @return bool
     */
    public function checkRouteAccess($routes)
    {
        // 创建对应的控制器
        $ca = Ibos::app()->createController($routes);
        list($controller, $actionId) = $ca;

        if (method_exists($controller, 'initBase')) {
            $controller->initBase();
        } else {
            $controller->init();
        }
        $module = $controller->getModule()->getId();
        // step1
        if (!$controller->filterNotAuthModule($module)) {
            $routes = strtolower($controller->getUniqueId() . '/' . $actionId);
            if ($controller->isFilterRoute) {
                $check = false;
                // step2：是否使用config里的配置路由去验证
                // 当useConfig被设置成true时，只有在config里设置的才会验证
                // 当useConfig被设置成false时，将会通过filterRoutes去过滤不需要验证的route
                if (!$controller->useConfig) {
                    $check = !$controller->filterRoutes($routes) ? true : false;
                } else {
                    $check = AuthItem::model()->checkIsInByRoute($routes) ? true : false;
                }
                if (true === $check) {
                    // step3
                    if (!Ibos::app()->user->checkAccess($routes, Auth::getParams($routes))) {
                        // 没有权限
                        return false;
                    }
                }
            }
        }
        return true;
    }

}
