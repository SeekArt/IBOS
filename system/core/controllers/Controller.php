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
 * @package application.core.controllers
 * @version $Id: controller.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\controllers;

use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\main\utils\Main as MainUtil;
use CController;
use CJSON;

class Controller extends CController {

    /**
     * 默认Jsonp回调函数
     */
    const DEFAULT_JSONP_HANDLER = 'jsonpReturn';

    /**
     * 布局类型
     * @var string 
     */
    public $layout = '';

    /**
     * 默认不进行权限验证的模块
     * @var type 
     */
    private $_notAuthModule = array( 'main', 'user', 'dashboard', 'message', 'weibo' );

    /**
     * 当前模块可访问的静态资源文件路径
     * @var string 
     */
    private $_assetUrl = '';

    public function __construct( $id, $module = null ) {
        IBOS::app()->setting->set( 'module', $module->getId() );
        parent::__construct( $id, $module );
    }

    /**
     * 检测是否需要更改密码
     */
    public function init() {
        parent::init();
        if ( !IBOS::app()->user->isGuest && IBOS::app()->user->isNeedReset && !IBOS::app()->request->isAjaxRequest ) {
            IBOS::app()->request->redirect( IBOS::app()->createUrl( 'user/default/reset' ) );
        }
    }

    /**
     * 错误异常处理
     * @return void 
     */
    public function actionError() {
        $error = IBOS::app()->errorHandler->error;
        if ( $error ) {
            $isAjaxRequest = IBOS::app()->request->getIsAjaxRequest();
            $this->error( $error['message'], '', array(), $isAjaxRequest );
        }
    }

    /**
     * 覆盖父类渲染视图方法，在视图变量处增加静态资源路径，合并语言包文件方法
     * @param string $view @see CController::render
     * @param array $data @see CController::render
     * @return @see CController::render
     */
    public function render( $view, $data = null, $return = false, $langSources = array() ) {
        if ( is_null( $data ) ) {
            $data = array();
        }
        IBOS::app()->setting->set( 'pageTitle', $this->getPageTitle() );
        IBOS::app()->setting->set( 'breadCrumbs', $this->getPageState( 'breadCrumbs', array() ) );
        $this->setPageState( 'breadCrumbs', null );
        !isset( $data['assetUrl'] ) && $data['assetUrl'] = $this->getAssetUrl();
        $data['lang'] = IBOS::getLangSources( $langSources );
		$data['language'] = IBOS::app()->getLanguage();
        return parent::render( $view, $data, $return );
    }

    /**
     * Ajax方式返回数据到客户端
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    public function ajaxReturn( $data, $type = '' ) {
        if ( empty( $type ) ) {
            $type = 'json';
        }
        
        switch ( strtoupper( $type ) ) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header( 'Content-Type:application/json; charset=' . CHARSET );
                exit( CJSON::encode( $data ) );
                break;
            case 'XML' :
                // 返回xml格式数据
                header( 'Content-Type:text/xml; charset=' . CHARSET );
                exit( xml_encode( $data ) );
                break;
            case 'JSONP':
                // 返回JSONP数据格式到客户端 包含状态信息
                header( 'Content-Type:text/html; charset=' . CHARSET );
                $handler = isset( $_GET['callback'] ) ? $_GET['callback'] : self::DEFAULT_JSONP_HANDLER;
                exit( $handler . '(' . (!empty( $data ) ? CJSON::encode( $data ) : '') . ');' );
                break;
            case 'EVAL' :
                // 返回可执行的js脚本
                header( 'Content-Type:text/html; charset=' . CHARSET );
                exit( $data );
                break;
            default :
                exit( $data );
                break;
        }
    }

    /**
     * 操作错误跳转的快捷方法
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param array $params	 输出页面配置数组
     * <pre>
     * 	$params = array(
     * 		// 操作信息类型【success | error | info】 默认为success
     * 		'messageType' => 'success',	
     * 		// 是否自动跳转 默认为true
     * 		'autoJump' => true,			
     * 		// 等待自动跳转时间，只有在autoJump为true时才有效
     * 		'timeout' => 3,				
     * 		// 供给选择的跳转链接地址，最多三个。只有在autoJump=false时才有效
     * 		'jumpLinksOptions' => array( '地址名1' => 'url1','地址名2' => 'url2' )
     * 		// 额外js代码
     * 		'script' = 'function ddd(){}', 
     * 	);
     * </pre>
     * @param boolean $ajax 是否为Ajax方式
     * @return void
     */
    public function error( $message = '', $jumpUrl = '', $params = array(), $ajax = false ) {
        $this->showMessage( $message, $jumpUrl, $params, 0, $ajax );
    }

    /**
     * 操作成功跳转的快捷方法
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param array $params	 输出页面配置数组
     * <pre>
     * 	$params = array(
     * 		// 操作信息类型【success | error | info】 默认为success
     * 		'messageType' => 'success',	
     * 		// 是否自动跳转 默认为true
     * 		'autoJump' => true,			
     * 		// 等待自动跳转时间，只有在autoJump为true时才有效
     * 		'timeout' => 3,				
     * 		// 供给选择的跳转链接地址，最多三个。只有在autoJump=false时才有效
     * 		'jumpLinksOptions' => array( '地址名1' => 'url1','地址名2' => 'url2' )
     * 		// 额外js代码
     * 		'script' = 'function ddd(){}', 
     * 	);
     * </pre>
     * @param boolean $ajax 是否为Ajax方式
     * @return void
     */
    public function success( $message = '', $jumpUrl = '', $params = array(), $ajax = false ) {
        $this->showMessage( $message, $jumpUrl, $params, 1, $ajax );
    }

