<?php

/**
 * Ibos 应用程序组件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
/**
 * 初始化Ibos Application,模块及分发控制器
 * @package application.core.components
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\components;

use application\core\model\Module;
use application\core\utils\IBOS;
use application\modules\role\utils\Auth;
use CAction;
use CController;
use CEvent;
use CWebApplication;

class Application extends CWebApplication {

    /**
     * 已安装的模块
     * @var array 
     */
    private $_enabledModule = array();

    /**
     * 准备初始化前的处理
     */
    protected function preinit() {
        parent::preinit();
        // 检查安装
        if ( !is_file( PATH_ROOT . '/data/install.lock' ) ) {
            header( 'Location:./install/' );
            exit();
        }
    }

    /**
     * 初始化方法：设置授权，配置所有已安装的模块
     * @return void
     */
    protected function init() {
        if ( !defined( 'IN_DEBUG' ) ) {
            $this->_enabledModule = Module::model()->fetchAllEnabledModule();
            foreach ( $this->getEnabledModule() as $module ) {
                $config = json_decode( $module['config'], true );
                if ( isset( $config['behaviors'] ) ) {
                    $this->attachBehaviors( $config['behaviors'] );
                }
                if ( isset( $config['config'] ) ) {
                    parent::configure( $config['config'] );
                }
            }
        }
        parent::init();
    }

    /**
     * 重写配置方法实现平台引擎驱动
     * @param array $config
     */
    public function configure( $config ) {
        // 初始化ENGINE定义的引擎驱动
        $engineClass = 'application\core\engines\\' . ( ucfirst( strtolower( ENGINE ) ) );
        $engine = new $engineClass( $config );
        IBOS::setEngine( $engine );
        parent::configure( $engine->getEngineConfig() );
    }

    /**
     * 执行Action前的动作。用于权限验证
     * step1：强制执行不验证模块的判断
     * step2：调用各自控制器定义的过滤路由方法，返回false表示验证不通过
     * step3：调用authManager组件进行路由的验证
     * @param CController $controller 控制器对象
     * @param CAction $action 动作对象
     * @return mixed
     */
    public function beforeControllerAction( $controller, $action ) {
        $module = $controller->getModule()->getId();
        // step1
        if ( !$controller->filterNotAuthModule( $module ) ) {
            $routes = strtolower( $controller->getUniqueId() . '/' . $action->getId() );
            // step2
            if ( !$controller->filterRoutes( $routes ) ) {
                // step3
                if ( !IBOS::app()->user->checkAccess( $routes, Auth::getParams( $routes ) ) ) {
                    // 没有权限 抛出错误
                    if ( isset( $this->rbacErrorPage ) ) {
                        // 定义权限错误页面
                        $controller->redirect( $this->rbacErrorPage );
                    } else {
                        $controller->error( IBOS::lang( 'Valid access', 'error' ), '', array( 'autoJump' => 0 ) );
                    }
                }
            }
        }
        return true;
    }

    /**
     * 当模块完成配置时发起一个事件
     * @param CEvent $event 事件参数
     * @return void 
     */
    public function onInitModule( $event ) {
        $this->raiseEvent( 'onInitModule', $event );
    }

    /**
     * 当更新缓存时发起一个事件
     * @param CEvent $event 事件参数
     * @return void
     */
    public function onUpdateCache( $event ) {
        $this->raiseEvent( 'onUpdateCache', $event );
    }

    /**
     * 返回可用的模块数组
     * @return array
     */
    public function getEnabledModule() {
        return (array) $this->_enabledModule;
    }

}