    /**
     * 输出信息
     * @param string $message 要输出的信息
     * @param string $jumpUrl 页面跳转地址
     * @param array $params	 输出页面配置数组
     * <pre>
     * 	$params = array(
     * 		// 操作信息类型【success | error | info】 默认为success
     * 		'messageType' => 'success',	
     * 		// 是否自动跳转 默认为true
     * 		'autoJump' => true,			
     * 		// 等待自动跳转时间，只有在autoJump为true时才有效
     * 		'timeout' => 3,				
     * 		// 供给选择的跳转链接地址，最多三个。只有在autoJump=false时才有效
     * 		'jumpLinksOptions' => array( '地址名1' => 'url1','地址名2' => 'url2' )
     * 		// 额外js代码
     * 		'script' = 'function ddd(){}', 
     * 	);
     * </pre>
     * @param integer $status 快捷处理信息状态，1为成功，0为错误，目前只提供了这两种方式
     * @param boolean $ajax 是否为Ajax方式
     * @return void 
     */
    public function showMessage( $message, $jumpUrl = '', $params = array(), $status = 1, $ajax = false ) {
		
        // AJAX提交方式的处理
        if ( $ajax === true || IBOS::app()->request->getIsAjaxRequest() ) {
            $data = is_array( $ajax ) ? $ajax : array();
            $data['msg'] = $message;
            $data['isSuccess'] = $status;
            $data['url'] = $jumpUrl;
            $this->ajaxReturn( $data );
        }
        $params['message'] = $message;
        // autoJump : 是否自动跳转
        $params['autoJump'] = isset( $params['autoJump'] ) ? $params['autoJump'] : true;
        // jumpLinksOptions : 不自动跳转的情况下，供选择跳转的url
        if ( !$params['autoJump'] ) {
            $params['jumpLinksOptions'] = isset( $params['jumpLinksOptions'] ) && is_array( $params['jumpLinksOptions'] ) ?
                    $params['jumpLinksOptions'] : array();
        } else {
            $params['jumpLinksOptions'] = array();
        }
        // 跳转url
        if ( !empty( $jumpUrl ) ) {
            $params['jumpUrl'] = $jumpUrl;
        } else {
            $params['jumpUrl'] = isset( $_SERVER["HTTP_REFERER"] ) ? $_SERVER["HTTP_REFERER"] : '';
        }
        // timeout ：自动跳转超时时间
        if ( !isset( $params['timeout'] ) ) {
            if ( $status ) {
                // 成功操作后默认停留1秒
                $params['timeout'] = 1;
            } else {
                // 发生错误时候默认停留3秒
                $params['timeout'] = 5;
            }
        }
        // 提示标题
        $params['msgTitle'] = $status ? IBOS::lang( 'Operation successful', 'message' ) :
                IBOS::lang( 'Operation failure', 'message' );
        // 如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if ( isset( $params['closeWin'] ) ) {
            $params['jumpUrl'] = 'javascript:window.close();';
        }
        // 自带脚本执行
        $params['script'] = isset( $params['script'] ) ? trim( $params['script'] ) : null;
        // 消息类型
        if ( !isset( $params['messageType'] ) ) {
            $params['messageType'] = $status ? 'success' : 'error';
        }
		
        if ( $status ) {
            MainUtil::setCookie( 'globalRemind', urlencode( $params['message'] ), 30);
            MainUtil::setCookie( 'globalRemindType', $params['messageType'], 30);
//			var_dump( urldecode( MainUtil::getCookie( 'globalRemind')) );
//			var_dump( MainUtil::getCookie( 'globalRemindType'));die;
            $this->redirect( $params['jumpUrl'] );
        } else {
            //15-7-27 下午1:51 gzdzl
            //判断jumpUrl是否为空，如果是空的话在错误提示页面会不停地重复跳转
            //默认跳转回到网站首页
            if ( empty( $params['jumpUrl'] ) ) {
                $params['jumpUrl'] = '/';
            }//15-7-27 下午1:51 gzdzl
            // 渲染视图
            $viewPath = $basePath = IBOS::app()->getViewPath();
            $viewFile = $this->resolveViewFile( 'showMessage', $viewPath, $basePath );
            $output = $this->renderFile( $viewFile, $params, true );
            echo $output;
        }
        exit();
    }

    /**
     * 获取控制器所属模块的静态资源发布文件夹
     * @param String $module 模块名
     * @return String 文件夹路径
     */
    public function getAssetUrl( $module = '' ) {
        if ( empty( $this->_assetUrl ) ) {
            if ( empty( $module ) ) {
                $module = IBOS::getCurrentModuleName();
            }
            $this->_assetUrl = IBOS::app()->assetManager->getAssetsUrl( $module );
        }
        return $this->_assetUrl;
    }

    /**
     * 设置title
     * @param string $title
     */
    public function setTitle( $title ) {
        IBOS::app()->setting->set( 'title', $title );
    }

    /**
     * 强制执行 验证模块方法，给出模块在notAuthModule数组里的都不进行后续权限验证
     * @param string $module
     * @final 子类不应该重写这个方法
     * @return boolean
     */
    public final function filterNotAuthModule( $module ) {
        return in_array( $module, $this->_notAuthModule );
    }

    /**
     * ICAPPLICATION组件会调用各控制器的此方法进行验证，子类可重写这个实现各自的验证
     * 规则
     * @param string $routes
     * @return boolean
     */
    public function filterRoutes( $routes ) {
        return false;
    }

}
